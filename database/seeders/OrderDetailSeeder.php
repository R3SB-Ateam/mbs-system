<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderDetails; // OrdersDetailモデルをuse
use App\Models\Orders; // Ordersモデルをuse
use Illuminate\Support\Facades\DB;

class OrderDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OrderDetails::truncate();

        $ordersIds = Orders::pluck('order_id');

        if ($ordersIds->isEmpty()) {
            $this->command->error('Ordersテーブルにデータが存在しません。先に注文データを登録してください。');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        $products = ['商品A', '商品B', '商品C', '商品D', '商品E', '雑誌X', '雑誌Y', '新聞Z'];

        for ($i = 0; $i < 50; $i++) {
            OrderDetails::create([
                'order_id' => $ordersIds->random(),
                'product_name' => $products[array_rand($products)],
                'unit_price' => fake()->numberBetween(50, 100) * 10,
                'quantity' => fake()->numberBetween(1, 5),
                'delivery_quantity' => 0, // 初期値
                'delivery_date' => null,
                'remarks' => null,
                'cancell_flag' => false,
            ]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}