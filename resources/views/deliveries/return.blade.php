<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>返品処理</title>
    {{-- すべてのCSSを統合したファイルを参照 --}}
    <link href="{{ asset('css/page/delivery_return.css') }}" rel="stylesheet">
</head>
<body class="c-body">
    <div class="l-container-narrow">
        <h1 class="c-heading-secondary">返品対象一覧（納品ID: {{ $delivery_id }}）</h1>

        <form action="{{ route('deliveries.processReturn') }}" method="POST">
            @csrf
            <input type="hidden" name="delivery_id" value="{{ $delivery_id }}">

            <div class="c-table-responsive"> {{-- テーブルがはみ出さないようにラッパーを追加 --}}
                <table class="c-table c-table--bordered">
                    <thead>
                        <tr class="c-table__head c-table__head--light">
                            <th class="c-table__th c-table__th--padded">商品名</th>
                            <th class="c-table__th c-table__th--padded">数量</th>
                            <th class="c-table__th c-table__th--padded">返品数</th>
                            <th class="c-table__th c-table__th--padded">返品理由</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deliveryDetails as $detail)
                            @if (!$detail->return_flag)
                                <tr class="c-table__row">
                                    <td class="c-table__td c-table__td--padded">{{ $detail->product_name }}</td>
                                    <td class="c-table__td c-table__td--padded">{{ $detail->delivery_quantity }}</td>
                                    <td class="c-table__td c-table__td--padded">
                                        <input type="number" name="return_quantities[{{ $detail->delivery_detail_id }}]"
                                                min="0" max="{{ $detail->delivery_quantity }}" class="c-form-input c-form-input--small">
                                    </td>
                                    <td class="c-table__td c-table__td--padded">
                                        <input type="text" name="reasons[{{ $detail->delivery_detail_id }}]"
                                                maxlength="255" class="c-form-input c-form-input--full">
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="c-button c-button--red c-button--mt4">
                返品実行
            </button>
        </form>

        <div class="c-button-group c-button-group--mt4">
            <a href="{{ route('deliveries.details', ['delivery_id' => $delivery_id]) }}"
               class="c-button c-button--gray">
                納品詳細に戻る
            </a>
        </div>
    </div>
</body>
</html>