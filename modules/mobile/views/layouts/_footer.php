<?php
/**
 * Shared footer: Lucide createIcons + ซ่อน loading overlay เมื่อพร้อม
 */
$this->registerJs(<<<'JS'
(function() {
    var overlay = document.getElementById('mobile-loading-overlay');
    var minShow = 300;
    var start = Date.now();
    function hideLoading() {
        var elapsed = Date.now() - start;
        var delay = elapsed >= minShow ? 50 : minShow - elapsed;
        setTimeout(function() {
            if (overlay) overlay.classList.add('mobile-loading-hidden');
        }, delay);
    }
    function init() {
        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        if (document.readyState === 'complete') hideLoading();
        else window.addEventListener('load', hideLoading);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
JS
, \yii\web\View::POS_READY);
