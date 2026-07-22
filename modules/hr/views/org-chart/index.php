<?php

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Organizational Chart';
$this->params['breadcrumbs'][] = $this->title;

$dataUrl = Url::to(['/hr/org-chart/data']);
$baseUrl = Yii::getAlias('@web');

// self-hosted treant-js@1.0.1 + raphael@2.3.0 (เดิม jsdelivr)
$this->registerCssFile('@web/libs/treant/Treant.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/libs/treant/raphael.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('@web/libs/treant/Treant.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile($baseUrl . '/js/hr-org-chart.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$css = <<<CSS
#org-chart-container {
    overflow: auto;
    min-height: 400px;
    background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
    border-radius: 0.5rem;
}
.Treant { padding: 20px; }
.Treant .node { padding: 0; }
.Treant .node .node-name,
.Treant .node .node-title { display: block; text-align: center; }
.Treant .node .node-name { font-weight: 600; font-size: 0.9rem; margin-top: 6px; }
.Treant .node .node-title { font-size: 0.8rem; color: #6c757d; margin-top: 2px; }
.Treant .node img {
    display: block;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto;
    border: 2px solid #e9ecef;
}
.Treant .node {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 10px 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    min-width: 140px;
}
.Treant .node:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.Treant .collapse-switch { background: #0d6efd; border-radius: 50%; width: 22px; height: 22px; }
CSS;
$this->registerCss($css);

$js = <<<JS
(function() {
    var dataUrl = "{$dataUrl}";
    var baseUrl = "{$baseUrl}";
    if (typeof window.BASE_URL === 'undefined') window.BASE_URL = baseUrl;
    if (typeof HrOrgChart !== 'undefined') {
        HrOrgChart.init('org-chart-container', dataUrl);
    }
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
<div class="org-chart-index">
    <div class="container-fluid py-3 py-md-4">
        <div class="row g-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 p-md-4">
                        <h1 class="h4 mb-3 mb-md-4"><?= Html::encode($this->title) ?></h1>
                        <div id="org-chart-container" class="w-100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
