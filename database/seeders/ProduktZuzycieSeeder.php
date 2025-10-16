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

            $path = $file->getPathname();
            $contents = file_get_contents($path);

            // --- 🔍 WYCIĄGNIJ DATĘ Z PIERWSZEGO WIERSZA ---
            preg_match('/(\d{2}\/\d{2}\/\d{2,4})/', $contents, $matches);
            $dataProt = null;
            if (!empty($matches[1])) {
                try {
                    $dataProt = Carbon::createFromFormat('d/m/y', $matches[1])->format('Y-m-d');
                    $this->command->info("📅 Wykryto datę protokołu: {$dataProt}");
                } catch (\Exception $e) {
                    $this->command->warn("⚠️ Nie udało się sparsować daty: {$matches[1]}");
                }
            } else {
                $this->command->warn("⚠️ Nie znaleziono daty w nagłówku pliku.");
            }

            // --- Kodowanie ---
            $encodings = ['UTF-8', 'ISO-8859-2', 'WINDOWS-1252', 'ASCII'];
            $encoding = mb_detect_encoding($contents, $encodings, true);

            if ($encoding && $encoding !== 'UTF-8') {
                $contents = mb_convert_encoding($contents, 'UTF-8', $encoding);
                file_put_contents($path, $contents);
            }

            $rows = array_map('str_getcsv', file($path));
            $insertData = [];
            $count = 0;

            foreach ($rows as $index => $row) {
                if (count($row) === 1 && trim($row[0]) === '') continue;

                if ($count === 0) {
                    $this->command->info("🔍 Struktura pierwszego wiersza:");
                    $this->command->info("   Liczba kolumn: " . count($row));
                    $this->command->info("   Kolumna 29 (dostawca): " . ($row[29] ?? 'BRAK'));
                    $this->command->info("   Kolumna 30 (artykul): " . ($row[30] ?? 'BRAK'));
                }

                if (count($row) < 47) {
                    $this->command->warn("⚠️ Wiersz #{$index} ma tylko " . count($row) . " kolumn (wymagane min. 47)");
                    continue;
                }

                $insertData[] = [
                    'region_id' => $region->id,
                    'dostawca' => isset($row[29]) ? substr($row[29], 0, 255) : null,   
                    'artykul' => isset($row[30]) ? substr($row[30], 0, 255) : null,   
                    'dzial' => isset($row[25]) ? substr($row[25], 0, 255) : null,
                    'ilosc' => isset($row[31]) ? (float) str_replace(',', '.', $row[31]) : null, 
                    'cena_netto' => isset($row[32]) ? (float) str_replace(',', '.', $row[32]) : null, 
                    'cena_brutto' => isset($row[33]) ? (float) str_replace(',', '.', $row[33]) : null, 
                    'wartosc_netto' => isset($row[34]) ? (float) str_replace(',', '.', $row[34]) : null, 
                    'wartosc_brutto' => isset($row[35]) ? (float) str_replace(',', '.', $row[35]) : null, 
                    'co_to' => isset($row[36]) ? substr($row[36], 0, 255) : null,
                    'vat' => isset($row[37]) ? $this->parseVatLikeController($row[37]) : null,
                    'NIP_Dostawcy' => isset($row[38]) ? substr($row[38], 0, 255) : null,
                    'ean' => isset($row[40]) ? substr($row[40], 0, 50) : null, 
                    'kod' => isset($row[41]) ? substr($row[41], 0, 50) : null, 
                    'powod' => isset($row[46]) ? substr($row[46], 0, 255) : null, 
                    'imported_at' => now(),
                    'data_protokolu' => $dataProt, // 🆕 dodane pole
                ];

                $count++;
            }

            if (!empty($insertData)) {
                try {
                    foreach (array_chunk($insertData, 100) as $chunk) {
                        DB::table('imported_records')->insert($chunk);
                    }
                    $this->command->info("✅ Zaimportowano: " . count($insertData) . " rekordów z {$filename}");
                } catch (\Exception $e) {
                    $this->command->error("❌ Błąd podczas importu {$filename}: " . $e->getMessage());
                    if (count($insertData) > 0) {
                        $this->command->error("🔍 Problem z pierwszym rekordem:");
                        $this->command->error(print_r($insertData[0], true));
                    }
                }
            } else {
                $this->command->warn("⚠️ Brak danych do importu z pliku {$filename}");
            }
        }

        $this->command->info("🎯 Import zakończony.");
    }

    private function parseVatLikeController($value)
    {
        if (empty($value)) return null;
        return rtrim(substr($value, 0, 5), '0');
    }
}
