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
use App\Models\ProduktFiltrTmp;


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



    // pojebie mnie z tą funkcją , jak coś to łączy parę na raz, 
    // żeby oszczędzić użytkownikowi 3 sekundy xd
    //ogólnie do automatycznie dodaje wpisy z obecnego miesiąca do 
    // filtra czyli tabelki tymczasowej
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

    // tylko jeśli bufor nie był wyczyszczony i nie ma wpisów w tym miesiącu
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

            // Dodatkowo usuń dane filtra tymczasowego dla użytkownika i regionu
            DB::table('produkty_filtr_tmp')
                ->where('user_id', $userId)
                ->where('region_id', $regionId)
                ->delete();
        });

        return redirect()->route('spisy.produkty', $spis->id)
            ->with('success', 'Spis został wyczyszczony, ilości przywrócone, a dane filtra tymczasowego usunięte.');
    }

// jeden z nielicznych kolosów Sylwestra Wardenki na Bakstona
// ogólnie to pobiera sobie rekordy z tabeli filtra tymczasowego
// i dla każdego z nich osobno robi FIFO w tabeli skany produktów
// FIFO działa na zasadzie najstarszy skanowany produkt jest brany jako pierwszy
// i z niego jest odejmowana ilość, jeśli ilość z tego skanu nie wystarcza
// to bierze się kolejny najstarszy skan i tak dalej aż do wyczerpania potrzebnej ilości
// jeśli nie uda się przydzielić pełnej ilości to jest komunikat w sesji
// i jest to robione w transakcji żeby nie było problemów z równoczesnym dostępem
// i żeby zawsze ilości się zgadzały
// jeśli coś się sypnie to loguje błąd do logów
// na końcu zwraca komunikat ile rekordów tymczasowych zostało dodanych
// oraz ewentualne ostrzeżenia o brakach
// ogólnie to pojebało mnie to wymyślająć ale działa i oszczędza w .... czasu użytkownikowi
public function addProdukty(Request $request, SpisZNatury $spis)
{
    Log::info('--- START addProdukty ---', [
        'spis_id' => $spis->id,
        'region'  => $spis->region_id,
        'request' => $request->all(),
    ]);

    // 1) pobieramy dane z tabeli produkty_filtr_tmp 
    //   dla danego użytkownika i regionu
    //  z opcjonalnym filtrem dat
    // jeśli nie ma rekordów to zwracamy błąd
    $filteredQuery = ProduktFiltrTmp::with('product.unit')
        ->where('region_id', $spis->region_id)
        ->where('user_id', auth()->id());

    if ($request->filled('date_from')) {
        $filteredQuery->whereDate('scanned_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $filteredQuery->whereDate('scanned_at', '<=', $request->date_to);
    }

    $filteredScans = $filteredQuery->get();

    Log::info('Ilość rekordów w przefiltrowanej tabeli (produkty_filtr_tmp)', [
        'count' => $filteredScans->count()
    ]);

    if ($filteredScans->isEmpty()) {
        Log::warning('Brak produktów w tabeli po filtrze (produkty_filtr_tmp)');
        return back()->with('error', 'Brak produktów w wybranym zakresie dat.');
    }

    // 2) bierzemy dokładnie wartości z tabeli produkty_filtr_tmp 
    //  które mają ilość > 0
    //  i tworzymy z nich tablicę potrzebnych rekordów
    // z sumowaniem ilości dla tych samych produktów
    // żeby nie robić FIFO dla każdego skanu osobno
    // tylko dla każdego produktu z sumą ilości
    $neededRecords = [];
    foreach ($filteredScans as $scan) {
        if ($scan->quantity > 0) {
            $neededRecords[] = [
                'product_id' => $scan->product_id,
                'quantity'   => round($scan->quantity, 2),
                'name'       => $scan->name,
                'price'      => $scan->price,
            ];
        }
    }

    Log::info('Potrzebne rekordy (produkty_filtr_tmp)', $neededRecords);

    if (empty($neededRecords)) {
        Log::warning('Brak ilości do dodania (produkty_filtr_tmp)');
        return back()->with('error', 'Brak ilości do dodania.');
    }

    $createdCount = 0;

    // 3) FIFO dla każdego rekordu osobno
    //  w transakcji
    //  z blokadą dla skanów produktów
    //  żeby nie było problemów z równoczesnym dostępem
    foreach ($neededRecords as $record) {
        $productId   = $record['product_id'];
        $totalNeeded = $record['quantity'];

        try {
            DB::transaction(function () use ($productId, $totalNeeded, $spis, &$createdCount) {
                Log::info("→ START FIFO dla produktu {$productId}", [
                    'needed_total' => $totalNeeded
                ]);

                $allScans = ProduktSkany::with('product.unit')
                    ->where('region_id', $spis->region_id)
                    ->where('product_id', $productId)
                    ->whereRaw('(COALESCE(quantity,0) - COALESCE(used_quantity,0)) > 0')
                    ->orderBy('scanned_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                Log::info("FIFO skany dla produktu {$productId}", [
                    'scans_count' => $allScans->count()
                ]);

                $remaining = round($totalNeeded, 2);

                foreach ($allScans as $scan) {
                    if ($remaining <= 0) break;

                    $available = round((float)$scan->quantity - (float)($scan->used_quantity ?? 0), 2);
                    if ($available <= 0) {
                        Log::debug("Scan {$scan->id} ma 0 dostępne");
                        continue;
                    }

                    $take = round(min($available, $remaining), 2);

                    Log::info("→ Scan {$scan->id}: available={$available}, take={$take}, remaining_before={$remaining}");

                    // pobierz cenę historyczną obowiązującą w momencie skanu
                    $price = DB::table('product_prices_history')
                        ->where('product_id', $productId)
                        ->where('changed_at', '<=', $scan->scanned_at)
                        ->orderBy('changed_at', 'desc')
                        ->value('price');

                    if (is_null($price)) {
                        $price = $scan->product->price ?? 0; // fallback gdy brak historii
                    }

                    // zapis TMP rekordu
                    $tmp = SpisProduktyTmp::create([
                        'spis_id'          => $spis->id,
                        'user_id'          => auth()->id(),
                        'product_id'       => $productId,
                        'region_id'        => $spis->region_id,
                        'produkt_skany_id' => $scan->id,
                        'name'             => $scan->product->name ?? 'Brak nazwy',
                        'price'            => $price ?? 0,
                        'quantity'         => $take,
                        'unit'             => optional($scan->product->unit)->name ?? '-',
                        'barcode'          => $scan->barcode,
                        'scanned_at'       => $scan->scanned_at,
                        'added_at'         => now(),
                    ]);

                    Log::info("Dodano TMP rekord", $tmp->toArray());
                    $createdCount++;

                    // aktualizacja użytych ilości
                    $scan->used_quantity = round((float)($scan->used_quantity ?? 0) + $take, 2);
                    $scan->save();

                    Log::info("Zaktualizowano used_quantity dla scan {$scan->id}", [
                        'used_quantity' => $scan->used_quantity
                    ]);

                    $remaining = round($remaining - $take, 2);

                    Log::info("Pozostało do przydzielenia dla produktu {$productId}", [
                        'remaining' => $remaining
                    ]);
                }

                if ($remaining > 0) {
                    $productName = $allScans->first()->product->name ?? "ID {$productId}";
                    Log::warning("Brakuje {$remaining} dla produktu {$productName}");
                    session()->flash('warning',
                        "Nie udało się przydzielić pełnej ilości dla produktu '{$productName}'. Brakuje {$remaining} szt.");
                }

                Log::info("→ END FIFO dla produktu {$productId}");
            }, 5);
        } catch (\Throwable $e) {
            Log::error("Błąd podczas addProdukty transaction", [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
        }
    }

    Log::info('--- END addProdukty ---', ['created_tmp' => $createdCount]);

    return back()->with('success',
        "Produkty dodane do tabeli tymczasowej według FIFO. Dodano {$createdCount} rekordów.");
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

    // Reset flagi "wyczyszczono"
    session()->forget('filter_cleared');

    // wyczyść poprzedni bufor użytkownika dla tego regionu
    DB::table('produkty_filtr_tmp')
        ->where('user_id', $userId)
        ->where('region_id', $regionId)
        ->delete();

    // pobierz dane po filtrze
    $query = ProduktSkany::with('product.unit')
        ->where('region_id', $regionId);

    if ($request->filled('date_from')) {
        $query->whereDate('scanned_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('scanned_at', '<=', $request->date_to);
    }

    $filtered = $query->get();

    // zapisz do bufora tymczasowego z sumowaniem po nazwie i cenie
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


// odkurzacz 
public function clearTemp(SpisZNatury $spis)
{
    $userId = auth()->id();
    $regionId = $spis->region_id;

    DB::table('produkty_filtr_tmp')
        ->where('user_id', $userId)
        ->where('region_id', $regionId)
        ->delete();

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
        $produktyTmp = SpisProduktyTmp::where('spis_id', $spis->id)
            ->where('user_id', $userId)
            ->get();

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

        SpisProduktyTmp::where('spis_id', $spis->id)
            ->where('user_id', $userId)
            ->delete();

        DB::table('produkty_filtr_tmp')
            ->where('user_id', $userId)
            ->where('region_id', $regionId)
            ->delete();
    });

    return redirect()->route('spisy.podsumowanie', $spis->id)
        ->with('success', 'Twoje produkty zostały przeniesione do spisu głównego, a dane tymczasowe usunięte.');
}
}



