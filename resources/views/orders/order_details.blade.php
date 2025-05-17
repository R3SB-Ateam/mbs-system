<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="max-w-5xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6 border-b pb-2">注文詳細 <span class="text-sm text-gray-600">(注文ID: {{ $order->order_id }})</span></h1>

        <!-- 注文情報 -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <h2 class="text-xl font-semibold mb-2">注文情報</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>顧客ID:</strong> {{ $order->customer_id }}</p>
                <p><strong>注文日:</strong> {{ $order->order_date }}</p>
                <p class="md:col-span-2"><strong>備考:</strong> {{ $order->remarks }}</p>
            </div>
        </div>

        <!-- 注文明細情報 -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <h2 class="text-xl font-semibold mb-4">注文商品の詳細</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-2 border-b">注文詳細ID</th>
                            <th class="px-4 py-2 border-b">商品名</th>
                            <th class="px-4 py-2 border-b">単価</th>
                            <th class="px-4 py-2 border-b">数量</th>
                            <th class="px-4 py-2 border-b">納品状況</th>
                            <th class="px-4 py-2 border-b">納品日</th>
                            <th class="px-4 py-2 border-b">備考</th>
                            <th class="px-4 py-2 border-b">キャンセル</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderDetails as $orderDetail)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $orderDetail->order_detail_id }}</td>
                                <td class="px-4 py-2 border-b">{{ $orderDetail->product_name }}</td>
                                <td class="px-4 py-2 border-b">{{ number_format($orderDetail->unit_price) }} 円</td>
                                <td class="px-4 py-2 border-b">{{ $orderDetail->quantity }}</td>
                                <td class="px-4 py-2 border-b">
                                    <span class="inline-block px-2 py-1 text-xs rounded 
                                        {{ $orderDetail->delivery_status == 1 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $orderDetail->delivery_status == 1 ? '納品済み' : '未納品' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border-b">{{ $orderDetail->delivery_date ?? '-' }}</td>
                                <td class="px-4 py-2 border-b">{{ $orderDetail->remarks }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($orderDetail->cancell_flag == 1)
                                        <span class="text-red-500 font-semibold">キャンセル済</span>
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

        <!-- ボタン -->
        <div class="flex justify-between">
            <a href="{{ route('orders.index') }}" class="inline-block px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                ← 注文一覧に戻る
            </a>
            <a href="{{ route('orders.cancel', ['order_id' => $order->order_id]) }}"
               class="inline-block px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                キャンセル
            </a>
        </div>
    </div>
</body>
</html>
