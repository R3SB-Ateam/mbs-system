<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規注文登録</title>
    <link href="{{ asset('css/page/new_order.css') }}" rel="stylesheet">
    
    <script>
        function addProductRow() {
            const container = document.getElementById('products-container');
            const newRow = document.createElement('div');
            newRow.className = 'product-row';
            newRow.innerHTML = `
                <input type="text" name="product_name[]" placeholder="商品名" required class="form-input">
                <input type="number" name="unit_price[]" placeholder="単価" min="0" required class="form-input">
                <input type="number" name="quantity[]" placeholder="数量" min="1" required class="form-input">
                <button type="button" onclick="removeProductRow(this)" class="btn-danger-text">削除</button>
            `;
            container.appendChild(newRow);
        }

        function removeProductRow(button) {
            button.parentElement.remove();
        }
    </script>
</head>
<body>
    <div class="page-container">
        <h1 class="page-title">新規注文登録</h1>

        <form method="POST" action="{{ route('orders.order_store') }}">
            @csrf
            <div class="form-group">
                <label for="customer_id" class="form-label">顧客選択:</label>
                <select name="customer_id" id="customer_id" required class="form-select">
                    <option value="">選択してください</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->customer_id }}">
                            {{ $customer->customer_id }} - {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="products-container" class="form-group">
                <div class="product-row">
                    <input type="text" name="product_name[]" placeholder="商品名" required class="form-input">
                    <input type="number" name="unit_price[]" placeholder="単価" min="0" required class="form-input">
                    <input type="number" name="quantity[]" placeholder="数量" min="1" required class="form-input">
                </div>
            </div>

            <button type="button" onclick="addProductRow()" class="btn-success btn-add-margin">商品を追加</button>

            <div class="form-group">
                <label for="remarks" class="form-label">備考:</label>
                <textarea name="remarks" id="remarks" rows="3" class="form-textarea"></textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('orders.index') }}" class="link-back">← 注文一覧へ戻る</a>
                <button type="submit" class="btn-primary">注文を登録</button>
            </div>

            @if ($errors->any())
                <div class="alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>・{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </form>
    </div>
</body>
</html>