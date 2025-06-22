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

    if ($request->has('store_id')) {
        session(['customers_filter.store_id' => $request->input('store_id')]);
    }

    $storeId = $request->input('store_id', session('customers_filter.store_id'));
    $today = Carbon::now()->toDateString();

    // ① 並び順の入力を取得
    $sortParams = $request->input('sort', []);
    $sortableColumns = [
        'customer_id' => 'customers.customer_id',
        'total_sales' => 'total_sales',
        'average_rt' => 'average_rt',
    ];

    // ② 注文明細ごとの集計サブクエリ
    $orderDetailsSummary = DB::table('order_details as od')
        ->join('orders as o', 'od.order_id', '=', 'o.order_id')
        ->leftJoin('delivery_details as dd', function ($join) {
            $join->on('od.order_detail_id', '=', 'dd.order_detail_id')
                 ->where('dd.return_flag', '=', 0);
        })
        ->leftJoin('deliveries as d', 'dd.delivery_id', '=', 'd.delivery_id')
        ->where('od.cancell_flag', 0)
        ->select(
            'o.customer_id',
            'od.quantity as total_order_quantity'
        )
        ->selectRaw('COALESCE(SUM(dd.unit_price * dd.delivery_quantity), 0) as sales_per_detail')
        ->selectRaw(
            'COALESCE(SUM(GREATEST(0, DATEDIFF(d.delivery_date, o.order_date)) * dd.delivery_quantity), 0) + ' .
            '(GREATEST(0, DATEDIFF(?, o.order_date)) * (od.quantity - COALESCE(SUM(dd.delivery_quantity), 0))) as weighted_days_per_detail',
            [$today]
        )
        ->groupBy('od.order_detail_id', 'o.customer_id', 'o.order_date', 'od.quantity');

    // ③ 顧客ごとの集計
    $customersQuery = DB::table('customers')
        ->leftJoinSub($orderDetailsSummary, 'summary', function ($join) {
            $join->on('customers.customer_id', '=', 'summary.customer_id');
        })
        ->when($storeId, function ($query, $storeId) {
            return $query->where('customers.store_id', $storeId);
        })
        ->select(
            'customers.customer_id',
            'customers.name',
            'customers.registration_date',
            'customers.phone_number'
        )
        ->selectRaw('COALESCE(SUM(summary.sales_per_detail), 0) as total_sales')
        ->selectRaw('COALESCE(SUM(summary.weighted_days_per_detail), 0) as total_weighted_days')
        ->selectRaw('COALESCE(SUM(summary.total_order_quantity), 0) as total_quantity')
        ->groupBy(
            'customers.customer_id',
            'customers.name',
            'customers.registration_date',
            'customers.phone_number'
        );

    // ④ ソートの適用（優先順に複数 orderBy）
    $hasSort = false;
    foreach ($sortableColumns as $key => $column) {
        if (!empty($sortParams[$key]) && in_array($sortParams[$key], ['asc', 'desc'])) {
            $customersQuery->orderBy($column, $sortParams[$key]);
            $hasSort = true;
        }
    }

    // ⑤ デフォルトで顧客ID昇順
    if (!$hasSort) {
        $customersQuery->orderBy('customers.customer_id', 'asc');
    }

    // ⑥ データ取得と平均RT計算
    $customers = $customersQuery->get()->map(function ($customer) {
        $customer->average_rt = ($customer->total_quantity > 0)
            ? round($customer->total_weighted_days / $customer->total_quantity, 2)
            : null;
        unset($customer->total_weighted_days, $customer->total_quantity);
        return $customer;
    });

    return view('customers.index', [
        'customers' => $customers,
        'stores' => $stores,
        'selectedStoreId' => $storeId,
        'sortOrders' => $sortParams,
    ]);
}

}