<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>納品登録</title>
    <link href="{{ asset('css/new_delivery.css') }}" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <h1 class="page-title">納品登録</h1>
        <form method="POST" action="{{ route('orders.delivery_store') }}">
            @csrf

            <div class="form-group">
                <label for="delivery_date" class="form-label">納品日:</label>
                <input type="date" id="delivery_date" name="delivery_date" required class="form-input-full">
            </div>

            <div class="form-group">
                <label for="remarks" class="form-label">備考:</label>
                <textarea id="remarks" name="remarks" rows="3" class="form-textarea"></textarea>
            </div>

            <h2 class="section-title">注文商品一覧</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr class="table-header">
                            <th class="table-cell-header">商品名</th>
                            <th class="table-cell-header">注文数</th>
                            <th class="table-cell-header">単価</th>
                            <th class="table-cell-header">納品数</th>
                        </tr>
                    </thead>
                    <tbody>
                        <input type="hidden" name="customer_id" value="{{ $customer_id }}">
                        @foreach ($orderIds as $orderId)
                            <input type="hidden" name="order_ids[]" value="{{ $orderId }}">
                        @endforeach
                        @foreach ($orderDetails as $orderDetail)
                            <tr>
                                <td class="table-cell">{{ $orderDetail->product_name }}</td>
                                <td class="table-cell">{{ number_format($orderDetail->quantity) }}</td>
                                <td class="table-cell">{{ number_format($orderDetail->unit_price,0) }}</td>
                                <td class="table-cell">
                                    <input type="hidden" name="order_detail_ids[]" value="{{ $orderDetail->order_detail_id }}">
                                    <input type="number" name="delivery_quantities[]" value="0" min="0" max="{{ $orderDetail->quantity }}" required class="quantity-input">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    納品登録
                </button>
            </div>
        </form>
    </div>
</body>
</html>