<?php

use app\components\AppHelper;
use app\modules\am\models\AssetAudit;
use app\modules\am\models\AssetAuditItem;
use app\modules\hr\models\Organization;
use app\widgets\datepicker\DatepickerThai;
use kartik\tree\TreeViewInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var AssetAudit $model */
/** @var AssetAuditItem[] $items */
/** @var array $conditionOptions */
/** @var array $departmentOptions */
/** @var array $statusOptions */

$items = $items ?? [new AssetAuditItem()];
$conditionOptions = $conditionOptions ?? [];
$departmentOptions = $departmentOptions ?? [];
$statusOptions = $statusOptions ?? AssetAudit::statusList();
$yearOptions = [];
$currentYear = (int) AppHelper::YearBudget();
for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
    $yearOptions[$y] = $y;
}
$lookupUrl = Url::to(['/am/audit/lookup-asset']);
$loadAssetsUrl = Url::to(['/am/audit/assets-by-department']);
$initialIndex = count($items);
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $form = ActiveForm::begin(['id' => 'asset-audit-form']); ?>

        <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <?= $form->field($model, 'audit_no')->textInput([
                    'readonly' => true,
                    'class' => 'form-control fw-semibold',
                    'placeholder' => 'ระบบจะออกเลขให้เอง',
                ])->hint('ตัวอย่าง: ตน.002/2568') ?>
            </div>
            <div class="col-12 col-md-3">
                <?= $form->field($model, 'fiscal_year')->dropDownList($yearOptions, [
                    'class' => 'form-select',
                ]) ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'department')->widget(TreeViewInput::className(), [
                    'query' => Organization::find()->addOrderBy('root, lft'),
                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                    'fontAwesome' => true,
                    'asDropdown' => true,
                    'multiple' => false,
                    'options' => ['class' => 'form-select', 'id' => 'audit-department'],
                ])->label('หน่วยงานที่ตรวจนับ') ?>
                <div class="d-flex justify-content-end mt-2">
                    <button type="button" id="load-assets-by-department" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-collection me-1"></i> โหลดทะเบียนทรัพย์สินตามหน่วยงาน
                    </button>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <?= $form->field($model, 'audit_date')->widget(DatepickerThai::class, [
                    'options' => [
                        'class' => 'form-control',
                        'placeholder' => 'วว/ดด/พ.ศ.',
                    ],
                ]) ?>
            </div>
            <div class="col-12 col-md-3">
                <?= $form->field($model, 'status')->dropDownList($statusOptions, [
                    'class' => 'form-select',
                ]) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'auditors')->textarea([
                    'rows' => 2,
                    'class' => 'form-control',
                    'placeholder' => 'ระบุรายชื่อผู้ตรวจนับ เช่น คณะกรรมการตรวจนับ / หัวหน้าฝ่าย / เจ้าหน้าที่พัสดุ',
                ]) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'summary_note')->textarea([
                    'rows' => 3,
                    'class' => 'form-control',
                    'placeholder' => 'หมายเหตุรวมของการตรวจนับ',
                ]) ?>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0">รายการที่ตรวจนับ</h5>
                <div class="text-muted small">หนึ่งใบตรวจนับสามารถมีได้หลายรายการ และโหลดจากหน่วยงานที่เลือกได้</div>
            </div>
            <button type="button" id="add-audit-item" class="btn btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i> เพิ่มรายการ
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="audit-item-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%">รหัส</th>
                        <th style="width: 34%">ชื่อครุภัณฑ์</th>
                        <th style="width: 18%">สภาพ</th>
                        <th style="width: 22%">หมายเหตุ</th>
                        <th style="width: 6%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                        <tr class="audit-item-row">
                            <td>
                                <input type="hidden" name="AssetAuditItem[<?= $i ?>][asset_id]" class="asset-id-input" value="<?= Html::encode($item->asset_id ?? '') ?>">
                                <?= Html::textInput("AssetAuditItem[{$i}][asset_code]", $item->asset_code, [
                                    'class' => 'form-control asset-code-input',
                                    'placeholder' => 'รหัสครุภัณฑ์',
                                ]) ?>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary lookup-asset-btn">
                                        ค้นหา
                                    </button>
                                </div>
                            </td>
                            <td>
                                <?= Html::textInput("AssetAuditItem[{$i}][asset_name]", $item->asset_name, [
                                    'class' => 'form-control asset-name-input',
                                    'placeholder' => 'ชื่อครุภัณฑ์',
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::dropDownList("AssetAuditItem[{$i}][asset_condition]", $item->asset_condition, $conditionOptions, [
                                    'class' => 'form-select asset-condition-input',
                                    'prompt' => '-- เลือกสภาพ --',
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::textarea("AssetAuditItem[{$i}][note]", $item->note, [
                                    'class' => 'form-control asset-note-input',
                                    'rows' => 2,
                                    'placeholder' => 'หมายเหตุ',
                                ]) ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger remove-audit-item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <?= Html::submitButton($model->isNewRecord ? 'บันทึกใบตรวจนับ' : 'บันทึกการแก้ไข', ['class' => 'btn btn-primary px-4']) ?>
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary px-4']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<table class="d-none">
    <tbody id="audit-item-template">
        <tr class="audit-item-row">
            <td>
                <input type="hidden" name="AssetAuditItem[{idx}][asset_id]" class="asset-id-input" value="">
                <input type="text" name="AssetAuditItem[{idx}][asset_code]" class="form-control asset-code-input" placeholder="รหัสครุภัณฑ์">
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary lookup-asset-btn">ค้นหา</button>
                </div>
            </td>
            <td>
                <input type="text" name="AssetAuditItem[{idx}][asset_name]" class="form-control asset-name-input" placeholder="ชื่อครุภัณฑ์">
            </td>
            <td>
                <select name="AssetAuditItem[{idx}][asset_condition]" class="form-select asset-condition-input">
                    <option value="">-- เลือกสภาพ --</option>
                    <?php foreach ($conditionOptions as $condId => $condName): ?>
                        <option value="<?= Html::encode($condId) ?>"><?= Html::encode($condName) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <textarea name="AssetAuditItem[{idx}][note]" class="form-control asset-note-input" rows="2" placeholder="หมายเหตุ"></textarea>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger remove-audit-item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </tbody>
</table>

<?php
$script = <<<'JS'
let auditItemIndex = __INITIAL_INDEX__;
const lookupAssetUrl = __LOOKUP_URL__;
const loadAssetsUrl = __LOAD_URL__;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function fillAuditRow(row, data) {
    row.find('.asset-id-input').val(data.asset_id || '');
    row.find('.asset-code-input').val(data.asset_code || '');
    row.find('.asset-name-input').val(data.asset_name || '');
    if (data.asset_condition && !row.find('.asset-condition-input').val()) {
        row.find('.asset-condition-input').val(data.asset_condition);
    }
}

function lookupAuditAsset(row) {
    const code = (row.find('.asset-code-input').val() || '').trim();
    if (!code) {
        row.find('.asset-id-input').val('');
        row.find('.asset-name-input').val('');
        return;
    }

    $.getJSON(lookupAssetUrl, { code: code })
        .done(function(res) {
            if (res && res.success && res.data) {
                fillAuditRow(row, res.data);
            } else if (res && res.message && typeof Swal !== 'undefined') {
                Swal.fire('ค้นหาไม่พบ', res.message, 'warning');
            }
        })
        .fail(function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire('ผิดพลาด', 'ไม่สามารถค้นหาครุภัณฑ์ได้', 'error');
            }
        });
}

function buildAuditRowHtml(idx, item) {
    let conditionHtml = '<option value="">-- เลือกสภาพ --</option>';
    __CONDITION_OPTIONS__

    return `
        <tr class="audit-item-row">
            <td>
                <input type="hidden" name="AssetAuditItem[${idx}][asset_id]" class="asset-id-input" value="${escapeHtml(item.asset_id || '')}">
                <input type="text" name="AssetAuditItem[${idx}][asset_code]" class="form-control asset-code-input" placeholder="รหัสครุภัณฑ์" value="${escapeHtml(item.asset_code || '')}">
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary lookup-asset-btn">ค้นหา</button>
                </div>
            </td>
            <td>
                <input type="text" name="AssetAuditItem[${idx}][asset_name]" class="form-control asset-name-input" placeholder="ชื่อครุภัณฑ์" value="${escapeHtml(item.asset_name || '')}">
            </td>
            <td>
                <select name="AssetAuditItem[${idx}][asset_condition]" class="form-select asset-condition-input">
                    ${conditionHtml}
                </select>
            </td>
            <td>
                <textarea name="AssetAuditItem[${idx}][note]" class="form-control asset-note-input" rows="2" placeholder="หมายเหตุ">${escapeHtml(item.note || '')}</textarea>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger remove-audit-item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
}

function appendAuditRow() {
    const html = $('#audit-item-template').html().replace(/\{idx\}/g, auditItemIndex);
    const row = $(html);
    $('#audit-item-table tbody').append(row);
    auditItemIndex++;
    return row;
}

function appendLoadedAuditRows(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return;
    }
    items.forEach(function(item) {
        const html = buildAuditRowHtml(auditItemIndex, item);
        const row = $(html);
        $('#audit-item-table tbody').append(row);
        if (item.asset_condition) {
            row.find('.asset-condition-input').val(item.asset_condition);
        }
        auditItemIndex++;
    });
}

function loadAssetsByDepartment() {
    const departmentId = $('#audit-department').val();
    if (!departmentId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('คำเตือน', 'กรุณาเลือกหน่วยงานก่อน', 'warning');
        }
        return;
    }

    const doLoad = function() {
        $.getJSON(loadAssetsUrl, { department_id: departmentId })
            .done(function(res) {
                if (res && res.success) {
                    $('#audit-item-table tbody').empty();
                    auditItemIndex = 0;
                    appendLoadedAuditRows(res.items || []);
                    if ((res.items || []).length === 0) {
                        appendAuditRow();
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('โหลดแล้ว', 'โหลดครุภัณฑ์ตามหน่วยงาน ' + (res.department ? res.department.name : '') + ' จำนวน ' + (res.count || 0) + ' รายการ', 'success');
                    }
                } else if (res && res.message && typeof Swal !== 'undefined') {
                    Swal.fire('ผิดพลาด', res.message, 'error');
                }
            })
            .fail(function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดทะเบียนทรัพย์สินได้', 'error');
                }
            });
    };

    if ($('#audit-item-table tbody tr').length > 0) {
        Swal.fire({
            title: 'โหลดทะเบียนทรัพย์สินตามหน่วยงาน?',
            text: 'รายการเดิมจะถูกล้างออกทั้งหมด',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'โหลด',
            cancelButtonText: 'ยกเลิก',
        }).then(function(result) {
            if (result.isConfirmed) {
                doLoad();
            }
        });
    } else {
        doLoad();
    }
}

$(document).on('click', '#add-audit-item', function() {
    const row = appendAuditRow();
    row.find('.asset-code-input').focus();
});

$(document).on('click', '#load-assets-by-department', function() {
    loadAssetsByDepartment();
});

$(document).on('click', '.lookup-asset-btn', function() {
    lookupAuditAsset($(this).closest('tr'));
});

$(document).on('blur change', '.asset-code-input', function() {
    lookupAuditAsset($(this).closest('tr'));
});

$(document).on('click', '.remove-audit-item', function() {
    const rows = $('#audit-item-table tbody tr');
    if (rows.length <= 1) {
        const row = rows.first();
        row.find('input, textarea').val('');
        row.find('select').val('');
        return;
    }
    $(this).closest('tr').remove();
});
JS;
$conditionJs = '';
foreach ($conditionOptions as $condId => $condName) {
    $conditionJs .= 'conditionHtml += \'<option value="' . addslashes(Html::encode((string) $condId)) . '">' . '\'
        + escapeHtml(' . json_encode(Html::encode((string) $condName)) . ') + \'</option>\';' . "\n    ";
}

$script = str_replace(
    ['__INITIAL_INDEX__', '__LOOKUP_URL__', '__LOAD_URL__', '__CONDITION_OPTIONS__'],
    [
        (string) $initialIndex,
        json_encode($lookupUrl),
        json_encode($loadAssetsUrl),
        $conditionJs,
    ],
    $script
);
$this->registerJs($script);
?>
