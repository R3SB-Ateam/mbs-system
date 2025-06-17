<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>注文書 - {{ $order->order_id }}</title>
    <link href="{{ asset('css/page/orderprint.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>注 文 書</h1>
            <div class="header-info">
                {{-- ★★★ 年・月・日を分割して表示するように修正 ★★★ --}}
                <p class="date">
                    <span>{{ \Carbon\Carbon::parse($order->order_date)->format('Y') }}</span> 年
                    <span>{{ \Carbon\Carbon::parse($order->order_date)->format('m') }}</span> 月
                    <span>{{ \Carbon\Carbon::parse($order->order_date)->format('d') }}</span> 日
                </p>
                <p>注文ID: {{ $order->order_id }}</p>
            </div>
        </header>

        <section class="address">
            <p class="customer-name">
                <span>{{ $order->customer->name }}</span> 様
            </p>
        </section>

        <section class="greeting">
            <p>下記の通り御注文申し上げます</p>
        </section>

        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 5%;"></th>
                    <th style="width: 45%;">品&nbsp;&nbsp;&nbsp;&nbsp;名</th>
                    <th style="width: 15%;">数&nbsp;&nbsp;量</th>
                    <th style="width: 15%;">単&nbsp;&nbsp;価</th>
                    <th style="width: 20%;">摘&nbsp;&nbsp;要</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->details as $index => $detail)
                    @if(!$detail->cancell_flag)
                        <tr>
                            <td class="num">{{ $index + 1 }}</td>
                            <td class="product-name">{{ $detail->product_name }}</td>
                            <td class="quantity">{{ number_format($detail->quantity) }}</td>
                            <td class="price">{{ number_format($detail->unit_price) }}</td>
                            <td class="remarks">{{ $detail->remarks }}</td>
                        </tr>
                    @endif
                @endforeach
                
                @for ($i = $order->details->where('cancell_flag', 0)->count(); $i < 13; $i++)
                    <tr>
                        <td class="num">{{ $i + 1 }}</td>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
                
                <tr class="remarks-footer">
                    <td colspan="5"><span>備考</span> {{ $order->remarks }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>