<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barcode;
use App\Models\ProduktSkany;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Auth;

class BarcodeController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        // 1. Najpierw sprawdzamy lokalnie
        $barcode = Barcode::where('barcode', $request->barcode)->first();

        if ($barcode) {
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

        // 2. Jeśli nie ma lokalnie -> sprawdzamy w API
        $ean = $request->barcode;
        $url = "http://192.168.210.219/automaty/wyszukaj_towar.php?search=" . urlencode($ean);

        try {
            $response = Http::timeout(5)->get($url);

            if ($response->failed()) {
                return response()->json([
                    'message' => 'Błąd połączenia z systemem sklepowym'
                ], 500);
            }

            $data = $response->json();
            Log::info('📦 Odpowiedź z API', ['data' => $data]);

            if (empty($data)) {
                return response()->json([
                    'message' => 'Produkt nie znaleziony ani lokalnie, ani w systemie sklepowym',
                ], 404);
            }

            $produktSklep = $data[0];

            // Obsłuż cenę – jeśli brak lub null, ustaw 0.99
            $price = !empty($produktSklep['cena_jednostkowa']) ? $produktSklep['cena_jednostkowa'] : 69.69;

            // 3. Utwórz produkt w bazie
            $unit = Unit::firstOrCreate(['code' => $produktSklep['jm']], [
                'name' => $produktSklep['jm']
            ]);

            $product = Product::create([
                'name'     => $produktSklep['nazwa_towaru'],
                'price'    => $price,
                'unit_id'  => $unit->id,
                'id_abaco' => $produktSklep['idabaco'] ?? null,
            ]);

            // 4. Dodaj wszystkie kody kreskowe
            $barcodes = $produktSklep['kody_plu'] ?? [$ean];
            foreach ($barcodes as $bc) {
                if ($bc) {
                    Barcode::firstOrCreate([
                        'product_id' => $product->id,
                        'barcode'    => $bc,
                    ]);
                }
            }

            return response()->json([
                'product' => [
                    'id'      => $product->id,
                    'name'    => $product->name,
                    'price'   => $product->price,
                    'unit'    => $unit->code,
                    'barcode' => $ean,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Błąd systemowy: ' . $e->getMessage()
            ], 500);
        }
    }


    public function save(Request $request)
    {
        $user = Auth::user(); // pobieramy aktualnie zalogowanego użytkownika

        if (!$user) {
            return response()->json([
                'message' => 'Brak zalogowanego użytkownika.'
            ], 403);
        }

        $data = $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'required|numeric|min:1',
            'barcode'    => 'nullable|string|max:13',
        ]);

        $scan = ProduktSkany::create([
            'product_id' => $data['product_id'],
            'user_id'    => $user->id,
            'region_id'  => $user->region_id ?? 1,
            'quantity'   => $data['quantity'],
            'barcode'    => $data['barcode'] ?? null,
            'scanned_at' => now(),
        ]);

        return response()->json(['scan' => $scan]);
    }
}
