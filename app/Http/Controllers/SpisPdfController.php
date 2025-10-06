<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpisZNatury;
use App\Models\SpisProdukty;

class SpisPdfController extends Controller
{
    public function show($spisId)
    {
        $spis = SpisZNatury::with('region')->findOrFail($spisId);

        $produkty = SpisProdukty::where('spis_id', $spisId)
            ->orderBy('id', 'asc')
            ->get();

        $products = $produkty
            ->groupBy('name')
            ->flatMap(fn($group) => $group)
            ->values()
            ->map(fn($item) => (object)[
                'name'        => $item->name,
                'ean'         => $item->barcode,
                'unit'        => $item->unit,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->price,
                'total_value' => $item->price * $item->quantity,
            ]);

        return view('spisy.podglad', [
            'spis'     => $spis,
            'products' => $products,
            'date'     => now()->format('d.m.Y'),
        ]);
    }
}
