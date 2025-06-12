<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Orders;
use App\Models\OrderDetails;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        // 店舗一覧を取得（セレクトボックス用）
        $stores = DB::table('stores')->get();

        // GETパラメータからstore_idを取得
        $storeId = $request->input('store_id');
        $keyword = $request->input('keyword');

        // ordersとcustomersを結合し、store_idで絞り込み
        $orders = DB::table('orders')
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id')
            ->when($storeId, function ($query, $storeId) {
                return $query->where('customers.store_id', $storeId);
            })
            ->when($keyword, function ($query, $keyword) {
                return $query->where(function ($q) use ($keyword) {
                    $q->where('orders.order_id', 'like', "%{$keyword}%")
                    ->orWhere('orders.remarks', 'like', "%{$keyword}%")
                    ->orWhere('customers.name', 'like', "%{$keyword}%");
                });
            })
            ->select('orders.*', 'customers.name as customer_name')
            ->orderBy('orders.order_id', 'desc')
            ->get();

        return view('orders.index', [
            'orders' => $orders,
            'stores' => $stores,
            'selectedStoreId' => $storeId, // ← 選択状態保持のため
            'keyword' => $keyword,
        ]);
    }


    public function newOrder(){
        // 顧客一覧を取得（customersテーブル）
        $customers = DB::table('customers')->get();

        // ビューへ顧客データを渡す
        return view('orders.new_order', ['customers' => $customers]);
        }


    public function order_store(Request $request){

        Log::debug('リクエスト内容:', $request->all());

        // バリデーション
        $validated = $request->validate([
            'customer_id' => 'required|integer',
            'product_name.*' => 'required|string',
            'unit_price.*' => 'required|numeric|min:0',
            'quantity.*' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // orders テーブルに注文を登録
            $orderId = DB::table('orders')->insertGetId([
                'customer_id' => $request->customer_id,
                'order_date' => now(), // 現在の日付
                'remarks' => $request->remarks,
            ]);

            // order_details テーブルに商品明細を登録
            $productNames = $request->product_name;
            $unitPrices = $request->unit_price;
            $quantities = $request->quantity;

            for ($i = 0; $i < count($productNames); $i++) {
                // Laravel側の修正（delivery_status を数値で）
                DB::table('order_details')->insert([
                    'order_id' => $orderId,
                    'product_name' => $productNames[$i],
                    'unit_price' => $unitPrices[$i],
                    'quantity' => $quantities[$i],
                    'delivery_status' => 0, // ← 文字列 '未納品' を数値 0 に
                    'remarks' => null,
                    'cancell_flag' => 0,
                ]);
            }

        DB::commit();
        return redirect()->route('orders.order_details', ['order_id' => $orderId])
                         ->with('success', '注文が登録されました');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('注文登録エラー: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => '注文の登録中にエラーが発生しました'])->withInput();
        }
    
    }


    public function orderDetails($order_id){
        $order = DB::table('orders')->where('order_id', $order_id)->first();
        if (!$order) {
            abort(404, 'Order not found');
        }

        $orderDetails = DB::table('order_details')
            ->where('order_id', $order_id)
            ->get();

        return view('orders.order_details', [
            'order' => $order,
            'orderDetails' => $orderDetails,
        ]);
    }


    public function showCancelForm($order_id)
    {
        // 注文情報を取得（必要であればビューに渡す）
        $order = DB::table('orders')->where('order_id', $order_id)->first();

        if (!$order) {
            return redirect()->route('orders.index')->withErrors('該当する注文が見つかりません。');
        }

        // 納品済・キャンセル済を除外した注文詳細を取得
        $orderDetails = DB::table('order_details')
            ->where('order_id', $order_id)
            ->where('delivery_status', 0)
            ->where('cancell_flag', 0)
            ->get();

        if ($orderDetails->isEmpty()) {
            return redirect()->route('orders.index')->withErrors('キャンセル可能な商品がありません。');
        }

        return view('orders.cancel', compact('order', 'orderDetails'));
    }





    public function processCancel(Request $request)
    {
        $cancelQuantities = $request->input('cancel_quantities', []);
        $reasons = $request->input('reasons', []);
        $order_id = $request->order_id;

        DB::beginTransaction();

        try {
            foreach ($cancelQuantities as $orderDetailId => $cancelQty) {
                $cancelQty = (int)$cancelQty;

                if ($cancelQty <= 0) {
                    continue;
                }

                $detail = DB::table('order_details')->where('order_detail_id', $orderDetailId)->first();

                if (!$detail || $detail->delivery_status == 1 || $detail->cancell_flag == 1) {
                    continue;
                }

                $originalQty = (int)$detail->quantity;
                $remainingQty = $originalQty - $cancelQty;

                // 元データをキャンセル済みにする
                DB::table('order_details')->where('order_detail_id', $orderDetailId)->update([
                    'cancell_flag' => 1,
                    'remarks' => DB::raw("CONCAT(IFNULL(remarks, ''), '【キャンセル理由】" . $reasons[$orderDetailId] . "')"),
                ]);

                // 残りがある場合、新規に登録
                if ($remainingQty > 0) {
                    DB::table('order_details')->insert([
                        'order_id' => $detail->order_id,
                        'product_name' => $detail->product_name,
                        'unit_price' => $detail->unit_price,
                        'quantity' => $remainingQty,
                        'delivery_status' => 0,
                        'cancell_flag' => 0,
                        'remarks' => $detail->remarks,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('orders.index')->with('success', 'キャンセル処理が完了しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('キャンセル処理エラー: ' . $e->getMessage());
            return redirect()->route('orders.index')->withErrors('キャンセル処理中にエラーが発生しました。');
        }
    }




}

