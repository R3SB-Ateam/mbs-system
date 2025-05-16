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
            padding: 40px;
            background-color: #3490dc;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            box-shadow: 3px 3px 6px -2px #555,
            3px 3px 8px rgba(255,255,255,0.8) inset;  
            }
        .btn:hover {
            background-color: #2779bd;
            box-shadow: -5px -5px 10px #668AD8,
            3px 3px 8px rgba(255,255,255,0.8) inset;
        }
    </style>
    <link rel="stylesheet" href="../resources/css/dashboard.css">
</head>
<body>
    <h1>ダッシュボード</h1>
    <div class="button-container">
        <a href="{{ route('orders.index') }}" class="btn" id="order_btn">注文一覧</a>
        <a href="{{ route('deliveries.index') }}" class="btn" id="deliveries_btn">納品一覧</a>
        <a href="{{ route('customers.index') }}" class="btn" id="customer_btn">顧客一覧</a>
        <a href="{{ route('customers.edit') }}" class="btn" id="customer_update_btn">顧客更新</a>
    </div>
</body>
</html>
