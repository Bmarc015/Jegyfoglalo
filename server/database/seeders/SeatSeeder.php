<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;
use App\Models\Seat;

class SeatSeeder extends Seeder
{
    public function run(): void
    {
       Seat::factory()->count(10)->uniqueSeat()->create();
    }
}
