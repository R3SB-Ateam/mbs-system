<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="max-w-6xl mx-auto px-6 py-8">

        <!-- タイトル -->
        <h1 class="text-2xl font-bold mb-6 border-b pb-2">顧客一覧</h1>

        <!-- 戻るボタン -->
        <div class="mb-6">
            <a href="{{ route('dashboard', ['store_id' => request('store_id', '')]) }}" class="inline-block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                ダッシュボードに戻る
            </a>
        </div>

        <!-- フィルター -->
        <form id="filter-form" method="GET" action="{{ route('customers.index') }}" class="mb-6 flex flex-col md:flex-row md:items-center gap-4">
            <div>
                <label for="store_id" class="block mb-1 text-sm font-medium">店舗を選択:</label>
                <select name="store_id" id="store_id" class="border border-gray-300 rounded px-3 py-2 w-48">
                    <option value="">全店舗</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->store_id }}" {{ $selectedStoreId == $store->store_id ? 'selected' : '' }}>
                            {{ $store->store_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="self-end md:self-auto">
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    絞り込み
                </button>
            </div>
        </form>

        <!-- 顧客テーブル -->
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table id="customer-table" class="min-w-full text-sm text-left">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 border-b">
                            <div class="flex items-center gap-1">
                                顧客ID
                                <select id="sort-customer-id" class="text-xs border border-gray-300 rounded px-1 py-0.5">
                                    <option value="">ー</option>
                                    <option value="asc">▲</option>
                                    <option value="desc">▼</option>
                                </select>
                            </div>
                        </th>
                        <th class="px-4 py-3 border-b">顧客名</th>
                        <th class="px-4 py-3 border-b">登録日</th>
                        <th class="px-4 py-3 border-b">電話番号</th>
                        <th class="px-4 py-3 border-b">
                            <div class="flex items-center gap-1">
                                売上
                                <select id="sort-total-sales" class="text-xs border border-gray-300 rounded px-1 py-0.5">
                                    <option value="">ー</option>
                                    <option value="asc">▲</option>
                                    <option value="desc">▼</option>
                                </select>
                            </div>
                        </th>
                        <th class="px-4 py-3 border-b">
                            <div class="flex items-center gap-1">
                                平均RT(日)
                                <select id="sort-average-rt" class="text-xs border border-gray-300 rounded px-1 py-0.5">
                                    <option value="">ー</option>
                                    <option value="asc">▲</option>
                                    <option value="desc">▼</option>
                                </select>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody id="customer-tbody" class="bg-white divide-y divide-gray-200">
                    @foreach ($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3" data-customer-id="{{ $customer->customer_id }}">{{ $customer->customer_id }}</td>
                            <td class="px-4 py-3">{{ $customer->name }}</td>
                            <td class="px-4 py-3">{{ $customer->registration_date }}</td>
                            <td class="px-4 py-3">{{ $customer->phone_number }}</td>
                            <td class="px-4 py-3" data-total-sales="{{ $customer->total_sales }}">{{ number_format($customer->total_sales) }}円</td>
                            <td class="px-4 py-3" data-average-rt="{{ $customer->average_rt ?? 0 }}">
                                {{ $customer->average_rt !== null ? $customer->average_rt . '日' : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <!-- ソートJS -->
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
