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
                            <button type="button" class="sort-button" data-key="customer_id">
                                <span class="arrow" data-key="customer_id">▲</span>
                            </button>
                        </th>
                        <th>顧客名</th>
                        <th>登録日</th>
                        <th>電話番号</th>
                        <th>
                            売上 
                            <button type="button" class="sort-button" data-key="total_sales">
                                <span class="arrow" data-key="total_sales">ー</span>
                            </button>
                        </th>
                        <th>
                            平均RT(日) 
                            <button type="button" class="sort-button" data-key="average_rt">
                                <span class="arrow" data-key="average_rt">ー</span>
                            </button>
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
        let sortState = {
            average_rt: 'asc',
            total_sales: 'asc',
            customer_id: 'asc',
        };

        const getCellValue = (tr, dataAttr) =>
            parseFloat(tr.querySelector(`[${dataAttr}]`)?.getAttribute(dataAttr)) || 0;

        const sortTable = () => {
            const tbody = document.getElementById("customer-tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            const sortOrder = [
                { key: 'average_rt', order: sortState.average_rt },
                { key: 'total_sales', order: sortState.total_sales },
                { key: 'customer_id', order: sortState.customer_id },
            ];

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

        document.querySelectorAll('.sort-button').forEach(button => {
            button.addEventListener('click', () => {
                const key = button.getAttribute('data-key');
                sortState[key] = sortState[key] === 'asc' ? 'desc' : 'asc';

                // アイコン表示を更新（オプション）
                document.querySelectorAll('.arrow').forEach(arrow => {
                    const arrowKey = arrow.getAttribute('data-key');
                    arrow.textContent = sortState[arrowKey] === 'asc' ? '▲' : '▼';
                });

                sortTable();
            });
        });

        // 初期ソート実行
        sortTable();
    </script>
</body>
</html>
