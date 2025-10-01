<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Barcode;
use App\Models\ProductPriceHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            fgetcsv($handle, 1000, ','); // Skip header

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                [$barcode, $name, $price, $id_abaco] = $data;

                $name    = mb_convert_encoding($name, 'UTF-8', 'auto');
                $barcode = mb_convert_encoding($barcode, 'UTF-8', 'auto');
                $price   = floatval(str_replace(',', '.', $price));

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

        // Insert products
        foreach ($products as $data) {
            $product = Product::create([
                'name'       => $data['name'],
                'id_abaco'   => $data['id_abaco'],
                'unit_id'    => $data['unit_id'],
                'created_at' => '2019-01-01 00:00:00',
                'updated_at' => '2019-01-01 00:00:00',
            ]);

            // pierwsza cena
            ProductPriceHistory::create([
                'product_id' => $product->id,
                'price'      => $data['price'],
                'changed_at' => '2019-01-01 00:00:00',
            ]);

            foreach (array_unique($data['barcodes']) as $b) {
                Barcode::create([
                    'product_id' => $product->id,
                    'barcode'    => $b,
                    'created_at' => '2019-01-01 00:00:00',
                    'updated_at' => '2019-01-01 00:00:00',
                ]);
            }
        }

        $this->generatePriceHistory();
    }

    // Generowanie liczby zmian według przybliżonego rozkładu Gaussa
    private function gaussianRand($max = 60, $stdDev = 10)
    {

        $truerand = true;

        if ($truerand) {
            // czysty, jednolity rand 0..max
            return rand(0, $max);
        }
        // suma 6 losowych liczb 0-1 dla przybliżenia Gaussa
        $sum = 0;
        for ($i = 0; $i < 6; $i++) {
            $sum += rand(0, 1000) / 1000; // 0..1
        }

        // rozkład wokół 0, odwrócony do 0..max
        $val = abs($stdDev * ($sum - 3)); // abs, największa gęstość blisko 0
        return max(0, min($max, round($val)));
    }

    private function generatePriceHistory()
    {
        $products = Product::all();
        $start = Carbon::create(2019, 1, 1);
        $end   = Carbon::now()->startOfMonth();

        $totalProducts = $products->count();
        $this->command->info("Generating price history month by month for {$totalProducts} products...");

        $allInserts = [];

        for ($date = $start->copy(); $date->lte($end); $date->addMonth()) {
            // liczba zmian w tym miesiącu według Gaussa
            $changesCount = $this->gaussianRand(30, 10);
            $changedProducts = $products->random(min($changesCount, $totalProducts));

            if ($changedProducts instanceof \Illuminate\Database\Eloquent\Collection) {
                $changedProducts = $changedProducts->all();
            } else {
                $changedProducts = [$changedProducts];
            }

            foreach ($changedProducts as $product) {
                $lastPrice = ProductPriceHistory::where('product_id', $product->id)
                    ->orderBy('changed_at', 'desc')
                    ->first()
                    ->price ?? rand(100, 500) / 10;

                $factor = (rand(0, 10) === 0)
                    ? 1 - (rand(5, 20) / 100)
                    : 1 + (rand(5, 20) / 100);

                $newPrice = round($lastPrice * $factor, 2);

                // losowy dzień w miesiącu
                $day = rand(1, $date->daysInMonth);
                $changedAt = $date->copy()
                    ->day($day)
                    ->setTime(rand(0,23), rand(0,59), rand(0,59))
                    ->addMilliseconds(rand(0, 999));

                $allInserts[] = [
                    'product_id' => $product->id,
                    'price'      => $newPrice,
                    'changed_at' => $changedAt,
                ];

                // update timestamp produktu
                $product->updated_at = $changedAt;
                $product->save();
            }
        }

        // Sortowanie całej listy po dacie
        usort($allInserts, fn($a, $b) => $a['changed_at'] <=> $b['changed_at']);

        // Batch insert
        foreach (array_chunk($allInserts, 600) as $chunk) {
            ProductPriceHistory::insert($chunk);
        }

        $this->command->info("Price history generated up to {$end->format('Y-m')}.");
    }
}
