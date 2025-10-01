<?php

namespace App\Http\Controllers;

use App\Models\SpisZNatury; 
use App\Models\SpisProduktyTmp;
use App\Models\ProduktFiltrTmp;
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
        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $produkt->update([
            'price' => $request->price,
        ]);

        return back()->with('success', "Cena produktu '{$produkt->name}' została zaktualizowana.");
    }


 public function updateQuantity(Request $request, SpisZNatury $spis, ProduktFiltrTmp $produkt)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0',
        ]);

        $produkt->update([
            'quantity' => $request->quantity,
        ]);

        return back()->with('success', "Ilość produktu '{$produkt->name}' została zaktualizowana.");
    }

    public function destroyFromFilter(SpisZNatury $spis, ProduktFiltrTmp $produkt)
    {
        $produkt->delete();

        return back()->with('success', "Produkt '{$produkt->name}' został usunięty z filtrów.");
    }








}
