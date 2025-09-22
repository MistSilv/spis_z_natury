<?php

namespace App\Http\Controllers;

use App\Models\SpisZNatury;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Models\ProduktSkany;
use App\Models\SpisProdukty; // 👈 zostajemy przy SpisProdukty

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
        // produkty ze skanów
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

        // produkty już dodane do spisu
        $produktySpisu = SpisProdukty::with('user')
            ->where('spis_id', $spis->id)
            ->orderBy('added_at', 'desc')
            ->paginate(50, ['*'], 'produktySpisuPage');

        return view('spisy.produkty', compact('spis', 'produkty', 'produktySpisu'));
    }

    public function addProdukty(Request $request, SpisZNatury $spis)
    {
        $produkty = ProduktSkany::with(['product.unit'])
            ->where('region_id', $spis->region_id);

        if ($request->filled('date_from')) {
            $produkty->whereDate('scanned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $produkty->whereDate('scanned_at', '<=', $request->date_to);
        }

        $produkty = $produkty->get();

        foreach ($produkty as $produkt) {
            SpisProdukty::create([
                'spis_id'   => $spis->id,
                'user_id'   => auth()->id(),
                'name'      => $produkt->product->name ?? 'Brak nazwy',
                'price'     => $produkt->product->price ?? 0,
                'quantity'  => $produkt->quantity ?? 1,
                'unit'      => $produkt->product->unit->name ?? '-',
                'barcode'   => $produkt->barcode,
                'added_at'  => now(),
            ]);
        }

        return redirect()->route('spisy.produkty', [
            'spis' => $spis->id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ])->with('success', 'Produkty zostały dodane do spisu.');
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

    $totalValue = $allProdukty->sum(fn($p) => $p->price * $p->quantity);
    $totalItems = $allProdukty->count();

    return view('spisy.podsumowanie', compact('spis', 'produktySpisu', 'totalValue', 'totalItems'));
}

public function updateProduktSpisu(Request $request, SpisZNatury $spis, SpisProdukty $produkt)
{
    $request->validate([
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|numeric|min:0',
    ]);

    $produkt->update([
        'price' => $request->price,
        'quantity' => $request->quantity,
    ]);

    return redirect()->route('spisy.podsumowanie', $spis->id)
                     ->with('success', 'Produkt zaktualizowany pomyślnie.');
}

public function deleteProduktSpisu(SpisZNatury $spis, SpisProdukty $produkt)
{
    $produkt->delete();
    
    return redirect()->route('spisy.podsumowanie', $spis->id)
                     ->with('success', 'Produkt usunięty pomyślnie.');
}


public function archiwum()
{
    $spisy = SpisZNatury::with(['user', 'region'])
        ->orderBy('created_at', 'desc')
        ->paginate(20); 
    return view('spisy.archiwum', compact('spisy'));
}


public function show(SpisZNatury $spis)
{
    return redirect()->route('spisy.podsumowanie', $spis->id);
}




}
