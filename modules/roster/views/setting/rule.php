<?php

use app\modules\roster\models\ShiftType;
use app\modules\roster\models\UnitRule;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $units */
/** @var int $unitId */
/** @var array<string, UnitRule[]> $grouped */
/** @var ShiftType[] $types */
/** @var bool $hasAny */

$this->title = 'กฎการจัดเวร';
$this->params['breadcrumbs'][] = ['label' => 'ตารางเวร', 'url' => ['/roster/period/index']];
$this->params['breadcrumbs'][] = $this->title;

$typeMap = [];
foreach ($types as $type) {
    $typeMap[(int) $type->id] = $type;
}
$typeOptions = [];
foreach ($types as $type) {
    $typeOptions[(int) $type->id] = $type->title;
}

$pairLabel = function (UnitRule $rule) use ($typeMap): string {
    $json = is_array($rule->data_json) ? $rule->data_json : json_decode((string) $rule->data_json, true);
    if (!is_array($json) || !isset($json['a'], $json['b'])) {
        return '-';
    }
    $a = $typeMap[(int) $json['a']] ?? null;
    $b = $typeMap[(int) $json['b']] ?? null;
    return ($a ? $a->title : '?') . ' + ' . ($b ? $b->title : '?');
};
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-shield-check"></i> <?= Html::encode($this->title) ?>
    </h4>
    <div class="text-body-secondary small">กฎเหล่านี้ใช้ <strong>เตือน</strong> ตอนจัดเวร ไม่บล็อกการบันทึก</div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/roster/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php if (empty($units)): ?>
    <div class="alert alert-warning border-0">
        <i class="bi bi-exclamation-triangle"></i> คุณยังไม่ได้เป็นหัวหน้าหน่วยงานใด จึงยังตั้งกฎไม่ได้
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

<?php if (!$hasAny): ?>
    <div class="card border shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-shield-plus fs-1 text-body-secondary"></i>
            <h6 class="mt-3 mb-1">หน่วยงานนี้ยังไม่ได้ตั้งกฎการจัดเวร</h6>
            <p class="text-body-secondary small mb-3">
                ระหว่างนี้ระบบใช้ <strong>กฎชุดแนะนำ</strong> เตือนให้อยู่แล้ว
                (พักระหว่างเวร 8 ชม. · ทำงานติดกันไม่เกิน 6 วัน · ไม่เกิน 48 ชม./สัปดาห์
                · ห้ามดึกติดเช้า)<br>
                กดปุ่มด้านล่างเพื่อบันทึกเป็นกฎของหน่วยงาน แล้วปรับตัวเลขเองได้
            </p>
            <button type="button" class="btn btn-primary" id="seed-rules">
                <i class="bi bi-magic"></i> ใช้กฎชุดแนะนำ
            </button>
        </div>
    </div>
