<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>納品一覧</title>
    <link href="{{ asset('css/page/delivery.css') }}" rel="stylesheet">
</head>
<body>
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

        <!-- 見出し -->
        <h1 class="c-heading-primary">納品一覧</h1>

        <!-- ナビボタン -->
        <div class="c-button-group">
            <a href="{{ route('dashboard', ['store_id' => request('store_id', '')]) }}" class="c-button c-button--gray">
                ダッシュボードに戻る
            </a>
        </div>

        <!-- 検索フォーム -->
        <form method="GET" action="{{ route('deliveries.index') }}" class="p-filter-form">
                <label for="store_id" class="c-form-label">店舗を選択:</label>
                <select name="store_id" id="store_id" class="c-form-select">
                    <option value="">全店舗</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->store_id }}" {{ $selectedStoreId == $store->store_id ? 'selected' : '' }}>
                            {{ $store->store_name }}
                        </option>
                    @endforeach
                </select>
                <input type="text" name="keyword" id="keyword" placeholder="納品ID、顧客名、備考で検索"
                    value="{{ $keyword ?? '' }}" class="keyword-text">
                <button type="submit" class="c-button c-button--green">検索</button>
        </form>

        <!-- 納品テーブル -->
        <div class="c-table-wrap">
            <table class="c-table">
                <thead class="c-table__head c-table__head--list">
                    <tr>
                        <th class="c-table__th">納品ID</th>
                        <th class="c-table__th">顧客ID</th>
                        <th class="c-table__th">顧客名</th>
                        <th class="c-table__th">納品日</th>
                        <th class="c-table__th">備考</th>
                    </tr>
                </thead>
                <tbody class="c-table__body">
                    @foreach ($deliveries as $delivery)
                        <tr class="c-table__row c-table__row--hover">
                            <td class="c-table__td">
                                <a href="{{ route('deliveries.details', ['delivery_id' => $delivery->delivery_id]) }}"
                                   class="c-link-primary">
                                    {{ number_format($delivery->delivery_id) }}
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

    </div>
</body>
</html>
