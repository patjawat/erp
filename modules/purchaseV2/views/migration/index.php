<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'ย้ายข้อมูลจากระบบเดิม';
$this->params['breadcrumbs'][] = ['label' => 'จัดซื้อจัดจ้าง V2', 'url' => ['/purchase-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$rows = $preview['rows'] ?? [];
$total = (int) ($preview['total'] ?? 0);
$canManage = Yii::$app->user->can('admin') || Yii::$app->user->can('purchase');
$migratedTotal = (int) ($preview['migrated_count'] ?? 0);
$remaining = max(0, $total - $migratedTotal);
?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/purchaseV2/menu', ['active' => 'migration']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="database-zap" class="text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">ตรวจสอบและย้ายข้อมูลจาก modules/purchase เดิมมายัง purchaseV2 แบบควบคุมได้</p>
</div>
<?php $this->endBlock(); ?>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="text-muted small">ข้อมูลเดิมทั้งหมด</div>
                <div class="display-6 fw-bold text-primary"><?= number_format($total, 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="text-muted small">ย้ายแล้ว</div>
                <div class="display-6 fw-bold text-success"><?= number_format($migratedTotal, 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="text-muted small">คงเหลือ</div>
                <div class="display-6 fw-bold text-warning"><?= number_format($remaining, 0) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body">
        <?php $form = ActiveForm::begin(['method' => 'get', 'options' => ['class' => 'row g-3 align-items-end']]); ?>
            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold">ค้นหา</label>
                <?= Html::textInput('q', Yii::$app->request->get('q'), ['class' => 'form-control', 'placeholder' => 'เลขที่ / ชื่อเรื่อง / ผู้ขอ / เลขเอกสาร']) ?>
            </div>
            <div class="col-12 col-lg-auto">
                <?= Html::submitButton('<i data-lucide="search" class="me-1"></i> ค้นหา', ['class' => 'btn btn-primary rounded-3 fw-semibold']) ?>
            </div>
            <div class="col-12 col-lg-auto">
                <?= Html::a('<i data-lucide="rotate-ccw" class="me-1"></i> ล้างค่า', ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            </div>
            <?php if ($canManage): ?>
                <div class="col-12 col-lg-auto ms-lg-auto">
                    <?= Html::a('<i data-lucide="database-zap" class="me-1"></i> ย้ายทั้งหมดที่ยังไม่ย้าย', ['/purchase-v2/migration/migrate-all'], [
                        'class' => 'btn btn-outline-primary rounded-3 fw-semibold',
                        'data' => [
                            'method' => 'post',
                            'confirm' => 'ยืนยันการย้ายข้อมูลที่ยังไม่เคยย้ายทั้งหมด?',
                        ],
                    ]) ?>
                </div>
            <?php endif; ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0 fw-bold">รายการจากระบบเดิม</h5>
            <div class="text-muted small">ตรวจสอบก่อนย้ายแต่ละรายการ</div>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold"><?= number_format(count($rows), 0) ?> แถวในหน้าปัจจุบัน</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>เอกสารเดิม</th>
                        <th>ผู้ขอ / หน่วยงาน</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">รายการ</th>
                        <th class="text-end">มูลค่า</th>
                        <th class="text-end text-nowrap">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= Html::encode($row['request_no']) ?></div>
                                <div class="text-muted small">Legacy ID: <?= Html::encode($row['legacy_id']) ?></div>
                                <div class="text-muted small"><?= Html::encode($row['request_title']) ?></div>
                                <?php if (!empty($row['migrated_request_no'])): ?>
                                    <div class="text-muted small">ใหม่: <?= Html::encode($row['migrated_request_no']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= Html::encode($row['requester_name']) ?></div>
                                <div class="text-muted small"><?= Html::encode($row['department_name']) ?></div>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($row['migrated'])): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-2">ย้ายแล้ว</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-3 py-2">รอย้าย</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column gap-1 align-items-center">
                                    <span class="badge bg-light text-dark border">รายการ <?= number_format((int) $row['item_count'], 0) ?></span>
                                    <span class="badge bg-light text-dark border">อนุมัติ <?= number_format((int) $row['approval_count'], 0) ?></span>
                                </div>
                            </td>
                            <td class="text-end fw-semibold"><?= number_format((float) $row['grand_total'], 2) ?></td>
                            <td class="text-end">
                                <?php if (empty($row['migrated'])): ?>
                                    <?= Html::a('<i data-lucide="database-zap" class="me-1"></i> ย้ายรายการนี้', ['/purchase-v2/migration/migrate', 'id' => $row['legacy_id']], [
                                        'class' => 'btn btn-sm btn-outline-primary rounded-3',
                                        'data' => [
                                            'method' => 'post',
                                            'confirm' => 'ยืนยันการย้ายรายการนี้?',
                                        ],
                                    ]) ?>
                                <?php else: ?>
                                    <?php if (!empty($row['migrated_request_id'])): ?>
                                        <?= Html::a('<i data-lucide="eye" class="me-1"></i> ดูใหม่', ['/purchase-v2/request/view', 'id' => $row['migrated_request_id']], [
                                            'class' => 'btn btn-sm btn-outline-success rounded-3 open-modal',
                                            'data' => ['size' => 'modal-xl'],
                                        ]) ?>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-2">ย้ายแล้ว</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
