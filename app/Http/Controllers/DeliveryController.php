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
    $request->validate([
        'customer_id' => 'required|exists:customers,customer_id',
        'delivery_date' => 'required|date',
        'order_detail_ids' => 'required|array',
        'order_detail_ids.*' => 'required|exists:order_details,order_detail_id',
        'delivery_quantities' => 'required|array',
        'delivery_quantities.*' => 'required|integer|min:1', // ★ここが重要★
    ],
    [
        'delivery_quantities.*.min' => '納品数量は1以上で入力してください。',
        'delivery_quantities.*.required' => '納品数量は必須です。',
        'delivery_quantities.*.integer' => '納品数量は数値で入力してください。',
    ]);

    DB::beginTransaction();

    try {
        $delivery = Deliveries::create([
            'customer_id' => $request->customer_id,
            'delivery_date' => $request->delivery_date,
            'remarks' => $request->remarks,
        ]);

        $orderDetailIds = $request->order_detail_ids;
        $deliveryQuantities = $request->delivery_quantities;

        foreach ($orderDetailIds as $index => $orderDetailId) {
            $deliveryQty = (int) $deliveryQuantities[$index];

            $orderDetail = DB::table('order_details')->where('order_detail_id', $orderDetailId)->first();

            if (!$orderDetail || $orderDetail->cancell_flag == 1) {
                continue;
            }

            // 既納品数を取得（null 対策）
            $currentDeliveredQty = (int) ($orderDetail->delivery_quantity ?? 0);
            $newDeliveredQty = $currentDeliveredQty + $deliveryQty;

            // 上限チェック（必要に応じて）
            if ($newDeliveredQty > $orderDetail->quantity) {
                throw new \Exception("納品数量が注文数を超えています（商品ID: {$orderDetailId}）");
            }

            // order_details テーブルを更新（納品数量を加算）
            DB::table('order_details')
                ->where('order_detail_id', $orderDetailId)
                ->update(['delivery_quantity' => $newDeliveredQty]);

            // delivery_details に登録
            DeliveryDetails::create([
                'delivery_id' => $delivery->delivery_id,
                'order_id' => $orderDetail->order_id,
                'order_detail_id' => $orderDetailId,
                'product_name' => $orderDetail->product_name,
                'unit_price' => $orderDetail->unit_price,
                'delivery_quantity' => $deliveryQty,
                'remarks' => $orderDetail->remarks,
                'return_flag' => 0,
            ]);
        }

        DB::commit();
        return redirect()->route('deliveries.details', ['delivery_id' => $delivery->delivery_id])
                        ->with('success', '納品を登録しました。');


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
        ->whereColumn('delivery_quantity', '<', 'quantity')
        ->get();

        // 未納品の商品が存在しない場合は戻す
        if ($orderDetails->isEmpty()) {
            return redirect()->route('orders.index')->withErrors('選択された注文には未納品の商品がありません。');
        }

        $orderDetails->each(function ($item) {
            $item->undelivered_quantity = $item->quantity - ($item->delivery_quantity ?? 0);
        });

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

        if ($request->has('store_id') || $request->has('keyword')) {
            session([
                'deliveries_filter.store_id' => $request->input('store_id'),
                'deliveries_filter.keyword' => $request->input('keyword'),
            ]);
        }
        
        $storeId = $request->input('store_id', session('deliveries_filter.store_id'));
        $keyword = $request->input('keyword', session('deliveries_filter.keyword'));
        

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
            ->select('deliveries.*', 'customers.name as customer_name')
            ->orderBy('deliveries.delivery_id', 'desc')
            ->get();

        return view('deliveries.index', [
            'deliveries' => $deliveries,
            'stores' => $stores,
            'selectedStoreId' => $storeId,
            'keyword' => $keyword,
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
        $request->validate([
            'delivery_id' => 'required|exists:deliveries,delivery_id',
            'return_quantities' => 'array',
            'return_quantities.*' => 'nullable|integer|min:0', // HTMLのmin="0"に合わせる
            'reasons' => 'array',
            'reasons.*' => 'nullable|string|max:255',
        ],
        [
            'return_quantities.*.min' => '返品数量は0以上で入力してください。', // エラーメッセージも0以上に更新
            'return_quantities.*.integer' => '返品数量は数値で入力してください。',
            'reasons.*.max' => '返品理由は255文字以内で入力してください。',
        ]);

        $returnQuantities = $request->input('return_quantities', []);
        $reasons = $request->input('reasons', []);
        $delivery_id = $request->delivery_id;

        DB::beginTransaction();

        try {
            // 実際に返品数量が1以上の商品のみを対象にする
            // ここで0をフィルタリングすることで、何も入力されなかったケースや0が入力されたケースを除外
            $targetDeliveryDetailIds = array_keys(array_filter($returnQuantities, fn($qty) => (int)$qty > 0));

            // 返品対象が一つもなければ、エラーを返す
            if (empty($targetDeliveryDetailIds)) {
                DB::rollBack();
                return redirect()->route('deliveries.details', ['delivery_id' => $delivery_id])
                                ->withErrors('返品する商品が選択されていないか、数量が0です。');
            }

            // 処理対象の納品詳細を一括で取得
            $detailsToProcess = DB::table('delivery_details')
                ->whereIn('delivery_detail_id', $targetDeliveryDetailIds)
                ->where('delivery_id', $delivery_id)
                ->where('return_flag', 0) // まだ返品されていないもののみを対象
                ->get()->keyBy('delivery_detail_id'); // IDをキーにしてアクセスしやすくする

            foreach ($returnQuantities as $deliveryDetailId => $returnQty) {
                $returnQty = (int)$returnQty;

                // フィルタリング済みだが、念のため個別の0以下のチェック
                if ($returnQty <= 0) {
                    continue;
                }

                $detail = $detailsToProcess->get($deliveryDetailId);

                // 対象の納品詳細が見つからない、または既に返品済みの場合はスキップ
                if (!$detail || $detail->return_flag == 1) {
                    continue;
                }

                $originalDeliveryQty = (int)$detail->delivery_quantity;

                // 返品数量が元の納品数量を超えていないかチェック
                if ($returnQty > $originalDeliveryQty) {
                    throw new \Exception("返品数量が納品数量を超えています（商品名: {$detail->product_name}、納品数: {$originalDeliveryQty}）");
                }

                $remainingDeliveryQty = $originalDeliveryQty - $returnQty;

                // 元の納品詳細レコードの数量を減らす、または削除
                if ($remainingDeliveryQty <= 0) {
                    // 数量が0以下になる場合、元のレコードを削除
                    DB::table('delivery_details')->where('delivery_detail_id', $deliveryDetailId)->delete();
                } else {
                    // 数量が残る場合、元のレコードの数量を更新
                    DB::table('delivery_details')->where('delivery_detail_id', $deliveryDetailId)->update([
                        'delivery_quantity' => $remainingDeliveryQty,
                    ]);
                }

                // 返品された商品を新しい delivery_detail_id で登録
                DB::table('delivery_details')->insert([
                    'delivery_id' => $detail->delivery_id,
                    'order_id' => $detail->order_id,
                    'order_detail_id' => $detail->order_detail_id, // 元の order_detail_id を保持
                    'product_name' => $detail->product_name,
                    'unit_price' => $detail->unit_price,
                    'delivery_quantity' => $returnQty, // 返品された数量
                    'return_flag' => 1, // 返品済みとしてマーク
                    // 元の備考に返品理由を追加
                    'remarks' => DB::raw("CONCAT(IFNULL('" . addslashes($detail->remarks) . "', ''), '【返品理由】" . ($reasons[$deliveryDetailId] ?? '') . "')"),
                ]);
            }

            DB::commit();
            return redirect()->route('deliveries.details', ['delivery_id' => $delivery_id])->with('success', '返品処理が完了しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('返品処理エラー: ' . $e->getMessage());
            return redirect()->route('deliveries.details', ['delivery_id' => $delivery_id])->withErrors('返品処理中にエラーが発生しました。' . $e->getMessage());
        }
    }




}
