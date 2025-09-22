<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $regions = DB::table('regions')->pluck('id', 'code'); // ['magazyn' => 1, 'sklep' => 2, 'garmaz' => 3, 'piekarnia' => 4]

        $users = [];

        $emailsCounter = [
            'sklep' => 1,
            'garmaz' => 1,
            'piekarnia' => 1,
            'magazyn' => 1,
        ];

        $regionCodes = ['garmaz', 'piekarnia', 'sklep', 'magazyn'];
        $regionWeights = [
            'garmaz' => 0.15,
            'piekarnia' => 0.15,
            'sklep' => 0.40,
            'magazyn' => 0.30,
        ];

        $totalEmployees = 34;
        $regionAssignments = [];

        // obliczamy liczbę pracowników na region
        foreach ($regionCodes as $code) {
            $regionAssignments[$code] = round($regionWeights[$code] * $totalEmployees);
        }

        // jeśli suma nie daje 34, korygujemy ostatni region
        $sumAssigned = array_sum($regionAssignments);
        if ($sumAssigned !== $totalEmployees) {
            $regionAssignments['magazyn'] += $totalEmployees - $sumAssigned;
        }

        // Tworzymy pracowników
        $counter = 1;
        foreach ($regionAssignments as $code => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $emailDomain = "{$emailsCounter[$code]}@{$code}.com";
                $users[] = [
                    'name' => "Pracownik {$counter} - {$code}",
                    'email' => $emailDomain,
                    'password' => Hash::make('1234567890'),
                    'role' => 'pracownik',
                    'region_id' => $regions[$code],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $emailsCounter[$code]++;
                $counter++;
            }
        }

        // Dodajemy księgowego
        $users[] = [
            'name' => 'Księgowość',
            'email' => 'ks@ks.com',
            'password' => Hash::make('1234567890'),
            'role' => 'ksiegowy',
            'region_id' => null, // przykładowo przypisany do magazynu
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Dodajemy admina
        $users[] = [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('1234567890'),
            'role' => 'admin',
            'region_id' => null, // przykładowo przypisany do magazynu
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('users')->insert($users);

        $this->command->info("Dodano 36 użytkowników: 34 pracowników, 1 księgowy, 1 admin.");
    }
}
