<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>納品一覧</title>
    {{-- すべてのCSSを統合したファイルを参照 --}}
    <link href="{{ asset('css/page/delivery.css') }}" rel="stylesheet">
</head>
<body class="c-body">
    <div class="l-container-wide">

        @if ($errors->any())
            <div class="c-error-message">
                <ul class="c-error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1 class="c-heading-primary">納品一覧</h1>

        <!-- ボタン -->
        <div class="mb-6">
            <a href="{{ route('dashboard', ['store_id' => request('store_id', '')]) }}" class="inline-block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">

                ← ダッシュボードに戻る
            </a>
        </div>

        <form method="GET" action="{{ route('deliveries.index') }}" class="p-filter-form">
            <div>
                <label for="store_id" class="c-form-label">店舗:</label>
                <select name="store_id" id="store_id" class="c-form-select">
                    <option value="">全店舗</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->store_id }}" {{ $selectedStoreId == $store->store_id ? 'selected' : '' }}>
                            {{ $store->store_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="p-filter-keyword-wrap">
                <label for="keyword" class="c-form-label">検索:</label>
                <input type="text" name="keyword" id="keyword" placeholder="納品ID、顧客名、備考で検索"
                    value="{{ $keyword ?? '' }}"
                    class="c-form-input">
            </div>

            {{-- 以前ここにあったソート用のプルダウンは削除しました --}}

            <div class="p-filter-button-wrap">
                <button type="submit" class="c-button c-button--green">
                    検索
                </button>
            </div>
        </form>

        <div class="c-table-wrap">
            <table class="c-table">
                <thead class="c-table__head c-table__head--list">
                    <tr>
                        <th class="c-table__th">納品ID</th>
                        <th class="c-table__th">顧客ID</th>
                        <th class="c-table__th">顧客名</th>
                        <th class="c-table__th">
                            納品日
                            {{-- 納品日のソートボタンをここに再配置します --}}
                            @php
                                // 現在のソート基準が 'delivery_date' であれば、現在のソート方向を取得。なければ 'desc' をデフォルトとする
                                $currentOrder = (isset($sortBy) && $sortBy == 'delivery_date') ? ($order ?? 'desc') : 'desc';

                                // 次にクリックしたときに適用するソート方向を決定
                                // 現在降順なら次は昇順、それ以外（現在昇順またはソートなし）なら降順
                                $nextOrder = ($currentOrder == 'desc') ? 'asc' : 'desc';

                                // 表示する矢印を決定（現在のソート方向を示す）
                                $arrow = ($currentOrder == 'desc') ? '↓' : '↑';
                            @endphp
                            <a href="{{ route('deliveries.index', array_merge(request()->query(), ['sort_by' => 'delivery_date', 'order' => $nextOrder])) }}" class="c-sort-toggle-link">
                                {{ $arrow }}
                            </a>
                        </th>
                        <th class="c-table__th">備考</th>
                    </tr>
                </thead>
                <tbody class="c-table__body">
                    @foreach ($deliveries as $delivery)
                        <tr class="c-table__row c-table__row--hover">
                            <td class="c-table__td">
                                <a href="{{ route('deliveries.details', ['delivery_id' => $delivery->delivery_id]) }}"
                                    class="c-link-primary">
                                    {{ $delivery->delivery_id }}
                                </a>
                            </td>
                            <td class="c-table__td">{{ $delivery->customer_id }}</td>
                            <td class="c-table__td">{{ $delivery->customer_name }}</td>
                            <td class="c-table__td">{{ $delivery->delivery_date }}</td>
                            <td class="c-table__td">{{ $delivery->remarks }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="c-button-group">
            <a href="{{ route('dashboard') }}" class="c-button c-button--gray">
                戻る
            </a>
        </div>

    </div>
</body>
</html>