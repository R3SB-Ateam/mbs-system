<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規注文登録</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function addProductRow() {
            const container = document.getElementById('products-container');
            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-4 gap-4 items-center mb-2';
            newRow.innerHTML = `
                <input type="text" name="product_name[]" placeholder="商品名" required class="border p-2">
                <input type="number" name="unit_price[]" placeholder="単価" min="0" required class="border p-2">
                <input type="number" name="quantity[]" placeholder="数量" min="1" required class="border p-2">
                <button type="button" onclick="removeProductRow(this)" class="text-red-500">削除</button>
            `;
            container.appendChild(newRow);
        }

        function removeProductRow(button) {
            button.parentElement.remove();
        }
    </script>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">新規注文登録</h1>

        <form method="POST" action="{{ route('orders.order_store') }}">
            @csrf
            <div class="mb-4">
                <label for="customer_id" class="block mb-1 font-semibold">顧客選択:</label>
                <select name="customer_id" id="customer_id" required class="border p-2 w-full">
                    <option value="">選択してください</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->customer_id }}">
                            {{ $customer->customer_id }} - {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="products-container" class="mb-4">
                <div class="grid grid-cols-4 gap-4 items-center mb-2">
                    <input type="text" name="product_name[]" placeholder="商品名" required class="border p-2">
                    <input type="number" name="unit_price[]" placeholder="単価" min="0" required class="border p-2">
                    <input type="number" name="quantity[]" placeholder="数量" min="1" required class="border p-2">
                </div>
            </div>

            <button type="button" onclick="addProductRow()" class="bg-green-500 text-white px-4 py-2 rounded mb-4">商品を追加</button>

            <div class="mb-4">
                <label for="remarks" class="block mb-1 font-semibold">備考:</label>
                <textarea name="remarks" id="remarks" rows="3" class="border p-2 w-full"></textarea>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('orders.index') }}" class="inline-block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    ← 注文一覧に戻る
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                    注文を登録
                </button>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 text-red-800 p-2 rounded mb-4">
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
