<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>納品書 - {{ $delivery->delivery_id }}</title>
    {{-- ★納品書専用のCSSを読み込み --}}
    <link href="{{ asset('css/page/deliveryprint.css') }}" rel="stylesheet">
</head>
<body>
    @php
        // ★合計金額の計算
        $subtotal = 0;
        foreach ($delivery->deliveryDetails as $detail) {
            if (!$detail->return_flag) { // 返品されていないもののみ計算
                $subtotal += $detail->unit_price * $detail->delivery_quantity;
            }
        }
        $tax_rate = 0.10; // 税率10%
        $tax = floor($subtotal * $tax_rate);
        $total_amount = $subtotal + $tax;
    @endphp

    <div class="container">
        <header class="header">
            <h1>納 品 書</h1>
            <div class="header-info">
                <p class="date">{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('Y年 m月 d日') }}</p>
                <p>納品ID: {{ $delivery->delivery_id }}</p>
            </div>
        </header>

        <section class="address">
            <p class="customer-name">
                <span>{{ $delivery->customer->name }}</span> 様
            </p>
        </section>

        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 45%;">品&nbsp;&nbsp;&nbsp;&nbsp;名</th>
                    <th style="width: 15%;">数&nbsp;&nbsp;量</th>
                    <th style="width: 15%;">単&nbsp;&nbsp;価</th>
                    <th style="width: 25%;">金&nbsp;&nbsp;額（税抜）</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($delivery->deliveryDetails as $detail)
                    @if(!$detail->return_flag)
                        <tr>
                            <td class="product-name">{{ $detail->product_name }}</td>
                            <td class="quantity">{{ number_format($detail->delivery_quantity) }}</td>
                            <td class="price">{{ number_format($detail->unit_price) }}</td>
                            <td class="price">{{ number_format($detail->unit_price * $detail->delivery_quantity) }}</td>
                        </tr>
                    @endif
                @endforeach
                
                {{-- テンプレートの5行に合わせるため、空の行を追加 --}}
                @for ($i = $delivery->deliveryDetails->where('return_flag', 0)->count(); $i < 13; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td class="summary-box">
                        <div class="summary-title">合　計（税抜）</div>
                        <div class="summary-value">{{ number_format($subtotal) }}</div>
                    </td>
                    <td class="summary-box">
                        <div class="summary-title">消費税 ({{ $tax_rate * 100 }}%)</div>
                        <div class="summary-value">{{ number_format($tax) }}</div>
                    </td>
                    <td colspan="2" class="summary-box total-box">
                        <div class="summary-title">税込合計金額</div>
                        <div class="summary-value total-value">{{ number_format($total_amount) }}</div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>