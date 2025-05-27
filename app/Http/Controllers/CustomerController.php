<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $stores = DB::table('stores')->get();
        $storeId = $request->input('store_id');
        $now = Carbon::now()->startOfDay()->toDateString(); // 今日の日付（文字列）

        // 顧客情報 + 売上 + 平均RTを集計
        $customers = DB::table('customers')
            ->leftJoin('deliveries', 'customers.customer_id', '=', 'deliveries.customer_id')
            ->leftJoin('delivery_details', 'deliveries.delivery_id', '=', 'delivery_details.delivery_id')
            ->leftJoin('orders', 'customers.customer_id', '=', 'orders.customer_id')
            ->leftJoin('order_details', 'orders.order_id', '=', 'order_details.order_id')
            ->when($storeId, function ($query, $storeId) {
                return $query->where('customers.store_id', $storeId);
            })
            ->select(
                'customers.customer_id',
                'customers.name',
                'customers.registration_date',
                'customers.phone_number',
                DB::raw('COALESCE(SUM(DISTINCT delivery_details.unit_price * delivery_details.delivery_quantity), 0) as total_sales'),

                // RT関連：納品日と注文日の差（日数）× 数量（キャンセル除外）
                DB::raw('SUM(CASE 
                    WHEN order_details.cancell_flag = 0 THEN 
                        DATEDIFF(
                            CASE 
                                WHEN order_details.delivery_status = 1 THEN order_details.delivery_date
                                ELSE "' . $now . '"
                            END,
                            orders.order_date
                        ) * order_details.quantity
                    ELSE 0 END
                ) as total_weighted_days'),

                // RT関連：有効な数量（キャンセル除外）
                DB::raw('SUM(CASE 
                    WHEN order_details.cancell_flag = 0 THEN order_details.quantity
                    ELSE 0 END
                ) as total_quantity')
            )
            ->groupBy(
                'customers.customer_id',
                'customers.name',
                'customers.registration_date',
                'customers.phone_number'
            )
            ->orderBy('customers.customer_id')
            ->get()
            ->map(function ($customer) {
                $customer->average_rt = ($customer->total_quantity > 0)
                    ? round($customer->total_weighted_days / $customer->total_quantity, 2)
                    : null;
                return $customer;
            });

        return view('customers.index', [
            'customers' => $customers,
            'stores' => $stores,
            'selectedStoreId' => $storeId,
        ]);
    }
}

