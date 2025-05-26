<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
            $oneWeekAgo = Carbon::now()->subDays(7);

            // 注文件数,納品件数,顧客数（過去一週間）
            $orderCount = Order::where('order_date','>=',$oneWeekAgo)->count();
            $deliveryCount = Deliveries::where('delivery_date','>=',$oneWeekAgo)->count();
            $total_price = DB::table('order_details')->where('unit_price', '>=', $oneWeekAgo)->sum('unit_price');
            $total_price = number_format($total_price);
        }else{
            // 合計件数（全体）
            $orderCount = Order::count();         // ← 別名 Order を使用
            $deliveryCount = Deliveries::count();
            $total_price = DB::table('order_details')->sum('unit_price');
            $total_price = number_format($total_price);
        }
        return view('dashboard', compact('orderCount', 'deliveryCount', 'total_price','filter'));
    }
}
