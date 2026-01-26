<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = [];

        // Szintek és az alapárak (Fondo/Kapu mögötti árak)
        $ranges = [
            ['start' => 101, 'end' => 134, 'base_price' => 12000], // 100-as szint
            ['start' => 200, 'end' => 234, 'base_price' => 10000], // 200-as szint
            ['start' => 301, 'end' => 337, 'base_price' => 8500],  // 300-as szint
            ['start' => 401, 'end' => 444, 'base_price' => 7000],  // 400-as szint
            ['start' => 501, 'end' => 544, 'base_price' => 5500],  // 500-as szint
            ['start' => 601, 'end' => 646, 'base_price' => 4000],  // 600-as szint
            ['start' => 701, 'end' => 710, 'base_price' => 2500],  // 700-as szint
        ];

        foreach ($ranges as $range) {
            for ($i = $range['start']; $i <= $range['end']; $i++) {
                $price = $range['base_price'];

                // Lateral (Oldalsó) szektorok prémium árazása
                // A beküldött képed alapján a szektorok vége (pl. x01-x10 és x30-x34) 
                // jellemzően a pálya hosszanti oldalán vannak.
                $lastTwoDigits = $i % 100;

                if (($lastTwoDigits >= 1 && $lastTwoDigits <= 10) ||
                    ($lastTwoDigits >= 30 && $lastTwoDigits <= 44)
                ) {
                    // A Lateral szektorok kb. 50%-kal drágábbak, mint a Fondo
                    $price = $price * 1.5;
                }

                $sectors[] = [
                    'sector_number' => $i,
                    'sector_price'  => (int)$price,
                ];
            }
        }

        // Adatbázisba szúrás
        \App\Models\Sector::insert($sectors);
    }
}
