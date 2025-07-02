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
        $stores = DB::table('stores')->get();

        $selectedStoreId = $request->input('store_id');
        $keyword = $request->input('keyword');

        if ($request->has('store_id') || $request->has('keyword')) {
            session([
                'orders_filter.store_id' => $selectedStoreId,
                'orders_filter.keyword' => $keyword,
            ]);
        }

        $selectedStoreId = $selectedStoreId ?? session('orders_filter.store_id');
        $keyword = $keyword ?? session('orders_filter.keyword');

        $orders = Orders::with(['customer', 'details'])
            ->when($selectedStoreId, function ($query, $selectedStoreId) {
                return $query->whereHas('customer', function ($q) use ($selectedStoreId) {
                    $q->where('store_id', $selectedStoreId);
                });
            })
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('order_id', 'like', "%{$keyword}%")
                        ->orWhere('remarks', 'like', "%{$keyword}%")
                        ->orWhereHas('customer', function ($q2) use ($keyword) {
                            $q2->where('name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->orderBy('order_id', 'desc') 
            ->get()
            ->each(function ($order) {
                $order->total_amount = $order->details
                    ->where('cancell_flag', 0)
                    ->sum(function ($detail) {
                        return $detail->unit_price * $detail->quantity;
                    });
            });

            return view('orders.index', compact('orders', 'stores', 'selectedStoreId', 'keyword'));
    }

    public function searchOrderDetails(Request $request, $order_id)
    {
        $keyword = $request->input('keyword');

        $order = Orders::with(['details' => function ($query) use ($keyword) {
            $query->where('product_name', 'like', "%{$keyword}%");
        }, 'customer'])->findOrFail($order_id);

        return view('orders.order_details', [
            'order' => $order,
            'orderDetails' => $order->details,
        ]);
    }


    public function newOrder(Request $request){
        // 顧客一覧を取得（customersテーブル）
        $storeId = $request->input('store_id');

        $query = DB::table('customers');
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        $customers = $query->get();

        return view('orders.new_order', compact('customers', 'storeId'));
    }


    public function order_store(Request $request){
        // バリデーション
        $validated = $request->validate([
            'customer_id' => 'required|integer',
            'product_name.*' => 'required|string',
            'unit_price.*' => 'required|numeric|min:0',
            'quantity.*' => 'required|integer|min:1',
            'product_note.*' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction(); // ここでトランザクションを開始

        try {
            $order = Orders::create([
                'customer_id' => $request->customer_id,
                'order_date' => now(),
                'remarks' => $request->remarks,
            ]);

            foreach ($request->product_name as $i => $name) {
                $order->details()->create([
                    'product_name' => $name,
                    'unit_price' => $request->unit_price[$i],
                    'quantity' => $request->quantity[$i],
                    'delivery_quantity' => 0,
                    'remarks' => $request->product_note[$i] ?? null,
                    'cancell_flag' => 0,
                ]);
            }

            DB::commit();
            return redirect()->route('orders.order_details', ['order_id' => $order->order_id])
                             ->with('success', '注文が登録されました');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('注文登録エラー: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => '注文の登録中にエラーが発生しました'])->withInput();
        }
    } // <<<<<<<<<<<<< order_store メソッドの閉じ括弧はここだけ

    public function orderDetails($order_id)
    {
        // 注文情報を取得（customer_name を結合）
        $order = DB::table('orders')
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id')
            ->select('orders.*', 'customers.name as customer_name')
            ->where('orders.order_id', $order_id)
            ->first();

        // 注文詳細を取得（必要なすべてのカラムを明示的に指定）
        $orderDetails = DB::table('order_details')
        ->where('order_details.order_id', $order_id)
        ->get();

        if (!$order) {
        // 注文が見つからない場合のエラーハンドリング
        abort(404, '指定された注文は見つかりません。');
        }

        return view('orders.order_details', compact('order', 'orderDetails'));
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
        ], [
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
                return redirect()->route('orders.order_details', ['order_id' => $order_id])
                    ->withErrors('キャンセルする商品が選択されていないか、数量が0です。');
            }

            $detailsToProcess = DB::table('order_details')
                ->whereIn('order_detail_id', $targetOrderDetailIds)
                ->where('order_id', $order_id)
                ->where('cancell_flag', 0)
                ->get()->keyBy('order_detail_id');

            foreach ($cancelQuantities as $orderDetailId => $cancelQty) {
                $cancelQty = (int)$cancelQty;

                if ($cancelQty <= 0) continue;

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

                if ($newOriginalQty <= 0) {
                    DB::table('order_details')->where('order_detail_id', $orderDetailId)->delete();
                } else {
                    DB::table('order_details')->where('order_detail_id', $orderDetailId)->update([
                        'quantity' => $newOriginalQty,
                    ]);
                }

                // remarksはPHPで結合してから登録する
                $newRemarks = ($detail->remarks ?? '') . '【キャンセル理由】' . ($reasons[$orderDetailId] ?? '');

                DB::table('order_details')->insert([
                    'order_id' => $detail->order_id,
                    'product_name' => $detail->product_name,
                    'unit_price' => $detail->unit_price,
                    'quantity' => $cancelQty,
                    'delivery_quantity' => 0,
                    'cancell_flag' => 1,
                    'remarks' => $newRemarks,
                ]);
            }

            DB::commit();
            return redirect()->route('orders.order_details', ['order_id' => $order_id])->with('success', 'キャンセル処理が完了しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('キャンセル処理エラー: ' . $e->getMessage());
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

    public function orderUpdate(Request $request, $order_id)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:1000',
            'details.*.product_name' => 'required|string|max:255',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.quantity' => 'required|integer|min:1',
        ]);

        // 注文の更新
        $order = Orders::findOrFail($order_id);
        $order->remarks = $request->input('remarks'); // ← 備考を更新
        $order->save();

        // 注文明細の更新
        foreach ($request->input('details') as $detailData) {
            $detail = OrderDetails::findOrFail($detailData['order_detail_id']);
            $detail->product_name = $detailData['product_name'];
            $detail->unit_price = $detailData['unit_price'];
            $detail->quantity = $detailData['quantity'];
            $detail->remarks = $detailData['remarks'] ?? null;
            $detail->save();
        }

        return redirect()->route('orders.order_details', ['order_id' => $order_id])
                             ->with('update_success', '注文内容を更新しました。');

    }


    public function showPrintPage(Orders $order)
    {
        // 注文に紐づく顧客と明細データを読み込む
        $order->load(['customer', 'details']);

        // 印刷レイアウト用のビューを返す
        return view('orders.print', compact('order'));
    }

}