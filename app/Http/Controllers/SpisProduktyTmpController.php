<?php

namespace App\Http\Controllers;

use App\Models\SpisZNatury; 
use App\Models\SpisProduktyTmp;
use Illuminate\Http\Request;

class SpisProduktyTmpController extends Controller
{
    public function index(SpisZNatury $spis)
    {
        $produktyTmp = SpisProduktyTmp::where('spis_id', $spis->id)
            ->orderBy('added_at', 'asc')
            ->get();

        return view('spisy.produkty', compact('spis', 'produktyTmp'));

    }

    public function update(Request $request, SpisZNatury $spis, SpisProduktyTmp $produkt)
    {
        $validated = $request->validate([
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
        ]);

        $produkt->update($validated);

        return back()->with('success', 'Zaktualizowano produkt tymczasowy.');
    }

    public function split(Request $request, SpisZNatury $spis, SpisProduktyTmp $produkt)
    {
        $request->validate([
            'split_quantity' => 'required|numeric|min:0.01',
        ]);

        $splitQty = $request->split_quantity;

        if ($splitQty >= $produkt->quantity) {
            return back()->with('error', 'Nie można wydzielić większej ilości niż dostępna.');
        }

        $produkt->quantity -= $splitQty;
        $produkt->save();

        SpisProduktyTmp::create([
            'spis_id' => $spis->id,
            'name' => $produkt->name,
            'price' => $produkt->price,
            'quantity' => $splitQty,
            'unit' => $produkt->unit,
            'barcode' => $produkt->barcode,
            'user_id' => $produkt->user_id,
            'added_at' => now(),
        ]);

        return back()->with('success', 'Podzielono produkt.');
    }

    public function destroy(SpisZNatury $spis, SpisProduktyTmp $produkt)
    {
        $produkt->delete();

        return back()->with('success', 'Usunięto produkt tymczasowy.');
    }
}
