<?php

namespace Database\Seeders;

use App\Models\MonthlyTarget;
use App\Models\Store;
use Illuminate\Database\Seeder;

class MonthlyTargetSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();
        $startMonth = now()->subMonths(12)->startOfMonth();

        foreach ($stores as $store) {
            $currentMonth = $startMonth->copy();

            for ($i = 0; $i < 13; $i++) {
                // 店舗ごとに基準額を変える（規模感の差を表現）
                $baseAmount = match (true) {
                    str_contains($store->code, 'TK') => rand(15000000, 25000000),
                    str_contains($store->code, 'KS') => rand(12000000, 20000000),
                    default => rand(10000000, 18000000),
                };

                MonthlyTarget::create([
                    'store_id' => $store->id,
                    'year_month' => $currentMonth->format('Y-m'),
                    'target_amount' => $baseAmount,
                ]);

                $currentMonth->addMonth();
            }
        }
    }
}