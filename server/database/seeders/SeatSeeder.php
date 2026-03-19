<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;
use App\Models\Seat;

class SeatSeeder extends Seeder
{
   public function run(): void
{
    $sectors = \App\Models\Sector::all();
    $games = \App\Models\Game::take(1)->get(); // Csak az első meccs kap székeket

    foreach ($games as $game) { // Minden meccshez legeneráljuk a székeket
        foreach ($sectors as $sector) {
            $seats = [];
            for ($row = 1; $row <= 10; $row++) {
                for ($col = 1; $col <= 15; $col++) {
                    $seats[] = [
                        'game_id'   => $game->id,   // <--- EZ KELL BELE!
                        'sector_id' => $sector->id,
                        'row'       => $row,
                        'col'       => $col,
                        'status'    => 0,
                    ];
                }
            }
            \App\Models\Seat::insert($seats);
        }
    }
}
}
