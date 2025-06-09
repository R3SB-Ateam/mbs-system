<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>納品一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="max-w-6xl mx-auto px-6 py-8">

        <!-- エラーメッセージ -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- タイトル -->
        <h1 class="text-2xl font-bold mb-6 border-b pb-2">納品一覧★</h1>

        <!-- ボタン -->
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="inline-block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                ← ダッシュボードに戻る
            </a>
        </div>

        <!-- フィルター -->
        <form method="GET" action="{{ route('deliveries.index') }}" class="mb-6 flex flex-col md:flex-row md:items-center gap-4">
            <div>
                <label for="store_id" class="block mb-1 text-sm font-medium">店舗を選択:</label>
                <select name="store_id" id="store_id" class="border border-gray-300 rounded px-3 py-2 w-48">
                    <option value="">全店舗</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->store_id }}" {{ $selectedStoreId == $store->store_id ? 'selected' : '' }}>
                            {{ $store->store_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1">
                <label for="keyword" class="block mb-1 text-sm font-medium">キーワード検索:</label>
                <input type="text" name="keyword" id="keyword" placeholder="納品ID、顧客名、備考で検索"
                    value="{{ $keyword ?? '' }}"
                    class="border border-gray-300 rounded px-3 py-2 w-full">
            </div>

            <div class="self-end md:self-auto">
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    絞り込み
                </button>
            </div>
        </form>

        <!-- 納品テーブル -->
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 border-b">納品ID</th>
                        <th class="px-4 py-3 border-b">顧客ID</th>
                        <th class="px-4 py-3 border-b">顧客名</th>
                        <th class="px-4 py-3 border-b">納品日</th>
                        <th class="px-4 py-3 border-b">備考</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($deliveries as $delivery)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('deliveries.details', ['delivery_id' => $delivery->delivery_id]) }}"
                                   class="text-blue-600 hover:underline font-medium">
                                    {{ $delivery->delivery_id }}
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ $delivery->customer_id }}</td>
                            <td class="px-4 py-3">{{ $delivery->customer_name }}</td>
                            <td class="px-4 py-3">{{ $delivery->delivery_date }}</td>
                            <td class="px-4 py-3">{{ $delivery->remarks }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>
