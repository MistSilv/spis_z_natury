<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\User;
use App\Models\Region;
use Carbon\Carbon;

class ProduktSkanySeeder extends Seeder
{
    public function run(): void
    {
        $regions = Region::all()->keyBy('code');
        $users = User::where('role', 'pracownik')->get();
        $units = DB::table('units')->pluck('code', 'id');
        $allProducts = Product::with(['barcodes', 'prices'])->get();

        // Bieżący miesiąc
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        $totalScans = 0;

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

                    $unitCode = $units[$product->unit_id] ?? 'szt';
                    $quantity = in_array($unitCode, ['szt', 'opak'])
                        ? (float) rand(1, 20)
                        : (float) round(rand(1, 5000) / 100, 2);

                    // Losowa data w bieżącym miesiącu
                    $day    = rand(1, $startOfMonth->daysInMonth);
                    $hour   = rand(8, 18);
                    $minute = rand(0, 59);
                    $second = rand(0, 59);
                    $scannedAt = Carbon::create(
                        $startOfMonth->year,
                        $startOfMonth->month,
                        $day,
                        $hour,
                        $minute,
                        $second
                    );

                    // Pobranie ostatniej ceny z historii cen
                    $lastPrice = $product->prices()->orderBy('changed_at', 'desc')->value('price');

                    $batch[] = [
                        'product_id'    => $product->id,
                        'user_id'       => $user->id,
                        'region_id'     => $region->id,
                        'quantity'      => $quantity,
                        'price_history' => $lastPrice ?? 0, // jeśli brak ceny, ustawiamy 0
                        'scanned_at'    => $scannedAt,
                        'barcode'       => $product->barcodes->first()->barcode ?? null,
                    ];

                    $productScans[$product->id]++;
                    $scansCount++;
                    $totalScans++;
                }
            }
        }

        // Wstawiamy paczkami, aby uniknąć problemów z dużą ilością rekordów
        if (!empty($batch)) {
            foreach (array_chunk($batch, 150) as $chunk) {
                DB::table('produkt_skany')->insert($chunk);
            }
        }

        $this->command->info("Seeder End. Added {$totalScans} skanów dla bieżącego miesiąca.");
    }
}
