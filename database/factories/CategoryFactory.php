<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'categoryName' => $this->faker->unique()->randomElement([
                'Writing & Drawing',
                'Paper Products',
                'Tools & Accessories',
                'Filing & Organizing',
                'Cleaning Essentials',
                'Technology & Electronics',
                'Office Supplies',
                'Facility & Utility',
                'Home Economics',
            ]),
            'description' => $this->faker->sentence(),
            'image' => null,
        ];
    }
}
