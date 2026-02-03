<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\Sector;
use App\Models\Seat;
use App\Models\Game;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestBase; // Feltételezve, hogy itt van a login/token kezelés
use PHPUnit\Framework\Attributes\DataProvider;

class PingTest extends TestCase
{
    use DatabaseTransactions;

    // Teszt adatok a POST kérésekhez
    private static function getSampleTeamData() { return ["team_name" => "Új Csapat", "team_city" => "Budapest"]; }
    private static function getSampleSectorData() { return ["sector_number" => 99, "sector_price" => 5000]; }
    private static function getSampleGameData($hId, $aId) { return ["team_home_id" => $hId, "team_away_id" => $aId, "game_date" => "2026-10-10 20:00:00"]; }

    /**
     * Adatszolgáltató a GET kérésekhez és jogosultságokhoz
     * [Route, Felhasználó Email, Jelszó, Elvárt HTTP Status]
     */
    public static function tablesGetDataProvider(): array
    {
        return [
            // Admin mindent lát (200)
            'admin_get_teams'   => ['teams',   'admin@example.com', '123', 200],
            'admin_get_sectors' => ['sectors', 'admin@example.com', '123', 200],
            'admin_get_games'   => ['games',   'admin@example.com', '123', 200],
            'admin_get_tickets' => ['tickets', 'admin@example.com', '123', 200],

            // Sima felhasználó (Vásárló)
            'user_get_teams'    => ['teams',   'user@example.com', '123', 200],
            'user_get_games'    => ['games',   'user@example.com', '123', 200],
            'user_get_users'    => ['users',   'user@example.com', '123', 403], // Tiltott az admin lista
        ];
    }

    /**
     * Adatszolgáltató POST és DELETE műveletekhez
     * [Route, Email, Password, PostAccess?, DeleteAccess?]
     */
    public static function tablesPostDeleteDataProvider(): array
    {
        return [
            // Admin tud létrehozni és törölni
            'admin_teams'   => ['teams',   'admin@example.com', '123', true, true],
            'admin_games'   => ['games',   'admin@example.com', '123', true, true],
            'admin_sectors' => ['sectors', 'admin@example.com', '123', true, true],

            // Sima felhasználó nem piszkálhatja az alapokat
            'user_teams_no' => ['teams',   'user@example.com', '123', false, false],
            'user_games_no' => ['games',   'user@example.com', '123', false, false],
        ];
    }

    #[DataProvider('tablesGetDataProvider')]
    public function test_api_get_endpoints($route, $email, $password, $expectedStatus): void
    {
        $response = $this->login($email, $password);
        $response->assertStatus(200);
        $token = $this->myGetToken($response);

        $uri = "/api/$route";
        $response = $this->myGet($uri, $token);
        $response->assertStatus($expectedStatus);

        $this->logout($token);
    }

    #[DataProvider('tablesPostDeleteDataProvider')]
    public function test_api_post_delete_endpoints($route, $email, $password, $canPost, $canDelete): void
    {
        $response = $this->login($email, $password);
        $token = $this->myGetToken($response);

        // POST teszt
        $data = match($route) {
            'teams' => self::getSampleTeamData(),
            'sectors' => self::getSampleSectorData(),
            'games' => self::getSampleGameData(1, 2), // Példa ID-k
            default => []
        };

        $uri = "/api/$route";
        $response = $this->myPost($uri, $data, $token);
        
        if ($canPost) {
            $response->assertSuccessful();
        } else {
            $response->assertForbidden(); // Vagy assertStatus(403)
        }

        // DELETE teszt (létrehozunk egyet factoryval, majd törölni próbáljuk)
        $model = match($route) {
            'teams' => Team::factory()->create(),
            'sectors' => Sector::factory()->create(),
            'games' => Game::factory()->create(),
            default => null
        };

        if ($model) {
            $deleteUri = "/api/$route/{$model->id}";
            $response = $this->myDelete($deleteUri, $token);
            
            if ($canDelete) {
                $response->assertSuccessful();
            } else {
                $response->assertForbidden();
            }
        }

        $this->logout($token);
    }
}