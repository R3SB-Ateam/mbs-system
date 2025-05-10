<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>納品一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="container mx-auto p-4">
        <h1 class="text-xl font-semibold mb-4">納品一覧</h1>

        <div class="mb-4">
            <a href="{{ route('dashboard') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                ダッシュボードに戻る
            </a>
        </div>

        <form method="GET" action="{{ route('deliveries.index') }}" class="mb-4">
            <label for="store_id">店舗を選択:</label>
            <select name="store_id" id="store_id" class="border p-1 rounded">
                <option value="">全店舗</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->store_id }}" {{ $selectedStoreId == $store->store_id ? 'selected' : '' }}>
                        {{ $store->store_name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="ml-2 px-3 py-1 bg-green-500 text-white rounded">絞り込み</button>
        </form>

        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border-b">納品ID</th>
                    <th class="p-2 border-b">納品日</th>
                    <th class="p-2 border-b">備考</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deliveries as $delivery)
                    <tr>
                        <td class="p-2 border-b">
                            <a href="{{ route('deliveries.details', ['delivery_id' => $delivery->delivery_id]) }}"
                               class="text-blue-600 hover:underline">
                                {{ $delivery->delivery_id }}
                            </a>
                        </td>
                        <td class="p-2 border-b">{{ $delivery->delivery_date }}</td>
                        <td class="p-2 border-b">{{ $delivery->remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
