<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">キャンセル対象一覧（注文ID: {{ $order->order_id }}）</h1>

        <form action="{{ route('orders.processCancel') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->order_id }}">

            <div class="overflow-x-auto">
                <table class="w-full table-auto border border-gray-300 bg-white shadow-md rounded-md">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="p-3 border">商品名</th>
                            <th class="p-3 border">数量</th>
                            <th class="p-3 border">キャンセル数</th>
                            <th class="p-3 border">キャンセル理由</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderDetails as $detail)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 border">{{ $detail->product_name }}</td>
                            <td class="p-3 border text-center">{{ $detail->quantity }}</td>
                            <td class="p-3 border text-center">
                                <input type="number" name="cancel_quantities[{{ $detail->order_detail_id }}]" min="0" max="{{ $detail->quantity }}"
                                       class="w-full border rounded px-2 py-1">
                            </td>
                            <td class="p-3 border">
                                <input type="text" name="reasons[{{ $detail->order_detail_id }}]" maxlength="255"
                                       class="w-full border rounded px-2 py-1">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded shadow">
                    キャンセル実行
                </button>
            </div>
        </form>
    </div>
</body>
</html>
