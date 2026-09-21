<?php

use yii\helpers\Html;
use app\components\RichText;
use app\modules\pm\components\KpiStatus;
use app\modules\pm\models\KpiIndicator;
use app\modules\pm\models\KpiIndicatorMonth;

/** @var app\modules\pm\models\KpiIndicator $indicator */
/** @var app\modules\pm\models\KpiIndicatorYear $entry @var int $year */
/** @var array $monthMap (month => KpiIndicatorMonth) @var int[] $yearOpts */

$this->title = 'บันทึกข้อมูล';
$this->beginBlock('page-title'); ?>บันทึกข้อมูล<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'kpi']) ?><?php $this->endBlock();

$aggLabel = KpiIndicator::aggregationList()[$indicator->aggregation] ?? '-';
$status = $entry->isNewRecord ? KpiStatus::NODATA : KpiStatus::evaluate($entry->target_value, $entry->actual_value, $indicator->operator);
?>

<?php foreach (['success' => 'success', 'error' => 'danger'] as $k => $cls): ?>
    <?php if (Yii::$app->session->hasFlash($k)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash($k)) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="mb-3">
    <h1 class="h5 mb-1">บันทึกข้อมูล</h1>
    <div class="text-body-secondary"><?= Html::encode(RichText::plain($indicator->name, 300)) ?><?= $indicator->unit ? ' (' . Html::encode($indicator->unit) . ')' : '' ?></div>
</div>

<div class="mb-3 d-flex flex-wrap align-items-center gap-2">
    <span class="text-muted small">ปีงบประมาณ:</span>
    <?php foreach ($yearOpts as $y): ?>
        <?= Html::a((string) $y, ['data', 'id' => $indicator->id, 'year' => $y], ['class' => 'btn btn-sm ' . ($y === $year ? 'btn-primary' : 'btn-outline-secondary')]) ?>
    <?php endforeach; ?>
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับทะเบียน', ['index'], ['class' => 'btn btn-sm btn-link text-decoration-none ms-auto']) ?>
</div>

<?= Html::beginForm(['data', 'id' => $indicator->id, 'year' => $year], 'post') ?>

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <h6 class="fw-bold mb-3">ข้อมูลรายปี พ.ศ. <?= (int) $year ?></h6>
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <label class="form-label">เป้าหมาย (Target)</label>
            <?= Html::input('number', 'target', $entry->target_value, ['class' => 'form-control', 'step' => 'any']) ?>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">ค่าจริงรายปี (คำนวณอัตโนมัติ)</label>
            <input type="text" class="form-control bg-body-secondary" value="<?= $entry->actual_value !== null ? Html::encode(rtrim(rtrim((string) $entry->actual_value, '0'), '.')) : '-' ?>" readonly>
            <div class="form-text"><?= Html::encode($aggLabel) ?></div>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">สถานะ</label>
            <div><span class="badge <?= KpiStatus::badgeClass($status) ?> fs-6"><?= Html::encode(KpiStatus::label($status)) ?></span></div>
        </div>
    </div>
</div></div>

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <h6 class="fw-bold mb-1">ข้อมูลรายเดือน</h6>
    <p class="small text-muted mb-3">กรอกผลงานรายเดือน (ต.ค.→ก.ย.) — ค่าจริงรายปีจะคำนวณให้อัตโนมัติเมื่อบันทึก</p>
    <div class="row g-3">
        <?php foreach (KpiIndicatorMonth::FISCAL_MONTHS as $m): ?>
            <?php $mm = $monthMap[$m] ?? null; ?>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small mb-1"><?= Html::encode(KpiIndicatorMonth::monthName($m)) ?></label>
                <?= Html::input('number', "Months[$m][value]", $mm?->value, ['class' => 'form-control form-control-sm', 'step' => 'any', 'placeholder' => '-']) ?>
            </div>
        <?php endforeach; ?>
    </div>
</div></div>

<div class="d-flex justify-content-end gap-2 mb-4">
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึกข้อมูล', ['class' => 'btn btn-primary']) ?>
</div>

<?= Html::endForm() ?>
