<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>納品詳細</title>
    {{-- すべてのCSSを統合したファイルを参照 --}}
    <link href="{{ asset('css/page/delivery_details.css') }}" rel="stylesheet">
</head>
<body class="c-body">
    <div class="l-container">
        <h1 class="c-heading-primary">
            納品詳細 <span class="c-text-id">(納品ID: {{ $delivery->delivery_id }})</span>
        </h1>

        <div class="c-action-buttons-wrap">
            <a href="{{ route('deliveries.index') }}" class="c-button c-button--gray">
                ← 納品一覧に戻る
            </a>
            <a href="{{ route('deliveries.return_form', ['delivery_id' => $delivery->delivery_id]) }}"
               class="c-button c-button--red">
                返品処理
            </a>
        </div>

        <div class="c-card">
            <h2 class="c-heading-secondary">納品情報</h2>
            <div class="p-delivery-info-grid">
                <p><strong>顧客ID:</strong> {{ $delivery->customer_id }}</p>
                <p><strong>納品日:</strong> {{ $delivery->delivery_date }}</p>
                <p class="p-delivery-info-remarks"><strong>備考:</strong> {{ $delivery->remarks }}</p>
            </div>
        </div>

        <div class="c-card">
            <h2 class="c-heading-secondary c-heading-secondary--mb4">納品商品一覧</h2>
            <div class="c-table-responsive">
                <table class="c-table">
                    <thead class="c-table__head">
                        <tr>
                            <th class="c-table__th">商品名</th>
                            <th class="c-table__th">数量</th>
                            <th class="c-table__th">備考</th>
                            <th class="c-table__th">返品状況</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deliveryDetails as $detail)
                            <tr class="c-table__row">
                                <td class="c-table__td">{{ $detail->product_name }}</td>
                                <td class="c-table__td">{{ $detail->delivery_quantity }}</td>
                                <td class="c-table__td">{{ $detail->remarks }}</td>
                                <td class="c-table__td">
                                    <span class="c-status-badge {{ $detail->return_flag ? 'c-status-badge--returned' : 'c-status-badge--not-returned' }}">
                                        {{ $detail->return_flag ? '済' : '未' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>