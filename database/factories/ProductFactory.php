<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'productName' => $this->faker->word(),
            'brandName' => $this->faker->company(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'category_id' => Category::factory(), // creates a category if none exists
            'unit' => $this->faker->randomElement(['pc', 'box', 'pack', 'set']),
            'quantity' => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->sentence(),
            'image' => null, // or use: $this->faker->imageUrl(640, 480, 'product')
            'user_id' => User::factory(), // creates a user if none exists
        ];
    }
}
