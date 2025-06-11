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