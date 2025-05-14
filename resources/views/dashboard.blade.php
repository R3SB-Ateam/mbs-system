<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ダッシュボード</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 50px;
            text-align: center;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 200px;
            margin: 0 auto;
        }
        .btn {
            padding: 15px;
            background-color: #3490dc;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #2779bd;
        }
    </style>
</head>
<body>
    <h1>ダッシュボード</h1>
    <div class="button-container">
        <a href="{{ route('orders.index') }}" class="btn">注文一覧</a>
        <a href="{{ route('deliveries.index') }}" class="btn">納品一覧</a>
        <a href="{{ route('customers.index') }}" class="btn">顧客一覧</a>
        <a href="{{ route('customers.edit') }}" class="btn">顧客更新画面</a>
    </div>
</body>
</html>
