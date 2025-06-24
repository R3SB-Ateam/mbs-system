<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客一覧</title>
    <link href="{{ asset('css/page/customer.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">

        <!-- タイトル -->
        <h1 class="title">顧客一覧</h1>

        <!-- ナビボタン -->
        <div class="button-wrapper">
            <a href="{{ route('dashboard', ['store_id' => request('store_id', '')]) }}" class="btn-back">
                ダッシュボードに戻る
            </a>
        </div>

        <!-- フィルター -->
        <form id="filter-form" method="GET" action="{{ route('customers.index') }}" class="filter-form">
            <div>
                <label for="store_id">店舗を選択:</label><br>
                <select name="store_id" id="store_id">
                    <option value="">全店舗</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->store_id }}" {{ $selectedStoreId == $store->store_id ? 'selected' : '' }}>
                            {{ $store->store_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit">絞り込み</button>
            </div>
        </form>

        <!-- 顧客テーブル -->
        <div class="table-container">
            <table id="customer-table">
                <thead>
                    <tr>
                        <th>
                            顧客ID
                            <select id="sort-customer-id" class="sort-select">
                                <option value="">ー</option>
                                <option value="asc">▲</option>
                                <option value="desc">▼</option>
                            </select>
                        </th>
                        <th>顧客名</th>
                        <th>登録日</th>
                        <th>電話番号</th>
                        <th>
                            売上
                            <select id="sort-total-sales" class="sort-select">
                                <option value="">ー</option>
                                <option value="asc">▲</option>
                                <option value="desc">▼</option>
                            </select>
                        </th>
                        <th>
                            平均RT(日)
                            <select id="sort-average-rt" class="sort-select">
                                <option value="">ー</option>
                                <option value="asc">▲</option>
                                <option value="desc">▼</option>
                            </select>
                        </th>
                    </tr>
                </thead>
                <tbody id="customer-tbody">
                    @foreach ($customers as $customer)
                        <tr>
                            <td data-customer-id="{{ $customer->customer_id }}">{{ $customer->customer_id }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->registration_date }}</td>
                            <td>{{ $customer->phone_number }}</td>
                            <td data-total-sales="{{ $customer->total_sales }}">{{ number_format($customer->total_sales) }}円</td>
                            <td data-average-rt="{{ $customer->average_rt ?? 0 }}">
                                {{ $customer->average_rt !== null ? $customer->average_rt . '日' : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ソートスクリプト -->
    <script>
        const getCellValue = (tr, dataAttr) => parseFloat(tr.querySelector(`[${dataAttr}]`)?.getAttribute(dataAttr)) || 0;

        const sortTable = () => {
            const tbody = document.getElementById("customer-tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            const sortOrder = [
                { key: 'customer_id', order: document.getElementById('sort-customer-id').value },
                { key: 'total_sales', order: document.getElementById('sort-total-sales').value },
                { key: 'average_rt', order: document.getElementById('sort-average-rt').value },
            ].filter(s => s.order);

            rows.sort((a, b) => {
                for (const { key, order } of sortOrder) {
                    const attr = {
                        customer_id: 'data-customer-id',
                        total_sales: 'data-total-sales',
                        average_rt: 'data-average-rt',
                    }[key];
                    const aVal = getCellValue(a, attr);
                    const bVal = getCellValue(b, attr);

                    if (aVal !== bVal) {
                        return order === 'asc' ? aVal - bVal : bVal - aVal;
                    }
                }
                return 0;
            });

            rows.forEach(row => tbody.appendChild(row));
        };

        ['sort-customer-id', 'sort-total-sales', 'sort-average-rt'].forEach(id => {
            document.getElementById(id).addEventListener('change', sortTable);
        });

        document.getElementById('sort-customer-id').value = 'asc';
        sortTable();
    </script>
</body>
</html>
