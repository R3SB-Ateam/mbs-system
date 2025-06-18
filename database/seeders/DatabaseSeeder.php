<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // データの依存関係を考慮し、この順序で呼び出す
        $this->call([
            StoreSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
            OrderDetailSeeder::class,
            DeliverySeeder::class,
            DeliveryDetailSeeder::class,
        ]);
    }
}