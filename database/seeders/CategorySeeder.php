<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => '新車', 'sort_order' => 1],
            ['name' => '中古車', 'sort_order' => 2],
            ['name' => '部品・アクセサリー', 'sort_order' => 3],
            ['name' => '整備・車検', 'sort_order' => 4],
            ['name' => '保険', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}