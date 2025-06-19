<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>顧客更新 - MBS System</title>
    <link rel="stylesheet" href="{{ asset('css/customers-update.css') }}?v=123">
</head>
<body>
    
    <a href="{{ route('dashboard', ['store_id' => request('store_id', '')]) }}" class="button-link gray back-to-dashboard">ダッシュボードに戻る</a>

    <div class="container">
        <h1>顧客情報を更新</h1>

        <!-- エラーメッセージ -->
        @if ($errors->any())
            <div class="message error">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- 成功メッセージ -->
        @if (session('success'))
            <div class="message success">
                {{ session('success') }}
            </div>
        @endif

        <!-- 要件説明 -->
        <div class="requirements">
            <h3>アップロード要件</h3>
            <ul>
                <li>Excel形式（.xlsx, .xls）のファイルのみ</li>
                <li>ファイル名に店舗情報（緑橋/今里/深江橋）を含める</li>
                <li>ファイルサイズは10MB以下</li>
                <li>A列：顧客ID、C列：名前は必須</li>
            </ul>
        </div>

        <!-- アップロードフォーム -->
        <form id="uploadForm" action="{{ route('customers.edit') }}" method="post" enctype="multipart/form-data">
            @csrf
            
            <div class="file-input-wrapper">
                <label for="excel_file">Excelファイルを選択してください</label>
                <input type="file" 
                       name="excel_file" 
                       id="excel_file" 
                       accept=".xlsx,.xls" 
                       required>
            </div>

            <!-- ファイル情報表示エリア -->
            <div id="fileInfo" class="file-info"></div>

            <!-- プログレスバー -->
            <div id="progressContainer" class="progress-container">
                <div id="progressBar" class="progress-bar"></div>
            </div>

            <button type="submit" id="submitBtn">
                アップロードして更新
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('excel_file');
            const fileInfo = document.getElementById('fileInfo');
            const form = document.getElementById('uploadForm');
            const submitBtn = document.getElementById('submitBtn');
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');

            // ファイル選択時の処理
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    displayFileInfo(file);
                    validateFile(file);
                } else {
                    hideFileInfo();
                }
            });

            // ファイル情報表示
            function displayFileInfo(file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const storeNames = ['緑橋', '今里', '深江橋'];
                const hasStoreName = storeNames.some(name => file.name.includes(name));
                
                fileInfo.innerHTML = `
                    <strong>選択されたファイル:</strong><br>
                    ファイル名: ${file.name}<br>
                    サイズ: ${fileSize} MB<br>
                    店舗情報: ${hasStoreName ? '✓ 含まれています' : '⚠️ 含まれていません'}
                `;
                fileInfo.style.display = 'block';
            }

            // ファイル情報非表示
            function hideFileInfo() {
                fileInfo.style.display = 'none';
            }

            // ファイルバリデーション
            function validateFile(file) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
                const storeNames = ['緑橋', '今里', '深江橋'];
                
                let isValid = true;
                let errorMessage = '';

                // サイズチェック
                if (file.size > maxSize) {
                    isValid = false;
                    errorMessage = 'ファイルサイズは10MB以下にしてください。';
                }

                // タイプチェック
                if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
                    isValid = false;
                    errorMessage = 'Excel形式（.xlsx, .xls）のファイルを選択してください。';
                }

                // 店舗名チェック
                const hasStoreName = storeNames.some(name => file.name.includes(name));
                if (!hasStoreName) {
                    isValid = false;
                    errorMessage = 'ファイル名に店舗情報（緑橋/今里/深江橋）が含まれている必要があります。';
                }

                submitBtn.disabled = !isValid;
                
                if (!isValid) {
                    showError(errorMessage);
                } else {
                    hideError();
                }
            }

            // エラー表示
            function showError(message) {
                const existingError = document.querySelector('.message.error');
                if (existingError) {
                    existingError.remove();
                }

                const errorDiv = document.createElement('div');
                errorDiv.className = 'message error';
                errorDiv.textContent = message;
                
                const form = document.getElementById('uploadForm');
                form.parentNode.insertBefore(errorDiv, form);
            }

            // エラー非表示
            function hideError() {
                const existingError = document.querySelector('.message.error');
                if (existingError) {
                    existingError.remove();
                }
            }

            // フォーム送信時の処理
            form.addEventListener('submit', function(e) {
                const file = fileInput.files[0];
                if (!file) {
                    e.preventDefault();
                    showError('ファイルを選択してください。');
                    return;
                }

                // UI更新
                submitBtn.disabled = true;
                submitBtn.textContent = '処理中...';
                progressContainer.style.display = 'block';
                
                // プログレスバーアニメーション（模擬）
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    progressBar.style.width = progress + '%';
                }, 200);

                // フォーム送信後のクリーンアップは、実際のレスポンス後に行われる
                setTimeout(() => {
                    clearInterval(interval);
                }, 10000);
            });

            // ドラッグ&ドロップ対応
            const container = document.querySelector('.container');
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                container.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                container.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                container.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                container.style.backgroundColor = '#f0f8ff';
            }

            function unhighlight(e) {
                container.style.backgroundColor = '#fff';
            }

            container.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length > 0) {
                    fileInput.files = files;
                    displayFileInfo(files[0]);
                    validateFile(files[0]);
                }
            }
        });
    </script>
</body>
</html>