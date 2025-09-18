<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barcode;
use App\Models\ProduktSkany; // Twój model dla skanów

class BarcodeController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        $barcode = Barcode::where('barcode', $request->barcode)->first();

        if (!$barcode) {
            return response()->json([
                'message' => 'Produkt nie znaleziony',
            ], 404);
        }

        $product = $barcode->product;

        return response()->json([
            'product' => [
                'id'      => $product->id,
                'name'    => $product->name,
                'price'   => $product->price,
                'unit'    => $product->unit->code ?? '',
                'barcode' => $barcode->barcode,
            ],
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'required|numeric|min:1',
            'barcode'    => 'nullable|string|max:13',
        ]);

        $scan = ProduktSkany::create([
            'product_id' => $data['product_id'],
            'user_id'    => auth()->id() ?? 1,  // lub dostosuj według potrzeb
            'region_id'  => 1,                  // lub pobierz dynamicznie
            'quantity'   => $data['quantity'],
            'barcode'    => $data['barcode'] ?? null,
        ]);

        return response()->json(['scan' => $scan]);
    }
}
