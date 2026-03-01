<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'image_path' => null,
            'postal_code' => $this->faker->postcode1 . '-' . $this->faker->numberBetween(1000, 9999),
            'address' => $this->faker->city . $this->faker->streetAddress,
            'building' => $this->faker->optional()->secondaryAddress,
        ];
    }
}