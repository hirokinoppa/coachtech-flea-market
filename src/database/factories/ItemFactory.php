<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => $this->faker->word(),
            'description' => $this->faker->sentence(10),
            'brand'       => $this->faker->optional()->word(),
            'image_path'  => null,
            'condition'   => $this->faker->randomElement(['good', 'fair', 'poor', 'bad']),
            'price'       => $this->faker->numberBetween(300, 50000),
            'is_sold'     => false,
            'sold_at'     => null,
        ];
    }

    public function sold(): self
    {
        return $this->state([
            'is_sold' => true,
            'sold_at' => now(),
        ]);
    }
}