<?php

use app\modules\hr\services\WorkforceFrameCalculator as Calc;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $year */
/** @var array $years */
/** @var app\modules\hr\models\WorkforceProfile $profile */
/** @var array $rows */
/** @var array $types */
/** @var array $totals */

$this->title = 'สรุปกรอบอัตรากำลัง';
$number = static fn ($value) => $value === null || $value === '' ? '—' : rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'workforce-frame']) ?>
<?php $this->endBlock(); ?>

<div class="card mb-3">
    <div class="card-body d-flex flex-wrap gap-3 align-items-end justify-content-between">
        <div>
            <div class="fw-semibold">
                ปีงบประมาณ <?= (int) $year ?> · ระดับ <?= Html::encode($profile->level_code ?: '—') ?> · <?= Html::encode($profile->statusLabel()) ?>
            </div>
            <p class="text-body-secondary small mb-0">
                ตารางนี้ใช้แทนไฟล์กรอกมือได้ — ส่งออกเป็น Excel แล้วแนบส่ง สสจ. ได้ทันที
                คอลัมน์ประเภทการจ้างแตกยอดจากทะเบียนบุคลากรโดยตรง ไม่ได้พิมพ์ซ้ำ
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <?= Html::beginForm(['report'], 'get', ['class' => 'd-flex align-items-end gap-2']) ?>
                <div>
                    <label class="form-label small mb-1">ปีงบประมาณ</label>
                    <select name="thai_year" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($years as $value => $label): ?>
                            <option value="<?= (int) $value ?>" <?= $year === (int) $value ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?= Html::endForm() ?>
            <?= Html::a('<i class="bi bi-file-earmark-excel me-1"></i> ส่งออก Excel',
                ['report', 'thai_year' => $year, 'format' => 'xlsx'],
                ['class' => 'btn btn-sm btn-success', 'data-pjax' => '0']) ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:4rem">ลำดับ</th>
                    <th style="min-width:22rem">สายงานตามเกณฑ์</th>
                    <th class="text-end" style="width:6rem">กรอบ</th>
                    <th class="text-end" style="width:7rem">มีอยู่จริง</th>
                    <th class="text-end" style="width:6rem">ส่วนขาด</th>
                    <?php foreach ($types as $type): ?>
                        <th class="text-end" style="width:6rem">
                            <?= Html::encode($type['title']) ?>
                            <?php if (!$type['in_frame']): ?>
                                <div class="fw-normal text-body-secondary" style="font-size:.7rem">ไม่นับในกรอบ</div>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                    <th style="width:11rem">ที่มา</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $index => $row): ?>
                    <?php $hasGap = $row['gap'] !== null && $row['gap'] > 0; ?>
                    <tr>
                        <td class="text-body-secondary font-monospace"><?= $row['line']->seq !== null ? (int) $row['line']->seq : $index + 1 ?></td>
                        <td>
                            <?= Html::encode($row['line']->title) ?>
                            <div class="small text-body-secondary"><?= Html::encode($row['line']->categoryLabel()) ?></div>
                        </td>
                        <td class="text-end font-monospace fw-semibold"><?= $number($row['frame']) ?></td>
                        <td class="text-end font-monospace"><?= (int) $row['in_frame'] ?></td>
                        <td class="text-end font-monospace <?= $hasGap ? 'text-danger-emphasis fw-semibold' : 'text-body-secondary' ?>">
                            <?= $number($row['gap']) ?>
                        </td>
                        <?php foreach (array_keys($types) as $typeId): ?>
                            <td class="text-end font-monospace <?= $types[$typeId]['in_frame'] ? '' : 'text-body-secondary' ?>">
                                <?= isset($row['by_type'][$typeId]) ? (int) $row['by_type'][$typeId] : '' ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="small text-body-secondary"><?= Html::encode(Calc::STATUS_LABELS[$row['status']] ?? $row['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-semibold bg-body-tertiary">
                    <td></td>
                    <td>รวม</td>
                    <td class="text-end font-monospace"><?= $number($totals['frame']) ?></td>
                    <td class="text-end font-monospace"><?= (int) $totals['in_frame'] ?></td>
                    <td class="text-end font-monospace"><?= $number($totals['gap']) ?></td>
                    <?php foreach (array_keys($types) as $typeId): ?>
                        <td class="text-end font-monospace"><?= (int) ($totals['by_type'][$typeId] ?? 0) ?></td>
                    <?php endforeach; ?>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<p class="small text-body-secondary mt-3">
    <i class="bi bi-info-circle me-1"></i>
    ยอด "มีอยู่จริง" นับเฉพาะประเภทการจ้างที่เกณฑ์ให้นับรวมในกรอบ
    ส่วนคอลัมน์ที่กำกับว่า "ไม่นับในกรอบ" แสดงไว้ให้เห็นภาพรวม แต่ไม่รวมในยอดดังกล่าว —
    <?= Html::a('ดูกรอบ Outsource', ['outsource', 'thai_year' => $year]) ?>
</p>
