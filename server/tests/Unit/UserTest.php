<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Ticket;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Tábla szerkezet ellenőrzése
     */
    public function test_user_table_has_expected_columns()
    {
        $columns = ['id', 'name', 'email', 'password', 'role'];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "A users táblából hiányzik a '$column' mező."
            );
        }
    }

    /**
     * Új felhasználó létrehozása és jelszó hashelés ellenőrzése
     */
    public function test_user_creation_and_password_hashing()
    {
        $rawPassword = 'secret-password-123';

        $user = User::create([
            'name' => 'Teszt Elek',
            'email' => 'teszt_' . uniqid() . '@example.com',
            'password' => Hash::make($rawPassword),
            'role' => 0
        ]);

        $this->assertDatabaseHas('users', ['email' => $user->email]);

        // Ellenőrizzük, hogy a jelszó nem nyers szövegként van tárolva
        $this->assertNotEquals($rawPassword, $user->password);

        // Ellenőrizzük, hogy a Hash::check felismeri-e
        $this->assertTrue(Hash::check($rawPassword, $user->password));
    }

    /**
     * Admin szerepkör (role) ellenőrzése
     */
    public function test_user_is_admin_method()
    {
        // Adjunk hozzá egy véletlenszerű számot vagy időbélyeget az emailhez
        $admin = User::factory()->create([
            'email' => 'admin_' . microtime(true) . '@test.hu',
            'role' => 1
        ]);

        $simpleUser = User::factory()->create([
            'email' => 'user_' . microtime(true) . '@test.hu',
            'role' => 0
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($simpleUser->isAdmin());
    }

    /**
     * Felhasználó és a jegyei közötti kapcsolat (Relationship)
     */
    public function test_user_has_many_tickets_relationship()
    {
        // Garantáltan egyedi email minden egyes hívásnál
        $user = User::factory()->create([
            'email' => 'user_' . microtime(true) . '@test.com'
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->tickets);
    }

    /**
     * Egyedi email cím kényszer ellenőrzése
     */
    public function test_email_must_be_unique()
    {
        $email = 'duplicate@example.com';

        User::create([
            'name' => 'User 1',
            'email' => $email,
            'password' => 'password',
            'role' => 0
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'name' => 'User 2',
            'email' => $email, // Ugyanaz az email -> hiba kell!
            'password' => 'password',
            'role' => 0
        ]);
    }
}
