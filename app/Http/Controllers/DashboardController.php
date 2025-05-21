<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// ↓ クラス名が Orders なので、as で別名をつける
use App\Models\Orders as Order;
use App\Models\Deliveries;
use App\Models\Customers;

class DashboardController extends Controller
{
    public function index()
    {
        $orderCount = Order::count();         // ← 別名 Order を使用
        $deliveryCount = Deliveries::count();
        $customerCount = Customers::count();

        return view('dashboard', compact('orderCount', 'deliveryCount', 'customerCount'));
    }
}
