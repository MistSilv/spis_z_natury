<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;   
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SpisZNatury;
use App\Models\Region;
use App\Models\ProduktSkany;
use App\Models\SpisProdukty;
use App\Models\SpisProduktyTmp;


class SpisZNaturyController extends Controller
{
    public function index()
    {
        $spisy = SpisZNatury::with(['user', 'region'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('spisy.index', compact('spisy'));
    }

    public function create(Request $request)
    {
        $regions = Region::all();
        $selectedRegion = $request->region_id ?? null;

        return view('spisy.create', compact('regions', 'selectedRegion'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $spis = SpisZNatury::create([
            'user_id' => auth()->id(),
            'region_id' => $request->region_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('spisy.produkty', $spis->id)
            ->with('success', 'Spis utworzony. Poniżej produkty dla wybranego regionu.');
    }



    //pojebie mnie z tą funkcją , jak coś to łączy parę na raz, żeby oszczędzić użytkownikowi 3 sekundy xd
    public function showProdukty(SpisZNatury $spis, Request $request)
{
    $userId = auth()->id();
    $regionId = $spis->region_id;

    $startOfMonth = now()->startOfMonth();
    $endOfMonth = now()->endOfMonth();

    // Sprawdź, czy użytkownik wcześniej wyczyścił bufor
    $filterCleared = session('filter_cleared', false);

    $hasCurrentMonth = DB::table('produkty_filtr_tmp')
        ->where('user_id', $userId)
        ->where('region_id', $regionId)
        ->whereBetween('scanned_at', [$startOfMonth, $endOfMonth])
        ->exists();

    // 🔹 tylko jeśli bufor nie był wyczyszczony i nie ma wpisów w tym miesiącu
    if (!$filterCleared && !$hasCurrentMonth) {
        $scans = ProduktSkany::with('product.unit')
            ->where('region_id', $regionId)
            ->whereBetween('scanned_at', [$startOfMonth, $endOfMonth])
            ->get();

        foreach ($scans as $scan) {
            $name = $scan->product->name ?? 'Brak nazwy';
            $price = $scan->price_history ?? 0;
            $quantity = round($scan->quantity, 2);
            $unit = optional($scan->product->unit)->name ?? '-';

            $existing = DB::table('produkty_filtr_tmp')
                ->where('user_id', $userId)
                ->where('region_id', $regionId)
                ->where('name', $name)
                ->where('price', $price)
                ->first();

            if ($existing) {
                DB::table('produkty_filtr_tmp')
                    ->where('id', $existing->id)
                    ->update([
                        'quantity' => $existing->quantity + $quantity,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('produkty_filtr_tmp')->insert([
                    'user_id' => $userId,
                    'region_id' => $regionId,
                    'product_id' => $scan->product_id,
                    'produkt_skany_id' => $scan->id,
                    'name' => $name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'barcode' => $scan->barcode,
                    'scanned_at' => $scan->scanned_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    $produkty = DB::table('produkty_filtr_tmp')
        ->where('user_id', $userId)
        ->where('region_id', $regionId)
        ->orderBy('name')
        ->paginate(50);

    $produktySpisu = SpisProduktyTmp::with('user')
        ->where('spis_id', $spis->id)
        ->orderBy('added_at', 'asc')
        ->paginate(50, ['*'], 'produktyTmpPage');

    return view('spisy.produkty', compact('spis', 'produkty', 'produktySpisu'));
}







     // idioto odporna funckja w trakcie kraftowania spisu 
   public function reset(SpisZNatury $spis)
    {
        $userId = auth()->id();
        $regionId = $spis->region_id;

        DB::transaction(function () use ($spis, $userId, $regionId) {
            // Pobierz wszystkie TMP produkty z tego spisu
            $produktyTmp = SpisProduktyTmp::where('spis_id', $spis->id)->get();

            foreach ($produktyTmp as $tmp) {
                if ($tmp->produkt_skany_id) {
                    // Cofnij zużycie w produkt_skany
                    ProduktSkany::where('id', $tmp->produkt_skany_id)
                        ->update([
                            'used_quantity' => DB::raw("GREATEST(0, used_quantity - {$tmp->quantity})")
                        ]);
                }
            }

            // Usuń wpisy tymczasowe dla spisu
            SpisProduktyTmp::where('spis_id', $spis->id)->delete();

            // 🔹 Dodatkowo usuń dane filtra tymczasowego dla użytkownika i regionu
            DB::table('produkty_filtr_tmp')
                ->where('user_id', $userId)
                ->where('region_id', $regionId)
                ->delete();
        });

        return redirect()->route('spisy.produkty', $spis->id)
            ->with('success', 'Spis został wyczyszczony, ilości przywrócone, a dane filtra tymczasowego usunięte.');
    }





















































public function addProdukty(Request $request, SpisZNatury $spis)
{
    $userId = auth()->id();
    $regionId = $spis->region_id;

    $filteredScans = DB::table('produkty_filtr_tmp')
        ->where('user_id', $userId)
        ->where('region_id', $regionId)
        ->get();

    if ($filteredScans->isEmpty()) {
        return back()->with('error', 'Brak zapisanych produktów z filtra. Użyj najpierw opcji "Filtruj".');
    }

    // grupowanie po product_id
    $neededQuantities = [];
    foreach ($filteredScans->groupBy('product_id') as $productId => $items) {
        $neededQuantities[$productId] = round($items->sum('quantity'), 2);
    }

    $addedCount = 0;

    foreach ($neededQuantities as $productId => $neededQty) {
        // 📦 znajdź wszystkie skany tego produktu (FIFO)
        $scans = ProduktSkany::where('product_id', $productId)
            ->where('region_id', $regionId)
            ->where('quantity', '>', 0)
            ->orderBy('scanned_at', 'asc') // najstarsze pierwsze
            ->orderBy('id', 'asc') // a w razie identycznych dat — po ID
            ->get();

        $remaining = $neededQty;

        foreach ($scans as $scan) {
            if ($remaining <= 0) break;

            $take = min($scan->quantity, $remaining);

            // 🧾 dodaj do spisu tymczasowego
            SpisProduktyTmp::create([
                'spis_id'          => $spis->id,
                'user_id'          => $userId,
                'product_id'       => $productId,
                'region_id'        => $regionId,
                'produkt_skany_id' => $scan->id,
                'name'             => $scan->product->name ?? 'Brak nazwy',
                'price'            => $scan->price_history ?? 0,
                'quantity'         => $take,
                'unit'             => optional($scan->product->unit)->name ?? '-',
                'barcode'          => $scan->barcode,
                'scanned_at'       => $scan->scanned_at,
                'added_at'         => now(),
            ]);

            // 🔄 zmniejsz dostępne ilości
            $scan->decrement('quantity', $take);
            $remaining -= $take;
            $addedCount++;
        }

        if ($remaining > 0) {
            Log::warning("Nie wystarczyło produktu ID {$productId} do pełnego dodania ({$remaining} brakujących jednostek)");
        }
    }

    return back()->with('success', "Dodano {$addedCount} pozycji do spisu (FIFO).");
}







//wolny kurdystan duplikatowy
public function filterProdukty(Request $request, SpisZNatury $spis)
{
    $request->validate([
        'date_from' => 'nullable|date',
        'date_to'   => 'nullable|date',
    ]);

    $userId = auth()->id();
    $regionId = $spis->region_id;

    // 🔹 Reset flagi "wyczyszczono"
    session()->forget('filter_cleared');

    // 🧹 wyczyść poprzedni bufor użytkownika dla tego regionu
    DB::table('produkty_filtr_tmp')
        ->where('user_id', $userId)
        ->where('region_id', $regionId)
        ->delete();

    // 🔎 pobierz dane po filtrze
    $query = ProduktSkany::with('product.unit')
        ->where('region_id', $regionId);

    if ($request->filled('date_from')) {
        $query->whereDate('scanned_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('scanned_at', '<=', $request->date_to);
    }

    $filtered = $query->get();

    // 💾 zapisz do bufora tymczasowego z sumowaniem po nazwie i cenie
    foreach ($filtered as $scan) {
        $name = $scan->product->name ?? 'Brak nazwy';
        $price = $scan->price_history ?? 0;
        $quantity = round($scan->quantity, 2);
        $unit = optional($scan->product->unit)->name ?? '-';

        $existing = DB::table('produkty_filtr_tmp')
            ->where('user_id', $userId)
            ->where('region_id', $regionId)
            ->where('name', $name)
            ->where('price', $price)
            ->first();

        if ($existing) {
            DB::table('produkty_filtr_tmp')
                ->where('id', $existing->id)
                ->update([
                    'quantity' => $existing->quantity + $quantity,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('produkty_filtr_tmp')->insert([
                'user_id' => $userId,
                'region_id' => $regionId,
                'product_id' => $scan->product_id,
                'produkt_skany_id' => $scan->id,
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity,
                'unit' => $unit,
                'barcode' => $scan->barcode,
                'scanned_at' => $scan->scanned_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    return redirect()->route('spisy.produkty', $spis->id)
        ->with('success', "Zapisano {$filtered->count()} rekordów (po zsumowaniu duplikatów wg nazwy i ceny) do bufora filtra.");
}





//filtracja dany delete 
public function clearTemp(SpisZNatury $spis)
{
    $userId = auth()->id();
    $regionId = $spis->region_id;

    DB::table('produkty_filtr_tmp')
        ->where('user_id', $userId)
        ->where('region_id', $regionId)
        ->delete();

         // 🔹 Flaga w sesji: użytkownik wyczyścił bufor
    session()->flash('filter_cleared', true);

    return back()->with('success', 'Bufor tymczasowy został wyczyszczony.');
}


































































    public function showTmpProdukty(SpisZNatury $spis)
    {
        $produktyTmp = SpisProduktyTmp::where('spis_id', $spis->id)
            ->orderBy('added_at', 'asc')
            ->get();

        return view('spisy.produkty_tmp', compact('spis', 'produktyTmp'));
    }

    public function showSpisProdukty(SpisZNatury $spis)
    {
        $produktySpisu = SpisProdukty::where('spis_id', $spis->id)
            ->orderBy('added_at', 'desc')
            ->paginate(50);

        return view('spisy.spis_produkty', compact('spis', 'produktySpisu'));
    }

    public function podsumowanieSpisu(SpisZNatury $spis)
    {
        $produktySpisu = SpisProdukty::where('spis_id', $spis->id)
            ->orderBy('added_at', 'desc')
            ->paginate(50);

        $allProdukty = SpisProdukty::where('spis_id', $spis->id)->get();
        $totalValue = $allProdukty->sum(function ($p) {
            return $p->price * $p->quantity;
        });
        $totalItems = $allProdukty->count();

        return view('spisy.podsumowanie', compact('spis', 'produktySpisu', 'totalValue', 'totalItems'));
    }



    public function archiwum(Request $request)
    {
        $query = SpisZNatury::with(['user', 'region'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $spisy = $query->paginate(20)->appends($request->all());
        $regions = Region::all();

        return view('spisy.archiwum', compact('spisy', 'regions'));
    }

    public function show(SpisZNatury $spis)
    {
        return redirect()->route('spisy.podsumowanie', $spis->id);
    }



    public function finalizeProdukty(SpisZNatury $spis)
{
    $userId = auth()->id();
    $regionId = $spis->region_id;

    DB::transaction(function () use ($spis, $userId, $regionId) {
        // ✅ Pobierz produkty tymczasowe tylko tego użytkownika
        $produktyTmp = SpisProduktyTmp::where('spis_id', $spis->id)
            ->where('user_id', $userId)
            ->get();

        // 💾 Przenieś do spisu głównego
        foreach ($produktyTmp as $tmp) {
            SpisProdukty::create([
                'spis_id'  => $spis->id,
                'user_id'  => $tmp->user_id,
                'name'     => $tmp->name,
                'price'    => $tmp->price,
                'quantity' => $tmp->quantity,
                'unit'     => $tmp->unit,
                'barcode'  => $tmp->barcode,
                'added_at' => $tmp->added_at,
            ]);
        }

        // 🧹 Usuń tymczasowe dane użytkownika z tabeli spis_produkty_tmp
        SpisProduktyTmp::where('spis_id', $spis->id)
            ->where('user_id', $userId)
            ->delete();

        // 🧹 Usuń także dane z bufora filtrów (produkty_filtr_tmp)
        DB::table('produkty_filtr_tmp')
            ->where('user_id', $userId)
            ->where('region_id', $regionId)
            ->delete();
    });

    return redirect()->route('spisy.podsumowanie', $spis->id)
        ->with('success', 'Twoje produkty zostały przeniesione do spisu głównego, a dane tymczasowe usunięte.');
}









}



