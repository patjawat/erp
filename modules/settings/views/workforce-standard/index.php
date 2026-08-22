<?php

use app\modules\hr\models\WorkforceStandardLine;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var WorkforceStandardLine[] $lines */
/** @var array $ruleMap */
/** @var array $mapCounts */
/** @var string $level */
/** @var string $category */
/** @var array $levels */
/** @var app\modules\hr\models\WorkforceProfile $profile */
/** @var int $year */
/** @var int $unverified */

$this->title = 'เกณฑ์กรอบอัตรากำลัง';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/settings/views/_workforce_menu', ['active' => 'workforce-standard']) ?>
<?php $this->endBlock(); ?>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-3 align-items-end justify-content-between">
            <div>
                <div class="fw-semibold">เกณฑ์การกำหนดกรอบอัตรากำลัง สป.สธ. ปี 2565–2569</div>
                <p class="text-body-secondary small mb-0">
                    เกณฑ์มาพร้อมระบบ แก้จากหน้าจอไม่ได้ — สิ่งที่ต้องทำคือจับคู่สายงานกับตำแหน่งของโรงพยาบาล
                </p>
            </div>
            <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex flex-wrap align-items-end gap-2']) ?>
                <?= Html::hiddenInput('thai_year', $year) ?>
                <div>
                    <label class="form-label small mb-1">ระดับโรงพยาบาล</label>
                    <select name="level" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">— ทุกระดับ —</option>
                        <?php foreach ($levels as $code => $title): ?>
                            <option value="<?= Html::encode($code) ?>" <?= $level === (string) $code ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
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
            <div class="small">ระบบยังบอกไม่ได้ว่าสายงานไหนมีกรอบได้บ้าง — <?= Html::a('ตั้งค่าโปรไฟล์โรงพยาบาล', ['/settings/workforce-profile']) ?></div>
        </div>
    </div>
<?php endif; ?>

<?php if ($unverified > 0): ?>
    <div class="alert alert-info d-flex gap-2 align-items-start">
        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
        <div>
            <div class="fw-semibold">มี <?= (int) $unverified ?> สายงานที่ยังไม่ยืนยันสำหรับระดับนี้</div>
            <div class="small">
                ช่องเหล่านี้อ่านจากเอกสารต้นทางไม่ชัดพอ ระบบจึงไม่ตัดสินให้ว่ามีกรอบหรือไม่มี
                ต้องตรวจกับเอกสารก่อนใช้ตัดสินใจจริง
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:5rem">ลำดับ</th>
                    <th>สายงานตามเกณฑ์</th>
                    <th style="width:11rem">ประเภท</th>
                    <th style="width:14rem">วิธีกำหนดกรอบ</th>
                    <?php if ($level !== ''): ?>
                        <th style="width:9rem">ระดับ <?= Html::encode($level) ?></th>
                    <?php endif; ?>
                    <th style="width:8rem" class="text-end">ตำแหน่งที่ผูก</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lines as $line): ?>
                    <?php
                    $rule = $ruleMap[$line->id] ?? null;
                    $linked = (int) ($mapCounts[$line->id] ?? 0);
                    ?>
                    <tr>
                        <td class="text-body-secondary font-monospace"><?= $line->seq !== null ? (int) $line->seq : '—' ?></td>
                        <td>
                            <div><?= Html::encode($line->title) ?></div>
                            <?php if ($line->note !== null && $line->note !== ''): ?>
                                <div class="small text-body-secondary"><?= Html::encode($line->note) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="small text-body-secondary"><?= Html::encode($line->categoryLabel()) ?></td>
                        <td>
                            <span class="badge <?= $line->isAutoCalculated() ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>">
                                <?= Html::encode($line->methodLabel()) ?>
                            </span>
                        </td>
                        <?php if ($level !== ''): ?>
                            <td>
                                <?php if ($rule === null || $rule->eligible === null): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis">ยังไม่ยืนยัน</span>
                                <?php elseif ((int) $rule->eligible === 1): ?>
                                    <span class="badge bg-primary-subtle text-primary-emphasis">มีกรอบได้</span>
                                <?php else: ?>
                                    <span class="text-body-secondary small">ไม่มีกรอบ</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td class="text-end">
                            <?php if ($linked > 0): ?>
                                <span class="badge bg-body-secondary text-body"><?= $linked ?></span>
                            <?php else: ?>
                                <span class="text-body-secondary">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <?= Html::a('<i class="bi bi-link-45deg me-1"></i> จับคู่กับตำแหน่งของโรงพยาบาล', ['map'], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('โปรไฟล์โรงพยาบาล', ['/settings/workforce-profile'], ['class' => 'btn btn-outline-secondary']) ?>
</div>
