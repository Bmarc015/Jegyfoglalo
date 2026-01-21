<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SectorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sector_number' => $this->faker->unique()->numberBetween(100, 300),
            'sector_price'  => $this->faker->numberBetween(3000, 15000),
        ];
    }
}
