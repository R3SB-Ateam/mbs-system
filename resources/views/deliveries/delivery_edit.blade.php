<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>納品修正</title>
    <link rel="stylesheet" href="{{ asset('css/page/order_edit_blade.css') }}">
</head>
<body>
<div class="container">
    <h1>納品修正 <span class="sub">（納品ID: {{ $delivery->delivery_id }}）</span></h1>

    <form method="POST" action="{{ route('deliveries.update', $delivery->delivery_id) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <h2>納品情報</h2>
            <div class="info-grid">
                <div>
                    <strong>顧客ID:</strong> {{ $delivery->customer_id }}<br>
                    <strong>顧客名:</strong> {{ $delivery->customer_name }}
                </div>
                <div><strong>注文日:</strong> {{ $delivery->order_date }}</div>
                <div class="remarks-row">
                    <strong>備考:</strong>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks', $delivery->remarks) }}</textarea>
                </div>
            </div>
        </div>

        <div class="order-details">
            <h2 class="section-title-detail">納品商品の修正</h2>
            <div class="table-container">
                <table class="table">
                    <colgroup>
                        <col style="width: 5%;">
                        <col style="width: 25%;">
                        <col style="width: 15%;">
                        <col style="width: 15%;">
                        <col style="width: 15%;">
                        <col style="width: 25%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>明細ID</th>
                            <th>商品名</th>
                            <th>単価</th>
                            <th>注文数</th>
                            <th>納品数</th>
                            <th>備考</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deliveryDetails as $index => $detail)
                            <tr>
                                <td>
                                    <input type="hidden" name="details[{{ $index }}][delivery_detail_id]" value="{{ $detail->delivery_detail_id }}">
                                    <input type="hidden" name="details[{{ $index }}][order_quantity]" value="{{ $detail->order_quantity }}">
                                    {{ $detail->delivery_detail_id }}
                                </td>
                                <td>{{ $detail->product_name }}</td>
                                <td>{{ number_format($detail->unit_price) }}</td>
                                <td>{{ $detail->order_quantity }}</td>
                                <td>
                                    <input type="number" name="details[{{ $index }}][delivery_quantity]"
                                           value="{{ old("details.$index.delivery_quantity", $detail->delivery_quantity) }}"
                                           class="form-input" step="1" min="0" max="{{ $detail->order_quantity }}">
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $index }}][remarks]"
                                           value="{{ old("details.$index.remarks", $detail->remarks) }}"
                                           class="form-input">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('deliveries.details', ['delivery_id' => $delivery->delivery_id]) }}" class="btn btn-secondary">← 納品詳細に戻る</a>
            <button type="submit" class="btn btn-primary">修正を確定</button>
        </div>
    </form>
</div>

@if (session('update_success'))
<script>
    alert('修正が完了しました');
    window.location.href = "{{ route('deliveries.details', ['delivery_id' => $delivery->delivery_id]) }}";
</script>
@endif

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    let hasInvalid = false;
    let message = '';

    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const id = row.querySelector('input[name$="[delivery_detail_id]"]').value;
        const qtyInput = row.querySelector('input[name$="[delivery_quantity]"]');
        const max = parseInt(row.querySelector('input[name$="[order_quantity]"]').value);
        const val = parseInt(qtyInput.value) || 0;

        if (val < 0 || val > max) {
            hasInvalid = true;
            message += `(明細ID: ${id}): 納品数量は0以上、注文数量(${max})以下で入力してください。\n`;
        }
    });

    if (hasInvalid) {
        e.preventDefault();
        alert(message);
    }
});
</script>
</body>
</html>
