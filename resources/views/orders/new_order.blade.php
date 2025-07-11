<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規注文登録</title>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="{{ asset('css/page/new_order.css') }}" rel="stylesheet">

    <!-- jQueryとjQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- 顧客データのJS変数 -->
    <script>
        const customers = @json($customers);
    </script>

    <!-- オートコンプリート -->
    <script>
        $(function() {
            $('#customer_search').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '{{ route("customers.search") }}',
                        dataType: 'json',
                        data: {
                            term: request.term,
                            store_id: $('#store_id').val(),
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 1,
                select: function(event, ui) {
                    $('#customer_search').val(ui.item.value);      // 「名前」だけ入力欄にセット
                    $('#customer_id').val(ui.item.customer_id);    // hiddenにIDをセット
                    return false;
                }
            });
            // 二重送信防止機能
            $('#orderForm').on('submit',function(){
                const $submitButton = $('#submitOrderButton');

                if($submitButton.attr('disabled')){
                    return false;
                }

                //フォーム送信時にボタンを無効化
                $submitButton.attr('disabled','disabled');
                //ボタンのテキストを更新
                $submitButton.text('処理中...');
            });
        });
    </script>

    <!-- 商品行の追加/削除 -->
    <script>
        function addProductRow() {
            const container = document.getElementById('products-container');
            const newRow = document.createElement('div');
            newRow.className = 'product-row';
            newRow.innerHTML = `
                <input type="text" name="product_name[]" placeholder="商品名" required class="form-input">
                <input type="number" name="unit_price[]" placeholder="単価" min="0" required class="form-input">
                <input type="number" name="quantity[]" placeholder="数量" min="1" required class="form-input">
                <input type="text" name="product_note[]" placeholder="備考" class="form-input">
                <button type="button" onclick="removeProductRow(this)" class="btn-danger-text">削除</button>
            `;
            container.appendChild(newRow);
        }

        function removeProductRow(button) {
            button.parentElement.remove();
        }
    </script>
</head>
<body>
    <div class="page-container">
        <h1 class="page-title">新規注文登録</h1>

        <form method="POST" action="{{ route('orders.order_store') }}" id="orderForm">
            @csrf
            <div class="form-group">
                <label for="customer_search" class="form-label">顧客検索:</label>
                <input type="text" id="customer_search" placeholder="名前かIDで検索" class="form-input" autocomplete="off" required>
                <input type="hidden" id="store_id" name="store_id" value="{{ request('store_id', '') }}">
                <input type="hidden" id="customer_id" name="customer_id" value="">
            </div>

            <div id="products-container" class="form-group">
                <div class="product-row">
                    <input type="text" name="product_name[]" placeholder="商品名" required class="form-input">
                    <input type="number" name="unit_price[]" placeholder="単価" min="0" required class="form-input">
                    <input type="number" name="quantity[]" placeholder="数量" min="1" required class="form-input">
                    <input type="text" name="product_note[]" placeholder="備考" class="form-input">
                </div>
            </div>

            <button type="button" onclick="addProductRow()" class="btn-success btn-add-margin">商品を追加</button>

            <div class="form-group">
                <label for="remarks" class="form-label">備考:</label>
                <textarea name="remarks" id="remarks" rows="3" class="form-textarea"></textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('orders.index') }}" class="link-back">← 注文一覧へ戻る</a>
                <button type="submit" class="btn-primary" id="submitOrderButton">注文を登録</button>
            </div>

            @if ($errors->any())
                <div id="alert-box" class="alert-error  fixed-alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>・{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </form>
    </div>
</body>
</html>