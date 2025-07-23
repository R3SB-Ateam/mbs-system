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
        <h1 class="c-heading-secondary">返品 納品ID: {{ $delivery_id }}</h1>

        <form action="{{ route('deliveries.processReturn') }}" method="POST" id="returnForm">
            @csrf
            <input type="hidden" name="delivery_id" value="{{ $delivery_id }}">

            <input type="hidden" name="submission_token" value="{{ session('return_submission_token_' . $delivery_id) }}">
            
            <div class="c-table-responsive"> {{-- テーブルがはみ出さないようにラッパーを追加 --}}
                <table class="c-table c-table--bordered">
                    <thead>
                        <tr class="c-table__head c-table__head--light">
                            <th class="c-table__th c-table__th--padded">納品明細ID</th>
                            <th class="c-table__th c-table__th--padded">商品名</th>
                            <th class="c-table__th c-table__th--padded">単価</th>
                            <th class="c-table__th c-table__th--padded">返品可能数量</th>
                            <th class="c-table__th c-table__th--padded">返品数量</th>
                            <th class="c-table__th c-table__th--padded">備考</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deliveryDetails as $detail)
                            @if (!$detail->return_flag)
                                <tr class="c-table__row">
                                    <td class="c-table__td c-table__td--padded">{{ $detail->delivery_detail_id }}</td>
                                    <td class="c-table__td c-table__td--padded">{{ $detail->product_name }}</td>
                                    <td class="c-table__td c-table__td--padded">{{ number_format($detail->unit_price) }}円</td>
                                    <td class="c-table__td c-table__td--padded">{{ $detail->delivery_quantity }}</td>
                                    <td class="c-table__td c-table__td--padded">
                                        <input type="number" name="return_quantities[{{ $detail->delivery_detail_id }}]"
                                        min="0" max="{{ $detail->delivery_quantity }}" class="c-form-input c-form-input--small">
                                    </td>
                                    <td class="c-table__td c-table__td--padded">{{ $detail->notes ?? '' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="c-form-group c-form-group--mt4">
                <label for="overall_return_notes" class="c-form-label">返品内容</label>
                <textarea id="overall_return_notes" name="overall_return_notes" rows="5" class="c-form-input c-form-input--full" placeholder="返品に関する全体的な詳細をここに入力してください。"></textarea>
            </div>

            <div class="c-button-group c-button-group--mt4">
            <a href="{{ route('deliveries.details', ['delivery_id' => $delivery_id]) }}"
               class="c-button c-button--gray">
                 戻る
            </a>
            <button type="submit" class="c-button c-button--red c-button--mt4">
                確定
            </button>

            </div>
        </form>
    </div>
    <script src="{{ asset('js/confirmNavigation.js') }}"></script>
</body>
</html>