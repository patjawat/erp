<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap5\LinkPager;
use app\modules\helpdesk2\models\Helpdesk;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\HelpdeskSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'งานซ่อมของช่าง (V2)';
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = $this->title;

$tickets = $dataProvider->getModels();
$kpiTotal = $dataProvider->getTotalCount();
$kpiOpen = 0;
$kpiInProgress = 0;
foreach ($tickets as $t) {
    if (in_array($t->status, ['pending', 'receive'], true)) {
        $kpiOpen++;
    } elseif ($t->status === 'in_progress') {
        $kpiInProgress++;
    }
}

$statusFilterOptions = [
    'pending' => 'รอรับเรื่อง',
    'receive' => 'รับเรื่องแล้ว',
    'in_progress' => 'กำลังดำเนินการ',
];
?>

<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="erp-icon-box bg-primary bg-opacity-10">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold"><?= Html::encode($this->title) ?></h4>
                            <div class="text-muted small">สรุปงานที่ต้องดำเนินการ และเข้าไปบันทึกผลการซ่อม</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                            ทั้งหมด <?= number_format($kpiTotal) ?>
                        </span>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">
                            เปิด/รับเรื่อง <?= number_format($kpiOpen) ?>
                        </span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                            กำลังดำเนินการ <?= number_format($kpiInProgress) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-funnel"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">ค้นหา / กรอง</h6>
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                    'action' => ['technician-v2'],
                    'options' => ['class' => 'row g-3 align-items-end'],
                ]); ?>
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'q')->textInput([
                        'placeholder' => 'รหัสงาน / อาการ / สถานที่',
                        'class' => 'form-control',
                    ])->label('คำค้น') ?>
                </div>
                <div class="col-12 col-md-2">
                    <?= $form->field($searchModel, 'status')->widget(Select2::class, [
                        'data' => $statusFilterOptions,
                        'options' => ['placeholder' => 'สถานะทั้งหมด'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('สถานะ') ?>
                </div>
                <div class="col-12 col-md-2">
                    <?= $form->field($searchModel, 'urgency')->widget(Select2::class, [
                        'data' => Helpdesk::listUrgency(),
                        'options' => ['placeholder' => 'ความเร่งด่วนทั้งหมด'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('ความเร่งด่วน') ?>
                </div>
                <div class="col-12 col-md-2">
                    <?= $form->field($searchModel, 'device_type_id')->widget(Select2::class, [
                        'data' => (new Helpdesk())->listDeviceType(),
                        'options' => ['placeholder' => 'อุปกรณ์ทั้งหมด'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('อุปกรณ์') ?>
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    <?= Html::a('<i class="bi bi-x-circle me-1"></i>ล้าง', ['technician-v2'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-list-check"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">รายการงาน</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">รหัสงานซ่อม</th>
                                <th scope="col">อุปกรณ์</th>
                                <th scope="col">อาการ</th>
                                <th scope="col">สถานที่</th>
                                <th scope="col">ผู้แจ้ง</th>
                                <th scope="col">ความเร่งด่วน</th>
                                <th scope="col">สถานะ</th>
                                <th scope="col" class="text-end" style="width: 220px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">ไม่พบงานในคิว</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $t): ?>
                                    <?php
                                    $viewUrl = Url::to(['/helpdesk/service/view-v2', 'id' => $t->id]);
                                    $updateUrl = Url::to(['/helpdesk/service/update-v2', 'id' => $t->id]);
                                    $req = $t->getUserReq();
                                    ?>
                                    <tr>
                                        <td class="fw-medium text-primary"><?= Html::encode($t->repair_number) ?></td>
                                        <td><?= Html::encode($t->deviceType->title ?? '-') ?></td>
                                        <td class="text-truncate" style="max-width: 320px;"><?= Html::encode($t->title) ?></td>
                                        <td><?= Html::encode($t->data_json['location'] ?? '-') ?></td>
                                        <td><?= Html::encode($req['fullname'] ?? '-') ?></td>
                                        <td><?= $t->viewUrgent()['view'] ?? '' ?></td>
                                        <td><?= $t->viewStatus() ?></td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <?= Html::a('<i class="bi bi-journal-text me-1"></i>บันทึกงาน', $updateUrl, ['class' => 'btn btn-primary']) ?>
                                                <?= Html::a('<i class="bi bi-eye me-1"></i>ดูใบงาน', $viewUrl, ['class' => 'btn btn-outline-secondary']) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($dataProvider->getTotalCount() > 0): ?>
                <div class="card-footer border-top bg-transparent">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="text-muted small">
                            แสดง <?= $dataProvider->getCount() ?> รายการ จากทั้งหมด <?= number_format($dataProvider->getTotalCount()) ?> รายการ
                        </div>
                        <?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

