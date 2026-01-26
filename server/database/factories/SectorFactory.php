<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SectorFactory extends Factory
{
    public function definition(): array
{
    return [
        // A képen látható legkisebb és legnagyobb szektorszám között generál
        'sector_number' => $this->faker->unique()->numberBetween(100, 710),
        'sector_price'  => $this->faker->randomElement([3000, 4500, 6000, 8000, 10000, 12000, 15000]),
    ];
}
}
