<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Game;
use App\Models\Seat;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'game_id' => Game::inRandomOrder()->first()->id,
            'seat_id' => Seat::inRandomOrder()->first()->id,
            'status'  => $this->faker->randomElement(['paid', 'cancelled']),
        ];
    }
}
