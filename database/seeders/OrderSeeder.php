<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Orders; // Orderモデルをuse
use App\Models\Customers; // Customerモデルをuse
use Illuminate\Support\Facades\DB; // DBファサードをuse

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 外部キー制約を一時的に無効化
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // テーブルを空にする
        Orders::truncate();

        // 既存の顧客IDを取得
        $customerIds = Customers::pluck('customer_id');

        if ($customerIds->isEmpty()) {
            $this->command->error('Customersテーブルにデータが存在しません。先に顧客データを登録してください。');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        for ($i = 0; $i < 50; $i++) {
            Orders::create([
                'customer_id' => $customerIds->random(),
                'order_date' => fake()->dateTimeBetween('-2 year', 'now'),
                'remarks' => null,
            ]);
        }
        
        // 外部キー制約を再度有効化
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}