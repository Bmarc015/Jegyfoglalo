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
        return [
            'sector_id' => Sector::inRandomOrder()->first()->id,
            'seat_number' => $this->faker->numberBetween(1, 999), // 1-999 között
            'row' => $this->faker->numberBetween(1, 50),          // 1-50 között
            'col' => $this->faker->numberBetween(1, 20),          // 1-20 között
        ];
    }

    /**
     * Generate a unique seat combination (seat_number + row + col)
     */
    public function uniqueSeat()
    {
        return $this->state(function (array $attributes) {
            do {
                $seat_number = $this->faker->numberBetween(1, 999);
                $row = $this->faker->numberBetween(1, 50);
                $col = $this->faker->numberBetween(1, 20);

                $exists = Seat::where('seat_number', $seat_number)
                    ->where('row', $row)
                    ->where('col', $col)
                    ->exists();
            } while ($exists);

            return [
                'seat_number' => $seat_number,
                'row' => $row,
                'col' => $col,
            ];
        });
    }
}
