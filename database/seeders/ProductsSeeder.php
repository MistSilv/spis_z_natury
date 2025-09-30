<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Barcode;
use App\Models\ProductPriceHistory;

class ProductsSeeder extends Seeder
{
    public function run()
    {
        $units = Unit::pluck('id')->toArray();
        $csvFile = database_path('db/products.csv');

        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found at ' . $csvFile);
            return;
        }

        $products = [];

        if (($handle = fopen($csvFile, 'r')) !== false) {
            // Skip header
            fgetcsv($handle, 1000, ',');

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                [$barcode, $name, $price, $id_abaco] = $data;

                // Convert strings to UTF-8
                $name    = mb_convert_encoding($name, 'UTF-8', 'auto');
                $barcode = mb_convert_encoding($barcode, 'UTF-8', 'auto');

                // Replace comma with dot for decimal
                $price = floatval(str_replace(',', '.', $price));

                // Create or find product key
                $key = $name . '|' . $id_abaco;

                if (!isset($products[$key])) {
                    $products[$key] = [
                        'name'     => $name,
                        'id_abaco' => $id_abaco,
                        'unit_id'  => $units[array_rand($units)],
                        'price'    => $price,
                        'barcodes' => []
                    ];
                }

                if ($barcode) {
                    $products[$key]['barcodes'][] = $barcode;
                }
            }
            fclose($handle);
        }

        // Insert products, prices and barcodes
        foreach ($products as $data) {
            $product = Product::create([
                'name'       => $data['name'],
                'id_abaco'   => $data['id_abaco'],
                'unit_id'    => $data['unit_id'],
                'created_at' => '2019-01-01 00:00:00',
                'updated_at' => '2019-01-01 00:00:00',
            ]);

            // Dodaj cenę do historii z datą 2019
            ProductPriceHistory::create([
                'product_id' => $product->id,
                'price'      => $data['price'],
                'changed_at' => '2019-01-01 00:00:00',
            ]);

            // Dodaj unikalne kody kreskowe
            $barcodes = array_unique($data['barcodes']);
            foreach ($barcodes as $b) {
                Barcode::create([
                    'product_id' => $product->id,
                    'barcode'    => $b,
                    'created_at' => '2019-01-01 00:00:00',
                    'updated_at' => '2019-01-01 00:00:00',
                ]);
            }

            do {
                $newPrice = round($data['price'] * (0.8 + mt_rand() / mt_getrandmax() * 0.4), 2);
                // zakres ±20% od ceny 2019
            } while ($newPrice == $data['price']); // upewnij się, że cena jest różna

            ProductPriceHistory::create([
                'product_id' => $product->id,
                'price'      => $newPrice,
                'changed_at' => '2025-10-02 00:00:00',
            ]);
        }

        $this->command->info(count($products) . ' products seeded successfully.');
    }
}
