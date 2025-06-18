<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryDetails; // DeliveryDetailモデルをuse
use App\Models\Deliveries; // Deliveryモデルをuse
use App\Models\OrderDetails; // OrderDetailモデルをuse
use Illuminate\Support\Facades\DB;

class DeliveryDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DeliveryDetails::truncate();

        $deliveryIds = Deliveries::pluck('delivery_id');
        // まだ配達されていない注文詳細を取得
        $orderDetails = OrderDetails::where('delivery_quantity', 0)->get();

        if ($deliveryIds->isEmpty() || $orderDetails->isEmpty()) {
            $this->command->warn('配達データまたは未配達の注文詳細データが存在しないため、DeliveryDetailsのSeedingをスキップしました。');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        // 50件、または未配達の注文詳細の件数のうち少ない方を作成
        $count = min(50, $orderDetails->count());

        for ($i = 0; $i < $count; $i++) {
            $orderDetail = $orderDetails->random();
            
            DeliveryDetails::create([
                'delivery_id' => $deliveryIds->random(),
                'order_id' => $orderDetail->order_id,
                'order_detail_id' => $orderDetail->order_detail_id,
                'product_name' => $orderDetail->product_name,
                'unit_price' => $orderDetail->unit_price,
                'delivery_quantity' => $orderDetail->quantity, // 注文数量をそのまま納品数量とする
                'remarks' => null,
                'return_flag' => false,
            ]);

            // 納品済みに更新
            $orderDetail->update([
                'delivery_quantity' => $orderDetail->quantity,
            ]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}