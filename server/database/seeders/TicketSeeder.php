<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Game;
use App\Models\Seat;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $gameIds = Game::pluck('id')->toArray();

        if (empty($userIds) || empty($gameIds)) {
            $this->command->error("Nincs elég user vagy meccs a jegyekhez!");
            return;
        }

        foreach (range(1, 30) as $i) {
            $gameId = $gameIds[array_rand($gameIds)];
            $occupiedSeatIds = Ticket::where('game_id', $gameId)->pluck('seat_id');

            $freeSeat = Seat::whereNotIn('id', $occupiedSeatIds)
                            ->inRandomOrder()
                            ->first();

            if ($freeSeat) {
                Ticket::create([
                    'user_id' => $userIds[array_rand($userIds)],
                    'game_id' => $gameId,
                    'seat_id' => $freeSeat->id, 
                    'status'  => 'paid',
                ]);
            }
        }
    }
}
