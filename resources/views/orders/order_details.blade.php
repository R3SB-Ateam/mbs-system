<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文明細</title>
    <link href="{{ asset('css/page/order_datails.css') }}" rel="stylesheet">
</head>
<body>

    @if (session('update_success'))
        <div id="success-alert" class="alert alert-success" style="background-color: #e6ffe6; padding: 10px; border: 1px solid #66cc66;">
            {{ session('update_success') }}
        </div>

        <script>
            setTimeout(() => {
                const alert = document.getElementById('success-alert');
                if (alert) alert.remove();
            }, 3000); // 3秒後に非表示
        </script>
    @endif



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
                <div class="table-wrapper">
                    <table class="table">
                        <colgroup>
                            <col style="width: 15%;">  <!-- 注文詳細ID -->
                            <col style="width: 15%;">  <!-- 商品名 -->
                            <col style="width: 10%;">  <!-- 単価 -->
                            <col style="width: 10%;">  <!-- 数量 -->
                            <col style="width: 15%;">  <!-- 納品数量 -->
                            <col style="width: 35%;">  <!-- 備考 -->
                        </colgroup>
                        
                        <thead class="table-head">
                            <tr>
                                <th>注文詳細ID</th>
                                <th>商品名</th>
                                <th>単価</th>
                                <th>数量</th>
                                <th>納品数量</th>
                                <th>備考</th>
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
                    </table>
                </div>
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
            <button class="btn btn-primary js-print-btn"
                    data-print-url="{{ route('orders.print_page', ['order' => $order->order_id]) }}">
                注文書印刷
            </button>
        </div>
    </div>
    <script src="{{ asset('js/print.js') }}" defer></script>
</body>
</html>