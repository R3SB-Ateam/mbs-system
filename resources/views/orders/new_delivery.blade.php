<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>納品登録</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-semibold mb-4">納品登録</h1>
        <form method="POST" action="{{ route('orders.delivery_store') }}">
            @csrf

            <div class="mb-4">
                <label for="delivery_date" class="block font-semibold mb-1">納品日:</label>
                <input type="date" id="delivery_date" name="delivery_date" required class="w-full border p-2 rounded">
            </div>

            <div class="mb-4">
                <label for="remarks" class="block font-semibold mb-1">備考:</label>
                <textarea id="remarks" name="remarks" rows="3" class="w-full border p-2 rounded"></textarea>
            </div>

            <h2 class="text-xl font-semibold mt-6 mb-2">注文商品一覧</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">商品名</th>
                            <th class="p-2 border">注文数</th>
                            <th class="p-2 border">単価</th>
                            <th class="p-2 border">納品数</th>
                        </tr>
                    </thead>
                    <tbody>
                        <input type="hidden" name="customer_id" value="{{ $customer_id }}">
                        @foreach ($orderIds as $orderId)
                            <input type="hidden" name="order_ids[]" value="{{ $orderId }}">
                        @endforeach
                        @foreach ($orderDetails as $orderDetail)
                            <tr>
                                <td class="p-2 border">{{ $orderDetail->product_name }}</td>
                                <td class="p-2 border">{{ $orderDetail->quantity }}</td>
                                <td class="p-2 border">{{ $orderDetail->unit_price }}</td>
                                <td class="p-2 border">
                                    <input type="hidden" name="order_detail_ids[]" value="{{ $orderDetail->order_detail_id }}">
                                    <input type="number" name="delivery_quantities[]" value="0" min="0" max="{{ $orderDetail->quantity }}" required class="w-24 border p-1 rounded">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    納品登録
                </button>
            </div>
        </form>
    </div>
</body>
</html>
