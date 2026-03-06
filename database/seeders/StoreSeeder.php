<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            ['area_id' => 1, 'name' => '東京中央店',   'code' => 'TK-001', 'address' => '東京都中央区銀座1-1-1',   'phone' => '03-1111-1111'],
            ['area_id' => 1, 'name' => '横浜港北店',   'code' => 'TK-002', 'address' => '神奈川県横浜市港北区1-2-3', 'phone' => '045-222-2222'],
            ['area_id' => 1, 'name' => 'さいたま大宮店', 'code' => 'TK-003', 'address' => '埼玉県さいたま市大宮区3-4-5', 'phone' => '048-333-3333'],
            ['area_id' => 2, 'name' => '大阪梅田店',   'code' => 'KS-001', 'address' => '大阪府大阪市北区梅田2-1-1', 'phone' => '06-4444-4444'],
            ['area_id' => 2, 'name' => '神戸三宮店',   'code' => 'KS-002', 'address' => '兵庫県神戸市中央区三宮1-1-1', 'phone' => '078-555-5555'],
            ['area_id' => 3, 'name' => '名古屋栄店',   'code' => 'CB-001', 'address' => '愛知県名古屋市中区栄3-1-1', 'phone' => '052-666-6666'],
            ['area_id' => 3, 'name' => '静岡駅前店',   'code' => 'CB-002', 'address' => '静岡県静岡市葵区紺屋町1-1', 'phone' => '054-777-7777'],
        ];

        foreach ($stores as $store) {
            Store::create($store);
        }
    }
}