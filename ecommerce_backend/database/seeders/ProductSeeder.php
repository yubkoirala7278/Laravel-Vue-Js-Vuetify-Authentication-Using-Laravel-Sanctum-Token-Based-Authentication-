<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::pluck('id')->toArray();

        if (empty($categories)) {
            $this->command->warn('No categories found. Please seed categories first.');
            return;
        }

        for ($i = 1; $i <= 30; $i++) {
            $name = 'Product ' . $i;
            Product::create([
                'name'          => $name,
                'description'   => fake()->paragraph(4),
                'price'         => fake()->randomFloat(2, 10, 500),
                'compare_price' => fake()->optional()->randomFloat(2, 20, 600),
                'image'         => 'categories/X36DpCkGutI7XpdH7Zrofl2dQ9ov0bTmSplz3hZd.png',
                'is_featured'   => fake()->randomElement(['Yes', 'No']),
                'status'        => fake()->randomElement(['active', 'inactive']),
                'category_id'   => fake()->randomElement($categories),
            ]);
        }
    }
}
