document.addEventListener('DOMContentLoaded', function () {
    const alertBox = document.getElementById('alert-box');
    if (alertBox) {
        setTimeout(() => {
            alertBox.classList.add('hide');
        }, 4000); // 4秒で非表示
    }
});