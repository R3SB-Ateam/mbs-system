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

        // 注文明細ごとの集計サブクエリ
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
                // 納品済み分: (納品日 - 注文日 - 1) * 納品数量
                'COALESCE(SUM(GREATEST(0, DATEDIFF(d.delivery_date, o.order_date) - 1) * dd.delivery_quantity), 0) + ' .
                // 未納分: (今日 - 注文日 - 1) * 未納数量
                '(GREATEST(0, DATEDIFF(?, o.order_date) - 1) * (od.quantity - COALESCE(SUM(dd.delivery_quantity), 0))) as weighted_days_per_detail',
                [$today]
            )
            ->groupBy('od.order_detail_id', 'o.customer_id', 'o.order_date', 'od.quantity');

        // 顧客ごとの最終集計
        $customers = DB::table('customers')
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
            )
            ->orderBy('customers.customer_id')
            ->get()
            ->map(function ($customer) {
                // 平均リードタイムを計算
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
        ]);
    }
}