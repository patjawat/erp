<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeePosition[] $positions */
/** @var array $mapped */
/** @var array $headcount */
/** @var array $lineOptions */
/** @var string $filter */
/** @var array $summary */

$this->title = 'จับคู่ตำแหน่งกับเกณฑ์';

$filters = [
    'staffed' => 'มีคนอยู่จริง',
    'unmapped' => 'ยังไม่จับคู่',
    'mapped' => 'จับคู่แล้ว',
    'all' => 'ทั้งหมด',
];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/settings/views/_workforce_menu', ['active' => 'workforce-map']) ?>
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
        <p class="text-body-secondary small mb-3">
            เอกสารเกณฑ์เรียกสายงานด้วยชื่อกลาง แต่ทะเบียนตำแหน่งของโรงพยาบาลตั้งชื่อเอง
            ตารางนี้คือสิ่งที่เชื่อมสองฝั่งเข้าด้วยกัน ทำครั้งเดียวแล้วกรอบไหลเข้าทั้งระบบ
        </p>
        <div class="row g-2 mb-3">
            <div class="col-6 col-lg-3">
                <div class="border rounded p-2">
                    <div class="small text-body-secondary">ตำแหน่งที่มีคนอยู่จริง</div>
                    <div class="fs-5 fw-semibold"><?= (int) $summary['staffed'] ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="border rounded p-2">
                    <div class="small text-body-secondary">จับคู่กับเกณฑ์แล้ว</div>
                    <div class="fs-5 fw-semibold text-primary-emphasis"><?= (int) $summary['done'] ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="border rounded p-2">
                    <div class="small text-body-secondary">ยืนยันว่าไม่มีในเกณฑ์</div>
                    <div class="fs-5 fw-semibold text-body-secondary"><?= (int) $summary['no_standard'] ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="border rounded p-2">
                    <div class="small text-body-secondary">ยังค้าง</div>
                    <div class="fs-5 fw-semibold <?= $summary['pending'] > 0 ? 'text-warning-emphasis' : '' ?>"><?= (int) $summary['pending'] ?></div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="btn-group btn-group-sm" role="group" aria-label="ตัวกรองรายการ">
                <?php foreach ($filters as $key => $label): ?>
                    <?= Html::a(
                        Html::encode($label),
                        ['map', 'filter' => $key],
                        ['class' => 'btn ' . ($filter === $key ? 'btn-primary' : 'btn-outline-secondary')]
                    ) ?>
                <?php endforeach; ?>
            </div>
            <?= Html::beginForm(['auto-match'], 'post') ?>
                <?= Html::submitButton('<i class="bi bi-magic me-1"></i> จับคู่อัตโนมัติตามชื่อ', ['class' => 'btn btn-sm btn-outline-primary']) ?>
            <?= Html::endForm() ?>
        </div>
        <div class="form-text mt-2">
            จับคู่อัตโนมัติทำเฉพาะชื่อที่ตรงกันเป๊ะ ไม่เดาจากชื่อคล้าย เพราะจับคู่ผิดจะทำให้กรอบผิดทั้งสายงาน
        </div>
    </div>
</div>

<?= Html::beginForm(['save-map'], 'post', ['id' => 'map-form']) ?>
<?= Html::hiddenInput('filter', $filter) ?>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>ตำแหน่งในทะเบียนของโรงพยาบาล</th>
                    <th style="width:7rem" class="text-end">มีคนอยู่</th>
                    <th style="width:40%">สายงานตามเกณฑ์</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($positions === []): ?>
                    <tr><td colspan="3" class="text-center text-body-secondary py-4">ไม่มีรายการในตัวกรองนี้</td></tr>
                <?php endif; ?>
                <?php foreach ($positions as $position): ?>
                    <?php
                    $id = (int) $position->id;
                    $staff = (int) ($headcount[$id] ?? 0);
                    $isMapped = array_key_exists($id, $mapped);
                    $current = $isMapped ? ($mapped[$id] === null ? 'none' : (string) $mapped[$id]) : '';
                    ?>
                    <tr>
                        <td>
                            <?= Html::encode($position->title) ?>
                            <?php if (!$isMapped && $staff > 0): ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis ms-1">ยังไม่จับคู่</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end font-monospace <?= $staff > 0 ? '' : 'text-body-secondary' ?>">
                            <?= $staff > 0 ? $staff : '—' ?>
                        </td>
                        <td>
                            <select name="line[<?= $id ?>]" class="form-select form-select-sm" aria-label="สายงานของ <?= Html::encode($position->title) ?>">
                                <option value="">— ยังไม่ตัดสิน —</option>
                                <option value="none" <?= $current === 'none' ? 'selected' : '' ?>>ไม่มีในเกณฑ์</option>
                                <?php foreach ($lineOptions as $group => $options): ?>
                                    <optgroup label="<?= Html::encode($group) ?>">
                                        <?php foreach ($options as $lineId => $label): ?>
                                            <option value="<?= (int) $lineId ?>" <?= $current === (string) $lineId ? 'selected' : '' ?>>
                                                <?= Html::encode($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึกการจับคู่', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('กลับไปทะเบียนเกณฑ์', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>
<?= Html::endForm() ?>
