<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
         User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 1,
            'billing_city' => 'Budapest',
            'billing_zip' => '1111',
            'billing_address' => 'Fő utca 1.',
            'password' => '123',
        ]);

        User::factory()->count(10)->create();
    }
}
