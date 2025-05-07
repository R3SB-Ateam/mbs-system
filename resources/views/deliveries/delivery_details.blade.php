<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>納品詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto p-4">
        <h1 class="text-xl font-semibold mb-4">納品詳細（納品ID: {{ $delivery->delivery_id }}）</h1>

        <div class="mb-4 space-x-4">
            <a href="{{ route('deliveries.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                納品一覧に戻る
            </a>
            <a href="{{ route('deliveries.return_form', ['delivery_id' => $delivery->delivery_id]) }}"
               class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                返品処理
            </a>
        </div>

        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border-b">商品名</th>
                    <th class="p-2 border-b">数量</th>
                    <th class="p-2 border-b">備考</th>
                    <th class="p-2 border-b">返品済</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deliveryDetails as $detail)
                    <tr>
                        <td class="p-2 border-b">{{ $detail->product_name }}</td>
                        <td class="p-2 border-b">{{ $detail->delivery_quantity }}</td>
                        <td class="p-2 border-b">{{ $detail->remarks }}</td>
                        <td class="p-2 border-b">
                            {{ $detail->return_flag ? '済' : '未' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
