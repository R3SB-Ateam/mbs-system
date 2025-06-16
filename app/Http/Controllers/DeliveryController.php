<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DeliveryController extends Controller
{
    /**
     * 納品一覧を表示し、フィルターとソートを適用します。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request) // ★このメソッドが存在するか確認してください★
    {
        // 店舗一覧（セレクトボックス用）
        $stores = DB::table('stores')->get();

        // セッションを使用したフィルター値の保持ロジックを優先します。
        // リクエストにstore_idまたはkeyword、sort_by、orderがある場合、セッションに保存
        if ($request->has('store_id') || $request->has('keyword') || $request->has('sort_by') || $request->has('order')) {
            Session::put('deliveries_filter.store_id', $request->input('store_id'));
            Session::put('deliveries_filter.keyword', $request->input('keyword'));
            Session::put('deliveries_filter.sort_by', $request->input('sort_by'));
            Session::put('deliveries_filter.order', $request->input('order', 'desc'));
        }

        // セッションからフィルター値を取得。リクエストにあればリクエストを優先
        $storeId = $request->input('store_id', Session::get('deliveries_filter.store_id'));
        $keyword = $request->input('keyword', Session::get('deliveries_filter.keyword'));
        $sortBy = $request->input('sort_by', Session::get('deliveries_filter.sort_by'));
        $order = $request->input('order', Session::get('deliveries_filter.order', 'desc')); // デフォルトは降順

        // deliveriesとcustomersを結合するクエリの基盤をここで定義します。
        $deliveriesQuery = DB::table('deliveries')
            ->join('customers', 'deliveries.customer_id', '=', 'customers.customer_id')
            ->select('deliveries.*', 'customers.name as customer_name');

        // store_idによるフィルタリングを適用
        if (!empty($storeId)) {
            $deliveriesQuery->where('customers.store_id', $storeId);
        }

        // キーワード検索を適用
        if (!empty($keyword)) {
            $deliveriesQuery->where(function ($q) use ($keyword) {
                $q->where('deliveries.delivery_id', 'like', "%{$keyword}%")
                  ->orWhere('deliveries.remarks', 'like', "%{$keyword}%")
                  ->orWhere('customers.name', 'like', "%{$keyword}%");
            });
        }

        // ソートの適用
        if ($sortBy === 'delivery_date') {
            $deliveriesQuery->orderBy('deliveries.delivery_date', $order);
        } else {
            // デフォルトのソート（delivery_id で降順）
            $deliveriesQuery->orderBy('deliveries.delivery_id', 'desc');
        }

        $deliveries = $deliveriesQuery->get();

        return view('deliveries.index', [
            'deliveries' => $deliveries,
            'stores' => $stores,
            'selectedStoreId' => $storeId,
            'keyword' => $keyword,
            'sortBy' => $sortBy,
            'order' => $order,
        ]);
    }

    /**
     * 指定された納品IDの詳細を表示します。
     *
     * @param string $delivery_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(string $delivery_id)
    {
        // 1. 納品情報と顧客情報を取得
        $delivery = DB::table('deliveries')
            ->join('customers', 'deliveries.customer_id', '=', 'customers.customer_id')
            ->where('deliveries.delivery_id', $delivery_id)
            ->select(
                'deliveries.*',
                'customers.name as customer_name',
                'customers.address as customer_address',
                'customers.phone_number as customer_tel'
            )
            ->first();

        // 納品データが見つからなければリダイレクト
        if (!$delivery) {
            return redirect()->route('deliveries.index')->with('error', '指定された納品が見つかりませんでした。');
        }

        // 2. 納品明細（商品一覧）を取得
        $deliveryDetails = DB::table('delivery_details')
            ->where('delivery_details.delivery_id', $delivery_id)
            ->select(
                'delivery_details.*',
                'delivery_details.product_name'
            )
            ->get();

        // 3. ビューにデータを渡す際のパスを修正し、両方のデータを渡す
        // ★このビューパスも、あなたのファイル構造に合わせて 'delivery_details' または 'deliveries.delivery_details' に設定してください★
        return view('deliveries.delivery_details', compact('delivery', 'deliveryDetails'));
    }
}