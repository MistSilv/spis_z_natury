<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Barcode;

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
                $name = mb_convert_encoding($name, 'UTF-8', 'auto');
                $barcode = mb_convert_encoding($barcode, 'UTF-8', 'auto');

                // Replace comma with dot for decimal
                $price = floatval(str_replace(',', '.', $price));

                // Create or find product key
                $key = $name . '|' . $id_abaco;

                if (!isset($products[$key])) {
                    $products[$key] = [
                        'name' => $name,
                        'id_abaco' => $id_abaco,
                        'unit_id' => $units[array_rand($units)],
                        'price' => $price,
                        'barcodes' => []
                    ];
                }

                if ($barcode) {
                    $products[$key]['barcodes'][] = $barcode;
                }
            }
            fclose($handle);
        }

        // Insert products and barcodes
        foreach ($products as $data) {
            $product = Product::create([
                'name' => $data['name'],
                'id_abaco' => $data['id_abaco'],
                'unit_id' => $data['unit_id'],
                'price' => $data['price'],
            ]);

            $barcodes = array_unique($data['barcodes']);
            foreach ($barcodes as $b) {
                Barcode::create([
                    'product_id' => $product->id,
                    'barcode' => $b
                ]);
            }
        }

        $this->command->info(count($products) . ' products seeded successfully.');
    }
}
