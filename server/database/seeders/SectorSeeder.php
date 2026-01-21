<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sectors')->insert([
            [
                'sector_number' => 101,
                'sector_price'  => 4500,
            ],
            [
                'sector_number' => 102,
                'sector_price'  => 5500,
              
            ],
            [
                'sector_number' => 201,
                'sector_price'  => 8000,
            ],
        ]);
    }
}
