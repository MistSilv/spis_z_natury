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

            // WCZYTAJ I PRZEKONWERTUJ PLIK TAK JAK W KONTROLLERZE
            $path = $file->getPathname();
            $contents = file_get_contents($path);

            // Poprawne kodowania - TAK JAK W KONTROLLERZE
            $encodings = ['UTF-8', 'ISO-8859-2', 'WINDOWS-1252', 'ASCII'];
            $encoding = mb_detect_encoding($contents, $encodings, true);

            if ($encoding && $encoding !== 'UTF-8') {
                $contents = mb_convert_encoding($contents, 'UTF-8', $encoding);
                file_put_contents($path, $contents);
            }

            // UŻYJ TEJ SAMEJ METODY CO KONTROLLER
            $rows = array_map('str_getcsv', file($path));
            $insertData = [];
            $count = 0;

            foreach ($rows as $index => $row) {
                // Jeżeli cały wiersz jest pusty (np. koniec pliku), pomiń tylko taki przypadek
                if (count($row) === 1 && trim($row[0]) === '') {
                    continue;
                }

                // Debug: pokaż strukturę pierwszego wiersza
                if ($count === 0) {
                    $this->command->info("🔍 Struktura pierwszego wiersza:");
                    $this->command->info("   Liczba kolumn: " . count($row));
                    $this->command->info("   Kolumna 29 (dostawca): " . ($row[29] ?? 'BRAK'));
                    $this->command->info("   Kolumna 30 (artykul): " . ($row[30] ?? 'BRAK'));
                }

                // Sprawdź czy mamy wystarczająco kolumn
                if (count($row) < 47) {
                    $this->command->warn("⚠️ Wiersz #{$index} ma tylko " . count($row) . " kolumn (wymagane min. 47)");
                    continue;
                }

                // UŻYJ DOKŁADNIE TEGO SAMEGO MAPOWANIA CO W KONTROLLERZE
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
                    'vat' => isset($row[37]) ? $this->parseVatLikeController($row[37]) : null, // ZMIENIONE
                    'NIP_Dostawcy' => isset($row[38]) ? substr($row[38], 0, 255) : null,
                    'ean' => isset($row[40]) ? substr($row[40], 0, 50) : null, 
                    'kod' => isset($row[41]) ? substr($row[41], 0, 50) : null, 
                    'powod' => isset($row[46]) ? substr($row[46], 0, 255) : null, 
                    'imported_at' => now(),

                ];

                $count++;

                // Debug: pokaż pierwszy rekord
                if ($count === 1) {
                    $this->command->info("🔍 Pierwszy rekord do wstawienia:");
                    $this->command->info("   Dostawca: " . $insertData[0]['dostawca']);
                    $this->command->info("   Artykuł: " . $insertData[0]['artykul']);
                    $this->command->info("   Ilość: " . $insertData[0]['ilosc']);
                }
            }

            if (!empty($insertData)) {
                try {
                    foreach (array_chunk($insertData, 100) as $chunk) {
                        DB::table('imported_records')->insert($chunk);
                    }
                    $this->command->info("✅ Zaimportowano: " . count($insertData) . " rekordów z {$filename}");
                } catch (\Exception $e) {
                    $this->command->error("❌ Błąd podczas importu {$filename}: " . $e->getMessage());
                    
                    // Debug: pokaż szczegóły błędu
                    if (count($insertData) > 0) {
                        $this->command->error("🔍 Problem z pierwszym rekordem:");
                        $this->command->error(print_r($insertData[0], true));
                    }
                }
            } else {
                $this->command->warn("⚠️ Brak danych do importu z pliku {$filename}");
                
                // Debug: pokaż co jest w pliku
                $this->command->info("🔍 Zawartość pliku (pierwsze 3 wiersze):");
                for ($i = 0; $i < min(3, count($rows)); $i++) {
                    $this->command->info("   Wiersz {$i}: " . json_encode($rows[$i]));
                }
            }
        }

        $this->command->info("🎯 Import zakończony.");
    }

    /**
     * Parsowanie VAT tak jak w kontrolerze - rtrim(substr($row[37], 0, 5), '0')
     */
    private function parseVatLikeController($value)
    {
        if (empty($value)) return null;
        return rtrim(substr($value, 0, 5), '0');
    }

    /**
     * Stara metoda - dla kompatybilności
     */
    private function parseNumber($value)
    {
        if (empty($value)) return null;
        $value = str_replace([' ', ','], ['', '.'], trim($value));
        return is_numeric($value) ? (float)$value : null;
    }

    /**
     * Stara metoda - dla kompatybilności  
     */
    private function parseVat($value)
    {
        if (empty($value)) return null;
        $num = preg_replace('/[^0-9]/', '', $value);
        if (!is_numeric($num)) return null;
        return ($num / 100) . '%';
    }
}