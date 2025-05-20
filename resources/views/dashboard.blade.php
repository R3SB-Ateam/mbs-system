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
        <a href="{{ route('orders.index') }}" class="btn">注文一覧</a>
        <a href="{{ route('deliveries.index') }}" class="btn">納品一覧</a>
        <a href="{{ route('customers.index') }}" class="btn">顧客一覧</a>
        <a href="{{ route('customers.edit') }}" class="btn">顧客更新</a>
    </div>
    <div class="content">
        <h1>ようこそ、MBSシステムへ</h1>
        <p>左側のメニューから各機能へ移動できます。</p>

        <div class="status-cards">
            <div class="status-card" style="border-left-color: #3498db;">
                <h3>注文件数</h3>
                <p>23 件</p>
            </div>
            <div class="status-card" style="border-left-color: #e67e22;">
                <h3>納品件数</h3>
                <p>5 件</p>
            </div>
            <div class="status-card" style="border-left-color: #2ecc71;">
                <h3>顧客数</h3>
                <p>112 名</p>
            </div>
        </div>
    </div>
</body>
</html>
