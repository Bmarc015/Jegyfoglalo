<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Mielőtt seedelünk, minden táblát töröljünk le.
        DB::statement('DELETE FROM tickets');
        DB::statement('DELETE FROM games');
        DB::statement('DELETE FROM teams');
        DB::statement('DELETE FROM seats');
        DB::statement('DELETE FROM sectors');
        DB::statement('DELETE FROM users');



        //Ami Seeder osztály itt fel van sorolva, annak lefut a run() metódusa
        $this->call([
            UserSeeder::class,    // Felhasználók (szerepkörök miatt)
            SectorSeeder::class,  // Szektorok (alaprajzhoz)
            TeamSeeder::class,    // Csapatok (kell a meccshez)
            GameSeeder::class,    // Meccsek (kell a székekhez/jegyekhez)
            SeatSeeder::class,    // Székek (amiket most generáltunk az adminnal)
        ]);
    }
}
