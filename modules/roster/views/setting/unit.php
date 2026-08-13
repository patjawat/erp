<?php

use app\modules\roster\models\UnitShift;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var array $units */
/** @var int $unitId */
/** @var app\modules\roster\models\ShiftType[] $types */
/** @var UnitShift[] $shifts */

$this->title = 'เวรของหน่วยงาน';
$this->params['breadcrumbs'][] = ['label' => 'ตารางเวร', 'url' => ['/roster/period/index']];
$this->params['breadcrumbs'][] = $this->title;

$totalNeeded = 0;
foreach ($shifts as $shift) {
    if ($shift->active) {
        $totalNeeded += (int) $shift->required_staff;
    }
}
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-clock"></i> <?= Html::encode($this->title) ?>
    </h4>
    <div class="text-body-secondary small">
        แต่ละหน่วยตั้งชื่อเวรและอัตราค่าตอบแทนเองได้ — ตั้งครั้งเดียวใช้ได้ทุกเดือน
    </div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/roster/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php if (empty($units)): ?>
    <div class="alert alert-warning border-0">
        <i class="bi bi-exclamation-triangle"></i>
        คุณยังไม่ได้เป็นหัวหน้าหน่วยงานใด จึงยังตั้งค่าเวรไม่ได้
    </div>
    <?php return; ?>
<?php endif; ?>

