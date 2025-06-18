<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 既存のデータを削除
        DB::table('stores')->delete();
        // 主キーの採番をリセット
        DB::statement('ALTER TABLE stores AUTO_INCREMENT = 1;');

        DB::table('stores')->insert([
            ['store_id' => 1, 'store_name' => '緑橋店'],
            ['store_id' => 2, 'store_name' => '今里店'],
            ['store_id' => 3, 'store_name' => '深江橋店'],
        ]);
    }
}