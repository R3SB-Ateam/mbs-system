/**
 * 指定されたURLを非表示のiframeで開き、印刷ダイアログを呼び出す関数
 * @param {string} url - 印刷したいページのURL
 */
const triggerPrint = (url) => {
    // 既存のiframeがあれば削除する（ボタンを連続で押された場合に対応）
    const oldIframe = document.getElementById('print-iframe');
    if (oldIframe) {
        oldIframe.remove();
    }

    // 新しいiframeを生成
    const iframe = document.createElement('iframe');
    iframe.id = 'print-iframe';
    iframe.src = url;

    // スタイルをまとめて設定し、画面外に隠す
    Object.assign(iframe.style, {
        position: 'absolute',
        border: '0',
        width: '0',
        height: '0',
        left: '-9999px',
        top: '-9999px',
    });

    // iframeの読み込みが完了したら印刷を実行
    iframe.onload = () => {
        try {
            iframe.contentWindow.print();
        } catch (error) {
            console.error('印刷に失敗しました。', error);
            iframe.remove(); // エラー時もiframeを削除
        }
    };

    // ページにiframeを追加して、URLの読み込みを開始
    document.body.appendChild(iframe);
};

// HTMLドキュメントの読み込みが完了したら、以下の処理を実行
document.addEventListener('DOMContentLoaded', () => {
    // 印刷ボタン要素を取得
    const printButton = document.getElementById('print-order-btn');

    // ボタンが存在する場合のみ、イベントリスナーを設定
    if (printButton) {
        printButton.addEventListener('click', () => {
            // ボタンのdata-print-url属性から、印刷ページのURLを取得
            const printUrl = printButton.dataset.printUrl;
            if (printUrl) {
                triggerPrint(printUrl);
            } else {
                console.error('印刷用のURLが指定されていません。');
            }
        });
    }
});