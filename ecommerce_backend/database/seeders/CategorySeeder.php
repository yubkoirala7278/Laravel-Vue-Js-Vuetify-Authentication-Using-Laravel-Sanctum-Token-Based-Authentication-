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

        $subCategoriesList = [
            'Electronics' => ['Mobile Phones', 'Laptops', 'Cameras', 'Headphones', 'Smart Watches'],
            'Fashion' => ['Men\'s Clothing', 'Women\'s Clothing', 'Kids\' Wear', 'Footwear', 'Watches'],
            'Home & Kitchen' => ['Furniture', 'Cookware', 'Home Decor', 'Storage & Organization', 'Bedding'],
            'Books' => ['Fiction', 'Non-Fiction', 'Children\'s Books', 'Textbooks', 'Self Help'],
            'Beauty & Personal Care' => ['Skincare', 'Makeup', 'Hair Care', 'Fragrances', 'Bath & Body'],
            'Health & Household' => ['Vitamins', 'First Aid', 'Personal Care Appliances', 'Medical Supplies', 'Cleaning Supplies'],
            'Toys & Games' => ['Board Games', 'Action Figures', 'Educational Toys', 'Dolls', 'Outdoor Play'],
            'Sports & Outdoors' => ['Exercise & Fitness', 'Camping & Hiking', 'Cycling', 'Team Sports', 'Water Sports'],
            'Automotive' => ['Car Electronics', 'Exterior Accessories', 'Interior Accessories', 'Motorcycle Parts', 'Tires & Wheels'],
            'Grocery' => ['Snacks', 'Beverages', 'Cooking Essentials', 'Dairy Products', 'Canned Goods'],
            'Pet Supplies' => ['Dog Food', 'Cat Toys', 'Pet Grooming', 'Aquarium Supplies', 'Pet Beds'],
            'Office Products' => ['Stationery', 'Office Furniture', 'Printers', 'Paper', 'Office Storage'],
            'Garden & Outdoor' => ['Outdoor Furniture', 'Gardening Tools', 'Grills', 'Plant Seeds', 'Patio Décor'],
            'Baby' => ['Diapers', 'Baby Food', 'Strollers', 'Baby Gear', 'Nursing & Feeding'],
            'Tools & Home Improvement' => ['Power Tools', 'Hand Tools', 'Lighting', 'Electrical', 'Hardware'],
            'Jewelry' => ['Necklaces', 'Earrings', 'Rings', 'Bracelets', 'Jewelry Sets'],
            'Shoes' => ['Men\'s Shoes', 'Women\'s Shoes', 'Kids\' Shoes', 'Sneakers', 'Boots'],
            'Watches' => ['Analog Watches', 'Digital Watches', 'Smart Watches', 'Luxury Watches', 'Sports Watches'],
            'Luggage & Travel' => ['Suitcases', 'Backpacks', 'Travel Accessories', 'Duffel Bags', 'Carry-ons'],
            'Video Games' => ['PlayStation Games', 'Xbox Games', 'Nintendo Games', 'Gaming Accessories', 'PC Games'],
            'Music Instruments' => ['Guitars', 'Keyboards', 'Drums', 'DJ Equipment', 'Recording Equipment'],
            'Handmade' => ['Handmade Jewelry', 'Handcrafted Home Décor', 'Artisanal Soaps', 'Crochet Items', 'Knitted Wear'],
            'Industrial & Scientific' => ['Lab Supplies', 'Test Instruments', 'Janitorial Supplies', 'Professional Tools', 'Safety Equipment'],
            'Software' => ['Antivirus', 'Operating Systems', 'Office Suites', 'Design Software', 'Utility Software'],
            'Cell Phones' => ['Smartphones', 'Phone Cases', 'Chargers', 'Screen Protectors', 'Power Banks'],
            'Appliances' => ['Refrigerators', 'Microwaves', 'Washing Machines', 'Vacuum Cleaners', 'Air Conditioners'],
            'Movies & TV' => ['DVDs', 'Blu-rays', 'TV Series', 'Movie Box Sets', 'Documentaries'],
            'Arts & Crafts' => ['Painting Supplies', 'Craft Kits', 'Drawing Tools', 'Sculpting Materials', 'Sewing & Embroidery'],
            'Collectibles' => ['Coins', 'Stamps', 'Action Figures', 'Autographed Memorabilia', 'Trading Cards'],
            'Musical CDs' => ['Rock', 'Pop', 'Classical', 'Jazz', 'Hip Hop'],
        ];

        foreach ($categories as $index => $name) {
            $categoryId = DB::table('categories')->insertGetId([
                'name' => $name,
                'slug' => Str::slug($name),
                'image' => 'categories/DCqJ0UN2W4TazthTgjv4sPi764sq791fPsqCek9l.png',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $subCats = $subCategoriesList[$name] ?? [];

            foreach ($subCats as $subCatName) {
                DB::table('sub_categories')->insert([
                    'name' => $subCatName,
                    'slug' => Str::slug($subCatName . '-' . Str::random(4)),
                    'category_id' => $categoryId,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
