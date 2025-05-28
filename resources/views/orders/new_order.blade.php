<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規注文登録</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #fafafa;
            color: #333;
            line-height: 1.6;
            padding: 20px 15px;
        }

        /* Main container - more compact */
        .main-container {
            max-width: 720px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        /* Header section - reduced padding */
        .header {
            padding: 24px 30px 16px;
            border-bottom: 1px solid #e5e5e5;
        }

        .title {
            font-size: 22px;
            font-weight: 600;
            color: #1a1a1a;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 13px;
            color: #666;
            margin-top: 3px;
        }

        /* Form content - reduced padding */
        .form-content {
            padding: 24px 30px;
        }

        /* Form sections - reduced spacing */
        .form-section {
            margin-bottom: 20px;
        }

        .form-section:last-of-type {
            margin-bottom: 0;
        }

        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #333;
            margin-bottom: 6px;
            display: block;
        }

        /* Compact input styling */
        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 15px;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        /* Product section - more compact */
        .product-section {
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            overflow: hidden;
        }

        .product-header {
            background: #f8f9fa;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e5e5;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 16px;
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-list {
            background: #fff;
        }

        .product-row {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 16px;
            align-items: center;
        }

        .product-row:last-child {
            border-bottom: none;
        }

        .product-input {
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 13px;
            background: #fff;
            transition: border-color 0.2s ease;
            outline: none;
        }

        .product-input:focus {
            border-color: #3b82f6;
        }

        /* Remove button - smaller */
        .remove-btn {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 6px;
            border-radius: 4px;
            transition: color 0.2s ease, background-color 0.2s ease;
            font-size: 14px;
            line-height: 1;
        }

        .remove-btn:hover {
            color: #ef4444;
            background-color: #fef2f2;
        }

        /* Add button - compact */
        .add-section {
            padding: 12px 16px;
            background: #f8f9fa;
            border-top: 1px solid #e5e5e5;
        }

        .add-btn {
            background: none;
            border: 1px dashed #9ca3af;
            color: #6b7280;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .add-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background-color: #f0f9ff;
        }

        /* Textarea - smaller height */
        .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 15px;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            outline: none;
            resize: vertical;
            height: 70px;
            font-family: inherit;
        }

        .form-textarea:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Footer buttons - reduced padding */
        .form-footer {
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e5e5e5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            outline: none;
        }

        .btn-secondary {
            background: #fff;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .btn-primary {
            background: #1f2937;
            color: #fff;
        }

        .btn-primary:hover {
            background: #111827;
        }

        .btn-primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            body {
                padding: 15px 8px;
            }
            
            .header, .form-content, .form-footer {
                padding: 18px 20px;
            }
            
            .product-header, .product-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .form-footer {
                flex-direction: column;
                gap: 12px;
            }
            
            .btn {
                width: 100%;
            }
        }

        /* Subtle animations */
        .product-row {
            transition: background-color 0.2s ease;
        }

        .product-row:hover {
            background-color: #fafbfc;
        }

        /* Focus states for accessibility */
        .btn:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
    </style>
    <script>
        function addProductRow() {
            const container = document.getElementById('products-container');
            const newRow = document.createElement('div');
            newRow.className = 'product-row';
            newRow.innerHTML = `
                <input type="text" name="product_name[]" placeholder="商品名" required class="product-input">
                <input type="number" name="unit_price[]" placeholder="0" min="0" required class="product-input">
                <input type="number" name="quantity[]" placeholder="1" min="1" required class="product-input">
                <button type="button" onclick="removeProductRow(this)" class="remove-btn" aria-label="商品を削除">×</button>
            `;
            container.appendChild(newRow);
            
            // 新しく追加された入力欄にフォーカス
            newRow.querySelector('input[type="text"]').focus();
        }

        function removeProductRow(button) {
            const container = document.getElementById('products-container');
            if (container.children.length > 1) {
                button.parentElement.remove();
            } else {
                alert('最低1つの商品は必要です');
            }
        }

        function goBack() {
            window.history.back();
        }

        function submitForm() {
            const form = document.querySelector('form');
            const customerSelect = document.getElementById('customer_id');
            const productInputs = document.querySelectorAll('input[name="product_name[]"]');
            
            // シンプルなバリデーション
            if (!customerSelect.value) {
                alert('顧客を選択してください');
                customerSelect.focus();
                return;
            }
            
            let hasValidProduct = false;
            productInputs.forEach(input => {
                if (input.value.trim()) {
                    hasValidProduct = true;
                }
            });
            
            if (!hasValidProduct) {
                alert('商品名を入力してください');
                productInputs[0].focus();
                return;
            }
            
            // 送信処理
            const submitBtn = document.querySelector('.btn-primary');
            submitBtn.disabled = true;
            submitBtn.textContent = '登録中...';
            
            setTimeout(() => {
                alert('注文を登録しました');
                submitBtn.disabled = false;
                submitBtn.textContent = '登録する';
            }, 1000);
        }
    </script>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1 class="title">新規注文</h1>
            <p class="subtitle">お客様の注文情報を入力してください</p>
        </div>

        <div class="form-content">
            <form>
                <div class="form-section">
                    <label for="customer_id" class="form-label">顧客</label>
                    <select name="customer_id" id="customer_id" required class="form-input">
                        <option value="">顧客を選択</option>
                        <option value="1">田中商事株式会社</option>
                        <option value="2">佐藤工業有限会社</option>
                        <option value="3">鈴木製作所</option>
                        <option value="4">山田電機株式会社</option>
                    </select>
                </div>

                <div class="form-section">
                    <label class="form-label">商品</label>
                    <div class="product-section">
                        <div class="product-header">
                            <div>商品名</div>
                            <div>単価</div>
                            <div>数量</div>
                        </div>
                        
                        <div class="product-list">
                            <div id="products-container">
                                <div class="product-row">
                                    <input type="text" name="product_name[]" placeholder="商品名" required class="product-input">
                                    <input type="number" name="unit_price[]" placeholder="0" min="0" required class="product-input">
                                    <input type="number" name="quantity[]" placeholder="1" min="1" required class="product-input">
                                    <button type="button" onclick="removeProductRow(this)" class="remove-btn" aria-label="商品を削除">×</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="add-section">
                            <button type="button" onclick="addProductRow()" class="add-btn">
                                <span>+</span>
                                <span>商品を追加</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <label for="remarks" class="form-label">備考</label>
                    <textarea name="remarks" id="remarks" class="form-textarea" placeholder="特記事項があれば入力してください"></textarea>
                </div>
            </form>
        </div>

        <div class="form-footer">
            <button type="button" onclick="goBack()" class="btn btn-secondary">キャンセル</button>
            <button type="button" onclick="submitForm()" class="btn btn-primary">登録する</button>
        </div>
    </div>
</body>
</html>