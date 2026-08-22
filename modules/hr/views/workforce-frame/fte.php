<?php

use app\modules\hr\models\WorkforceFrame;
use app\modules\hr\services\WorkforceFrameCalculator as Calc;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $year */
/** @var app\modules\hr\models\WorkforceProfile $profile */
/** @var array $rows */
/** @var bool $editable */

$this->title = 'กรอก FTE สายวิชาชีพ';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'workforce-frame']) ?>
<?php $this->endBlock(); ?>

<?php foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls): ?>
    <?php if (Yii::$app->session->hasFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
            <?= Yii::$app->session->getFlash($key) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card mb-3">
    <div class="card-body">
        <div class="fw-semibold">
            รอบปี <?= (int) $year ?> · <?= Html::encode($profile->statusLabel()) ?>
        </div>
        <p class="text-body-secondary small mb-0">
            เกณฑ์ สป.สธ. ให้โรงพยาบาลคำนวณ FTE ตามภาระงานเองสำหรับสายงานเหล่านี้
            ระบบคำนวณแทนไม่ได้ กรอกตัวเลขที่คำนวณได้แล้วลงไป ช่องว่าง = ยังไม่ได้กำหนดกรอบ
        </p>
    </div>
</div>

<?php if (!$editable): ?>
    <div class="alert alert-secondary d-flex gap-2 align-items-start">
        <i class="bi bi-lock-fill flex-shrink-0 mt-1"></i>
        <div class="small">
            <?= $profile->isLocked()
                ? 'รอบนี้' . Html::encode($profile->statusLabel()) . 'แล้ว ตัวเลขถูกล็อกไว้อ้างอิง แก้ไขไม่ได้'
                : 'คุณมีสิทธิ์ดูอย่างเดียว การแก้ไขต้องเป็นเจ้าหน้าที่ HR' ?>
        </div>
    </div>
<?php endif; ?>

<?= Html::beginForm(['save-fte'], 'post', ['id' => 'fte-form']) ?>
<?= Html::hiddenInput('thai_year', $year) ?>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>สายงานตามเกณฑ์</th>
                    <th style="width:7rem" class="text-end">มีอยู่จริง</th>
                    <th style="width:9rem">FTE / กรอบ</th>
                    <th style="width:28%">หมายเหตุ</th>
                    <th style="width:8rem" class="text-end">ส่วนขาด</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="5" class="text-center text-body-secondary py-4">ไม่มีสายงานที่ต้องกรอก FTE</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $line = $row['line'];
                    /** @var WorkforceFrame|null $saved */
                    $saved = $row['saved'] ?? null;
                    $gap = $row['gap'];
                    ?>
                    <tr>
                        <td>
                            <div><?= Html::encode($line->title) ?></div>
                            <div class="small text-body-secondary">
                                <?= Html::encode($line->categoryLabel()) ?>
                                <?php if ($row['status'] === Calc::STATUS_MANUAL_FTE): ?>
                                    · <span class="badge bg-success-subtle text-success-emphasis">กรอกแล้ว</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-end font-monospace"><?= (int) $row['in_frame'] ?></td>
                        <td>
                            <?= Html::textInput('fte[' . (int) $line->id . ']', $saved?->frame_qty, [
                                'class' => 'form-control form-control-sm text-end',
                                'inputmode' => 'decimal',
                                'disabled' => !$editable,
                                'aria-label' => 'FTE ของ ' . $line->title,
                            ]) ?>
                        </td>
                        <td>
                            <?= Html::textInput('note[' . (int) $line->id . ']', $saved?->note, [
                                'class' => 'form-control form-control-sm',
                                'placeholder' => 'ที่มาของ FTE เช่น ภาระงาน OPD ปี 2568',
                                'disabled' => !$editable,
                                'aria-label' => 'หมายเหตุของ ' . $line->title,
                            ]) ?>
                        </td>
                        <td class="text-end font-monospace <?= $gap !== null && $gap > 0 ? 'text-danger-emphasis fw-semibold' : 'text-body-secondary' ?>">
                            <?= $gap === null ? '—' : rtrim(rtrim(number_format((float) $gap, 2), '0'), '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($editable): ?>
    <div class="d-flex gap-2 mt-3">
        <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึก FTE', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('กลับไปหน้ากรอบ', ['index', 'thai_year' => $year], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
<?php else: ?>
    <div class="mt-3"><?= Html::a('กลับไปหน้ากรอบ', ['index', 'thai_year' => $year], ['class' => 'btn btn-outline-secondary']) ?></div>
<?php endif; ?>
<?= Html::endForm() ?>
