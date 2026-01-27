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
            'email' => 'admin@minta.hu',
            'role' => 1,
            'password' => '123',
        ]);

        User::factory()->count(10)->create();
    }
}
