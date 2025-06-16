<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Orders;
use App\Models\OrderDetails;
use App\Models\Deliveries;
use App\Models\DeliveryDetails;


class DeliveryController extends Controller
{

    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            $delivery = Deliveries::create([
                'customer_id' => $request->customer_id,
                'delivery_date' => $request->delivery_date,
                'remarks' => $request->remarks,
            ]);
    
            $orderDetailIds = $request->order_detail_ids;
            $deliveryQuantities = $request->delivery_quantities;
    
            foreach ($orderDetailIds as $index => $originalOrderDetailId) {
                $deliveryQty = (int) $deliveryQuantities[$index];
    
                $original = DB::table('order_details')->where('order_detail_id', $originalOrderDetailId)->first();
    
                if (!$original || $original->cancell_flag == 1 || $original->delivery_status == 1) {
                    continue;
                }
    
                $originalQty = (int) $original->quantity;
                $orderId = $original->order_id;
    
                // 納品済み用 order_detail 登録
                $deliveredOrderDetailId = null;
                if ($deliveryQty > 0) {
                    $deliveredOrderDetailId = DB::table('order_details')->insertGetId([
                        'order_id' => $orderId,
                        'product_name' => $original->product_name,
                        'unit_price' => $original->unit_price,
                        'quantity' => $deliveryQty,
                        'delivery_status' => 1,
                        'delivery_date' => $request->delivery_date,
                        'cancell_flag' => 0,
                        'remarks' => $original->remarks,
                    ]);
    
                    // 納品明細を登録（←新しいIDを使う！）
                    DeliveryDetails::create([
                        'delivery_id' => $delivery->delivery_id,
                        'order_id' => $orderId,
                        'order_detail_id' => $deliveredOrderDetailId,
                        'product_name' => $original->product_name,
                        'unit_price' => $original->unit_price,
                        'delivery_quantity' => $deliveryQty,
                        'remarks' => $original->remarks,
                        'return_flag' => 0,
                    ]);
                }
    
                // 未納用 order_detail 登録
                $remainingQty = $originalQty - $deliveryQty;
                if ($remainingQty > 0) {
                    DB::table('order_details')->insert([
                        'order_id' => $orderId,
                        'product_name' => $original->product_name,
                        'unit_price' => $original->unit_price,
                        'quantity' => $remainingQty,
                        'delivery_status' => 0,
                        'cancell_flag' => 0,
                        'remarks' => $original->remarks,
                    ]);
                }
    
                // order_detail を削除
                DB::table('order_details')->where('order_detail_id', $originalOrderDetailId)->delete();
            }
    
