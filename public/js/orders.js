/* error文が5秒ほどでフェードアウト*/
window.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toast-message');
    const error = document.querySelector('.error-message');

    if (toast) {
        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s ease-out';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500); // DOMから削除（任意）
        }, 5000);
    }

    if (error) {
        setTimeout(() => {
            error.style.transition = 'opacity 0.5s ease-out';
            error.style.opacity = '0';
            setTimeout(() => error.remove(), 500); // DOMから削除（任意）
        }, 5000);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('input[name="order_ids[]"]');
    const storageKey = 'selectedOrderIds_ordersPage';
    let selectedIds = JSON.parse(localStorage.getItem(storageKey) || '[]');

    checkboxes.forEach(cb => {
        if (cb.disabled) {
            // 納品済みチェックボックスのIDはlocalStorageから削除
            selectedIds = selectedIds.filter(id => id !== cb.value);
        } else if (selectedIds.includes(cb.value)) {
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
        });
    });

    // 納品済みIDを削除した状態で保存
    localStorage.setItem(storageKey, JSON.stringify(selectedIds));
});
