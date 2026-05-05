<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetAuditSearch $searchModel */
/** @var array $overallKpi */
/** @var array $typeTotals */
/** @var int|null $kpiFiscalYear */
/** @var int $noDepartmentCount */

$this->title = 'KPI ตรวจนับพัสดุประจำปี';
$this->params['breadcrumbs'][] = ['label' => 'ตรวจนับพัสดุประจำปี', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'KPI Dashboard';
?>

<style>
.audit-dashboard-hero {
    background:
        radial-gradient(circle at top right, rgba(13, 110, 253, 0.14), transparent 28%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid rgba(13, 110, 253, 0.08);
}

.audit-kpi-card {
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: 0 0.75rem 1.75rem rgba(15, 23, 42, 0.04);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.audit-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 1rem 2rem rgba(15, 23, 42, 0.08);
}

.audit-kpi-value {
    letter-spacing: -0.02em;
    line-height: 1;
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
    <i data-lucide="layout-dashboard" class="me-2"></i>
    <?= Html::encode($this->title) ?>
  </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2 flex-wrap">
    <?= Html::a('กลับไปหน้ารายการ', ['index'], ['class' => 'btn btn-outline-primary']) ?>
    <?= Html::a('สร้างใบตรวจนับ', ['create'], ['class' => 'btn btn-primary']) ?>
</div>
<?php $this->endBlock(); ?>

<div class="audit-dashboard">
    <div class="card audit-dashboard-hero border-0 shadow-sm mb-3">
        <div class="card-body p-4 p-lg-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="pe-lg-4">
                    <div class="d-inline-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">Annual KPI</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">Audit</span>
                    </div>
                    <h1 class="h3 mb-2 fw-semibold text-body">แดชบอร์ด KPI การตรวจนับ</h1>
                    <div class="text-muted mb-0">
                        สรุปผลการตรวจนับครุภัณฑ์ทั้งหมด แยกตามประเภท และแสดงภาพรวมความคืบหน้าของงานตรวจนับ
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?= Html::a('ตารางรายการตรวจนับ', ['index'], ['class' => 'btn btn-outline-success']) ?>
                    <?= Html::a('รายงานครุภัณฑ์คงเหลือ', ['/am/report/register'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">ตัวกรอง KPI</h5>
                    <div class="text-muted small">เลือกปีงบประมาณเพื่อดูสรุปเฉพาะรอบการตรวจนับนั้น</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">Dashboard</span>
                    <?= Html::a('ล้างตัวกรอง', ['dashboard'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                </div>
            </div>
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['dashboard']]); ?>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'fiscal_year')->textInput(['placeholder' => 'ปีงบประมาณ'])->label('ปีงบประมาณ') ?>
                </div>
                <div class="col-12 col-md-2">
                    <?= Html::submitButton('แสดงผล', ['class' => 'btn btn-outline-primary w-100']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php $overall = $overallKpi ?? ['total' => 0, 'checked' => 0, 'remaining' => 0, 'percent' => 0]; ?>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-3">
            <div class="card audit-kpi-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="text-muted small mb-1">รายการที่ต้องตรวจนับ</div>
                            <div class="fs-3 fw-bold audit-kpi-value"><?= number_format((int) $overall['total']) ?></div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">Total</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card audit-kpi-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="text-muted small mb-1">ตรวจแล้ว</div>
                            <div class="fs-3 fw-bold text-success audit-kpi-value"><?= number_format((int) $overall['checked']) ?></div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">Done</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card audit-kpi-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="text-muted small mb-1">ยังไม่ตรวจ</div>
                            <div class="fs-3 fw-bold text-danger audit-kpi-value"><?= number_format((int) $overall['remaining']) ?></div>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">Remain</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card audit-kpi-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="text-muted small mb-1">อัตราการตรวจนับ</div>
                            <div class="fs-3 fw-bold text-primary audit-kpi-value"><?= Html::encode($overall['percent']) ?>%</div>
                        </div>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">KPI</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="card audit-kpi-card border-0 h-100">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="text-muted small mb-1">ครุภัณฑ์ที่ยังไม่กำหนดหน่วยงานรับผิดชอบ</div>
                        <div class="fs-3 fw-bold text-warning audit-kpi-value"><?= number_format((int) $noDepartmentCount) ?></div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">No Department</span>
                        <?= Html::a('ดูรายการครุภัณฑ์', ['/am/equip/index', 'AssetSearch' => ['no_department' => 1]], ['class' => 'btn btn-outline-warning']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card audit-table-card border-0 shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">KPI ตามประเภททรัพย์สิน</h5>
                <div class="text-muted small">
                    ทุกรายการ = ทะเบียนครุภัณฑ์ทั้งหมด
                    <?php if (!empty($kpiFiscalYear)): ?>
                        | ตรวจแล้ว = รายการที่ตรวจในปีงบประมาณ <?= Html::encode($kpiFiscalYear) ?>
                    <?php else: ?>
                        | ตรวจแล้ว = รายการที่ตรวจตามข้อมูลปีงบประมาณที่มีอยู่ทั้งหมด
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ประเภท</th>
                        <th class="text-end">ตรวจแล้ว</th>
                        <th class="text-end">ทั้งหมด</th>
                        <th class="text-end">เหลือ</th>
                        <th class="text-end">เปอร์เซ็นต์</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($typeTotals ?? []) as $row): ?>
                        <tr>
                            <td><?= Html::encode($row['type']) ?></td>
                            <td class="text-end"><?= number_format((int) $row['checked']) ?></td>
                            <td class="text-end"><?= number_format((int) $row['total']) ?></td>
                            <td class="text-end"><?= number_format((int) $row['remaining']) ?></td>
                            <td class="text-end">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                                    <?= Html::encode($row['percent']) ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($typeTotals)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">ยังไม่มีข้อมูลประเภททรัพย์สิน</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
