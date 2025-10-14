<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ProduktZuzycieSeeder extends Seeder
{
    public function run()
    {
        $dir = database_path('db/zuzycie');

        if (!File::exists($dir)) {
            $this->command->error("Folder nie istnieje: {$dir}");
            return;
        }

        $files = File::files($dir);

        if (empty($files)) {
            $this->command->warn("Brak plików CSV w katalogu {$dir}");
            return;
        }

        $regions = DB::table('regions')->get()->keyBy('code');
        $this->command->info("Dostępne regiony: " . $regions->pluck('code')->implode(', '));

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $regionCode = pathinfo($filename, PATHINFO_FILENAME);
            
            $this->command->info("⏳ Przetwarzam plik: {$filename} (szukam regionu: '{$regionCode}')");

            $region = $regions->get($regionCode);

            if (!$region) {
                $this->command->warn("⚠️ Region o kodzie '{$regionCode}' nie istnieje — pomijam plik {$filename}");
                continue;
            }

            if (($handle = fopen($file->getPathname(), 'r')) === false) {
                $this->command->error("Nie można otworzyć pliku {$filename}");
                continue;
            }

            $insertData = [];

            while (($data = fgetcsv($handle, 10000, ',')) !== false) {
                if (empty(array_filter($data))) continue;

                // Pomijamy nagłówki i stopki
                if (isset($data[29]) && trim($data[29]) === 'Dostawca') continue;
                if (str_contains(implode(' ', $data), 'Dok. ogólem') ||
                    str_contains(implode(' ', $data), 'Wydruk zakończony')) continue;

                // Pobieramy dane z właściwych kolumn
                $supplier = trim($data[29] ?? '');
                $product  = trim($data[30] ?? '');

                if (!empty($supplier) && !empty($product)) {
                    $quantity      = $this->parseNumber($data[31] ?? null);
                    $priceNet      = $this->parseNumber($data[32] ?? null);
                    $priceGross    = $this->parseNumber($data[33] ?? null);
                    $valueNet      = $this->parseNumber($data[34] ?? null);
                    $valueGross    = $this->parseNumber($data[35] ?? null);
                    $vat           = $this->parseVat($data[37] ?? null);
                    $idabaco       = trim($data[40] ?? '');
                    $ean           = trim($data[41] ?? '');
                    $powod         = trim($data[46] ?? '');

                    $insertData[] = [
                        'region_id'      => $region->id,
                        'dostawca'       => $supplier,
                        'artykul'        => $product,
                        'ilosc'          => $quantity,
                        'cena_netto'     => $priceNet,
                        'cena_brutto'    => $priceGross,
                        'wartosc_netto'  => $valueNet,
                        'wartosc_brutto' => $valueGross,
                        'vat'            => $vat,
                        'kod'       => $idabaco,
                        'ean'            => $ean,
                        'powod'          => $powod,

                    ];
                }
            }

            fclose($handle);

            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 100) as $chunk) {
                    DB::table('imported_records')->insert($chunk);
                }
                $this->command->info("✅ Zaimportowano: " . count($insertData) . " rekordów z {$filename}");
            } else {
                $this->command->warn("⚠️ Brak danych do importu z pliku {$filename}");
            }
        }

        $this->command->info("🎯 Import zakończony.");
    }

    private function parseNumber($value)
    {
        if (empty($value)) return null;
        $value = str_replace([' ', ','], ['', '.'], trim($value));
        return is_numeric($value) ? (float)$value : null;
    }

    private function parseVat($value)
    {
        if (empty($value)) return null;
        // Usuń wszystko oprócz cyfr, podziel przez 100 i dodaj "%"
        $num = preg_replace('/[^0-9]/', '', $value);
        if (!is_numeric($num)) return null;
        return ($num / 100) . '%';
    }

}
