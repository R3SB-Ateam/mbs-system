<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>注文修正</title>
    <link rel="stylesheet" href="{{ asset('css/page/order_edit_blade.css') }}">
</head>
<body>
<div class="container">
    <h1>注文修正 <span class="sub">（注文ID: {{ $order->order_id }}）</span></h1>

    <!-- 注文情報 -->
    <div class="card">
        <h2>注文情報</h2>
        <div class="info-grid">
            <div>
                <strong>顧客ID:</strong> {{ $order->customer_id }}<br>
                <strong>顧客名:</strong> {{ $order->customer_name }}
            </div>
            <div><strong>注文日:</strong> {{ $order->order_date }}</div>
            <div class="remarks-row">
                <strong>備考:</strong>
                <textarea name="remarks" rows="2" class="form-control">{{ old('remarks', $order->remarks) }}</textarea>
            </div>
        </div>
    </div>

    <!-- 商品修正フォーム -->
    <form method="POST" action="{{ route('orders.update', $order->order_id) }}">
        @csrf
        @method('PUT')

        <div class="order-details">
            <h2 class="section-title-detail">注文商品の修正</h2>
            <div class="table-container">
                <table class="table">
                    <colgroup>
                        <col style="width: 10%;">
                        <col style="width: 30%;">
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                    </colgroup>
                    <thead>
                    <tr>
                        <th>明細ID</th>
                        <th>商品名</th>
                        <th>単価</th>
                        <th>数量</th>
                        <th>明細備考</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($orderDetails as $index => $detail)
                        <tr>
                            <td>
                                <input type="hidden" name="details[{{ $index }}][order_detail_id]" value="{{ $detail->order_detail_id }}">
                                {{ $detail->order_detail_id }}
                            </td>
                            <td>
                                <input type="text" name="details[{{ $index }}][product_name]" value="{{ old("details.$index.product_name", $detail->product_name) }}" class="form-input">
                            </td>
                            <td>
                                <input type="number" name="details[{{ $index }}][unit_price]" 
                                    value="{{ old("details.$index.unit_price", intval($detail->unit_price)) }}" 
                                    class="form-input" step="1">
                                <span>円</span>
                            </td>
                            <td>
                                <input type="number" name="details[{{ $index }}][quantity]" value="{{ old("details.$index.quantity", $detail->quantity) }}" class="form-input" step="1">
                            </td>
                            <td>
                                <input type="text" name="details[{{ $index }}][remarks]" value="{{ old("details.$index.remarks", $detail->remarks) }}" class="form-input">
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('orders.order_details', ['order_id' => $order->order_id]) }}" class="btn btn-secondary">← 注文詳細に戻る</a>
            <button type="submit" class="btn btn-primary">修正を確定</button>
        </div>
    </form>
</div>
@if (session('update_success'))
<script>
    alert('修正が完了しました');
    window.location.href = "{{ route('orders.order_details', ['order_id' => $order->order_id]) }}";
</script>
@endif

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    let hasInvalid = false;
    let message = '';

    const unitPrices = document.querySelectorAll('input[name^="details"][name$="[unit_price]"]');
    const quantities = document.querySelectorAll('input[name^="details"][name$="[quantity]"]');
    const productNames = document.querySelectorAll('input[name^="details"][name$="[product_name]"]');

    // 単価・数量 = 0 チェック
    unitPrices.forEach((unitInput, index) => {
        const quantityInput = quantities[index];
        const unit = parseFloat(unitInput.value) || 0;
        const qty = parseFloat(quantityInput.value) || 0;

        if (unit <= 0 || qty <= 0) {
            hasInvalid = true;
            message += `明細 ${index + 1}: 単価・数量は1以上で入力してください。`;
        }
    });

    if (hasInvalid) {
        e.preventDefault();
        alert(message);
        return;
    }

    // 商品名空欄チェック
    let hasEmptyName = false;
    productNames.forEach((p) => {
        if (p.value.trim() === '') {
            hasEmptyName = true;
        }
    });

    if (hasEmptyName) {
        const result = confirm('商品名が空白の明細があります。本当に登録してよろしいですか？');
        if (!result) {
            e.preventDefault(); // 「いいえ」を選んだら止める
        }
        // 「はい」の場合は何もしない → フォーム送信継続される
    }
});
</script>

</body>
</html>
