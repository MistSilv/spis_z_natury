<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\User;
use App\Models\Region;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ProduktSkanySeeder extends Seeder
{
    public function run(): void
    {
        $regions = Region::all()->keyBy('code'); // ['magazyn'=>..., 'sklep'=>..., 'garmaz'=>..., 'piekarnia'=>...]
        $users = User::where('role', 'pracownik')->get();
        $units = DB::table('units')->pluck('code', 'id'); // [1=>'szt', 2=>'kg', ...]

        // Okres od stycznia 2019 do teraz
        $period = CarbonPeriod::create('2019-01-01', now()->endOfMonth());

        foreach ($period as $month) {
            foreach ($users as $user) {
                $region = $user->region;
                if (!$region) continue;

                $products = Product::all();

                $scansCount = 0;

                while ($scansCount < 100) { // każdy pracownik skanuje ~100 produktów w danym miesiącu
                    foreach ($products as $product) {
                        $unitCode = $units[$product->unit_id];
                        $repeats = rand(1, 3); // produkt może wystąpić 1-3 razy z różnymi ilościami

                        for ($i = 0; $i < $repeats && $scansCount < 100; $i++) {
                            // Ilość
                            if (in_array($unitCode, ['szt', 'opak'])) {
                                $quantity = rand(1, 20); // liczba całkowita
                            } else { // kg, l
                                $quantity = round(rand(1, 5000) / 100, 2); // decimal(15,2)
                            }

                            // Data skanu: 25-28 dzień wybranego miesiąca
                            $day = rand(25, 28);
                            $hour = rand(8, 18);
                            $minute = rand(0, 59);
                            $second = rand(0, 59);
                            $scannedAt = Carbon::create($month->year, $month->month, $day, $hour, $minute, $second);

                            DB::table('produkt_skany')->insert([
                                'product_id' => $product->id,
                                'user_id'    => $user->id,
                                'region_id'  => $region->id,
                                'quantity'   => $quantity,
                                'scanned_at' => $scannedAt,
                                'barcode'    => $product->barcodes()->inRandomOrder()->first()->barcode ?? null,
                            ]);

                            $scansCount++;
                        }

                        if ($scansCount >= 100) break;
                    }
                }
            }
        }
    }
}
