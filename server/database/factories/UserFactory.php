<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        static $number = 0;
        $current = $number;   // eltesszük az aktuális értéket
        $number++;

        return [
            'name' => 'Vásárló' . $current,
            'email' => 'vasarlo' . $current . "@minta.hu",
            'role' => 2,
            'email_verified_at' => now(),
            'password' => Hash::make('123'),
        ];
    }
}
