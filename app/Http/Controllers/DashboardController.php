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
        // URLパラメータかセッションの順で絞り込み条件を取得
        $selectedStoreId = $request->input('store_id', session('dashboard_store_id', ''));
        if ($selectedStoreId === null || $selectedStoreId === '') {
            $selectedStoreId = '';
        }

        $filter = $request->input('filter', session('dashboard_filter', 'all'));
        if (!in_array($filter, ['all', 'recent'], true)) {
            $filter = 'all';
        }

        // 絞り込み条件をセッションに保存（次回アクセス用）
        session([
            'dashboard_store_id' => $selectedStoreId,
            'dashboard_filter' => $filter,
        ]);

        $stores = Store::all();
        $oneWeekAgo = Carbon::now()->subDays(7);

        $orderQuery = DB::table('orders')
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id');

        $deliveryQuery = DB::table('deliveries')
            ->join('customers', 'deliveries.customer_id', '=', 'customers.customer_id');

        $priceQuery = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id');

        if (!empty($selectedStoreId)) {
            $orderQuery->where('customers.store_id', $selectedStoreId);
            $deliveryQuery->where('customers.store_id', $selectedStoreId);
            $priceQuery->where('customers.store_id', $selectedStoreId);
        }

        if ($filter === 'recent') {
            $orderQuery->where('orders.order_date', '>=', $oneWeekAgo);
            $deliveryQuery->where('deliveries.delivery_date', '>=', $oneWeekAgo);
            $priceQuery->where('orders.order_date', '>=', $oneWeekAgo);
        }

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
