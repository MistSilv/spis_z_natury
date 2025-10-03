<?php

namespace App\Http\Controllers;

use App\Models\SpisZNatury; 
use App\Models\SpisProduktyTmp;
use App\Models\ProduktFiltrTmp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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

    public function storeByEan(Request $request, SpisZNatury $spis)
    {
        $request->validate([
            'ean' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $userId = auth()->id();
        $regionId = $spis->region_id;

        $barcode = \App\Models\Barcode::with('product.unit')
            ->where('barcode', $request->ean)
            ->first();

        if (!$barcode) {
            return back()->with('error', "Nie znaleziono produktu o kodzie EAN: {$request->ean}");
        }

        $product = $barcode->product;

        $scan = \App\Models\ProduktSkany::where('product_id', $product->id)
            ->where('region_id', $regionId)
            ->orderByDesc('scanned_at')
            ->first();

        if (!$scan) {
            return back()->with('error', "Brak skanu dla produktu '{$product->name}' w regionie '{$spis->region->name}'.");
        }

        $name = $product->name ?? 'Brak nazwy';
        $price = $scan->price_history ?? 0;
        $quantity = round($request->quantity, 2);
        $unit = optional($product->unit)->name ?? '-';
        $barcodeValue = $scan->barcode ?? $request->ean;

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

            return back()->with('success', "Zaktualizowano ilość produktu '{$name}' (+{$quantity}).");
        }

        DB::table('produkty_filtr_tmp')->insert([
            'user_id' => $userId,
            'region_id' => $regionId,
            'product_id' => $product->id,
            'produkt_skany_id' => $scan->id,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'unit' => $unit,
            'barcode' => $barcodeValue,
            'scanned_at' => $scan->scanned_at,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Dodano produkt '{$name}' (EAN: {$request->ean}) w ilości {$quantity}.");
    }









}
