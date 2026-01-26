<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // A CSV fájl elérési útja
        $filePath = database_path('data/teams.csv');

        if (!File::exists($filePath)) {
            $this->command->error("A CSV fájl nem található: $filePath");
            return;
        }

        // Fájl beolvasása soronként
        $csvData = file($filePath);

        foreach ($csvData as $row) {
            // Sor szétszedése vessző mentén (név, város)
            $data = str_getcsv($row);

            if (count($data) >= 2) {
                Team::create([
                    'team_name' => trim($data[0]),
                    'team_city' => trim($data[1]),
                ]);
            }
        }

        $this->command->info(count($csvData) . " csapat sikeresen betöltve a CSV-ből!");
    }
}
