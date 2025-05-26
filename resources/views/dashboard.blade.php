<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ダッシュボード</title>
    <link rel="stylesheet" href="../resources/css/dashboard.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="button-container">
        <h2>ダッシュボード</h2>
        <a href="{{ route('orders.index') }}" class="btn">注文1覧</a>
        <a href="{{ route('deliveries.index') }}" class="btn">納品1覧</a>
        <a href="{{ route('customers.index') }}" class="btn">顧客1覧</a>
        <a href="{{ route('customers.edit') }}" class="btn">顧客更新</a>
    </div>
    <div class="content">
        <h1>ようこそ、MBSシステムへ</h1>
        <p id="menu-info">左側のメニューから各機能へ移動できます。</p>

        <form method="GET" action="{{ route('dashboard') }}" id="filter-toggle" class="filter-toggle">
        <fieldset>
        <legend>表示範囲</legend>
            <input type="radio" name="filter" value="all" id="filter-all" onchange="this.form.submit()" {{ $filter === 'all' ? 'checked' : '' }}>
            <label for="filter-all">全体</label>

            <input type="radio" name="filter" value="recent" id="filter-recent" onchange="this.form.submit()" {{ $filter === 'recent' ? 'checked' : '' }}>
            <label for="filter-recent">直近1週間</label>
        </fieldset>

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
                <h3>{{$filter === 'recent' ? '1週間の合計金額' : '全体の合計金額'}}</h3>
                <p>{{$customerCount}} 円</p>
            </div>
        </div>
    </div>
    <script src="/mbs-system/resources/js/dashboard.js"></script>
</body>
</html>
