<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Region;

class ImportCsvController extends Controller
{
    public function showForm()
    {
        return view('import.form');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $regionCode = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $region = Region::where('code', $regionCode)->first();

        if (!$region) {
            return back()->withErrors([
                'csv_file' => "Nie znaleziono regionu o kodzie '{$regionCode}' w tabeli regions."
            ]);
        }

        $contents = file_get_contents($path);

        // Poprawne kodowania
        $encodings = ['UTF-8', 'ISO-8859-2', 'WINDOWS-1252', 'ASCII'];
        $encoding = mb_detect_encoding($contents, $encodings, true);

        if ($encoding && $encoding !== 'UTF-8') {
            $contents = mb_convert_encoding($contents, 'UTF-8', $encoding);
            file_put_contents($path, $contents);
        }

        $rows = array_map('str_getcsv', file($path));
        $count = 0;

      foreach ($rows as $index => $row) {
            // Jeżeli cały wiersz jest pusty (np. koniec pliku), pomiń tylko taki przypadek
            if (count($row) === 1 && trim($row[0]) === '') {
                continue;
            }

            // Debug: zobacz strukturę danych
            if ($count === 0) {
                \Log::info("Struktura wiersza {$index}:", $row);
            }

            DB::table('imported_records')->insert([
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
                'vat' => isset($row[37]) ? rtrim(substr($row[37], 0, 5), '0') : null,
                'NIP_Dostawcy' => isset($row[38]) ? substr($row[38], 0, 255) : null,
                'ean' => isset($row[40]) ? substr($row[40], 0, 50) : null, 
                'kod' => isset($row[41]) ? substr($row[41], 0, 50) : null, 
                'powod' => isset($row[46]) ? substr($row[46], 0, 255) : null, 
                'imported_at' => now(),
            ]);

            $count++;
        }

        return back()->with('success', "Zaimportowano {$count} rekordów dla regionu {$region->name}.");
    }
}