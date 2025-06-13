<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8" />
    <title>注文修正画面</title>
    <link href="{{ asset('css/page/order_edit_blade.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>注文修正 (注文ID: {{ $order->order_id }})</h1>

        <form action="{{ route('orders.update', $order->order_id) }}" method="POST">
            @csrf

            <div>
                <p><strong>注文日:</strong> {{ $order->order_date }}</p>
                <p><strong>顧客ID:</strong> {{ $order->customer_id }}</p>
                <p><strong>顧客名:</strong> {{ $order->customer_name }}</p>
            </div>

            <div class="form-group">
                <label for="remarks">備考</label>
                <textarea name="remarks" id="remarks" rows="2" class="form-control">{{ old('remarks', $order->remarks) }}</textarea>
            </div>

            <h3>注文明細</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>明細ID</th>
                        <th>商品名</th>
                        <th>単価</th>
                        <th>数量</th>
                        <th>備考</th>
                        <th>小計</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderDetails as $detail)
                        <tr>
                            <td>{{ $detail->order_detail_id }}</td>
                            <td>{{ $detail->product_name }}</td>
                            <td>
                                <input type="number" name="order_details[{{ $detail->order_detail_id }}][unit_price]"
                                    value="{{ $detail->unit_price }}" step="0.01" class="form-control" required>
                            </td>
                            <td>
                                <input type="number" name="order_details[{{ $detail->order_detail_id }}][quantity]"
                                    value="{{ $detail->quantity }}" class="form-control" required>
                            </td>
                            <td>
                                <input type="text" name="order_details[{{ $detail->order_detail_id }}][remarks]"
                                    value="{{ $detail->remarks }}" class="form-control">
                            </td>
                            <td>{{ number_format($detail->unit_price * $detail->quantity) }} 円</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <a href="{{ route('orders.order_details', ['order_id' => $order->order_id]) }}">戻る</a>
            <button type="submit" class="btn btn-primary">確定</button>
        </form>
    </div>
</body>
</html>
