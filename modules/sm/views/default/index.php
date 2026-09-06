<?php

/**
 * ภาพรวมงานพัสดุ (/sm)
 *
 * @var yii\web\View $this
 * @var app\modules\purchase\models\OrderSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 */

$this->title = 'ภาพรวมงานพัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบขอซื้อ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = 'ภาพรวม';
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-clipboard-data text-primary"></i>
    <?= $this->title ?>
    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis fs-6 fw-medium">ปีงบ <?= $dashboard->year ?></span>
  </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
  <?= $this->render('_search_year', ['model' => $searchModel]) ?>
  <?= $this->render('menu', ['active' => 'dashboard']) ?>
</div>
<?php $this->endBlock(); ?>

<?= $this->render('_plan_compare', ['dashboard' => $dashboard, 'onlyAlert' => true]) ?>

<?= $this->render('_kpi', ['dashboard' => $dashboard]) ?>

<div class="row g-3 mt-0">
  <div class="col-lg-8">
    <?= $this->render('_watchlist', ['dashboard' => $dashboard]) ?>
  </div>
  <div class="col-lg-4">
    <?= $this->render('_pipeline', ['dashboard' => $dashboard]) ?>
  </div>
</div>

<div class="row g-3 mt-0">
  <div class="col-lg-8">
    <?= $this->render('_monthly_chart', ['dashboard' => $dashboard]) ?>
  </div>
  <div class="col-lg-4">
    <?= $this->render('_budget_type', ['dashboard' => $dashboard]) ?>
  </div>
</div>

<div class="row g-3 mt-0">
  <div class="col-lg-8">
    <?= $this->render('_reconcile', ['dashboard' => $dashboard]) ?>
  </div>
  <div class="col-lg-4">
    <?= $this->render('_reconcile_summary', ['dashboard' => $dashboard]) ?>
  </div>
</div>

<div class="row g-3 mt-0">
  <div class="col-12">
    <?= $this->render('_plan_compare', ['dashboard' => $dashboard, 'onlyAlert' => false]) ?>
  </div>
</div>

<div class="row g-3 mt-0">
  <div class="col-12">
    <?= $this->render('_subtype_detail', ['dashboard' => $dashboard]) ?>
  </div>
</div>
