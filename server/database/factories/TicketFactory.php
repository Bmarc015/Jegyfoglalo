<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Game;
use App\Models\Seat;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            // Csak akkor generál új rekordot, ha a seederben nem adtuk meg fixen
            'user_id' => User::factory(), 
            'game_id' => Game::factory(),
            'seat_id' => Seat::factory(),
            'status'  => $this->faker->randomElement(['reserved', 'paid', 'cancelled']),
        ];
    }
}
