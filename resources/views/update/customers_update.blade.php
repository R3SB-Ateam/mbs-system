
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Excelファイルアップロード</title>
        <link rel="stylesheet" href="style.css"> <!-- スタイルシートをリンク -->
    </head>
    <body>
        <h1>Excelファイルをアップロード</h1>
        
        <div class="button-container">
            <form action="" method="post" enctype="multipart/form-data">
                <label for="excel_file">Excelファイルを選択してください</label>
                <br>
                <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls" required>
                <br><br>
                <button type="submit" class="btn">アップロードして更新</button>
            </form>
        </div>
    </body>
    </html>