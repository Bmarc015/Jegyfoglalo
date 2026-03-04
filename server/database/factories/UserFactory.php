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
            'name' => 'Customer' . $current,
            'email' => 'customer' . $current . "@example.com",
            'role' => 2,
            'billing_city' => $this->faker->city(),
            'billing_zip' => $this->faker->numerify('####'),
            'billing_address' => $this->faker->streetAddress(),
            'email_verified_at' => now(),
            'password' => Hash::make('123'),
        ];
    }
}
