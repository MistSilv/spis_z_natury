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
        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $produkt->update([
            'price' => $request->price,
        ]);

        return back()->with('success', "Cena produktu '{$produkt->name}' została zaktualizowana.");
    }

}
