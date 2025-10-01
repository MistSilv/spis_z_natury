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

        // --- USTAWIENIE ZAKRESU DAT ---
        $fromDate = Carbon::create(2025, 5, 1, 0, 0, 0);
        $toDate = Carbon::now()->subDay()->endOfDay();
        //$toDate   = Carbon::create(2025, 9, 30, 23, 59, 59);
        // --------------------------------

        $totalScans = 0;
        $batch = [];

        // Pętla po miesiącach
        for ($date = $fromDate->copy()->startOfMonth(); $date->lte($toDate); $date->addMonth()) {
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd   = $date->copy()->endOfMonth();

            // Liczba skanów w tym miesiącu
            $scansThisMonth = rand(800, 1400);

            for ($i = 0; $i < $scansThisMonth; $i++) {
                $region = $regions->random();
                $regionUsers = $users->where('region_id', $region->id);
                if ($regionUsers->isEmpty()) continue;
                $user = $regionUsers->random();

                $product = $allProducts->random();
                $unitCode = $units[$product->unit_id] ?? 'szt';
                $quantity = in_array($unitCode, ['szt', 'opak'])
                    ? (float) rand(1, 20)
                    : (float) round(rand(1, 5000) / 100, 2);

                // Daty w ostatnich 3-5 dniach miesiąca
                $day = $monthEnd->day - rand(0, min(4, $monthEnd->day - 1));
                $hour = rand(8, 18);
                $minute = rand(0, 59);
                $second = rand(0, 59);
                $scannedAt = Carbon::create($monthEnd->year, $monthEnd->month, $day, $hour, $minute, $second);

                // Cena produktu obowiązująca w dniu skanu
                $lastPrice = $product->prices()
                    ->where('changed_at', '<=', $scannedAt)
                    ->orderBy('changed_at', 'desc')
                    ->value('price') ?? 0;

                $batch[] = [
                    'product_id'    => $product->id,
                    'user_id'       => $user->id,
                    'region_id'     => $region->id,
                    'quantity'      => $quantity,
                    'price_history' => $lastPrice,
                    'scanned_at'    => $scannedAt,
                    'barcode'       => $product->barcodes->first()->barcode ?? null,
                ];

                $totalScans++;
            }
        }

        // Wstawiamy paczkami
        if (!empty($batch)) {
            foreach (array_chunk($batch, 150) as $chunk) {
                DB::table('produkt_skany')->insert($chunk);
            }
        }

        $this->command->info("Seeder End. Added {$totalScans} skanów w zakresie od {$fromDate} do {$toDate}.");
    }
}
