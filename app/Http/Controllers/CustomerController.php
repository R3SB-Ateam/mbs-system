<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\OrderDetails;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $stores = DB::table('stores')->get();
        $storeId = $request->input('store_id');

        $customers = DB::table('customers')
            ->leftJoin('deliveries', 'customers.customer_id', '=', 'deliveries.customer_id')
            ->leftJoin('delivery_details', 'deliveries.delivery_id', '=', 'delivery_details.delivery_id')
            ->when($storeId, function ($query, $storeId) {
                return $query->where('customers.store_id', $storeId);
            })
            ->select(
                'customers.customer_id',
                'customers.name',
                'customers.registration_date',
                'customers.phone_number',
                DB::raw('COALESCE(SUM(delivery_details.unit_price * delivery_details.delivery_quantity), 0) as total_sales')
            )
            ->groupBy(
                'customers.customer_id',
                'customers.name',
                'customers.registration_date',
                'customers.phone_number',
            )
            ->orderBy('customers.customer_id')
            ->get();

        return view('customers.index', [
            'customers' => $customers,
            'stores' => $stores,
            'selectedStoreId' => $storeId,
        ]);
    }

}
