document.addEventListener('DOMContentLoaded', function() {
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

    // --- 納品登録機能 ---
    const deliveryQuantities = document.querySelectorAll('input[name="delivery_quantities[]"]');
    const deliveryForm = document.querySelector('form'); // フォーム要素を取得

    // フォーム送信時の処理（localStorageクリアと納品数チェック）
    if (deliveryForm) {
        deliveryForm.addEventListener('submit', function(event) {
            // イベントのデフォルト動作を一旦停止
            event.preventDefault(); 

            // 納品数チェック
            let totalDeliveredQuantity = 0;
            deliveryQuantities.forEach(input => {
                totalDeliveredQuantity += parseInt(input.value) || 0;
            });

            if (totalDeliveredQuantity === 0) {
                // 納品数が0の場合はポップアップを表示し、ここで処理を終了
                alert('納品登録を行うには、少なくとも1つの商品の納品数を1以上にしてください。');
                return; 
            }

            // 納品数が1以上の場合は、localStorageをクリアし、フォームを送信
            localStorage.removeItem(storageKey);
            // フォームをプログラム的に再送信
            this.submit(); 
        });
    }
});