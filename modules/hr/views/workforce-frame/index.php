<?php

use app\modules\hr\models\WorkforceStandardLine;
use app\modules\hr\services\WorkforceFrameCalculator as Calc;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $year */
/** @var array $years */
/** @var app\modules\hr\models\WorkforceProfile $profile */
/** @var array $rows */
/** @var string $category */
/** @var array $summary */
/** @var array $unmapped */
/** @var array $outOfScope */
/** @var bool $canManage */
/** @var bool $canApprove */

use app\modules\hr\models\WorkforceProfile;

$this->title = 'กรอบอัตรากำลัง';
$locked = $profile->isLocked();
$editable = $canManage && !$locked;

$statusBadge = static function (string $status): string {
    $class = match ($status) {
        Calc::STATUS_CALCULATED => 'bg-success-subtle text-success-emphasis',
        Calc::STATUS_NEEDS_FTE => 'bg-warning-subtle text-warning-emphasis',
        Calc::STATUS_NOT_ELIGIBLE => 'bg-body-secondary text-body-secondary',
        default => 'bg-info-subtle text-info-emphasis',
    };

    return Html::tag('span', Html::encode(Calc::STATUS_LABELS[$status] ?? $status), ['class' => 'badge ' . $class]);
};

$number = static fn ($value) => $value === null ? '—' : rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
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
    <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div>
            <span class="badge <?= match ($profile->status) {
                WorkforceProfile::STATUS_APPROVED, WorkforceProfile::STATUS_CLOSED => 'bg-success-subtle text-success-emphasis',
                WorkforceProfile::STATUS_SUBMITTED => 'bg-info-subtle text-info-emphasis',
                default => 'bg-secondary-subtle text-secondary-emphasis',
            } ?>">
                <?php if ($locked): ?><i class="bi bi-lock-fill me-1"></i><?php endif; ?>
                รอบปี <?= (int) $year ?> · <?= Html::encode($profile->statusLabel()) ?>
            </span>
            <?php if ($profile->approved_at !== null): ?>
                <span class="small text-body-secondary ms-2">อนุมัติเมื่อ <?= Html::encode($profile->approved_at) ?></span>
            <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('<i class="bi bi-file-earmark-spreadsheet me-1"></i> สรุป / ส่งออก', ['report', 'thai_year' => $year], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?= Html::a('<i class="bi bi-people me-1"></i> Outsource', ['outsource', 'thai_year' => $year], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?php if ($canManage): ?>
                <?= Html::a('<i class="bi bi-pencil-square me-1"></i> กรอก FTE', ['fte', 'thai_year' => $year], ['class' => 'btn btn-sm btn-outline-primary']) ?>
            <?php endif; ?>
            <?php if ($editable && $profile->canSubmit()): ?>
                <?= Html::beginForm(['submit'], 'post', ['class' => 'd-inline']) . Html::hiddenInput('thai_year', $year) ?>
                <?= Html::submitButton('<i class="bi bi-send me-1"></i> ส่งให้ผู้อำนวยการ', ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
            <?php if ($canApprove && $profile->canApprove()): ?>
                <?= Html::beginForm(['approve'], 'post', ['class' => 'd-inline']) . Html::hiddenInput('thai_year', $year) ?>
                <?= Html::submitButton('<i class="bi bi-check2-circle me-1"></i> อนุมัติ', ['class' => 'btn btn-sm btn-success']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
            <?php if ($canApprove && $profile->status !== WorkforceProfile::STATUS_DRAFT): ?>
                <?= Html::beginForm(['reopen'], 'post', ['class' => 'd-inline']) . Html::hiddenInput('thai_year', $year) ?>
                <?= Html::submitButton('เปิดกลับมาแก้', ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-2">
            <div class="small text-body-secondary">กรอบที่คำนวณได้</div>
            <div class="fs-4 fw-semibold"><?= $number($summary['frame']) ?></div>
            <div class="small text-body-secondary">จาก <?= (int) $summary['calculated'] ?> สายงาน</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-2">
            <div class="small text-body-secondary">มีอยู่จริง (นับในกรอบ)</div>
            <div class="fs-4 fw-semibold"><?= (int) $summary['in_frame'] ?></div>
            <div class="small text-body-secondary">ตามเกณฑ์ 5 ประเภทการจ้าง</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-2">
            <div class="small text-body-secondary">Outsource</div>
            <div class="fs-4 fw-semibold"><?= (int) $summary['outsource'] ?></div>
            <div class="small text-body-secondary">ลูกจ้างรายวัน / จ้างเหมา</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body py-2">
            <div class="small text-body-secondary">ยังคำนวณไม่ได้</div>
            <div class="fs-4 fw-semibold <?= $summary['blocked'] > 0 ? 'text-warning-emphasis' : '' ?>"><?= (int) $summary['blocked'] ?></div>
            <div class="small text-body-secondary">สายงานที่ติดเงื่อนไข</div>
        </div></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-3 align-items-end justify-content-between">
            <div>
                <div class="fw-semibold">
                    เกณฑ์ระดับ <?= Html::encode($profile->level_code ?: '— ยังไม่ตั้ง —') ?>
                    <?php if ($profile->catchment_population !== null): ?>
                        · ประชากร <?= number_format((int) $profile->catchment_population) ?>
                    <?php endif; ?>
                </div>
                <p class="text-body-secondary small mb-0">
                    กรอบคำนวณจากเกณฑ์ สป.สธ. เทียบกับทะเบียนบุคลากรโดยตรง — ยังแก้ไขจากหน้านี้ไม่ได้
                </p>
            </div>
            <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex flex-wrap align-items-end gap-2']) ?>
                <div>
                    <label class="form-label small mb-1">ปีงบประมาณ</label>
                    <select name="thai_year" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($years as $value => $label): ?>
                            <option value="<?= (int) $value ?>" <?= $year === (int) $value ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">ประเภทสายงาน</label>
                    <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">— ทั้งหมด —</option>
                        <?php foreach (WorkforceStandardLine::CATEGORY_LABELS as $code => $title): ?>
                            <option value="<?= Html::encode($code) ?>" <?= $category === (string) $code ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<?php if ($profile->level_code === null || $profile->level_code === ''): ?>
    <div class="alert alert-warning d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div>
            <div class="fw-semibold">ยังไม่ได้ตั้งระดับโรงพยาบาล</div>
            <div class="small">ระบบยังคำนวณกรอบไม่ได้เลย — <?= Html::a('ไปตั้งค่าโปรไฟล์โรงพยาบาล', ['/settings/workforce-profile']) ?></div>
        </div>
    </div>
<?php elseif ($profile->missingDrivers() !== []): ?>
    <div class="alert alert-warning d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div>
            <div class="fw-semibold">ยังกรอกตัวเลขที่สูตรใช้ไม่ครบ <?= count($profile->missingDrivers()) ?> รายการ</div>
            <div class="small">
                <?= Html::encode(implode(' · ', $profile->missingDrivers())) ?> —
                <?= Html::a('ไปกรอก', ['/settings/workforce-profile', 'thai_year' => $year]) ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>สายงานตามเกณฑ์</th>
                    <th style="width:6rem" class="text-end">กรอบ</th>
                    <th style="width:7rem" class="text-end">มีอยู่จริง</th>
                    <th style="width:6rem" class="text-end">ส่วนขาด</th>
                    <th style="width:12rem">ที่มา</th>
                    <th style="width:3rem"></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="6" class="text-center text-body-secondary py-4">ไม่มีสายงานในตัวกรองนี้</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $index => $row): ?>
                    <?php
                    $line = $row['line'];
                    $hasGap = $row['gap'] !== null && $row['gap'] > 0;
                    $collapseId = 'calc-' . $index;
                    ?>
                    <tr>
                        <td>
                            <div><?= Html::encode($line->title) ?></div>
                            <div class="small text-body-secondary">
                                <?= Html::encode($line->categoryLabel()) ?>
                                <?php if ($row['outsource'] > 0): ?>
                                    · Outsource <?= (int) $row['outsource'] ?> คน
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-end font-monospace fw-semibold"><?= $number($row['frame']) ?></td>
                        <td class="text-end font-monospace"><?= (int) $row['in_frame'] ?></td>
                        <td class="text-end font-monospace <?= $hasGap ? 'text-danger-emphasis fw-semibold' : 'text-body-secondary' ?>">
                            <?= $row['gap'] === null ? '—' : $number($row['gap']) ?>
                        </td>
                        <td><?= $statusBadge($row['status']) ?></td>
                        <td class="text-end">
                            <?php if ($row['calc'] !== []): ?>
                                <button class="btn btn-sm btn-link p-0 text-decoration-none" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>"
                                        aria-expanded="false" aria-controls="<?= $collapseId ?>"
                                        aria-label="ดูที่มาของ <?= Html::encode($line->title) ?>">
                                    <i class="bi bi-calculator"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($row['calc'] !== []): ?>
                        <tr class="collapse" id="<?= $collapseId ?>">
                            <td colspan="6" class="bg-body-tertiary">
                                <div class="small">
                                    <div class="fw-semibold mb-1">ที่มาของตัวเลข</div>
                                    <?php foreach ($row['calc'] as [$label, $value]): ?>
                                        <div class="d-flex justify-content-between border-bottom py-1">
                                            <span class="text-body-secondary"><?= Html::encode($label) ?></span>
                                            <span class="font-monospace"><?= Html::encode($value) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($line->note !== null && $line->note !== ''): ?>
                                        <div class="text-body-secondary mt-2"><i class="bi bi-book me-1"></i><?= Html::encode($line->note) ?></div>
                                    <?php endif; ?>

                                    <?php if ($editable && $row['status'] !== Calc::STATUS_NOT_ELIGIBLE): ?>
                                        <?= Html::beginForm(['override'], 'post', ['class' => 'row g-2 align-items-end mt-2 pt-2 border-top']) ?>
                                            <?= Html::hiddenInput('thai_year', $year) . Html::hiddenInput('line_id', (int) $line->id) ?>
                                            <div class="col-6 col-md-2">
                                                <label class="form-label small mb-1">กรอกทับเป็น</label>
                                                <?= Html::textInput('frame_qty', $row['saved']?->source === 'override' ? $row['saved']->frame_qty : '', [
                                                    'class' => 'form-control form-control-sm text-end',
                                                    'inputmode' => 'decimal',
                                                    'placeholder' => 'ว่าง = ใช้ค่าเกณฑ์',
                                                ]) ?>
                                            </div>
                                            <div class="col-12 col-md-8">
                                                <label class="form-label small mb-1">เหตุผล (บังคับ)</label>
                                                <?= Html::textInput('override_reason', $row['saved']?->override_reason, [
                                                    'class' => 'form-control form-control-sm',
                                                    'placeholder' => 'เช่น เขตอนุมัติกรอบเพิ่มตามหนังสือที่ ...',
                                                ]) ?>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <?= Html::submitButton('บันทึก', ['class' => 'btn btn-sm btn-outline-primary w-100']) ?>
                                            </div>
                                        <?= Html::endForm() ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($unmapped !== []): ?>
    <div class="card mt-3">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
            <span class="fw-semibold">ตำแหน่งที่ยังไม่ได้จับคู่กับเกณฑ์ (<?= count($unmapped) ?>)</span>
            <?php if ($canManage): ?>
                <?= Html::a('ไปจับคู่', ['/settings/workforce-standard/map'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <p class="small text-body-secondary">คนกลุ่มนี้ยังไม่ถูกนับเข้ากรอบสายงานใด — ตัวเลขในตารางด้านบนจึงยังไม่ครบ</p>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($unmapped as $item): ?>
                    <span class="badge bg-warning-subtle text-warning-emphasis">
                        <?= Html::encode($item['title']) ?> · <?= (int) $item['count'] ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($outOfScope !== []): ?>
    <div class="card mt-3">
        <div class="card-header bg-body-tertiary fw-semibold">ตำแหน่งที่ยืนยันแล้วว่าไม่มีในเกณฑ์ (<?= count($outOfScope) ?>)</div>
        <div class="card-body d-flex flex-wrap gap-2">
            <?php foreach ($outOfScope as $item): ?>
                <span class="badge bg-body-secondary text-body">
                    <?= Html::encode($item['title']) ?> · <?= (int) $item['count'] ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
