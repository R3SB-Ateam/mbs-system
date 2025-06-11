<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// ↓ クラス名が Orders なので、as で別名をつける
use App\Models\Orders as Order;
use App\Models\Deliveries;
use App\Models\Customers;
use App\Models\Store;  // 例：店舗モデルが Store なら


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'all');
        if (!in_array($filter, ['all', 'recent'], true)) {
            $filter = 'all';
        }

        $stores = Store::all();
        $selectedStoreId = $request->input('store_id', '');

        $oneWeekAgo = Carbon::now()->subDays(7);

        // 注文数
        $orderQuery = DB::table('orders')
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id');

        // 納品数（order_id 経由ではなく customer_id で直接つなぐ）
        $deliveryQuery = DB::table('deliveries')
            ->join('customers', 'deliveries.customer_id', '=', 'customers.customer_id');

        // 合計金額（order_details → orders → customers）
        $priceQuery = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id');


        // 店舗絞り込み
        if (!empty($selectedStoreId)) {
            $orderQuery->where('customers.store_id', $selectedStoreId);
            $deliveryQuery->where('customers.store_id', $selectedStoreId);
            $priceQuery->where('customers.store_id', $selectedStoreId);
        }

        // 日付絞り込み（直近1週間のみ）
        if ($filter === 'recent') {
            $orderQuery->where('orders.order_date', '>=', $oneWeekAgo);
            $deliveryQuery->where('deliveries.delivery_date', '>=', $oneWeekAgo);
            $priceQuery->where('orders.order_date', '>=', $oneWeekAgo); // 商品ごとの注文日でフィルター
        }

        // 実行
        $orderCount = $orderQuery->count();
        $deliveryCount = $deliveryQuery->count();
        $total_price = number_format($priceQuery->sum(DB::raw('order_details.unit_price * order_details.quantity')));

        return view('dashboard', compact(
            'orderCount',
            'deliveryCount',
            'total_price',
            'filter',
            'stores',
            'selectedStoreId'
        ));
    }
}
