function updateMenuText() {
    const p = document.getElementById("menu-info");
    if (window.innerWidth <= 768) {
        p.textContent = "下側のメニューから各機能へ移動できます。";
    } else {
        p.textContent = "左側のメニューから各機能へ移動できます。";
    }
}

window.addEventListener("load", updateMenuText);
window.addEventListener("resize", updateMenuText);
