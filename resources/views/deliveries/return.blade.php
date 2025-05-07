<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>返品処理</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto p-4">
        <h1 class="text-xl font-semibold mb-4">返品対象一覧（納品ID: {{ $delivery_id }}）</h1>

        <form action="{{ route('deliveries.processReturn') }}" method="POST">
            @csrf
            <input type="hidden" name="delivery_id" value="{{ $delivery_id }}">

            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border-b">商品名</th>
                        <th class="p-2 border-b">数量</th>
                        <th class="p-2 border-b">返品数</th>
                        <th class="p-2 border-b">返品理由</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deliveryDetails as $detail)
                        @if (!$detail->return_flag)
                            <tr>
                                <td class="p-2 border-b">{{ $detail->product_name }}</td>
                                <td class="p-2 border-b">{{ $detail->delivery_quantity }}</td>
                                <td class="p-2 border-b">
                                    <input type="number" name="return_quantities[{{ $detail->delivery_detail_id }}]"
                                           min="0" max="{{ $detail->delivery_quantity }}" class="border rounded px-2 py-1 w-20">
                                </td>
                                <td class="p-2 border-b">
                                    <input type="text" name="reasons[{{ $detail->delivery_detail_id }}]"
                                           maxlength="255" class="border rounded px-2 py-1 w-full">
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <button type="submit" class="bg-red-500 text-white px-4 py-2 mt-4 rounded hover:bg-red-600">
                返品実行
            </button>
        </form>

        <div class="mt-4">
            <a href="{{ route('deliveries.details', ['delivery_id' => $delivery_id]) }}"
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                納品詳細に戻る
            </a>
        </div>
    </div>
</body>
</html>
