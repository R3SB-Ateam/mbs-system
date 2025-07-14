document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    let isFormChanged = false;

    if (form) {
        // フォーム内の変更を検知してフラグを立てる
        form.addEventListener('change', () => {
            isFormChanged = true;
        });

        // 送信前の確認ダイアログ
        form.addEventListener('submit', function(e) {
            if (isFormChanged) {
                const confirmSubmit = confirm('この内容で確定しますか？');
                if (!confirmSubmit) {
                    e.preventDefault();
                    return;
                }
            }
            // 確認OKなら警告を無効化
            isFormChanged = false;
        });
    }

    // 「戻る」ボタン
    const backButtons = document.querySelectorAll('.btn-back, .btn-secondary');

    backButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            if (isFormChanged) {
                const confirmBack = confirm('作業内容が未保存です。本当に戻ってもよろしいですか？（変更内容は破棄されます）');
                if (!confirmBack) {
                    e.preventDefault();
                } else {
                    // 確認OKなら警告を無効化し二重警告を防止
                    isFormChanged = false;
                }
            }
        });
    });

    // ページ離脱時の警告
    window.addEventListener('beforeunload', function (e) {
        if (isFormChanged) {
            const message = '作業中の内容があります。保存せずにページを移動してもよろしいですか？';
            e.preventDefault();
            e.returnValue = message;
            return message;
        }
    });
});
