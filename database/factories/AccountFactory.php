<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => $this->faker->phoneNumber(),
            'password' => bcrypt('password'),
            'kind' => $this->faker->numberBetween(1, 3),
            'full_name' => $this->faker->name(),
            'nickname' => $this->faker->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'avatar_path' => $this->faker->imageUrl(),
            'last_login' => $this->faker->dateTime(),
            'reset_pwd_code' => "kkask",
            'reset_pwd_time' => now(),
            'attempt_forget_pwd' => $this->faker->numberBetween(0, 5),
            'attempt_login' => $this->faker->numberBetween(0, 5),
            'is_super_admin' => $this->faker->boolean(),
            'role_id' => 1,  // Gán trực tiếp role_id = 1
            'status' => $this->faker->randomElement([0, 1]),
        ];
    }
}
