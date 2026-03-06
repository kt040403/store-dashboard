<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['name' => '関東エリア', 'region' => '関東'],
            ['name' => '関西エリア', 'region' => '関西'],
            ['name' => '中部エリア', 'region' => '中部'],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}