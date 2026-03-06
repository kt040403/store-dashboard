<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();
        $products = Product::all();
        $startDate = now()->subMonths(12)->startOfMonth();
        $endDate = now()->subDay();

        $sales = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            foreach ($stores as $store) {
                // 1日あたり3〜8件の売上をランダム生成
                $dailyCount = rand(3, 8);

                for ($i = 0; $i < $dailyCount; $i++) {
                    $product = $products->random();
                    $quantity = $product->price >= 1000000 ? 1 : rand(1, 5);
                    $unitPrice = $product->price;
                    $total = $unitPrice * $quantity;

                    $sales[] = [
                        'store_id' => $store->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $total,
                        'sale_date' => $currentDate->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // 1000件ごとにバルクインサート（メモリ節約）
            if (count($sales) >= 1000) {
                Sale::insert($sales);
                $sales = [];
            }

            $currentDate->addDay();
        }

        // 残りを挿入
        if (!empty($sales)) {
            Sale::insert($sales);
        }
    }
}