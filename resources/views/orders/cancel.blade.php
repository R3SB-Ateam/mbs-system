<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文詳細</title>
    <link href="{{ asset('css/page/cancel.css') }}" rel="stylesheet">
</head>
<body>
    <div class="page-container">
        <h1 class="page-title">キャンセル対象一覧（注文ID: {{ $order->order_id }}）</h1>

        <form action="{{ route('orders.processCancel') }}" method="POST" class="form-container">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->order_id }}">

            <div class="table-wrapper">
                <table class="data-table">
                    <thead class="table-header">
                        <tr>
                            <th class="table-header-cell">商品名</th>
                            <th class="table-header-cell">数量</th>
                            <th class="table-header-cell">キャンセル数</th>
                            <th class="table-header-cell">キャンセル理由</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderDetails as $detail)
                        <tr class="table-row">
                            <td class="table-cell">{{ $detail->product_name }}</td>
                            <td class="table-cell-center">{{ number_format($detail->cancellable_quantity) }}</td>
                            <td class="table-cell-center">
                                <input type="number" name="cancel_quantities[{{ $detail->order_detail_id }}]"
                                       min="0"
                                       max="{{ $detail->cancellable_quantity }}"
                                       value="0"
                                       class="input-number">
                            </td>
                            <td class="table-cell">
                                <input type="text" name="reasons[{{ $detail->order_detail_id }}]" maxlength="255"
                                       class="input-text">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="btn">
                <a href="{{ route('orders.order_details', ['order_id' => $order->order_id]) }}" class="btn btn-back">← 注文詳細へ戻る</a>
                <button type="submit" class="btn btn-danger">
                    キャンセル実行
                </button>
            </div>
        </form>
    </div>
</body>
</html>