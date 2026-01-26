<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Team;
use App\Models\Game;

class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        $teams = Team::pluck('id')->toArray();
        if (count($teams) < 2) {
            return [];
        }

        $home = $this->faker->randomElement($teams);

        do {
            $away = $this->faker->randomElement($teams);
        } while ($away === $home);

        $date = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $hour = $this->faker->numberBetween(14, 21);
        $minuteOptions = [0, 30, 45];
        $minute = $this->faker->randomElement($minuteOptions);
        $date->setTime($hour, $minute, 0);

        return [
            'team_home_id' => $home,
            'team_away_id' => $away,
            'game_date'    => $date,
        ];
    }
}
