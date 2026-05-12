<?php

use app\components\AppHelper;
use app\modules\am\models\AssetAudit;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var AssetAudit $model */

$this->title = $model->audit_no;
$this->params['breadcrumbs'][] = ['label' => 'ตรวจนับพัสดุประจำปี', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="file-check" class="me-2"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h3 class="mb-1"><?= Html::encode($model->audit_no) ?></h3>
                <div class="text-muted">
                    ปีงบประมาณ <?= Html::encode($model->thai_year) ?>
                    <?php if ($model->audit_date): ?>
                        | วันที่ตรวจนับ <?= Html::encode(AppHelper::convertToThai($model->audit_date)) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex gap-2">
                <?= Html::a('Export Excel', ['export-excel', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
                <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-outline-danger',
                    'data' => [
                        'confirm' => 'ต้องการลบใบตรวจนับนี้หรือไม่?',
                        'method' => 'post',
                    ],
                ]) ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php $summary = $model->progressSummary; ?>
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <div class="card border-0 bg-light h-100">
                    <div class="card-body">
                        <div class="text-muted small">ตรวจแล้ว</div>
                        <div class="fs-3 fw-bold text-success"><?= number_format((int) $summary['checked']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card border-0 bg-light h-100">
                    <div class="card-body">
                        <div class="text-muted small">ยังไม่ตรวจ</div>
                        <div class="fs-3 fw-bold text-danger"><?= number_format((int) $summary['remaining']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card border-0 bg-light h-100">
                    <div class="card-body">
                        <div class="text-muted small">รายการทั้งหมด</div>
                        <div class="fs-3 fw-bold"><?= number_format((int) $summary['total']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card border-0 bg-light h-100">
                    <div class="card-body">
                        <div class="text-muted small">อัตราการตรวจนับ</div>
                        <div class="fs-3 fw-bold text-primary"><?= Html::encode($summary['percent']) ?>%</div>
                        <div class="progress mt-2" style="height: 8px;">
                            <?php $percent = (float) ($summary['percent'] ?? 0); ?>
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= Html::encode($percent) ?>%" aria-valuenow="<?= Html::encode($percent) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($summary['scopeResolved'])): ?>
            <div class="alert alert-warning">
                ยังไม่สามารถคำนวณ KPI ครบถ้วนได้ เพราะใบตรวจนับนี้ยังไม่ได้ระบุหน่วยงาน
            </div>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-12 col-md-3">
                <div class="text-muted small">เลขที่ตรวจนับ</div>
                <div class="fw-semibold"><?= Html::encode($model->audit_no) ?></div>
            </div>
            <div class="col-12 col-md-3">
                <div class="text-muted small">ปีงบประมาณ</div>
                <div class="fw-semibold"><?= Html::encode($model->thai_year) ?></div>
            </div>
            <div class="col-12 col-md-3">
                <div class="text-muted small">หน่วยงาน</div>
                <div class="fw-semibold"><?= Html::encode($model->departmentRef->name ?? '-') ?></div>
            </div>
            <div class="col-12 col-md-3">
                <div class="text-muted small">วันที่ตรวจนับ</div>
                <div class="fw-semibold"><?= Html::encode($model->audit_date ? AppHelper::convertToThai($model->audit_date) : '-') ?></div>
            </div>
            <div class="col-12 col-md-3">
                <div class="text-muted small">สถานะ</div>
                <div>
                    <span class="badge bg-<?= $model->status === AssetAudit::STATUS_ACTIVE ? 'success' : 'warning' ?>">
                        <?= Html::encode($model->getStatusLabel()) ?>
                    </span>
                </div>
            </div>
            <div class="col-12">
                <div class="text-muted small">ผู้ตรวจนับ</div>
                <div class="fw-semibold"><?= Html::encode($model->auditorLabel) ?></div>
            </div>
            <div class="col-12">
                <div class="text-muted small">หมายเหตุรวม</div>
                <div><?= nl2br(Html::encode($model->summary_note ?: '-')) ?></div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">KPI ตามประเภททรัพย์สิน</h5>
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
                        <?php foreach (($summary['types'] ?? []) as $row): ?>
                            <tr>
                                <td><?= Html::encode($row['type']) ?></td>
                                <td class="text-end"><?= number_format((int) $row['checked']) ?></td>
                                <td class="text-end"><?= number_format((int) $row['total']) ?></td>
                                <td class="text-end"><?= number_format((int) $row['remaining']) ?></td>
                                <td class="text-end"><?= Html::encode($row['percent']) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($summary['types'])): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">ไม่มีข้อมูลประเภททรัพย์สินสำหรับคำนวณ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">รายการที่ตรวจนับ</h5>
    </div>
    <div class="card-body">


        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;" class="text-center">ลำดับ</th>
                        <th>รหัส</th>
                        <th>ชื่อครุภัณฑ์</th>
                        <th>สภาพ</th>
                        <th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->auditItems as $index => $item): ?>
                        <tr>
                            <td class="text-center fw-semibold"><?= $index + 1 ?></td>
                            <td><?= Html::encode($item->asset_code) ?></td>
                            <td><?= Html::encode($item->asset_name) ?></td>
                            <td><?= Html::encode($item->condition->name ?? $item->asset_condition ?? '-') ?></td>
                            <td><?= nl2br(Html::encode($item->note ?: '-')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($model->auditItems)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">ไม่มีรายการ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
