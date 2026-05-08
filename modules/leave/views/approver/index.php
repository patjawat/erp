<?php

use app\components\widgets\DataSummaryWidget;
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
    <div>
        <h4 class="text-body mb-1 d-flex align-items-center gap-2 fw-bold">
            <span class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center">
                <i class="bi bi-person-check"></i>
            </span>
            <?= Html::encode($this->title) ?>
        </h4>
        <div class="small text-muted">
            จัดการผู้อนุมัติและตรวจสอบ workflow ของใบลาแบบรวมศูนย์
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>

<div class="d-flex flex-column gap-3 gap-lg-4">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex align-items-start gap-3">
                <span class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-search"></i>
                </span>
                <div>
                    <div class="fw-bold text-body">การค้นหา</div>
                    <div class="small text-muted">กรองรายการตามช่วงเวลา สถานะ และผู้ขออนุมัติ</div>
                </div>
            </div>
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

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="bi bi-ui-checks"></i>
                    </span>
                    <div>
                        <div class="fw-bold text-body">ทะเบียนวันลา</div>
                        <div class="small text-muted">รายการที่อยู่ระหว่างตรวจสอบและอนุมัติทั้งหมด</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge rounded-pill text-bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2">
                        <i class="bi bi-collection me-1"></i>
                        <?= number_format($totalCount, 0) ?> รายการ
                    </span>
                    <?= Html::a(
                        '<i class="bi bi-file-earmark-excel me-1"></i> ส่งออก Excel',
                        array_merge(['/leave/approver/export'], Yii::$app->request->queryParams),
                        ['class' => 'btn btn-outline-success rounded-pill shadow-sm']
                    ) ?>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?= $this->render('list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]) ?>
        </div>
       <div class="card-footer bg-body border-top py-3 px-4">
        <?php
        echo DataSummaryWidget::widget([
            'dataProvider' => $dataProvider,
            'pagerOptions' => [],
        ]);
        ?>
    </div>
    </div>
</div>
