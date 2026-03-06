<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AreaSeeder::class,
            StoreSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
            SaleSeeder::class,
            MonthlyTargetSeeder::class,
        ]);
    }
}