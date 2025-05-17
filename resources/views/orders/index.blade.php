<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    @if (session('success'))
        <div
            id="toast-message"
            class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg transition-opacity duration-500">
            {{ session('success') }}
        </div>
    @endif

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
        <h1 class="text-xl font-semibold mb-4">注文一覧</h1>
        <div class="mb-4 space-x-4">
            <a href="{{ route('orders.new_order') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                新規注文登録
            </a>
            <a href="{{ route('dashboard') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                ダッシュボードに戻る
            </a>
        </div>
        <form method="GET" action="{{ route('orders.index') }}" class="mb-4">
            <label for="store_id">店舗を選択:</label>
            <select name="store_id" id="store_id" class="border p-1 rounded">
                <option value="">全店舗</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->store_id }}" {{ $selectedStoreId == $store->store_id ? 'selected' : '' }}>
                        {{ $store->store_name }}
                    </option>
                @endforeach
            </select>

            <input type="text" name="keyword" placeholder="注文ID、顧客名、備考で検索" value="{{ $keyword ?? '' }}"
           class="border p-1 rounded w-64">

            <button type="submit" class="ml-2 px-3 py-1 bg-blue-500 text-white rounded">絞り込み</button>
        </form>
        <form method="POST" action="{{ route('orders.delivery_prepare') }}">
            @csrf
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th></th>
                        <th class="p-2 border-b">注文ID</th>
                        <th class="p-2 border-b">顧客ID</th>
                        <th class="p-2 border-b">顧客名</th>
                        <th class="p-2 border-b">注文日</th>
                        <th class="p-2 border-b">備考</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><input type="checkbox" name="order_ids[]" value="{{ $order->order_id }}"></td>
                            <td class="p-2 border-b">
                                <a href="{{ route('orders.order_details', ['order_id' => $order->order_id]) }}" class="text-blue-600 hover:underline">
                                    {{ $order->order_id }}
                                </a>
                            </td>
                            <td class="p-2 border-b">{{ $order->customer_id }}</td>
                            <td class="p-2 border-b">{{ $order->customer_name }}</td>
                            <td class="p-2 border-b">{{ $order->order_date }}</td>
                            <td class="p-2 border-b">{{ $order->remarks }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                納品登録
            </button>
        </form>
    </div>
</body>
</html>
