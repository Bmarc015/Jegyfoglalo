<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $users  = DB::table('users')->pluck('id');
        $games  = DB::table('games')->pluck('id');
        $seats  = DB::table('seats')->pluck('id');

        foreach (range(1, 10) as $i) {
            DB::table('tickets')->insert([
                'user_id'   => $users->random(),
                'game_id'   => $games->random(),
                'seat_id'   => $seats->random(),
                'status'    => ['reserved', 'paid', 'cancelled'][array_rand(['reserved', 'paid', 'cancelled'])],
            ]);
        }
    }
}
