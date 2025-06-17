window.addEventListener('DOMContentLoaded', () => {
    // 🔹 トースト＆エラーのフェードアウト
    const toast = document.getElementById('toast-message');
    const error = document.querySelector('.error-message');

    [toast, error].forEach(el => {
        if (el) {
            setTimeout(() => {
                el.style.transition = 'opacity 0.5s ease-out';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }, 5000);
        }
    });

    // 🔹 チェックボックスの状態保存と復元
    const checkboxes = document.querySelectorAll('input[name="order_ids[]"]:not(:disabled)');
    const storageKey = 'selectedOrderIds_ordersPage';
    let selectedIds = JSON.parse(localStorage.getItem(storageKey) || '[]');

    const selectAllCheckbox = document.getElementById('select-all-checkbox');

    checkboxes.forEach(cb => {
        if (selectedIds.includes(cb.value)) {
            cb.checked = true;
        }

        cb.addEventListener('change', () => {
            if (cb.checked) {
                if (!selectedIds.includes(cb.value)) {
                    selectedIds.push(cb.value);
                }
            } else {
                selectedIds = selectedIds.filter(id => id !== cb.value);
            }
            localStorage.setItem(storageKey, JSON.stringify(selectedIds));
            updateSelectAllCheckbox();
        });
    });

    function updateSelectAllCheckbox() {
        if (selectAllCheckbox) {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        }
    }

    updateSelectAllCheckbox(); // 初期反映

    // 🔹 全選択・全解除処理
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', () => {
            const checked = selectAllCheckbox.checked;
            selectedIds = [];

            checkboxes.forEach(cb => {
                cb.checked = checked;
                if (checked) selectedIds.push(cb.value);
            });

            localStorage.setItem(storageKey, JSON.stringify(selectedIds));
        });
    }

    // 🔹 フォーム送信時にlocalStorage削除（納品登録など）
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault(); // 一旦止めて
            localStorage.removeItem(storageKey); // チェック状態をクリア
            form.submit(); // 再送信
        });
    }
});
