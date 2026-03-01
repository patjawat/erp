<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ภาพรวมการลา';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-calendar-check"></i>
        ภาพรวมการลา
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('../menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-6">
        <?= $this->render('@app/modules/hr/views/leave/dashboard/leave_summary_year', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]) ?>
    </div>
    <div class="col-6">
        <?= $this->render('@app/modules/hr/views/leave/dashboard/leave_summary_month', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]) ?>
    </div>
    <div class="col-12">
        <?= $this->render('@app/modules/hr/views/leave/dashboard/leave_summary', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]) ?>
    </div>
</div>
