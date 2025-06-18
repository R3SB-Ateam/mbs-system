<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /**
     * 納品一覧を表示します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 選択された店舗IDを取得
        $selectedStoreId = $request->input('store_id');
        // キーワードを取得
        $keyword = $request->input('keyword');

        // 店舗一覧を取得（フィルタリング用）
        $stores = DB::table('stores')->get();

        // 納品データを取得
        $query = DB::table('deliveries')
                    ->join('customers', 'deliveries.customer_id', '=', 'customers.customer_id')
                    ->select('deliveries.*', 'customers.name as customer_name');

        // 店舗によるフィルタリング
        if ($selectedStoreId) {
            $query->where('deliveries.store_id', $selectedStoreId);
        }

        // キーワードによる検索
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('deliveries.delivery_id', 'like', '%' . $keyword . '%')
                  ->orWhere('customers.name', 'like', '%' . $keyword . '%')
                  ->orWhere('deliveries.remarks', 'like', '%' . $keyword . '%');
            });
        }

        // 納品日順にソート（新しいものが上に来るように）
        $deliveries = $query->orderBy('deliveries.delivery_date', 'desc')->get();

        return view('deliveries.index', compact('deliveries', 'stores', 'selectedStoreId', 'keyword'));
    }

    // ... show メソッドや showReturnForm メソッドなど、他の既存のメソッド ...

    /**
     * 指定された納品の詳細を表示します。
     *
     * @param string $delivery_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(string $delivery_id)
    {
        // 1. 納品情報と顧客名を取得
        $delivery = DB::table('deliveries')
            ->join('customers', 'deliveries.customer_id', '=', 'customers.customer_id')
            ->where('deliveries.delivery_id', $delivery_id)
            ->select('deliveries.*', 'customers.name as customer_name')
            ->first();

        // 納品が見つからない場合はリダイレクト
        if (!$delivery) {
            return redirect()->route('deliveries.index')->with('error', '指定された納品が見つかりませんでした。');
        }

        // 2. 納品明細（商品一覧）を取得
        // unit_price を取得するために、select() に明示的に含めるか、テーブルに直接あることを確認してください。
        $deliveryDetails = DB::table('delivery_details')
            ->where('delivery_id', $delivery_id)
            ->select(
                'delivery_details.*',
                'delivery_details.product_name' // product_nameも必要であれば取得
                // 'delivery_details.unit_price' // 単価を削除したのでこの行は不要です
            )
            ->get();

        // 納品情報と納品明細をビューに渡して表示
        return view('deliveries.delivery_details', compact('delivery', 'deliveryDetails'));
    }

    /**
     * 返品フォームを表示します。
     *
     * @param string $delivery_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showReturnForm(string $delivery_id)
    {
        // 該当する納品情報を取得し、ビューに渡します。
        $delivery = DB::table('deliveries')
            ->join('customers', 'deliveries.customer_id', '=', 'customers.customer_id')
            ->where('deliveries.delivery_id', $delivery_id)
            ->select('deliveries.*', 'customers.name as customer_name')
            ->first();

        if (!$delivery) {
            return redirect()->route('deliveries.index')->with('error', '指定された納品が見つかりませんでした。');
        }

        // 納品明細（delivery_details）も取得してビューに渡す
        // 返品フォームでも単価などが必要であれば、select() で取得してください
        $deliveryDetails = DB::table('delivery_details')
            ->where('delivery_id', $delivery_id)
            ->get();

        // compactに 'deliveryDetails' も追加
        return view('deliveries.return', compact('delivery', 'deliveryDetails'));
    }
}