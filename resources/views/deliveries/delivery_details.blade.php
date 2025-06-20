<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>納品明細</title>
    {{-- すべてのCSSを統合したファイルを参照 --}}
    <link href="{{ asset('css/page/delivery_details.css') }}" rel="stylesheet">
</head>
<body class="c-body">
    <div class="l-container">
        <h1 class="c-heading-primary">
            納品明細 <span class="c-text-id">(納品ID: {{ $delivery->delivery_id }})</span>
        </h1>

        <div class="c-card">
            <h2 class="c-heading-secondary">納品情報</h2>
            <div class="p-delivery-info-grid">
                <p><strong>顧客ID:</strong> {{ $delivery->customer_id }}</p>
                <p><strong>顧客名:</strong> {{ $delivery->customer_name }}</p>
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
                            <th class="c-table__th">注文ID</th>
                            <th class="c-table__th">注文明細ID</th>
                            <th class="c-table__th">商品名</th>
                            <th class="c-table__th">数量</th>
                            <th class="c-table__th">単価</th>
                            <th class="c-table__th">備考</th>
                            <th class="c-table__th">返品状況</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalAmount = 0;
                        @endphp
                        @foreach ($deliveryDetails as $detail)
                            <tr class="c-table__row">
                                <td class="c-table__td">{{ $detail->order_id }}</td>
                                <td class="c-table__td">{{ $detail->order_detail_id }}</td>
                                <td class="c-table__td">{{ $detail->product_name }}</td>
                                <td class="c-table__td">{{ $detail->delivery_quantity }}</td>
                                <td class="c-table__td">{{ number_format($detail->unit_price) }}円</td> {{-- 単価列を追加 --}}
                                <td class="c-table__td">{{ $detail->remarks }}</td>
                                <td class="c-table__td">
                                    <span class="c-status-badge {{ $detail->return_flag ? 'c-status-badge--returned' : 'c-status-badge--not-returned' }}">
                                        {{ $detail->return_flag ? '済' : '未' }}
                                    </span>
                                </td>
                            </tr>
                            @php
                                $totalAmount += $detail->delivery_quantity * $detail->unit_price;
                            @endphp
                        @endforeach
                        {{-- 合計金額欄を追加 --}}
                        <tr class="c-table__row c-table__row--total">
                            <td class="c-table__td" colspan="4"><strong>合計金額</strong></td>
                            <td class="c-table__td" colspan="3"><strong>{{ number_format($totalAmount) }}円</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="c-action-buttons-wrap">
            <a href="{{ route('deliveries.index') }}" class="c-button c-button--gray">
                戻る
            </a>
            <a href="{{ route('deliveries.return_form', ['delivery_id' => $delivery->delivery_id]) }}"
               class="c-button c-button--red">
                返品
            </a>
            <button class="c-button btn-primary js-print-btn"
                    data-print-url="{{ route('deliveries.print_page', ['delivery' => $delivery->delivery_id]) }}">
                納品書を印刷
            </button>
        </div>
    </div>
    <script src="{{ asset('js/print.js') }}" defer></script>
</body>
</html>