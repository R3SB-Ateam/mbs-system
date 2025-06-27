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
                        <button type="button" class="sort-button" data-key="customer_id">
                            顧客ID <span class="arrow" data-key="customer_id">▲</span>
                        </button>
                        </th>
                        <th>顧客名</th>
                        <th>登録日</th>
                        <th>電話番号</th>
                        <th>
                        <button type="button" class="sort-button" data-key="total_sales">
                            売上 <span class="arrow" data-key="total_sales">ー</span>
                        </button>
                        </th>
                        <th>
                        <button type="button" class="sort-button" data-key="average_rt">
                            平均RT(日) <span class="arrow" data-key="average_rt">ー</span>
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
        const getCellValue = (tr, dataAttr) =>
        parseFloat(tr.querySelector(`[${dataAttr}]`)?.getAttribute(dataAttr)) || 0;

        // ソート状態（昇順/降順 or 未選択）を管理
        const sortStates = {
        average_rt: '',
        total_sales: '',
        customer_id: ''
        };

        // 固定優先順位（変えない）
        const sortPriority = ['average_rt', 'total_sales', 'customer_id'];

        const getCellValue = (tr, dataAttr) =>
        parseFloat(tr.querySelector(`[${dataAttr}]`)?.getAttribute(dataAttr)) || 0;

        const sortTable = () => {
        const tbody = document.getElementById("customer-tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));

        rows.sort((a, b) => {
            for (const key of sortPriority) {
            const order = sortStates[key];
            if (!order) continue;

            const attrMap = {
                average_rt: 'data-average-rt',
                total_sales: 'data-total-sales',
                customer_id: 'data-customer-id'
            };

            const attr = attrMap[key];
            const aVal = getCellValue(a, attr);
            const bVal = getCellValue(b, attr);

            if (aVal !== bVal) {
                return order === 'asc' ? aVal - bVal : bVal - aVal;
            }
            }
            return 0;
        });

        rows.forEach(row => tbody.appendChild(row));
        updateArrows();
        };

        const updateArrows = () => {
        document.querySelectorAll('.arrow').forEach(el => {
            const key = el.getAttribute('data-key');
            const order = sortStates[key];
            el.textContent = order === 'asc' ? '▲' :
                            order === 'desc' ? '▼' : 'ー';
        });
        };

        document.querySelectorAll('.sort-button').forEach(button => {
        button.addEventListener('click', () => {
            const key = button.getAttribute('data-key');

            // 昇順 → 降順 → 未選択 の順で切り替え
            sortStates[key] = sortStates[key] === ''
            ? 'asc'
            : sortStates[key] === 'asc'
            ? 'desc'
            : '';

            sortTable();
        });
        });

        // 初期状態（必要ならここで初期値設定可能）
        sortTable();
    </script>
</body>
</html>