<?php else: ?>
    <form id="rule-form">
        <?= Html::hiddenInput('unit_id', $unitId) ?>

        <div class="card border shadow-sm mb-3">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0"><i class="bi bi-hourglass-split"></i> เวลาพักและวันทำงานต่อเนื่อง</h6>
            </div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <tbody class="table-group-divider">
                        <?php foreach ([
                            UnitRule::KEY_MIN_REST_HOURS,
                            UnitRule::KEY_MAX_REST_VIOLATIONS,
                            UnitRule::KEY_MAX_CONSECUTIVE_WORKDAYS,
                        ] as $key): ?>
                            <?php foreach ($grouped[$key] ?? [] as $rule): ?>
                                <tr>
                                    <td style="width:70px" class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input type="checkbox" class="form-check-input"
                                                   name="rule[<?= $rule->id ?>][active]" value="1"
                                                   <?= $rule->active ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                    <td><?= Html::encode(UnitRule::keyLabels()[$key] ?? $key) ?></td>
                                    <td style="width:140px">
                                        <input type="number" min="0" max="99" class="form-control text-center"
                                               name="rule[<?= $rule->id ?>][int_value]"
                                               value="<?= $rule->int_value !== null ? (int) $rule->int_value : '' ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border shadow-sm mb-3">
            <div class="card-header bg-body-tertiary d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                <h6 class="mb-0"><i class="bi bi-arrow-left-right"></i> กฎเวรต่อเนื่อง</h6>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <select class="form-select form-select-sm w-auto" id="pair-key">
                        <option value="<?= UnitRule::KEY_FORBID_SAME_DAY ?>">ห้ามวันเดียวกัน</option>
                        <option value="<?= UnitRule::KEY_FORBID_NEXT_DAY ?>">ห้ามต่อวันถัดไป</option>
                    </select>
                    <select class="form-select form-select-sm w-auto" id="pair-a">
                        <?= Html::renderSelectOptions(null, $typeOptions) ?>
                    </select>
                    <span class="text-body-secondary small">→</span>
                    <select class="form-select form-select-sm w-auto" id="pair-b">
                        <?= Html::renderSelectOptions(null, $typeOptions) ?>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-pair">
                        <i class="bi bi-plus-lg"></i> เพิ่ม
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <tbody class="table-group-divider">
                        <?php
                        $pairRules = array_merge(
                            $grouped[UnitRule::KEY_FORBID_SAME_DAY] ?? [],
                            $grouped[UnitRule::KEY_FORBID_NEXT_DAY] ?? []
                        );
                        ?>
                        <?php if (empty($pairRules)): ?>
                            <tr><td class="text-body-secondary text-center py-4">ยังไม่มีกฎเวรต่อเนื่อง</td></tr>
                        <?php endif; ?>
                        <?php foreach ($pairRules as $rule): ?>
                            <tr>
                                <td style="width:70px" class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input type="checkbox" class="form-check-input"
                                               name="rule[<?= $rule->id ?>][active]" value="1"
                                               <?= $rule->active ? 'checked' : '' ?>>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                        <?= $rule->rule_key === UnitRule::KEY_FORBID_SAME_DAY ? 'วันเดียวกัน' : 'วันถัดไป' ?>
                                    </span>
                                    <span class="ms-2"><?= Html::encode($pairLabel($rule)) ?></span>
                                </td>
                                <td class="text-end" style="width:80px">
                                    <?= Html::a('<i class="bi bi-trash"></i>', ['rule-delete', 'id' => $rule->id], [
                                        'class' => 'btn btn-sm btn-outline-danger rule-delete',
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0"><i class="bi bi-repeat"></i> เวรชนิดเดียวติดกันสูงสุด</h6>
            </div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <tbody class="table-group-divider">
                        <?php if (empty($grouped[UnitRule::KEY_MAX_CONSECUTIVE_SHIFT])): ?>
                            <tr><td class="text-body-secondary text-center py-4">ยังไม่มีกฎ</td></tr>
                        <?php endif; ?>
                        <?php foreach ($grouped[UnitRule::KEY_MAX_CONSECUTIVE_SHIFT] ?? [] as $rule): ?>
                            <?php $type = $typeMap[(int) $rule->shift_type_id] ?? null; ?>
                            <tr>
                                <td style="width:70px" class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input type="checkbox" class="form-check-input"
                                               name="rule[<?= $rule->id ?>][active]" value="1"
                                               <?= $rule->active ? 'checked' : '' ?>>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($type): ?>
                                        <span class="badge rounded-pill px-3 <?= $type->cellClass() ?>">
                                            <?= Html::encode($type->short_name) ?>
                                        </span>
                                        <span class="ms-2"><?= Html::encode($type->title) ?> ติดกันไม่เกิน</span>
                                    <?php endif; ?>
                                </td>
                                <td style="width:140px">
                                    <input type="number" min="1" max="31" class="form-control text-center"
                                           name="rule[<?= $rule->id ?>][int_value]"
                                           value="<?= $rule->int_value !== null ? (int) $rule->int_value : '' ?>">
                                </td>
                                <td style="width:60px" class="text-body-secondary">วัน</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-body-tertiary d-flex justify-content-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> บันทึกกฎทั้งหมด</button>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php
$baseUrl = Url::to(['rule']);
$saveUrl = Url::to(['rule-save']);
$seedUrl = Url::to(['rule-seed']);
$pairUrl = Url::to(['rule-pair']);
$js = <<<JS
$('#unit-picker').on('change', function () {
    window.location.href = '{$baseUrl}?unit_id=' + $(this).val();
});

$('#seed-rules').on('click', function () {
    \$.post('{$seedUrl}?unit_id=' + $('#unit-picker').val(), function (res) {
        if (res.status === 'success') { window.location.reload(); }
        else if (typeof warning === 'function') { warning(res.message); }
    });
});

$('#rule-form').on('submit', function (e) {
    e.preventDefault();
    \$.post('{$saveUrl}', $(this).serialize(), function (res) {
        if (typeof success === 'function') { success(res.message); } else { alert(res.message); }
    });
});

$('#add-pair').on('click', function () {
    var a = $('#pair-a').val(), b = $('#pair-b').val();
    if (a === b) {
        if (typeof warning === 'function') { warning('เลือกเวร 2 ชนิดที่ต่างกัน'); }
        return;
    }
    \$.post('{$pairUrl}', {
        unit_id: $('#unit-picker').val(),
        rule_key: $('#pair-key').val(),
        a: a, b: b
    }, function (res) {
        if (res.status === 'success') { window.location.reload(); }
        else if (typeof warning === 'function') { warning(res.message); }
    });
});

$('body').on('click', '.rule-delete', function (e) {
    e.preventDefault();
    if (!window.confirm('ลบกฎนี้?')) { return; }
    \$.get($(this).attr('href'), function (res) {
        if (res.status === 'success') { window.location.reload(); }
    });
});
JS;
$this->registerJs($js);
?>
