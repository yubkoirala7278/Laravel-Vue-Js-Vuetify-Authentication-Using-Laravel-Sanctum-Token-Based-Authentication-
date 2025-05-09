<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $colors = [
            'Red', 'Blue', 'Green', 'Yellow', 'Orange',
            'Purple', 'Pink', 'Brown', 'Black', 'White',
            'Gray', 'Cyan', 'Magenta', 'Lime', 'Teal',
            'Indigo', 'Violet', 'Maroon', 'Navy', 'Olive'
        ];

        foreach ($colors as $color) {
            DB::table('colors')->insert([
                'name' => $color,
                'slug' => Str::slug($color),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