            DB::commit();
            return redirect()->route('orders.index')->with('success', '納品を登録しました。');
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('納品登録エラー: ' . $e->getMessage());
            return redirect()->route('orders.index')->withErrors('納品登録時にエラーが発生しました。');
        }
    }
    
    


    public function prepare(Request $request) //納品登録画面へ遷移するための処理
    {
        // フォームから渡された注文IDの配列を取得
        $orderIds = $request->input('order_ids', []);

        if (empty($orderIds)) {
            return redirect()->route('orders.index')->withErrors('注文が選択されていません');
        }

        // 選択された注文のレコードを取得
        $orders = Orders::whereIn('order_id', $orderIds)->get();

        if ($orders->isEmpty()) {
            return redirect()->route('orders.index')->withErrors('選択された注文が見つかりません');
        }

        // 顧客IDがすべて同じかどうかをチェック
        $customerIds = $orders->pluck('customer_id')->unique();

        if ($customerIds->count() !== 1) {
            return redirect()->route('orders.index')->withErrors('同一の顧客に対する注文のみを選択してください');
        }

        $customer_id = $customerIds->first();

        // キャンセルされておらず、かつ納品済みでない商品だけを取得
        $orderDetails = DB::table('order_details')
        ->whereIn('order_id', $orderIds)
        ->where('cancell_flag', 0)
        ->where('delivery_status', 0)
        ->get();

        // 未納品の商品が存在しない場合は戻す
        if ($orderDetails->isEmpty()) {
            return redirect()->route('orders.index')->withErrors('選択された注文には未納品の商品がありません。');
        }

        return view('orders.new_delivery', [
            'orderIds' => $orderIds,
            'orderDetails' => $orderDetails,
            'customer_id' => $customer_id,
        ]);
    }


    public function index(Request $request)
    {
        // 店舗一覧（セレクトボックス用）
        $stores = DB::table('stores')->get();

        // クエリパラメータからstore_id取得
        $storeId = $request->input('store_id');
        $keyword = $request->input('keyword');

        $sortBy = $request->input('sort_by');
        $order = $request->input('order', 'desc');

        // deliveriesとcustomersを結合し、store_idで絞り込み
        $deliveries = DB::table('deliveries')
            ->join('customers', 'deliveries.customer_id', '=', 'customers.customer_id')
            ->when($storeId, function ($query, $storeId) {
                return $query->where('customers.store_id', $storeId);
            })
            ->when($keyword, function ($query, $keyword) {
                return $query->where(function ($q) use ($keyword) {
                    $q->where('deliveries.delivery_id', 'like', "%{$keyword}%")
                    ->orWhere('deliveries.remarks', 'like', "%{$keyword}%")
                    ->orWhere('customers.name', 'like', "%{$keyword}%");
                });
            })
            ->select('deliveries.*', 'customers.name as customer_name');


            // ソートの適用
        if ($sortBy === 'delivery_date') {
            // 'delivery_date' が指定された場合、その順序でソート
            $deliveries->orderBy('deliveries.delivery_date', $order);
        } else {
            // それ以外（デフォルトまたは未指定）の場合は、delivery_id で降順ソート
            $deliveries->orderBy('deliveries.delivery_id', 'desc');
        }

        $deliveries = $deliveries->get();

        return view('deliveries.index', [
            'deliveries' => $deliveries,
            'stores' => $stores,
            'selectedStoreId' => $storeId,
            'keyword' => $keyword,
            'sortBy' => $sortBy,
            'order' => $order,
        ]);
    }


    public function show($delivery_id) // 納品詳細を表示する処理
    {
        $delivery = DB::table('deliveries')->where('delivery_id', $delivery_id)->first();
        $deliveryDetails = DB::table('delivery_details')
            ->where('delivery_id', $delivery_id)
            ->get();

        if (!$delivery) {
            return redirect()->route('deliveries.index')->withErrors('納品情報が見つかりません。');
        }

        return view('deliveries.delivery_details', compact('delivery', 'deliveryDetails'));
    }

    // 返品フォーム表示
    public function showReturnForm($delivery_id)
    {
        $deliveryDetails = DB::table('delivery_details')
            ->where('delivery_id', $delivery_id)
            ->get();

        return view('deliveries.return', compact('delivery_id', 'deliveryDetails'));
    }

    // 返品処理実行
    public function processReturn(Request $request)
    {
        $returnQuantities = $request->input('return_quantities', []);
        $reasons = $request->input('reasons', []);
        $delivery_id = $request->delivery_id;

        DB::beginTransaction();

        try {
            foreach ($returnQuantities as $deliveryDetailId => $returnQty) {
                $returnQty = (int)$returnQty;

                if ($returnQty <= 0) {
                    continue;
                }

                $detail = DB::table('delivery_details')->where('delivery_detail_id', $deliveryDetailId)->first();

                if (!$detail || $detail->return_flag == 1) {
                    continue;
                }

                $originalQty = (int)$detail->delivery_quantity;
                $remainingQty = $originalQty - $returnQty;

                // 元レコードを返品済みにする
                DB::table('delivery_details')->where('delivery_detail_id', $deliveryDetailId)->update([
                    'return_flag' => 1,
                    'remarks' => DB::raw("CONCAT(IFNULL(remarks, ''), '【返品理由】" . addslashes($reasons[$deliveryDetailId]) . "')"),
                ]);

                // 残数があれば、新たに再納品として登録
                if ($remainingQty > 0) {
                    DB::table('delivery_details')->insert([
                        'delivery_id' => $detail->delivery_id,
                        'order_id' => $detail->order_id,
                        'order_detail_id' => $detail->order_detail_id,
                        'product_name' => $detail->product_name,
                        'unit_price' => $detail->unit_price,
                        'delivery_quantity' => $remainingQty,
                        'return_flag' => 0,
                        'remarks' => $detail->remarks,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('deliveries.index')->with('success', '返品処理が完了しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('返品処理エラー: ' . $e->getMessage());
            return redirect()->route('deliveries.index')->withErrors('返品処理中にエラーが発生しました。');
        }
    }




}
