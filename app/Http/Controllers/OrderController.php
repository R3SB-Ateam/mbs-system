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

        // GETパラメータからstore_idを取得 ← ここが重要
        $storeId = $request->input('store_id');
        $keyword = $request->input('keyword');

        if ($request->has('store_id') || $request->has('keyword')) {
            session([
                'orders_filter.store_id' => $request->input('store_id'),
                'orders_filter.keyword' => $request->input('keyword'),
            ]);
        }

        $storeId = $request->input('store_id', session('orders_filter.store_id'));
        $keyword = $request->input('keyword', session('orders_filter.keyword'));

        // 店舗一覧を取得（セレクトボックス用）
        $orders = DB::table('orders')
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id')
            ->leftJoin('order_details', 'orders.order_id', '=', 'order_details.order_id')
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
        ->select(
            'orders.*',
            'customers.name as customer_name',
            DB::raw("CASE
                WHEN MIN(order_details.quantity - COALESCE(order_details.delivery_quantity, 0)) = 0 THEN '納品済み'
                WHEN MIN(order_details.quantity - COALESCE(order_details.delivery_quantity, 0)) > 0 THEN '未納品'
                ELSE '不明'
                END as delivery_status_text"),
            DB::raw('COALESCE(SUM(order_details.unit_price * order_details.quantity), 0) as total_amount')
        )

        ->groupBy('orders.order_id', 'customers.name', 'orders.customer_id', 'orders.order_date', 'orders.remarks')
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
        // 注文情報に顧客名を含めて取得
        $order = DB::table('orders')
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id')
            ->select('orders.*', 'customers.name as customer_name')
            ->where('orders.order_id', $order_id)
            ->first();

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
            ->where('cancell_flag', 0)
            ->whereColumn('delivery_quantity', '<', 'quantity')
            ->get();

        if ($orderDetails->isEmpty()) {
            return redirect()->route('orders.index')->withErrors('キャンセル可能な商品がありません。');
        }

        $orderDetails->each(function ($item) {
            // 未納品数 = 注文数 - 納品済み数量
            $item->cancellable_quantity = $item->quantity - ($item->delivery_quantity ?? 0);
        });

        return view('orders.cancel', compact('order', 'orderDetails'));
    }





    public function processCancel(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,order_id',
            'cancel_quantities' => 'array',
            'cancel_quantities.*' => 'nullable|integer|min:0',
            'reasons' => 'array',
            'reasons.*' => 'nullable|string|max:255',
        ],
        [
            'cancel_quantities.*.min' => 'キャンセル数量は0以上で入力してください。',
            'cancel_quantities.*.integer' => 'キャンセル数量は数値で入力してください。',
            'reasons.*.max' => 'キャンセル理由は255文字以内で入力してください。',
        ]);


        $cancelQuantities = $request->input('cancel_quantities', []);
        $reasons = $request->input('reasons', []);
        $order_id = $request->order_id;

        DB::beginTransaction();

        try {
            $targetOrderDetailIds = array_keys(array_filter($cancelQuantities, fn($qty) => (int)$qty > 0));

            if (empty($targetOrderDetailIds)) {
                DB::rollBack();
                return redirect()->route('orders.order_details', ['order_id' => $order_id])->withErrors('キャンセルする商品が選択されていないか、数量が0です。');
            }

            $detailsToProcess = DB::table('order_details')
                ->whereIn('order_detail_id', $targetOrderDetailIds)
                ->where('order_id', $order_id)
                ->where('cancell_flag', 0)
                ->get()->keyBy('order_detail_id');

            foreach ($cancelQuantities as $orderDetailId => $cancelQty) {
                $cancelQty = (int)$cancelQty;

                if ($cancelQty <= 0) {
                    continue;
                }

                $detail = $detailsToProcess->get($orderDetailId);

                if (!$detail || $detail->cancell_flag == 1 || ($detail->delivery_quantity ?? 0) >= $detail->quantity) {
                    continue;
                }

                $originalQty = (int)$detail->quantity;
                $deliveredQty = (int)($detail->delivery_quantity ?? 0);
                $currentAvailableQty = $originalQty - $deliveredQty;

                if ($cancelQty > $currentAvailableQty) {
                    throw new \Exception("キャンセル数量が未納品数を超えています（商品ID: {$orderDetailId}、未納品数: {$currentAvailableQty}）");
                }

                // 元の商品数量をキャンセル分だけ減らす
                $newOriginalQty = $originalQty - $cancelQty;

                // 元の商品の数量が0になる場合、その行を削除
                if ($newOriginalQty <= 0) {
                    DB::table('order_details')->where('order_detail_id', $orderDetailId)->delete();
                } else {
                    // そうでない場合、数量を更新
                    DB::table('order_details')->where('order_detail_id', $orderDetailId)->update([
                        'quantity' => $newOriginalQty,
                    ]);
                }

                // キャンセルされた商品を新しい order_detail_id で登録
                DB::table('order_details')->insert([
                    'order_id' => $detail->order_id,
                    'product_name' => $detail->product_name,
                    'unit_price' => $detail->unit_price,
                    'quantity' => $cancelQty, // キャンセル数量を登録
                    'delivery_quantity' => 0, // キャンセル商品は納品ゼロ
                    'cancell_flag' => 1, // キャンセル済みフラグを立てる
                    // 元の備考にキャンセル理由を追加
                    'remarks' => DB::raw("CONCAT(IFNULL('" . addslashes($detail->remarks) . "', ''), '【キャンセル理由】" . ($reasons[$orderDetailId] ?? '') . "')"),
                ]);
            }

            DB::commit();
            return redirect()->route('orders.order_details', ['order_id' => $order_id])->with('success', 'キャンセル処理が完了しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('キャンセル処理エラー: ' . $e->getMessage());
            return redirect()->route('orders.order_details', ['order_id' => $order_id])->withErrors('キャンセル処理中にエラーが発生しました。' . $e->getMessage());
        }
    }

    public function orderEdit($order_id)
    {
        $order = DB::table('orders')
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id')
            ->select('orders.*', 'customers.name as customer_name')
            ->where('orders.order_id', $order_id)
            ->first();

        if (!$order) {
            abort(404);
        }

        $orderDetails = DB::table('order_details')
            ->where('order_id', $order_id)
            ->where('cancell_flag', 0)
            ->get();

        return view('orders.order_edit', compact('order', 'orderDetails'));
    }

    public function update(Request $request, $order_id)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string',
            'order_details.*.unit_price' => 'required|numeric|min:0',
            'order_details.*.quantity' => 'required|integer|min:1',
            'order_details.*.remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // 注文情報更新
            DB::table('orders')
                ->where('order_id', $order_id)
                ->update(['remarks' => $request->remarks]);

            // 注文明細の更新
            foreach ($request->order_details as $detailId => $data) {
                DB::table('order_details')
                    ->where('order_detail_id', $detailId)
                    ->update([
                        'unit_price' => $data['unit_price'],
                        'quantity' => $data['quantity'],
                        'remarks' => $data['remarks'],
                    ]);
            }

            DB::commit();
            return redirect()->route('orders.index')->with('success', '注文を更新しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("注文修正エラー: " . $e->getMessage());
            return back()->withErrors('注文の更新中にエラーが発生しました。');
        }
    }

}
