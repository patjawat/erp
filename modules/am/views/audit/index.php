<?php

use app\components\AppHelper;
use app\modules\am\models\AssetAudit;
use yii\helpers\Html;
use yii\bootstrap5\LinkPager;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetAuditSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ตรวจนับพัสดุประจำปี';
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
.audit-hero {
    background:
        radial-gradient(circle at top right, rgba(13, 110, 253, 0.14), transparent 28%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid rgba(13, 110, 253, 0.08);
}

.audit-table-card {
    overflow: hidden;
}

.audit-table-card .table {
    margin-bottom: 0;
}
</style>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
<i data-lucide="file-check" class="me-2"></i>
    <?= $this->title ?>
  </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'work']) ?>
<?php $this->endBlock(); ?>

<div class="audit-index">
    <div class="card audit-hero border-0 shadow-sm mb-3">
        <div class="card-body p-4 p-lg-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="pe-lg-4">
                    <div class="d-inline-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">Annual Audit</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">AM</span>
                    </div>
                    <h1 class="h3 mb-2 fw-semibold text-body">ตรวจนับพัสดุประจำปี</h1>
                    <div class="text-muted mb-0">
                        ตามระเบียบข้อ 209 ตรวจนับพัสดุอย่างน้อยปีละ 1 ครั้ง ก่อนสิ้นปีงบประมาณ
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?= Html::a('KPI Dashboard', ['dashboard'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('รายงานครุภัณฑ์คงเหลือ', ['/am/report/register'], ['class' => 'btn btn-outline-success']) ?>
                    <?= Html::a('สร้างใบตรวจนับ', ['create'], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">ตัวกรองรายการ</h5>
                    <div class="text-muted small">ค้นหาใบตรวจนับจากเลขที่ ปีงบประมาณ สถานะ หรือคำค้นเพิ่มเติม</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">Filter</span>
                    <?= Html::a('ล้างตัวกรอง', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                </div>
            </div>
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'audit_no')->textInput(['placeholder' => 'เลขที่ตรวจนับ'])->label('เลขที่ตรวจนับ') ?>
                </div>
                <div class="col-12 col-md-2">
                    <?= $form->field($searchModel, 'fiscal_year')->textInput(['placeholder' => 'ปีงบประมาณ'])->label('ปีงบประมาณ') ?>
                </div>
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'status')->dropDownList(AssetAudit::statusList(), ['prompt' => '-- สถานะทั้งหมด --'])->label('สถานะ') ?>
                </div>
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'q')->textInput(['placeholder' => 'ค้นหาจากเลขที่/ผู้ตรวจนับ/หมายเหตุ'])->label('คำค้น') ?>
                </div>
                <div class="col-12 col-md-1">
                    <?= Html::submitButton('ค้นหา', ['class' => 'btn btn-outline-primary w-100']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm audit-table-card">
        <?php $models = $dataProvider->getModels(); ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>เลขที่ตรวจนับ</th>
                        <th style="width: 110px;" class="text-center">ปีงบประมาณ</th>
                        <th>หน่วยงาน</th>
                        <th style="width: 120px;" class="text-center">วันที่ตรวจนับ</th>
                        <th>ผู้ตรวจนับ</th>
                        <th style="min-width: 240px;">ความคืบหน้า</th>
                        <th style="width: 100px;" class="text-center">คงเหลือ</th>
                        <th style="width: 120px;" class="text-center">อัตรา</th>
                        <th style="width: 130px;" class="text-center">สถานะ</th>
                        <th style="width: 140px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php foreach ($models as $index => $model): ?>
                        <?php
                        $summary = $model->progressSummary;
                        $isScoped = !empty($summary['scopeResolved']);
                        $percent = (float) ($summary['percent'] ?? 0);
                        $checked = (int) ($summary['checked'] ?? 0);
                        $total = (int) ($summary['total'] ?? 0);
                        $remaining = (int) ($summary['remaining'] ?? 0);
                        $barClass = $percent >= 100 ? 'bg-success' : ($percent >= 50 ? 'bg-primary' : 'bg-warning');
                        $barLabelClass = $percent >= 100 ? 'success' : ($percent >= 50 ? 'primary' : 'warning');
                        $statusClass = match ($model->status) {
                            AssetAudit::STATUS_ACTIVE => 'success',
                            AssetAudit::STATUS_CLOSED => 'secondary',
                            default => 'warning',
                        };
                        ?>
                        <tr>
                            <td class="text-muted"><?= number_format((int) ($dataProvider->pagination->offset + $index + 1)) ?></td>
                            <td>
                                <?= Html::a(Html::encode($model->audit_no), ['view', 'id' => $model->id], ['class' => 'fw-semibold text-decoration-none']) ?>
                            </td>
                            <td class="text-center"><?= Html::encode($model->fiscal_year) ?></td>
                            <td><?= Html::encode($model->departmentRef->name ?? '-') ?></td>
                            <td class="text-center"><?= $model->audit_date ? Html::encode(AppHelper::convertToThai($model->audit_date)) : '-' ?></td>
                            <td><?= Html::encode($model->auditorLabel) ?></td>
                            <td>
                                <?php if (!$isScoped): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ยังไม่ระบุหน่วยงาน</span>
                                <?php else: ?>
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                        <div class="small text-body-secondary"><?= number_format($checked) ?> / <?= number_format($total) ?> รายการ</div>
                                        <span class="badge bg-<?= $barLabelClass ?> bg-opacity-10 text-<?= $barLabelClass ?> border border-<?= $barLabelClass ?>-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($percent) ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar <?= $barClass ?>" role="progressbar" style="width: <?= Html::encode($percent) ?>%" aria-valuenow="<?= Html::encode($percent) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?= $isScoped ? Html::encode($remaining) : '-' ?>
                            </td>
                            <td class="text-center">
                                <?= $isScoped
                                    ? '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">' . Html::encode(($model->progressSummary['percent'] ?? 0) . '%') . '</span>'
                                    : '-' ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $statusClass ?> bg-opacity-10 text-<?= $statusClass ?> border border-<?= $statusClass ?>-subtle rounded-pill fw-medium px-2 py-1">
                                    <?= Html::encode($model->getStatusLabel()) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <?= Html::a('<i class="fa-solid fa-eye"></i>', ['view', 'id' => $model->id], [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'title' => 'ดูรายละเอียด',
                                    ]) ?>
                                    <?= Html::a('<i class="fa-solid fa-pen"></i>', ['update', 'id' => $model->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'แก้ไข',
                                    ]) ?>
                                    <?= Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $model->id], [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'data' => [
                                            'confirm' => 'ยืนยันลบรายการนี้หรือไม่?',
                                            'method' => 'post',
                                        ],
                                        'title' => 'ลบ',
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($models)): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">ไม่พบข้อมูลใบตรวจนับ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 px-3 py-3 border-top">
            <div class="text-muted small">
                แสดง <?= number_format((int) $dataProvider->getCount()) ?> รายการจากทั้งหมด <?= number_format((int) $dataProvider->getTotalCount()) ?> รายการ
            </div>
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
            ]) ?>
        </div>
    </div>
</div>
