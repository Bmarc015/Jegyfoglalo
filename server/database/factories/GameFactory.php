<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Team;

class GameFactory extends Factory
{
    public function definition(): array
    {
        $teams = Team::pluck('id')->toArray();
        $home  = $this->faker->randomElement($teams);
        do {
            $away = $this->faker->randomElement($teams);
        } while ($away === $home);

        return [
            'team_home_id' => $home,
            'team_away_id' => $away,
            'game_date' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
        ];
    }
}
