<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>注文一覧</title>
    <link rel="stylesheet" href="{{ asset('css/page/orders.css') }}">
    <script src="{{ asset('js/orders.js') }}"></script>

</head>
<body>
    @if (session('success'))
        <div id="toast-message">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="error-message">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container">
        <h1>注文一覧</h1>

        <form method="GET" action="{{ route('orders.index') }}" class="search-form">
            <label for="store_id">店舗を選択:</label>
            <select name="store_id" id="store_id">
                <option value="" {{ $selectedStoreId === '' ? 'selected' : '' }}>全店舗</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->store_id }}" {{ $selectedStoreId == $store->store_id ? 'selected' : '' }}>
                        {{ $store->store_name }}
                    </option>
                @endforeach
            </select>

            <input type="text" name="keyword" placeholder="注文ID、顧客名、備考で検索" value="{{ $keyword ?? '' }}" class="keyword-text">
            <button type="submit" class="filter-button">絞り込み</button>
        </form>

        
        <form method="POST" action="{{ route('orders.delivery_prepare') }}">
        <div class="table-wrapper">
            @csrf
            <table>
                <colgroup>
                    <col style="width: 5%;">
                    <col style="width: 10%;">
                    <col style="width: 12%;">
                    <col style="width: 10%;">
                    <col style="width: 15%;">
                    <col style="width: 13%;">
                    <col style="min-width: 80px;">
                    <col style="min-width: 150px;">
                </colgroup>
                <thead>
                    <tr>
                        <th></th>
                        <th>注文ID</th>
                        <th>納品状況</th>
                        <th>顧客ID</th>
                        <th>顧客名</th>
                        <th>注文日</th>
                        <th>注文金額</th>
                        <th>備考</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><input 
                                    type="checkbox" 
                                    name="order_ids[]" 
                                    value="{{ $order->order_id }}"
                                    {{ $order->delivery_status_text === '納品済み' ? 'disabled' : '' }}>
                            </td>
                            <td><a href="{{ route('orders.order_details', ['order_id' => $order->order_id]) }}" class="table-link">{{ number_format($order->order_id) }}</a></td>
                            <td>{{ $order->delivery_status_text }}</td>
                            <td>{{ $order->customer_id }}</td>
                            <td>{{ $order->customer->name ?? '不明'}}</td>
                            <td>{{ $order->order_date }}</td>
                            <td>{{ number_format($order->total_amount) }}</td> <!-- 注文金額 -->
                            <td>{{ $order->remarks }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <div class="nav-buttons">
                <a href="{{ route('dashboard', ['store_id' => request('store_id', '')]) }}" class="button-link gray">ダッシュボードに戻る</a>
                <a href="{{ route('orders.new_order') }}" class="button-link blue">新規注文登録</a>
            <button type="submit" class="submit-button">納品登録</button>
            </div>
        </form>
    </div>
</body>
</html>
