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

        // フィルタリング、検索条件をセッションから取得、またはリクエストから設定
        $storeId = $request->input('store_id');
        $keyword = $request->input('keyword'); // リクエストから直接取得

        // store_idのセッションハンドリングを改善
        if ($request->has('store_id')) {
            session(['customers_filter.store_id' => $storeId]);
        } elseif (session()->has('customers_filter.store_id')) {
            $storeId = session('customers_filter.store_id');
        } else {
            $storeId = '';
        }

        // キーワード検索の修正箇所
        if ($request->has('keyword')) {
            session(['customers_filter.keyword' => $keyword]);
        } else {
            $keyword = session('customers_filter.keyword');
        }
        if (!isset($keyword)) {
            $keyword = '';
        }

        // --- ソート条件の処理を開始 ---
        $requestedSortBy   = $request->input('sort_by');
        $sortOrders        = [];
        $sessionSortOrders = session('customers_filter.sort_orders');

        if ($request->has('sort_by')) {
            switch ($requestedSortBy) {
                case 'total_sales_asc':
                    $sortOrders = ['total_sales' => 'asc'];
                    break;
                case 'total_sales_desc':
                    $sortOrders = ['total_sales' => 'desc'];
                    break;
                case 'average_rt_asc':
                    $sortOrders = ['average_rt' => 'asc'];
                    break;
                case 'average_rt_desc':
                    $sortOrders = ['average_rt' => 'desc'];
                    break;
                case 'sales_main_rt_sub_asc':
                    $sortOrders = ['total_sales' => 'asc', 'average_rt' => 'asc'];
                    break;
                case 'sales_main_rt_sub_desc':
                    $sortOrders = ['total_sales' => 'desc', 'average_rt' => 'asc'];
                    break;
                case 'rt_main_sales_sub_asc':
                    $sortOrders = ['average_rt' => 'asc', 'total_sales' => 'asc'];
                    break;
                case 'rt_main_sales_sub_desc':
                    $sortOrders = ['average_rt' => 'desc', 'total_sales' => 'asc'];
                    break;
                case '': // ソートなし (顧客ID昇順)
                    $sortOrders = ['customer_id' => 'asc'];
                    break;
                default:
                    $sortOrders = ['customer_id' => 'asc'];
                    \Log::warning('Unknown sort_by value received: ' . $requestedSortBy);
                    break;
            }
        } elseif ($sessionSortOrders && is_array($sessionSortOrders)) {
            $sortOrders = $sessionSortOrders;
        } else {
            $sortOrders = ['customer_id' => 'asc'];
        }

        session(['customers_filter.sort_orders' => $sortOrders]);
        // --- ソート条件の処理を終了 ---

        // ドロップダウンのselected状態を制御するための値を決定
        $selectedSortValue = ''; // デフォルトは「ソートなし」に対応する空文字列

        if ($sortOrders === ['customer_id' => 'asc']) {
            $selectedSortValue = '';
        } elseif ($sortOrders === ['total_sales' => 'asc']) {
            $selectedSortValue = 'total_sales_asc';
        } elseif ($sortOrders === ['total_sales' => 'desc']) {
            $selectedSortValue = 'total_sales_desc';
        } elseif ($sortOrders === ['average_rt' => 'asc']) {
            $selectedSortValue = 'average_rt_asc';
        } elseif ($sortOrders === ['average_rt' => 'desc']) {
            $selectedSortValue = 'average_rt_desc';
        } elseif ($sortOrders === ['total_sales' => 'asc', 'average_rt' => 'asc']) {
            $selectedSortValue = 'sales_main_rt_sub_asc';
        } elseif ($sortOrders === ['total_sales' => 'desc', 'average_rt' => 'asc']) {
            $selectedSortValue = 'sales_main_rt_sub_desc';
        } elseif ($sortOrders === ['average_rt' => 'asc', 'total_sales' => 'asc']) {
            $selectedSortValue = 'rt_main_sales_sub_asc';
        } elseif ($sortOrders === ['average_rt' => 'desc', 'total_sales' => 'asc']) {
            $selectedSortValue = 'rt_main_sales_sub_desc';
        }

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
                'COALESCE(SUM(GREATEST(0, DATEDIFF(d.delivery_date, o.order_date) - 1) * dd.delivery_quantity), 0) + ' .
                '(GREATEST(0, DATEDIFF(?, o.order_date) - 1) * (od.quantity - COALESCE(SUM(dd.delivery_quantity), 0))) as weighted_days_per_detail',
                [$today]
            )
            ->groupBy('od.order_detail_id', 'o.customer_id', 'o.order_date', 'od.quantity');

        // 顧客ごとの最終集計
        $customersQuery = DB::table('customers')
            ->leftJoinSub($orderDetailsSummary, 'summary', function ($join) {
                $join->on('customers.customer_id', '=', 'summary.customer_id');
            })
            ->where('customers.deletion_flag', 0) 
            ->when($storeId, function ($query, $storeId) {
                return $query->where('customers.store_id', $storeId);
            })
            ->when($keyword, function ($query, $keyword) {
                return $query->where(function ($q) use ($keyword) {
                    $q->where('customers.name', 'like', "%{$keyword}%")
                      ->orWhere('customers.phone_number', 'like', "%{$keyword}%")
                      ->orWhere('customers.staff', 'like', "%{$keyword}%")
                      ->orWhere('customers.address', 'like', "%{$keyword}%")
                      ->orWhere('customers.delivery_location', 'like', "%{$keyword}%");
                });
            })
            ->select(
                'customers.customer_id',
                'customers.name',
                'customers.registration_date',
                'customers.phone_number',
                'customers.address',
                'customers.delivery_location',
                'customers.staff as staff_name'
            )
            ->selectRaw('COALESCE(SUM(summary.sales_per_detail), 0) as total_sales')
            ->selectRaw('COALESCE(SUM(summary.weighted_days_per_detail), 0) as total_weighted_days')
            ->selectRaw('COALESCE(SUM(summary.total_order_quantity), 0) as total_quantity')
            ->groupBy(
                'customers.customer_id',
                'customers.name',
                'customers.registration_date',
                'customers.phone_number',
                'customers.address',
                'customers.delivery_location',
                'customers.staff'
            );

        // ★ ここから追加/修正: ソートが「ソートなし」ではない場合にのみフィルタリングを適用 ★
        if ($selectedSortValue !== '') { // selectedSortValue が空文字列（ソートなし）ではない場合
            $customersQuery->having('total_sales', '>', 0);
        }
        // ★ ここまで追加/修正 ★

        // ソートロジックを動的に適用
        foreach ($sortOrders as $column => $order) {
            if ($column === 'total_sales') {
                $customersQuery->orderByRaw('total_sales ' . ($order === 'desc' ? 'DESC' : 'ASC'));
            } elseif ($column === 'average_rt') {
                // average_rtは最終的にPHPで計算されるため、ここではDBレベルのソートは行わない。
                // ただし、DBレベルでソートする必要がある場合は、SQLでの計算式をorderByRawに含める。
                // 現在のロジックでは、PHPで計算後にコレクションをソートする。
            } elseif ($column === 'customer_id') {
                $customersQuery->orderBy('customers.customer_id', $order);
            }
        }

        $customers = $customersQuery->get()
            ->map(function ($customer) {
                // average_rtの計算
                $customer->average_rt = ($customer->total_quantity > 0)
                    ? round($customer->total_weighted_days / $customer->total_quantity, 2)
                    : null;
                unset($customer->total_weighted_days, $customer->total_quantity);
                return $customer;
            });

        // ★ ここから追加/修正: ソートが「ソートなし」ではない場合にのみPHPでのフィルタリングを適用 ★
        if ($selectedSortValue !== '') { // selectedSortValue が空文字列（ソートなし）ではない場合
            $customers = $customers->filter(function ($customer) {
                // average_rtがnullでないこと、かつ文字列のハイフンでないことを確認
                return $customer->average_rt !== null && $customer->average_rt !== '-';
            });
        }
        // ★ ここまで追加/修正 ★

        // PHPで計算されたaverage_rtに基づいてソートを適用
        if (isset($sortOrders['average_rt'])) {
            $customers = $customers->sortBy(function ($customer) {
                return $customer->average_rt;
            }, SORT_NUMERIC, $sortOrders['average_rt'] === 'desc');
        } elseif (isset($sortOrders['total_sales']) && isset($sortOrders['average_rt'])) {
             $customers = $customers->sortBy(function ($customer) {
                 return $customer->total_sales;
             }, SORT_NUMERIC, $sortOrders['total_sales'] === 'desc')
             ->sortBy(function ($customer) {
                 return $customer->average_rt;
             }, SORT_NUMERIC, $sortOrders['average_rt'] === 'desc');
        }

        return view('customers.index', [
            'customers'         => $customers,
            'stores'            => $stores,
            'selectedStoreId'   => $storeId,
            'keyword'           => $keyword,
            'sortOrders'        => $sortOrders,
            'selectedSortValue' => $selectedSortValue,
        ]);
    }
}