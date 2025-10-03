<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barcode;
use App\Models\ProduktSkany;
use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\Unit;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BarcodeController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        $ean = $request->barcode;

        // 1. Sprawdź lokalnie
        $barcode = Barcode::where('barcode', $ean)->first();

        if ($barcode) {
            $product = $barcode->product;
            $latestPrice = $product->latestPrice;

            // Sprawdź, czy minął 1 dzień od updated_at produktu
            if (Carbon::parse($product->updated_at)->lt(now()->subDay())) {
                $url = "http://192.168.210.219/automaty/wyszukaj_towar.php?search=" . urlencode($ean);

                try {
                    $response = Http::timeout(5)->get($url);

                    if ($response->ok()) {
                        $data = $response->json();
                        if (!empty($data)) {

                            //$manualPrice = 123.45;
                            $produktSklep = $data[0];
                            $newPrice = $manualPrice ?? (!empty($produktSklep['cena_jednostkowa'])
                                ? $produktSklep['cena_jednostkowa']
                                : $latestPrice?->price);

                            // Jeśli cena się zmieniła – dodaj nową do historii
                            if ($newPrice != $latestPrice?->price) {
                                $product->prices()->create([
                                    'price' => $newPrice,
                                    'changed_at' => now(),
                                ]);
                                $latestPrice = $product->latestPrice; // odśwież
                            }
                        }
                    }
                     $product->touch();
                } catch (\Exception $e) {
                    // API error ignored
                }

                // Aktualizacja updated_at tylko jeśli API zostało uruchomione
               
            }

            return response()->json([
                'product' => [
                    'id'      => $product->id,
                    'name'    => $product->name,
                    'price'   => $latestPrice?->price ?? 0,
                    'unit'    => $product->unit->code ?? '',
                    'barcode' => $barcode->barcode,
                ],
            ]);
        }

        // 2. Produkt nie znaleziony lokalnie -> sprawdz w API
        $url = "http://192.168.210.219/automaty/wyszukaj_towar.php?search=" . urlencode($ean);

        try {
            $response = Http::timeout(5)->get($url);

            if ($response->failed()) {
                return response()->json([
                    'message' => 'Błąd połączenia z systemem sklepowym'
                ], 500);
            }

            $data = $response->json();

            if (empty($data)) {
                return response()->json([
                    'message' => 'Produkt nie znaleziony ani lokalnie, ani w systemie sklepowym',
                ], 404);
            }

            $produktSklep = $data[0];

            // Cena domyślna jeśli brak w API
            $price = !empty($produktSklep['cena_jednostkowa']) ? $produktSklep['cena_jednostkowa'] : 69.69;

            // Utwórz jednostkę jeśli brak
            $unit = Unit::firstOrCreate(['code' => $produktSklep['jm']], [
                'name' => $produktSklep['jm']
            ]);

            // Utwórz produkt
            $product = Product::create([
                'name'     => $produktSklep['nazwa_towaru'],
                'unit_id'  => $unit->id,
                'id_abaco' => $produktSklep['idabaco'] ?? null,
            ]);

            // Zapisz cenę w historii
            $product->prices()->create([
                'price'      => $price,
                'changed_at' => now(),
            ]);

            // Dodaj wszystkie kody kreskowe
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
                    'price'   => $price,
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
        $user = Auth::user();

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

        $product = Product::findOrFail($data['product_id']);
        
        $scanTimestamp = now();



        $priceAtScan = $product->priceAt($scanTimestamp);


        $scan = ProduktSkany::create([
            'product_id'    => $data['product_id'],
            'user_id'       => $user->id,
            'region_id'     => $user->region_id ?? 1,
            'price_history' => $priceAtScan,
            'quantity'      => $data['quantity'],
            'barcode'       => $data['barcode'] ?? null,
            'scanned_at'    => $scanTimestamp,
        ]);


        return response()->json(['scan' => $scan]);
    }

}
