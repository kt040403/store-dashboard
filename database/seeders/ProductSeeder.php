<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['category_id' => 1, 'name' => 'セダンA',       'code' => 'NC-001', 'price' => 3000000],
            ['category_id' => 1, 'name' => 'SUV-B',         'code' => 'NC-002', 'price' => 4500000],
            ['category_id' => 1, 'name' => 'コンパクトC',    'code' => 'NC-003', 'price' => 2000000],
            ['category_id' => 2, 'name' => '中古セダンA',    'code' => 'UC-001', 'price' => 1500000],
            ['category_id' => 2, 'name' => '中古SUV-B',     'code' => 'UC-002', 'price' => 2500000],
            ['category_id' => 2, 'name' => '中古コンパクトC', 'code' => 'UC-003', 'price' => 1000000],
            ['category_id' => 3, 'name' => 'カーナビセット',  'code' => 'AC-001', 'price' => 150000],
            ['category_id' => 3, 'name' => 'ドライブレコーダー', 'code' => 'AC-002', 'price' => 50000],
            ['category_id' => 4, 'name' => '車検パック',     'code' => 'SV-001', 'price' => 80000],
            ['category_id' => 4, 'name' => 'オイル交換',     'code' => 'SV-002', 'price' => 5000],
            ['category_id' => 5, 'name' => '自動車保険プランA', 'code' => 'IN-001', 'price' => 120000],
            ['category_id' => 5, 'name' => '自動車保険プランB', 'code' => 'IN-002', 'price' => 80000],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}