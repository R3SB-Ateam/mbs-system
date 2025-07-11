<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文明細</title>
    <link href="{{ asset('css/page/order_datails.css') }}" rel="stylesheet">
    <style>
        /* 新しいアラートメッセージ用のスタイルを追加 */
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid;
            border-radius: 4px;
            font-weight: bold;
            text-align: center; /* 中央寄せ */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* 影 */
            position: relative; /* アラートが複数表示された際に重ならないように */
            z-index: 1000; /* 他の要素より手前に表示 */
        }
        .alert-success {
            background-color: #e6ffe6; /* 薄い緑 */
            border-color: #66cc66; /* 緑 */
            color: #338833; /* 濃い緑 */
        }
        .alert-error {
            background-color: #ffe6e6; /* 薄い赤 */
            border-color: #cc6666; /* 赤 */
            color: #883333; /* 濃い赤 */
        }
    </style>
</head>
<body>

    {{-- 成功メッセージ (新規登録、キャンセル成功時など) --}}
    {{-- idを 'success-alert-general' など、既存と区別できるものに変更しました --}}
    @if (session('success'))
        <div id="success-alert-general" class="alert alert-success">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('success-alert-general');
                if (alert) alert.remove();
            }, 3000); // 3秒後に非表示
        </script>
    @endif

    {{-- エラーメッセージ (キャンセル失敗時など) --}}
    @if (session('error'))
        <div id="error-alert" class="alert alert-error">
            {{ session('error') }}
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('error-alert');
                if (alert) alert.remove();
            }, 3000); // 3秒後に非表示
        </script>
    @endif

    {{-- 更新成功メッセージ (既存のコード、idを 'update-success-alert' に変更) --}}
    @if (session('update_success'))
        <div id="update-success-alert" class="alert alert-success">
            {{ session('update_success') }}
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('update-success-alert');
                if (alert) alert.remove();
            }, 3000); // 3秒後に非表示
        </script>
    @endif

    <div class="container">
        <h1 class="page-title">
            注文明細 <span class="sub-title">(注文ID: {{ $order->order_id }})</span>
        </h1>

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

        <div class="order-details">
            <h2 class="section-title-detail">注文商品の明細</h2>
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="table">
                        <colgroup>
                            <col style="width: 12%;">    <col style="width: 18%;">    <col style="width: 10%;">    <col style="width: 8%;">    <col style="width: 12%;">    <col style="width: 25%;">    <col style="width: 15%;">    </colgroup>
                        
                        <thead class="table-head">
                            <tr>
                                <th>注明細ID</th>
                                <th>商品名</th>
                                <th>単価</th>
                                <th>数量</th>
                                <th>納品数量</th>
                                <th>備考</th>
                                <th>キャンセル状況</th> {{-- 新しく追加する列のヘッダー --}}
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
                                <td>
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

        <div class="btn-container">
            <a href="{{ route('orders.index') }}" class="btn btn-back">
                注文一覧に戻る
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