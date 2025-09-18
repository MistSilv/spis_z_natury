<?php

namespace App\Http\Controllers;

use App\Models\SpisZNatury;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Models\ProduktSkany;

class SpisZNaturyController extends Controller
{
    // Wyświetla listę spisów
    public function index()
    {
        $spisy = SpisZNatury::with(['user', 'region'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('spisy.index', compact('spisy'));
    }

    // Formularz tworzenia spisu
    public function create(Request $request)
    {
        $regions = Region::all();
        $selectedRegion = $request->region_id ?? null;

        return view('spisy.create', compact('regions', 'selectedRegion'));
    }

    // Zapis spisu
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

   public function showProdukty(SpisZNatury $spis)
{
    $produkty = ProduktSkany::with(['product.unit'])
        ->where('region_id', $spis->region_id)
        ->orderBy('scanned_at', 'desc')
        ->get();

    return view('spisy.produkty', compact('spis', 'produkty'));
}

}
