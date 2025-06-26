<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['categoryName' => 'Writing & Drawing'],
            ['categoryName' => 'Paper Products'],
            ['categoryName' => 'Tools & Accessories'],
            ['categoryName' => 'Filing & Organizing'],
            ['categoryName' => 'Cleaning Essentials'],
            ['categoryName' => 'Technology & Electronics'],
            ['categoryName' => 'Office Supplies'],
            ['categoryName' => 'Facility & Utility'],
            ['categoryName' => 'Home Economics'],
        ]);
    }
}
