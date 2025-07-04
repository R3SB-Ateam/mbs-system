<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ダッシュボード</title>
    <link rel="stylesheet" href="{{ asset('css/page/dashboard.css') }}">
    <script src="{{ asset('js/dashboard.js') }}"></script>
    
</head>
<body>
    <div class="button-container">
        <h2>ダッシュボード</h2>
        <a href="{{ route('orders.index', ['store_id' => $selectedStoreId]) }}" class="btn">注文</a>
        <a href="{{ route('deliveries.index', ['store_id' => $selectedStoreId]) }}" class="btn">納品</a>
        <a href="{{ route('customers.index', ['store_id' => $selectedStoreId]) }}" class="btn">顧客</a>
        <a href="{{ route('customers.edit', ['store_id' => $selectedStoreId]) }}" class="btn">顧客更新</a>

    </div>
    <div class="content">
        <h1>ようこそ、MBSシステムへ</h1>
        <p id="menu-info">左側のメニューから各機能へ移動できます。</p>

        <!-- 店舗選択フォーム -->
        <form method="GET" action="{{ route('dashboard') }}" id="store-select-form">
            <label for="store-select">店舗を選択:</label>
            <select name="store_id" id="store-select" onchange="this.form.submit()">
                <option value="">全店舗</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->store_id }}" {{ $selectedStoreId == $store->store_id ? 'selected' : '' }}>
                        {{ $store->store_name }}
                    </option>
                @endforeach
            </select>
        </form>

        <!-- 表示範囲フォーム -->
        <form method="GET" action="{{ route('dashboard') }}" id="filter-toggle" class="filter-toggle">
            <!-- ここで現在選択中のstore_idを隠しフィールドで渡す -->
            <input type="hidden" name="store_id" value="{{ $selectedStoreId }}">

            <fieldset>
                <legend>表示範囲</legend>
                <input type="radio" name="filter" value="all" id="filter-all" onchange="this.form.submit()" {{ $filter === 'all' ? 'checked' : '' }}>
                <label for="filter-all">全体</label>

                <input type="radio" name="filter" value="recent" id="filter-recent" onchange="this.form.submit()" {{ $filter === 'recent' ? 'checked' : '' }}>
                <label for="filter-recent">直近1週間</label>
            </fieldset>
        </form>


        <div class="status-cards">
            <div class="status-card" style="border-left-color: #3498db;">
                <h3>{{$filter === 'recent' ? '1週間の注文件数' : '全体の注文件数'}}</h3>
                <p>{{$orderCount}} 件</p>
            </div>
            <div class="status-card" style="border-left-color: #e67e22;">
                <h3>{{$filter === 'recent' ? '1週間の納品件数' : '全体の納品件数'}}</h3>
                <p>{{$deliveryCount}} 件</p>
            </div>
            <div class="status-card" style="border-left-color: #2ecc71;">
                <h3>{{$filter === 'recent' ? '直近1週間の合計金額' : '全体の合計金額'}}</h3>
                <p>{{$total_price}} 円</p>
            </div>
        </div>
    </div>
</body>
</html>
