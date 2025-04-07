<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Electronics',
            'Fashion',
            'Home & Kitchen',
            'Books',
            'Beauty & Personal Care',
            'Health & Household',
            'Toys & Games',
            'Sports & Outdoors',
            'Automotive',
            'Grocery',
            'Pet Supplies',
            'Office Products',
            'Garden & Outdoor',
            'Baby',
            'Tools & Home Improvement',
            'Jewelry',
            'Shoes',
            'Watches',
            'Luggage & Travel',
            'Video Games',
            'Music Instruments',
            'Handmade',
            'Industrial & Scientific',
            'Software',
            'Cell Phones',
            'Appliances',
            'Movies & TV',
            'Arts & Crafts',
            'Collectibles',
            'Musical CDs'
        ];

        foreach ($categories as $name) {
            DB::table('categories')->insert([
                'name' => $name,
                'slug' => Str::slug($name),
                'image' => 'categories/X36DpCkGutI7XpdH7Zrofl2dQ9ov0bTmSplz3hZd.png',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
