<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文明細</title>
    <link href="{{ asset('css/page/order_datails.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="page-title">
            注文明細 <span class="sub-title">(注文ID: {{ $order->order_id }})</span>
        </h1>

        <!-- 注文情報 -->
        <div class="order-summary">
            <h2 class="box-title">注文情報</h2>
            <div class="info-grid">
            <p><span class="text-label">顧客ID:</span> {{ $order->customer_id }}</p>

            <div class="grid-row">
                
                <p class="order-date-inline"><span class="text-label">注文日:</span> {{ $order->order_date }}</p>
            </div>
            <p><span class="text-label">顧客名:</span> {{ $order->customer_name }}</p>
            <p class="span-full"><span class="text-label">備考:</span> {{ $order->remarks }}</p>
        </div>

        </div>

        <!-- 注文明細情報 -->
        <div class="order-details">
            <h2 class="section-title-detail">注文商品の詳細</h2>
            <div class="table-container">
                <table class="table">
                    <div class="table-wrapper">
                    <colgroup>
                        <col style="width: 12%;">  <!-- 注文詳細ID -->
                        <col style="width: 13%;">  <!-- 商品名 -->
                        <col style="width: 11%;">  <!-- 単価 -->
                        <col style="width: 9%;">   <!-- 数量 -->
                        <col style="width: 13%;">  <!-- 納品状況 -->
                        <col style="width: 22%;">  <!-- 備考（キャンセル理由が入るので広め） -->
                        <col style="width: 12%;">  <!-- キャンセル（キャンセル済み表示のスペース確保） -->
                    </colgroup>
                    
                    <thead class="table-head">
                        <tr>
                            <th>注文詳細ID</th>
                            <th>商品名</th>
                            <th>単価</th>
                            <th>数量</th>
                            <th>納品数量</th>
                            <th>備考</th>
                            <th>キャンセル</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderDetails as $orderDetail)
                            <tr class="table-row">
                                <td>{{ $orderDetail->order_detail_id }}</td>
                                <td>{{ $orderDetail->product_name }}</td>
                                <td>{{ number_format($orderDetail->unit_price) }} 円</td>
                                <td>{{ number_format($orderDetail->quantity) }}</td>
                                <td>
                                    {{ number_format($orderDetail->delivery_quantity) }} / {{ number_format($orderDetail->quantity) }}
                                </td>
                                <td class="remarks-cell">{{ $orderDetail->remarks }}</td>
                                <td class="last-child">
                                    @if ($orderDetail->cancell_flag == 1)
                                        <span class="text-cancel">キャンセル済</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    </div>
                </table>
            </div>
        </div>

        <!-- ボタン -->
        <div class="btn-container">
            <a href="{{ route('orders.index') }}" class="btn btn-back">
                ← 注文一覧に戻る
            </a>
            <a href="{{ route('orders.order_edit', ['order_id' => $order->order_id]) }}" class="btn btn-edit">
                修正する
            </a>
            <a href="{{ route('orders.cancel', ['order_id' => $order->order_id]) }}"
               class="btn btn-cancel">
                キャンセル
            </a>
        </div>
    </div>
</body>
</html>