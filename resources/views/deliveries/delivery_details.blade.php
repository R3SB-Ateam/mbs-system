<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>納品詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="max-w-5xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6 border-b pb-2">
            納品詳細 <span class="text-sm text-gray-600">(納品ID: {{ $delivery->delivery_id }})</span>
        </h1>

        <!-- アクションボタン -->
        <div class="mb-6 flex flex-wrap gap-4">
            <a href="{{ route('deliveries.index') }}"
               class="inline-block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                ← 納品一覧に戻る
            </a>
            <a href="{{ route('deliveries.return_form', ['delivery_id' => $delivery->delivery_id]) }}"
               class="inline-block px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                返品処理
            </a>
        </div>

        <!-- 納品情報 -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <h2 class="text-xl font-semibold mb-2">納品情報</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>顧客ID:</strong> {{ $delivery->customer_id }}</p>
                <p><strong>納品日:</strong> {{ $delivery->delivery_date }}</p>
                <p class="md:col-span-2"><strong>備考:</strong> {{ $delivery->remarks }}</p>
            </div>
        </div>

        <!-- 納品詳細テーブル -->
        <div class="bg-white shadow rounded-lg p-4">
            <h2 class="text-xl font-semibold mb-4">納品商品一覧</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-2 border-b">商品名</th>
                            <th class="px-4 py-2 border-b">数量</th>
                            <th class="px-4 py-2 border-b">備考</th>
                            <th class="px-4 py-2 border-b">返品状況</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deliveryDetails as $detail)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $detail->product_name }}</td>
                                <td class="px-4 py-2 border-b">{{ $detail->delivery_quantity }}</td>
                                <td class="px-4 py-2 border-b">{{ $detail->remarks }}</td>
                                <td class="px-4 py-2 border-b">
                                    <span class="inline-block px-2 py-1 text-xs rounded 
                                        {{ $detail->return_flag ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $detail->return_flag ? '済' : '未' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
