<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Game;
use App\Models\Seat;
use Illuminate\Database\Seeder;
use Faker\Factory;


class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('hu-HU');
        $userIds = User::where('role', '!=', 1)->pluck('id')->toArray();
        $gameIds = Game::pluck('id')->toArray();

        if (empty($userIds) || empty($gameIds)) {
            $this->command->error("Nincs elég user vagy meccs a jegyekhez!");
            return;
        }
        $gamesCount = count($gameIds);
        $userCount = count($userIds);
        $ticketCounter = $gamesCount * $userCount;

        foreach (range(1, $ticketCounter) as $i) {
            $gameId = $gameIds[array_rand($gameIds)];
            $occupiedSeatIds = Ticket::where('game_id', $gameId)->pluck('seat_id');

            $freeSeat = Seat::whereNotIn('id', $occupiedSeatIds)
                ->inRandomOrder()
                ->first();
            $randomStatus = $faker->randomElement(['reserved', 'paid', 'cancelled']);

            if ($freeSeat) {
                Ticket::create([
                    'user_id' => $userIds[array_rand($userIds)],
                    'game_id' => $gameId,
                    'seat_id' => $freeSeat->id,
                    'status'  => $randomStatus,
                ]);
            }
        }
    }
}
