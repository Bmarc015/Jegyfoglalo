<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Seat;
use App\Models\Sector;

class SeatFactory extends Factory
{
    protected $model = Seat::class;

    public function definition()
    {

    }

    /**
     * Generate a unique seat combination (seat_number + row + col)
     */
   
}
