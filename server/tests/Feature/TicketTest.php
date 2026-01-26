<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Game;
use App\Models\Seat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TicketTest extends TestCase
{
   use RefreshDatabase; // Ez létrehozza a táblákat (teams, games, seats stb.)

    /**
     * Segédfüggvény a teszt-user bejelentkezéséhez
     */
    private function getAuthToken(string $email, string $password = 'password')
    {
        $response = $this->postJson('/api/users/login', [
            'email' => $email,
            'password' => $password,
        ]);

        return $response->json('data')['token'] ?? null;
    }

    /**
     * TESZT: Vásárló vs Admin jogosultság az index végponton
     */
    public function test_ticket_visibility_logic(): void
    {
        // 1. Létrehozunk egy admint és egy vásárlót
        $admin = User::factory()->create(['role' => 1, 'password' => Hash::make('password')]);
        $user = User::factory()->create(['role' => 3, 'password' => Hash::make('password')]);
        $otherUser = User::factory()->create(['role' => 3]);

        // 2. Létrehozunk alapadatokat (Meccs, Szék)
        $game = Game::factory()->create();
        $seat1 = Seat::factory()->create();
        $seat2 = Seat::factory()->create();

        // 3. Jegyek kiosztása
        $myTicket = Ticket::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'seat_id' => $seat1->id,
            'status' => 'paid'
        ]);

        $othersTicket = Ticket::create([
            'user_id' => $otherUser->id,
            'game_id' => $game->id,
            'seat_id' => $seat2->id,
            'status' => 'paid'
        ]);

        // --- VÁSÁRLÓ TESZTELÉSE ---
        $userToken = $this->getAuthToken($user->email);
        $userResponse = $this->withHeader('Authorization', "Bearer $userToken")
                             ->getJson('/api/tickets');

        $userResponse->assertStatus(200);
        $userResponse->assertJsonCount(1); // Csak a sajátját látja
        $userResponse->assertJsonFragment(['id' => $myTicket->id]);
        $userResponse->assertJsonMissing(['id' => $othersTicket->id]);

        // --- ADMIN TESZTELÉSE ---
        $adminToken = $this->getAuthToken($admin->email);
        $adminResponse = $this->withHeader('Authorization', "Bearer $adminToken")
                              ->getJson('/api/tickets');

        $adminResponse->assertStatus(200);
        // Az admin látja mindkettőt (és az összes többit is az adatbázisban)
        $adminResponse->assertJsonFragment(['id' => $myTicket->id]);
        $adminResponse->assertJsonFragment(['id' => $othersTicket->id]);
    }

    /**
     * TESZT: Adatbázis szintű védelem (Egy székre csak egy jegy egy meccsen)
     */
    public function test_cannot_book_same_seat_for_same_game_twice(): void
    {
        $game = Game::factory()->create();
        $seat = Seat::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Első foglalás - Sikerülnie kell
        Ticket::create([
            'user_id' => $user1->id,
            'game_id' => $game->id,
            'seat_id' => $seat->id,
            'status' => 'paid'
        ]);

        // Második foglalás ugyanarra a székre és meccsre - SQL hibát kell dobnia a Unique Constraint miatt
        $this->expectException(\Illuminate\Database\QueryException::class);

        Ticket::create([
            'user_id' => $user2->id,
            'game_id' => $game->id,
            'seat_id' => $seat->id,
            'status' => 'paid'
        ]);
    }
}