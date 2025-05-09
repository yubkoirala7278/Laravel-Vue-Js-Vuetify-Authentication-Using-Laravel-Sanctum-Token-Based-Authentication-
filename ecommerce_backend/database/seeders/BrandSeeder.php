<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $brands = [
            'Nike',
            'Adidas',
            'Puma',
            'Reebok',
            'Under Armour',
            'Apple',
            'Samsung',
            'Sony',
            'LG',
            'Dell',
            'HP',
            'Lenovo',
            'Asus',
            'Acer',
            'Microsoft',
            'Canon',
            'Nikon',
            'Panasonic',
            'GoPro',
            'Philips',
            'Bose',
            'JBL',
            'Beats',
            'Xiaomi',
            'OnePlus',
            'Vivo',
            'Oppo',
            'Realme',
            'Motorola',
            'Huawei',
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->insert([
                'name' => $brand,
                'slug' => Str::slug($brand),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
