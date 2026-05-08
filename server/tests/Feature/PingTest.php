<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Sector;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ApiHelpers;
use Tests\TestCase;

class PingTest extends TestCase
{
    use DatabaseTransactions, ApiHelpers;

    private function ensureUser(string $email, string $password, int $role): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Teszt Felhasználó',
                'password' => Hash::make($password),
                'role' => $role,
            ]
        );
    }

    private function createTeamsForGame(): array
    {
        $home = Team::create(['team_name' => 'Hazai ' . uniqid(), 'team_city' => 'Budapest']);
        $away = Team::create(['team_name' => 'Vendég ' . uniqid(), 'team_city' => 'Győr']);

        return [$home, $away];
    }

    private function getPostData(string $route): array
    {
        return match ($route) {
            'teams' => [
                'team_name' => 'Új Csapat ' . uniqid(),
                'team_city' => 'Budapest',
            ],
            'games' => (function () {
                [$home, $away] = $this->createTeamsForGame();

                return [
                    'team_home_id' => $home->id,
                    'team_away_id' => $away->id,
                    'game_date' => '2026-10-10 20:00:00',
                ];
            })(),
            'sectors' => [
                'id' => (string) random_int(8000, 9999),
                'sector_name' => 'T' . random_int(100, 999),
                'sector_price' => 12000,
            ],
            default => [],
        };
    }

    private function getCreatedIdFromResponse($response, string $route): string|int
    {
        $data = $response->json('data');

        return match ($route) {
            'sectors' => $data['id'],
            default => $data['id'],
        };
    }

    public static function tablesGetDataProvider(): array
    {
        return [
            'admin_get_teams' => ['teams', 'admin@example.com', '123', 1, 200],
            'admin_get_sectors' => ['sectors', 'admin@example.com', '123', 1, 200],
            'admin_get_games' => ['games', 'admin@example.com', '123', 1, 200],
            'admin_get_tickets' => ['tickets', 'admin@example.com', '123', 1, 200],
            'user_get_teams' => ['teams', 'user@example.com', '123', 2, 200],
            'user_get_games' => ['games', 'user@example.com', '123', 2, 200],
            'user_get_users' => ['users', 'user@example.com', '123', 2, 403],
        ];
    }

    public static function tablesPostDeleteDataProvider(): array
    {
        return [
            'admin_teams' => ['teams', 'admin@minta.com', '123', 1, true],
            'admin_games' => ['games', 'admin@minta.com', '123', 1, true],
            'admin_sectors' => ['sectors', 'admin@minta.com', '123', 1, true],
            'user_teams_no' => ['teams', 'vasarlo@minta.com', '123', 2, false],
            'user_games_no' => ['games', 'vasarlo@minta.com', '123', 2, false],
        ];
    }

    public function test_ping_endpoint_returns_api_text(): void
    {
        $response = $this->get('/api/x');

        $response->assertStatus(200);
        $response->assertSee('API');
    }

    #[DataProvider('tablesGetDataProvider')]
    public function test_api_get_endpoints($route, $email, $password, $role, $expectedStatus): void
    {
        $this->ensureUser($email, $password, $role);

        $response = $this->login($email, $password);
        $response->assertStatus(200);
        $token = $this->myGetToken($response);

        $response = $this->myGet("/api/$route", $token);
        $response->assertStatus($expectedStatus);

        $this->logout($token);
    }

    #[DataProvider('tablesPostDeleteDataProvider')]
    public function test_api_post_delete_endpoints($route, $email, $password, $role, $canWrite): void
    {
        $this->ensureUser($email, $password, $role);

        $response = $this->login($email, $password);
        $response->assertStatus(200);
        $token = $this->myGetToken($response);

        $response = $this->myPost("/api/$route", $this->getPostData($route), $token);

        if (!$canWrite) {
            $response->assertStatus(403);
            $this->logout($token);
            return;
        }

        $response->assertStatus(200);
        $createdId = $this->getCreatedIdFromResponse($response, $route);

        $deleteResponse = $this->myDelete("/api/$route/$createdId", $token);
        $deleteResponse->assertStatus(200);

        $this->logout($token);
    }
}
