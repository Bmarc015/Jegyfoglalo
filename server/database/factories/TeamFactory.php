<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_name' => ucfirst($this->faker->unique()->word()) . 's',
            'team_city' => $this->faker->city(),
        ];
    }
}
