<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $listLeaveType */
/** @var array $listLeaveStatus */
/** @var array $summaryByStatus */
/** @var array $summaryByLeaveType */
/** @var array $leaveTypeColors */

$this->title = 'ผู้ตรวจสอบวันลา';
$this->params['breadcrumbs'][] = $this->title;

$totalCount = (int) $dataProvider->getTotalCount();
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/menu_admin', ['active' => 'approver']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 w-100">
    <h4 class="text-body mb-0 d-flex align-items-center gap-2">
        <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-person-check fs-5"></i>
        </span>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header">
        <h6 class="mt-2 mb-0"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('_search', [
            'model' => $searchModel,
            'listLeaveType' => $listLeaveType,
            'listLeaveStatus' => $listLeaveStatus,
        ]) ?>
    </div>
</div>

<?= $this->render('_summary', [
    'summaryByStatus' => $summaryByStatus ?? [],
    'summaryByLeaveType' => $summaryByLeaveType ?? [],
    'listLeaveStatus' => $listLeaveStatus ?? [],
    'listLeaveType' => $listLeaveType ?? [],
    'leaveTypeColors' => $leaveTypeColors ?? [],
]) ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mt-2 mb-0">
                <i class="bi bi-ui-checks"></i> ทะเบียนวันลา
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1"><?= number_format($totalCount, 0) ?></span> รายการ
            </h6>


            <div class="d-flex gap-2">
               
                <?= Html::a(
                    '<i class="bi bi-file-earmark-excel me-1"></i> ส่งออก Excel',
                    array_merge(['/leave/approver/export'], Yii::$app->request->queryParams),
                    ['class' => 'btn btn-success btn-sm']
                ) ?>
            </div>
        </div>
    </div>
    <div class="card-body p-0" >
        
        <?= $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]) ?>
    </div>
    <?php if ($dataProvider->getCount() > 0): ?>
    <div class="card-footer text-muted d-flex justify-content-center">
        <?= \yii\bootstrap5\LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'firstPageLabel' => 'หน้าแรก',
            'lastPageLabel' => 'หน้าสุดท้าย',
        ]) ?>
    </div>
    <?php endif; ?>
</div>
