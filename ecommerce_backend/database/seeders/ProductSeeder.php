<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch IDs for related tables
        $categories = Category::pluck('id')->toArray();
        $subCategories = SubCategory::pluck('id')->toArray();
        $brands = Brand::pluck('id')->toArray();
        $colors = Color::pluck('id')->toArray();

        // Check if required related data exists
        if (empty($categories)) {
            $this->command->warn('No categories found. Please seed categories first.');
            return;
        }

        if (empty($subCategories)) {
            $this->command->info('No sub-categories found. Sub-category fields will be null.');
        }

        if (empty($brands)) {
            $this->command->info('No brands found. Brand fields will be null.');
        }

        if (empty($colors)) {
            $this->command->info('No colors found. Color fields will be null.');
        }

        // Create 30 products
        for ($i = 1; $i <= 30; $i++) {
            $name = 'Product ' . $i;
            $slug = Str::slug($name . '-' . fake()->unique()->numberBetween(1000, 9999)); // Ensure unique slug

            Product::create([
                'slug'          => $slug,
                'name'          => $name,
                'description'   => fake()->paragraph(4),
                'price'         => fake()->randomFloat(2, 10, 500),
                'compare_price' => fake()->optional(0.7)->randomFloat(2, 20, 600), // 70% chance of having a compare price
                'image'         => 'categories/DCqJ0UN2W4TazthTgjv4sPi764sq791fPsqCek9l.png',
                'is_featured'   => fake()->randomElement(['Yes', 'No']),
                'status'        => fake()->randomElement(['active', 'inactive']),
                'category_id'   => fake()->randomElement($categories),
                'sub_category_id' => !empty($subCategories) ? fake()->randomElement($subCategories) : null,
                'brand_id'      => !empty($brands) ? fake()->randomElement($brands) : null,
                'color_id'      => !empty($colors) ? fake()->randomElement($colors) : null,
            ]);
        }

        $this->command->info('Successfully seeded 30 products.');
    }
}
