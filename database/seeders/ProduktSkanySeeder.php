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
        $regions = Region::all()->keyBy('code');
        $users = User::where('role', 'pracownik')->get();
        $units = DB::table('units')->pluck('code', 'id');
        $allProducts = Product::all();

        $period = CarbonPeriod::create('2025-01-01', '1 month', now()->endOfMonth());

        $totalScans = 0;
        $currentYear = null;
        $yearScans = 0;

        foreach ($period as $month) {
            $monthScans = 0;
            $batch = []; // tu zbieramy wszystkie rekordy do insertu

            foreach ($regions as $regionCode => $region) {
                $regionUsers = $users->where('region_id', $region->id);

                $activeCount = match($regionCode) {
                    'garmaz', 'piekarnia' => rand(1, min(3, $regionUsers->count())),
                    'sklep', 'magazyn'    => rand(3, min(6, $regionUsers->count())),
                    default => 0
                };

                if ($activeCount === 0) continue;

                $activeUsers = $regionUsers->random($activeCount);

                foreach ($activeUsers as $user) {
                    $scansCount = 0;
                    $productScans = [];

                    while ($scansCount < 100) {
                        $product = $allProducts->random();

                        if (!isset($productScans[$product->id])) {
                            $productScans[$product->id] = 0;
                        }
                        if ($productScans[$product->id] >= 3) {
                            continue;
                        }

                        $unitCode = $units[$product->unit_id];
                        $quantity = in_array($unitCode, ['szt', 'opak'])
                            ? (float) rand(1, 20)
                            : (float) round(rand(1, 5000) / 100, 2);

                        $day = rand(25, 28);
                        $hour = rand(8, 18);
                        $minute = rand(0, 59);
                        $second = rand(0, 59);
                        $scannedAt = Carbon::create($month->year, $month->month, $day, $hour, $minute, $second);

                        $batch[] = [
                            'product_id' => $product->id,
                            'user_id'    => $user->id,
                            'region_id'  => $region->id,
                            'quantity'   => $quantity,
                            'scanned_at' => $scannedAt,
                            'barcode'    => $product->barcodes()->first()->barcode ?? null, // barcode z produktu
                        ];

                        $productScans[$product->id]++;
                        $scansCount++;
                        $monthScans++;
                    }
                }
            }

            if (!empty($batch)) {
                foreach (array_chunk($batch, 300) as $chunk) {
                    DB::table('produkt_skany')->insert($chunk);
                }
            }

            // jeśli zmienił się rok, wyświetlamy sumę za poprzedni rok
            if ($currentYear !== $month->year) {
                if ($currentYear !== null) {
                    $this->command->info("Rok {$currentYear} End. Added {$yearScans} skan.");
                }
                $currentYear = $month->year;
                $yearScans = 0;
            }

            $yearScans += $monthScans;
            $totalScans += $monthScans;

            $this->command->info("Msc {$month->format('Y-m')} end. Gen {$monthScans} skan.");
        }

        // Wyświetlenie sumy za ostatni rok
        $this->command->info("Rok {$currentYear} end. Added {$yearScans} skan.");
        $this->command->info("Seeder End. Added {$totalScans} skan.");
    }
}
