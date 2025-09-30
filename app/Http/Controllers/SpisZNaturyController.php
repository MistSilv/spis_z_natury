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

    public function showProdukty(SpisZNatury $spis, Request $request)
    {
        // Produkty zeskanowane
        $produkty = ProduktSkany::with(['product.unit'])
            ->where('region_id', $spis->region_id);

        if ($request->filled('date_from')) {
            $produkty->whereDate('scanned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $produkty->whereDate('scanned_at', '<=', $request->date_to);
        }

        $produkty = $produkty->orderBy('scanned_at', 'desc')
            ->paginate(50)
            ->appends($request->all());

        // Produkty tymczasowe (do edycji)
        $produktySpisu = SpisProduktyTmp::with('user')
            ->where('spis_id', $spis->id)
            ->orderBy('added_at', 'asc')
            ->paginate(50, ['*'], 'produktyTmpPage');

        return view('spisy.produkty', compact('spis', 'produkty', 'produktySpisu'));
    }




















































public function addProdukty(Request $request, SpisZNatury $spis)
{
    Log::info('--- START addProdukty ---', [
        'spis_id' => $spis->id,
        'region'  => $spis->region_id,
        'request' => $request->all(),
    ]);

    // 1) produkty widoczne dla użytkownika (filtr dat)
    $filteredQuery = ProduktSkany::with('product.unit')
        ->where('region_id', $spis->region_id);

    if ($request->filled('date_from')) {
        $filteredQuery->whereDate('scanned_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $filteredQuery->whereDate('scanned_at', '<=', $request->date_to);
    }

    $filteredScans = $filteredQuery->get();

    Log::info('Ilość rekordów w przefiltrowanej tabeli', [
        'count' => $filteredScans->count()
    ]);

    if ($filteredScans->isEmpty()) {
        Log::warning('Brak produktów w tabeli po filtrze');
        return back()->with('error', 'Brak produktów w wybranym zakresie dat.');
    }

    // 2) sumujemy potrzeby wg produktu jako float (np. 2 miejsca po przecinku)
    $neededQuantities = [];
    foreach ($filteredScans->groupBy('product_id') as $productId => $scans) {
        $total = round($scans->sum('quantity'), 2);
        if ($total > 0) {
            $neededQuantities[$productId] = $total;
        }
    }

    Log::info('Potrzebne ilości (z tabeli po filtrze)', $neededQuantities);

    if (empty($neededQuantities)) {
        Log::warning('Brak ilości do dodania');
        return back()->with('error', 'Brak ilości do dodania.');
    }

    $createdCount = 0;

    // 3) przetwarzamy każdy produkt w osobnej transakcji (FIFO)
    foreach ($neededQuantities as $productId => $totalNeeded) {
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

                    // zapis TMP rekordu
                    $tmp = SpisProduktyTmp::create([
                        'spis_id'    => $spis->id,
                        'user_id'    => auth()->id(),
                        'product_id' => $productId,
                        'region_id'  => $spis->region_id,
                        'name'       => $scan->product->name ?? 'Brak nazwy',
                        'price'      => $scan->product->price ?? 0,
                        'quantity'   => $take,
                        'unit'       => optional($scan->product->unit)->name ?? '-',
                        'barcode'    => $scan->barcode,
                        'scanned_at' => $scan->scanned_at,
                        'added_at'   => now(),
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
    DB::transaction(function () use ($spis) {
        $produktyTmp = SpisProduktyTmp::where('spis_id', $spis->id)->get();

        foreach ($produktyTmp as $tmp) {
            SpisProdukty::create([
                'spis_id'    => $spis->id,
                'user_id'    => $tmp->user_id,
                'name'       => $tmp->name,
                'price'      => $tmp->price,
                'quantity'   => $tmp->quantity,
                'unit'       => $tmp->unit,
                'barcode'    => $tmp->barcode,
                'added_at'   => $tmp->added_at,
            ]);
        }

        SpisProduktyTmp::where('spis_id', $spis->id)->delete();
    });

    return redirect()->route('spisy.podsumowanie', $spis->id)
        ->with('success', 'Produkty zostały przeniesione do spisu głównego i zapisane.');
}
}



