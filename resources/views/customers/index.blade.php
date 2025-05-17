<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>顧客一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
<div class="container mx-auto p-4">
    <h1 class="text-xl font-semibold mb-4">顧客一覧</h1>

    <div class="mb-4">
        <a href="{{ route('dashboard') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            ダッシュボードに戻る
        </a>
    </div>

    <!-- 店舗フィルター -->
    <form method="GET" action="{{ route('customers.index') }}" class="mb-4">
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

    <!-- 顧客一覧テーブル -->
    <table class="min-w-full bg-white border border-gray-300">
        <thead>
        <tr class="bg-gray-100">
            <th class="p-2 border-b">顧客ID</th>
            <th class="p-2 border-b">顧客名</th>
            <th class="p-2 border-b">登録日</th>
            <th class="p-2 border-b">電話番号</th>
            <th class="p-2 border-b">売上</th>
            <th class="p-2 border-b">平均RT</th> {{-- 後で実装予定 --}}
        </tr>
        </thead>
        <tbody>
        @foreach ($customers as $customer)
            <tr>
                <td class="p-2 border-b">{{ $customer->customer_id }}</td>
                <td class="p-2 border-b">{{ $customer->name }}</td>
                <td class="p-2 border-b">{{ $customer->registration_date }}</td>
                <td class="p-2 border-b">{{ $customer->phone_number }}</td>
                <td class="p-2 border-b">{{ number_format($customer->total_sales) }}円</td>
                <td class="p-2 border-b">-</td> {{-- 平均RTは後で対応 --}}
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
