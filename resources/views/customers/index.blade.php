<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客一覧</title>
    {{-- カスタムCSSファイルを読み込む --}}
    <link href="{{ asset('css/page/customers.css') }}" rel="stylesheet">
</head>
<body class="c-body">
    <div class="l-container">

        <h1 class="c-heading-primary">顧客一覧</h1>

        {{-- ダッシュボードに戻るボタンをh1の直下に配置 --}}
        <div class="p-button-back-wrap">
            <a href="{{ route('dashboard', ['store_id' => request('store_id', '')]) }}" class="c-button c-button--gray">
                ← ダッシュボードに戻る
            </a>
        </div>

        {{-- フィルタフォームとソート用ドロップダウンを統合し、Flexboxで整形 --}}
        <form method="GET" action="{{ route('customers.index') }}" class="p-filter-form" id="customer-filter-form">
            <div>
                <label for="store_id" class="c-form-label">店舗を選択:</label>
                <select name="store_id" id="store_id" class="c-form-select">
                    <option value="" {{ $selectedStoreId == '' ? 'selected' : '' }}>全店舗</option>

                    @foreach ($stores as $store)
                        <option value="{{ $store->store_id }}" {{ $selectedStoreId == $store->store_id ? 'selected' : '' }}>
                            {{ $store->store_id }} : {{ $store->store_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="p-search-input-wrap">
                <label for="keyword" class="c-form-label">キーワード検索:</label>
                <input type="text" name="keyword" id="keyword" class="c-form-input" placeholder="顧客名、電話番号などで検索" value="{{ $keyword ?? '' }}">
            </div>

            {{-- ★ここが変更点です★ --}}
            {{-- p-sort-controls を div で囲み、label と select に適切なクラスを適用 --}}
            <div>
                <label for="sort_by" class="c-form-label">ソート:</label>
                <select name="sort_by" id="sort_by" class="c-form-select p-sort-controls-select"> {{-- p-sort-controls-select クラスを追加 --}}
                    {{-- Controllerから渡される selectedSortValue を使用して選択状態を制御 --}}
                    <option value="" {{ ($selectedSortValue ?? '') === '' ? 'selected' : '' }}>ソートなし (顧客ID昇順)</option>
                    <option value="total_sales_asc" {{ ($selectedSortValue ?? '') === 'total_sales_asc' ? 'selected' : '' }}>売上 昇順</option>
                    <option value="total_sales_desc" {{ ($selectedSortValue ?? '') === 'total_sales_desc' ? 'selected' : '' }}>売上 降順</option>
                    <option value="average_rt_asc" {{ ($selectedSortValue ?? '') === 'average_rt_asc' ? 'selected' : '' }}>平均RT 昇順</option>
                    <option value="average_rt_desc" {{ ($selectedSortValue ?? '') === 'average_rt_desc' ? 'selected' : '' }}>平均RT 降順</option>
                    
                    {{-- 複合ソートの選択肢 --}}
                    <option value="sales_main_rt_sub_asc" {{ ($selectedSortValue ?? '') === 'sales_main_rt_sub_asc' ? 'selected' : '' }}>売上 昇順 (メイン) + 平均RT 昇順 (サブ)</option>
                    <option value="sales_main_rt_sub_desc" {{ ($selectedSortValue ?? '') === 'sales_main_rt_sub_desc' ? 'selected' : '' }}>売上 降順 (メイン) + 平均RT 昇順 (サブ)</option>
                    <option value="rt_main_sales_sub_asc" {{ ($selectedSortValue ?? '') === 'rt_main_sales_sub_asc' ? 'selected' : '' }}>平均RT 昇順 (メイン) + 売上 昇順 (サブ)</option>
                    <option value="rt_main_sales_sub_desc" {{ ($selectedSortValue ?? '') === 'rt_main_sales_sub_desc' ? 'selected' : '' }}>平均RT 降順 (メイン) + 売上 昇順 (サブ)</option>
                </select>
            </div>

            {{-- ボタンアクション用のコンテナ（絞り込みボタンのみ） --}}
            <div class="p-form-actions">
                <div class="p-filter-button-wrap">
                    <button type="submit" class="c-button c-button--green">
                        絞り込み
                    </button>
                </div>
            </div>
        </form>

        <div class="c-table-wrap">
            <table class="c-table">
                <thead class="c-table__head">
                    <tr>
                        <th class="c-table__th">顧客ID</th>
                        <th class="c-table__th">顧客名</th>
                        <th class="c-table__th">担当者名</th>
                        <th class="c-table__th">電話番号</th>
                        <th class="c-table__th">住所</th>
                        <th class="c-table__th">配達条件等</th>
                        <th class="c-table__th">登録日</th>
                        <th class="c-table__th">売上</th>
                        <th class="c-table__th">平均RT(日)</th>
                    </tr>
                </thead>
                <tbody class="c-table__body">
                    @foreach ($customers as $customer)
                        <tr class="c-table__row c-table__row--hover">
                            <td class="c-table__td">{{ $customer->customer_id }}</td>
                            <td class="c-table__td">{{ $customer->name }}</td>
                            <td class="c-table__td">{{ $customer->staff_name ?? '-' }}</td>
                            <td class="c-table__td">{{ $customer->phone_number }}</td>
                            <td class="c-table__td">{{ $customer->address ?? '-' }}</td>
                            <td class="c-table__td">{{ $customer->delivery_location ?? '-' }}</td>
                            <td class="c-table__td">{{ $customer->registration_date }}</td>
                            <td class="c-table__td">{{ number_format($customer->total_sales) }}円</td>
                            <td class="c-table__td">
                                {{ $customer->average_rt !== null ? $customer->average_rt . '日' : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- JavaScriptをボディの最後に追記 --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sortSelect = document.getElementById('sort_by');
            const storeSelect = document.getElementById('store_id');
            const filterForm = document.getElementById('customer-filter-form');

            // 要素が存在するかチェックしてからイベントリスナーを追加
            if (sortSelect && filterForm) { 
                sortSelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }

            // 店舗選択のプルダウンにchangeイベントリスナーを追加
            if (storeSelect && filterForm) {
                storeSelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });
    </script>
</body>
</html>