<div class="card border shadow-sm mb-3">
    <div class="card-body">
        <label class="form-label fw-semibold">หน่วยงาน</label>
        <?php if (count($units) === 1): ?>
            <div class="form-control-plaintext border rounded px-3 py-2 bg-body-tertiary d-flex align-items-center gap-2">
                <i class="bi bi-building text-body-secondary"></i>
                <span class="fw-semibold"><?= Html::encode(reset($units)) ?></span>
            </div>
            <input type="hidden" id="unit-picker" value="<?= $unitId ?>">
        <?php else: ?>
            <select class="form-select" id="unit-picker">
                <?php foreach ($units as $id => $name): ?>
                    <option value="<?= (int) $id ?>" <?= $unitId === (int) $id ? 'selected' : '' ?>>
                        <?= Html::encode($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>
</div>

<?php Pjax::begin(['id' => 'roster-setting']); ?>
<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
        <h6 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-clock-history"></i> เวรที่หน่วยงานนี้ใช้
            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">
                ต้องการรวม <?= (int) $totalNeeded ?> คน/วัน
            </span>
        </h6>
        <?= Html::a('<i class="bi bi-plus-lg"></i> เพิ่มเวร', ['shift-form', 'unit_id' => $unitId], [
            'class' => 'btn btn-sm btn-primary open-modal',
            'data' => ['size' => 'modal-lg'],
        ]) ?>
    </div>

    <div class="card-body p-0">
        <?php if (empty($shifts)): ?>
            <div class="text-center py-5">
                <i class="bi bi-clock fs-1 text-body-secondary"></i>
                <h6 class="mt-3 mb-1">หน่วยงานนี้ยังไม่ได้ตั้งเวร</h6>
                <p class="text-body-secondary small mb-3">
                    เพิ่มเวรที่ใช้จริง เช่น เช้า · บ่าย · ดึก · บ่ายดึก · Refer · On call
                </p>
                <?= Html::a('<i class="bi bi-plus-lg"></i> เพิ่มเวรแรก', ['shift-form', 'unit_id' => $unitId], [
                    'class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg'],
                ]) ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th style="width:80px">ย่อ</th>
                            <th>ชื่อเวร</th>
                            <th style="width:150px">ตำแหน่ง</th>
                            <th style="width:110px">หมวด</th>
                            <th style="width:150px">เวลา</th>
                            <th class="text-center" style="width:90px">ชั่วโมง</th>
                            <th class="text-center" style="width:110px">ต้องการ</th>
                            <th class="text-end" style="width:150px">ค่าตอบแทน</th>
                            <th class="text-end" style="width:110px"></th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($shifts as $shift): ?>
                            <tr class="<?= $shift->active ? '' : 'opacity-50' ?>">
                                <td>
                                    <span class="badge rounded-pill fs-6 px-3 <?= $shift->cellClass() ?>">
                                        <?= Html::encode($shift->displayShort()) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= Html::encode($shift->displayName()) ?></div>
                                    <?php if ($shift->is_standby): ?>
                                        <span class="badge bg-info-subtle text-info-emphasis">
                                            <i class="bi bi-telephone"></i> รอเรียก/นอกหน่วย
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!$shift->active): ?>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">ปิดใช้</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <?php if ($shift->position_id): ?>
                                        <?= Html::encode($shift->positionName()) ?>
                                    <?php else: ?>
                                        <span class="text-body-secondary">ไม่จำกัด</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-body-secondary">
                                    <?= Html::encode($shift->shiftType ? $shift->shiftType->title : '-') ?>
                                </td>
                                <td class="small">
                                    <?= Html::encode($shift->timeRangeLabel()) ?>
                                    <?php if ($shift->cross_midnight): ?>
                                        <span class="badge bg-info-subtle text-info-emphasis">ข้ามวัน</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= $shift->hours !== null ? rtrim(rtrim((string) $shift->hours, '0'), '.') : '–' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-body-tertiary text-body border">
                                        <?= $shift->hasRequirement() ? Html::encode($shift->requiredLabel()) : '–' ?>
                                    </span>
                                </td>
                                <td class="text-end small">
                                    <?= Html::encode($shift->payLabel()) ?>
                                    <?php if ($shift->pay_rate && $shift->pay_unit === UnitShift::PAY_PER_HOUR && $shift->hours): ?>
                                        <div class="text-body-secondary">= <?= number_format($shift->payAmount(), 2) ?> บ./เวร</div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?= Html::a('<i class="bi bi-pencil"></i>', ['shift-form', 'id' => $shift->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary open-modal',
                                        'data' => ['size' => 'modal-lg'], 'title' => 'แก้ไข',
                                    ]) ?>
                                    <?= Html::a($shift->active ? '<i class="bi bi-slash-circle"></i>' : '<i class="bi bi-arrow-counterclockwise"></i>',
                                        ['shift-disable', 'id' => $shift->id], [
                                            'class' => 'btn btn-sm ' . ($shift->active ? 'btn-outline-danger' : 'btn-outline-success') . ' shift-toggle',
                                            'title' => $shift->active ? 'ปิดใช้งาน' : 'เปิดใช้งาน',
                                            'data' => ['confirm-text' => $shift->active
                                                ? 'ปิดใช้งานเวรนี้? ตารางเวรเดิมที่ใช้เวรนี้จะยังอยู่ครบ'
                                                : 'เปิดใช้งานเวรนี้อีกครั้ง?'],
                                        ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-footer bg-body-tertiary text-body-secondary small">
        <i class="bi bi-info-circle"></i>
        <strong>จำนวนที่ต้องการ</strong> ใช้เตือนในกริดว่าจัดครบหรือยัง (เช่น 1/2 = ขาด 1 คน) ทุกชนิดเวรรวมถึง On call ·
        <strong>รอเรียก/นอกหน่วย</strong> จะถูกยกเว้นจากกฎเวลาพักและวันทำงานติดกัน แต่ยังนับจำนวนคนตามปกติ
    </div>
</div>
<?php Pjax::end(); ?>

<?php
$baseUrl = Url::to(['unit']);
$js = <<<JS
$('#unit-picker').on('change', function () {
    window.location.href = '{$baseUrl}?unit_id=' + $(this).val();
});

$('body').off('click.shiftToggle').on('click.shiftToggle', '.shift-toggle', function (e) {
    e.preventDefault();
    if (!window.confirm($(this).data('confirm-text'))) { return; }
    $.get($(this).attr('href'), function (res) {
        if (res.status === 'success') {
            if (typeof success === 'function') { success('บันทึกแล้ว'); }
            if (typeof erpReloadPjax === 'function') { erpReloadPjax(res.container); } else { location.reload(); }
        } else if (typeof warning === 'function') {
            warning(res.message);
        }
    });
});
JS;
$this->registerJs($js);
?>
