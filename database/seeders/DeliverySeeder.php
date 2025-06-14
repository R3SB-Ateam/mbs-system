<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deliveries; // Deliveryモデルをuse
use App\Models\Customers; // Customersモデルをuse
use Illuminate\Support\Facades\DB;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Deliveries::truncate();

        $customersIds = Customers::pluck('customer_id');

        if ($customersIds->isEmpty()) {
            $this->command->error('Customersテーブルにデータが存在しません。先に顧客データを登録してください。');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        for ($i = 0; $i < 50; $i++) {
            Deliveries::create([
                'customer_id' => $customersIds->random(),
                'delivery_date' => fake()->dateTimeBetween('-1 year', 'now'),
                'remarks' => null,
            ]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}