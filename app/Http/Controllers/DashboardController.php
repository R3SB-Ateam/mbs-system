<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
// ↓ クラス名が Orders なので、as で別名をつける
use App\Models\Orders as Order;
use App\Models\Deliveries;
use App\Models\Customers;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter','all'); // 'all' or 'recent'

        if (!in_array($filter,['all','recent'],true)){
            $filter = 'all'; // 無効な値ならデフォルトに戻す
        }

        if ($filter === 'recent') {
            
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();

            // 注文件数,納品件数,顧客数（過去二日）
            $orderCount = Order::where('order_date','>=',$yesterday)->count();
            $deliveryCount = Deliveries::where('delivery_date','>=',$yesterday)->count();
            $customerCount = Customers::where('registration_date','>=',$yesterday)->count();
        }else{
            // 合計件数（全体）
            $orderCount = Order::count();         // ← 別名 Order を使用
            $deliveryCount = Deliveries::count();
            $customerCount = Customers::count();
        }
        return view('dashboard', compact('orderCount', 'deliveryCount', 'customerCount','filter'));
    }
}
