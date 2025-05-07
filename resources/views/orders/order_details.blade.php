<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto p-4">
        <h1 class="text-xl font-semibold mb-4">注文詳細 (注文ID: {{ $order->order_id }})</h1>
        
        <!-- 注文情報 -->
        <div class="mb-4">
            <h2 class="text-lg font-semibold">注文情報</h2>
            <p><strong>顧客ID:</strong> {{ $order->customer_id }}</p>
            <p><strong>注文日:</strong> {{ $order->order_date }}</p>
            <p><strong>備考:</strong> {{ $order->remarks }}</p>
        </div>

        <!-- 注文明細情報 -->
        <h2 class="text-lg font-semibold mb-2">注文商品の詳細</h2>
        <table class="min-w-full bg-white border border-gray-300 mb-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border-b">商品名</th>
                    <th class="p-2 border-b">単価</th>
                    <th class="p-2 border-b">数量</th>
                    <th class="p-2 border-b">納品状況</th>
                    <th class="p-2 border-b">備考</th>
                    <th class="p-2 border-b">キャンセル</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderDetails as $orderDetail)
                    <tr>
                        <td class="p-2 border-b">{{ $orderDetail->product_name }}</td>
                        <td class="p-2 border-b">{{ $orderDetail->unit_price }}</td>
                        <td class="p-2 border-b">{{ $orderDetail->quantity }}</td>
                        <td class="p-2 border-b">
                            {{ $orderDetail->delivery_status == 1 ? '納品済み' : '未納品' }}
                        </td>
                        <td class="p-2 border-b">{{ $orderDetail->remarks }}</td>
                        <td class="p-2 border-b">
                            {{ $orderDetail->cancell_flag == 1 ? 'キャンセル済' : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('orders.index') }}" class="text-blue-600 hover:underline">注文一覧に戻る</a>
        <a href="{{ route('orders.cancel', ['order_id' => $order->order_id]) }}" class="text-blue-600 hover:underline">
            キャンセル
        </a>

    </div>
</body>
</html>