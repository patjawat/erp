<?php
/**
 * Shared footer: load mobile-shared.js, run Lucide createIcons, hide loading overlay.
 */
$this->registerJsFile('@web/js/mobile-shared.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);

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
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
            if (typeof window.mobileMarkDecorativeIcons === 'function') {
                window.mobileMarkDecorativeIcons();
            }
        }
        if (document.readyState === 'complete') hideLoading();
        else window.addEventListener('load', hideLoading);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
JS
, \yii\web\View::POS_READY);
