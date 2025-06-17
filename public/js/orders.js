window.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toast-message');
    const error = document.querySelector('.error-message');

    if (toast) {
        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s ease-out';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 5000);
    }

    if (error) {
        setTimeout(() => {
            error.style.transition = 'opacity 0.5s ease-out';
            error.style.opacity = '0';
            setTimeout(() => error.remove(), 500);
        }, 5000);
    }

    // チェックボックス処理
    const checkboxes = document.querySelectorAll('input[name="order_ids[]"]:not(:disabled)');
    const storageKey = 'selectedOrderIds_ordersPage';
    let selectedIds = JSON.parse(localStorage.getItem(storageKey) || '[]');

    const selectAllCheckbox = document.getElementById('select-all-checkbox');

    // 個別チェックボックスの状態を復元
    checkboxes.forEach(cb => {
        if (selectedIds.includes(cb.value)) {
            cb.checked = true;
        }

        // 個別チェックの変更イベント
        cb.addEventListener('change', () => {
            if (cb.checked) {
                if (!selectedIds.includes(cb.value)) {
                    selectedIds.push(cb.value);
                }
            } else {
                selectedIds = selectedIds.filter(id => id !== cb.value);
            }
            localStorage.setItem(storageKey, JSON.stringify(selectedIds));

            // ヘッダーの全選択チェックボックス状態を更新
            updateSelectAllCheckbox();
        });
    });

    // 全選択チェックボックスの状態更新関数
    function updateSelectAllCheckbox() {
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
    }

    // 最初にヘッダーのチェック状態を設定
    updateSelectAllCheckbox();

    // ヘッダーの全選択チェックボックスクリックイベント
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', () => {
            const checked = selectAllCheckbox.checked;
            checkboxes.forEach(cb => {
                cb.checked = checked;
                if (checked) {
                    if (!selectedIds.includes(cb.value)) {
                        selectedIds.push(cb.value);
                    }
                } else {
                    selectedIds = [];
                }
            });
            localStorage.setItem(storageKey, JSON.stringify(selectedIds));
        });
    }

    // フォーム送信時に localStorage を削除
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            localStorage.removeItem(storageKey);
            form.submit();
        });
    }
});
