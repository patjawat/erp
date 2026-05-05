<?php

use app\components\AppHelper;
use app\modules\am\models\AssetAudit;
use app\modules\am\models\AssetAuditItem;
use app\modules\hr\models\Organization;
use app\widgets\datepicker\DatepickerThai;
use kartik\tree\TreeViewInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var AssetAudit $model */
/** @var AssetAuditItem[] $items */
/** @var array $conditionOptions */
/** @var array $departmentOptions */
/** @var array $statusOptions */

$items            = $items ?? [new AssetAuditItem()];
$conditionOptions = $conditionOptions ?? [];
$statusOptions    = $statusOptions ?? AssetAudit::statusList();
$yearOptions      = [];
$currentYear      = (int) AppHelper::YearBudget();
for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
    $yearOptions[$y] = $y;
}
$lookupUrl    = Url::to(['/am/audit/lookup-asset']);
$loadAssetsUrl = Url::to(['/am/audit/assets-by-department']);
$initialIndex = count($items);
$isNew        = $model->isNewRecord;
?>

<!-- Mini Stepper Hint -->
<div class="d-flex align-items-center gap-2 mb-4 ps-1 flex-wrap">
    <?php $hints = ['กรอกข้อมูล', 'เพิ่มรายการพัสดุ', 'บันทึก']; ?>
    <?php foreach ($hints as $hi => $hlabel): ?>
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold small <?= $hi === 0 ? 'bg-primary text-white' : 'bg-light text-muted border' ?>"
                 style="width:28px;height:28px;font-size:.75rem"><?= $hi + 1 ?></div>
            <span class="small <?= $hi === 0 ? 'fw-semibold text-primary' : 'text-muted' ?>"><?= $hlabel ?></span>
        </div>
        <?php if ($hi < count($hints) - 1): ?>
            <div class="bg-light" style="height:2px;width:24px;"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<?php $form = ActiveForm::begin(['id' => 'asset-audit-form']); ?>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger rounded-3 mb-4']) ?>

<!-- Section 1: ข้อมูลการตรวจนับ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                 style="width:32px;height:32px">
                <i class="bi bi-info-circle text-primary small"></i>
            </div>
            <span class="fw-semibold">ข้อมูลการตรวจนับ</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <?= $form->field($model, 'audit_no')->textInput([
                    'readonly'    => true,
                    'class'       => 'form-control bg-light fw-semibold font-monospace',
                    'placeholder' => 'ระบบจะออกเลขให้อัตโนมัติ',
                ])->hint('<i class="bi bi-info-circle me-1 text-muted"></i><small>ตัวอย่าง: ตน.002/2568</small>') ?>
            </div>
            <div class="col-6 col-md-4">
                <?= $form->field($model, 'fiscal_year')->dropDownList($yearOptions, ['class' => 'form-select']) ?>
            </div>
            <div class="col-6 col-md-4">
                <?= $form->field($model, 'audit_date')->widget(DatepickerThai::class, [
                    'options' => [
                        'class'       => 'form-control',
                        'placeholder' => 'วว/ดด/พ.ศ.',
                    ],
                ]) ?>
            </div>
            <div class="col-12 col-md-8">
                <?= $form->field($model, 'department')->widget(TreeViewInput::className(), [
                    'query'          => Organization::find()->addOrderBy('root, lft'),
                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                    'rootOptions'    => ['label' => '<i class="fa fa-building"></i>'],
                    'fontAwesome'    => true,
                    'asDropdown'     => true,
                    'multiple'       => false,
                    'options'        => ['class' => 'form-select', 'id' => 'audit-department'],
                    'pluginOptions'  => ['closeOnSelect' => true],
                    'pluginEvents'   => [
                        'select2:select' => new JsExpression('function() {
                            var $el = $(this);
                            setTimeout(function() { try { $el.select2("close"); } catch (e) {} }, 50);
                        }'),
                    ],
                ])->label('หน่วยงานที่ตรวจนับ') ?>
            </div>
            <div class="col-12 col-md-4">
                <?= $form->field($model, 'status')->dropDownList($statusOptions, ['class' => 'form-select']) ?>
            </div>
            <div class="col-12">
                <?= $this->render('@app/components/ui/input_emp', [
                    'form'        => $form,
                    'model'       => $model,
                    'fieldName'   => 'emp_id',
                    'label'       => 'ผู้ตรวจนับ',
                    'placeholder' => 'เลือกผู้ตรวจนับ',
                ]) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'summary_note')->textarea([
                    'rows'        => 2,
                    'class'       => 'form-control',
                    'placeholder' => 'หมายเหตุรวมของการตรวจนับ (ถ้ามี)',
                ])->label('หมายเหตุรวม') ?>
            </div>
        </div>
    </div>
</div>

<!-- Section 2: รายการพัสดุ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:32px;height:32px">
                    <i class="bi bi-list-check text-primary small"></i>
                </div>
                <span class="fw-semibold">รายการพัสดุที่ตรวจนับ</span>
                <span class="badge bg-primary bg-opacity-10 text-primary" id="item-count-badge"><?= count($items) ?> รายการ</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="load-assets-by-department" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-cloud-download me-1"></i> โหลดจากหน่วยงาน
                </button>
                <button type="button" id="add-audit-item" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มรายการ
                </button>
            </div>
        </div>
        <div class="text-muted small mt-2">
            <i class="bi bi-lightbulb me-1 text-warning"></i>
            กรอกรหัสแล้วกด <kbd>Tab</kbd> หรือคลิก <i class="bi bi-search"></i> เพื่อดึงชื่อครุภัณฑ์อัตโนมัติ
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="audit-item-table">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width:42px">#</th>
                    <th style="width:22%">รหัสครุภัณฑ์</th>
                    <th>ชื่อครุภัณฑ์</th>
                    <th style="width:16%">สภาพ</th>
                    <th style="width:22%">หมายเหตุ</th>
                    <th class="text-center" style="width:50px"></th>
                </tr>
            </thead>
            <tbody id="audit-item-tbody">
                <?php foreach ($items as $i => $item): ?>
                <tr class="audit-item-row">
                    <td class="text-center text-muted small row-num"><?= $i + 1 ?></td>
                    <td>
                        <input type="hidden" name="AssetAuditItem[<?= $i ?>][asset_id]"
                               class="asset-id-input" value="<?= Html::encode($item->asset_id ?? '') ?>">
                        <div class="input-group input-group-sm">
                            <?= Html::textInput("AssetAuditItem[{$i}][asset_code]", $item->asset_code, [
                                'class'       => 'form-control asset-code-input font-monospace',
                                'placeholder' => 'รหัส',
                            ]) ?>
                            <button type="button" class="btn btn-outline-secondary lookup-asset-btn" title="ค้นหาครุภัณฑ์">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </td>
                    <td>
                        <?= Html::textInput("AssetAuditItem[{$i}][asset_name]", $item->asset_name, [
                            'class'       => 'form-control form-control-sm asset-name-input',
                            'placeholder' => 'ชื่อครุภัณฑ์',
                        ]) ?>
                    </td>
                    <td>
                        <?= Html::dropDownList("AssetAuditItem[{$i}][asset_condition]", $item->asset_condition, $conditionOptions, [
                            'class'  => 'form-select form-select-sm asset-condition-input',
                            'prompt' => '-- สภาพ --',
                        ]) ?>
                    </td>
                    <td>
                        <?= Html::textInput("AssetAuditItem[{$i}][note]", $item->note, [
                            'class'       => 'form-control form-control-sm asset-note-input',
                            'placeholder' => 'หมายเหตุ',
                        ]) ?>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-audit-item" title="ลบรายการ">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    <div id="empty-state" class="text-center py-5 <?= count($items) > 0 ? 'd-none' : '' ?>">
        <i class="bi bi-inbox fs-1 text-muted d-block mb-2" style="opacity:.2"></i>
        <div class="text-muted small">ยังไม่มีรายการ — กด <strong>เพิ่มรายการ</strong> หรือ <strong>โหลดจากหน่วยงาน</strong></div>
    </div>
</div>

<!-- Action Footer -->
<div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-2">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary px-4']) ?>
    <?= Html::submitButton(
        '<i class="bi bi-check-lg me-1"></i> ' . ($isNew ? 'บันทึกใบตรวจนับ' : 'บันทึกการแก้ไข'),
        ['class' => 'btn btn-primary px-4']
    ) ?>
</div>

<?php ActiveForm::end(); ?>

<!-- Hidden Template Row -->
<table class="d-none" aria-hidden="true">
    <tbody id="audit-item-template">
        <tr class="audit-item-row">
            <td class="text-center text-muted small row-num">?</td>
            <td>
                <input type="hidden" name="AssetAuditItem[{idx}][asset_id]" class="asset-id-input" value="">
                <div class="input-group input-group-sm">
                    <input type="text" name="AssetAuditItem[{idx}][asset_code]"
                           class="form-control asset-code-input font-monospace" placeholder="รหัส">
                    <button type="button" class="btn btn-outline-secondary lookup-asset-btn" title="ค้นหาครุภัณฑ์">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </td>
            <td>
                <input type="text" name="AssetAuditItem[{idx}][asset_name]"
                       class="form-control form-control-sm asset-name-input" placeholder="ชื่อครุภัณฑ์">
            </td>
            <td>
                <select name="AssetAuditItem[{idx}][asset_condition]" class="form-select form-select-sm asset-condition-input">
                    <option value="">-- สภาพ --</option>
                    <?php foreach ($conditionOptions as $condId => $condName): ?>
                        <option value="<?= Html::encode($condId) ?>"><?= Html::encode($condName) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input type="text" name="AssetAuditItem[{idx}][note]"
                       class="form-control form-control-sm asset-note-input" placeholder="หมายเหตุ">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-audit-item" title="ลบรายการ">
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
let auditSubmitConfirmed = false;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function updateRowNumbers() {
    $('#audit-item-tbody tr.audit-item-row').each(function(i) {
        $(this).find('.row-num').text(i + 1);
    });
}

function updateItemCount() {
    const count = $('#audit-item-tbody tr.audit-item-row').length;
    $('#item-count-badge').text(count + ' รายการ');
    if (count === 0) {
        $('#empty-state').removeClass('d-none');
    } else {
        $('#empty-state').addClass('d-none');
    }
}

function setRowLoading(row, loading) {
    const btn = row.find('.lookup-asset-btn');
    if (loading) {
        btn.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
    } else {
        btn.html('<i class="bi bi-search"></i>').prop('disabled', false);
    }
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
    setRowLoading(row, true);
    $.getJSON(lookupAssetUrl, { code: code })
        .done(function(res) {
            if (res && res.success && res.data) {
                fillAuditRow(row, res.data);
            } else if (res && res.message && typeof Swal !== 'undefined') {
                Swal.fire({ title: 'ไม่พบครุภัณฑ์', text: res.message, icon: 'warning', timer: 2000, showConfirmButton: false });
            }
        })
        .fail(function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire('ผิดพลาด', 'ไม่สามารถค้นหาครุภัณฑ์ได้', 'error');
            }
        })
        .always(function() {
            setRowLoading(row, false);
        });
}

function buildAuditRowHtml(idx, item) {
    let conditionHtml = '<option value="">-- สภาพ --</option>';
    __CONDITION_OPTIONS__

    return `
        <tr class="audit-item-row">
            <td class="text-center text-muted small row-num">?</td>
            <td>
                <input type="hidden" name="AssetAuditItem[${idx}][asset_id]" class="asset-id-input" value="${escapeHtml(item.asset_id || '')}">
                <div class="input-group input-group-sm">
                    <input type="text" name="AssetAuditItem[${idx}][asset_code]" class="form-control asset-code-input font-monospace" placeholder="รหัส" value="${escapeHtml(item.asset_code || '')}">
                    <button type="button" class="btn btn-outline-secondary lookup-asset-btn" title="ค้นหาครุภัณฑ์"><i class="bi bi-search"></i></button>
                </div>
            </td>
            <td>
                <input type="text" name="AssetAuditItem[${idx}][asset_name]" class="form-control form-control-sm asset-name-input" placeholder="ชื่อครุภัณฑ์" value="${escapeHtml(item.asset_name || '')}">
            </td>
            <td>
                <select name="AssetAuditItem[${idx}][asset_condition]" class="form-select form-select-sm asset-condition-input">
                    ${conditionHtml}
                </select>
            </td>
            <td>
                <input type="text" name="AssetAuditItem[${idx}][note]" class="form-control form-control-sm asset-note-input" placeholder="หมายเหตุ" value="${escapeHtml(item.note || '')}">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-audit-item" title="ลบรายการ"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
}

function appendAuditRow() {
    const html = $('#audit-item-template').html().replace(/\{idx\}/g, auditItemIndex);
    const row = $(html);
    $('#audit-item-tbody').append(row);
    auditItemIndex++;
    updateRowNumbers();
    updateItemCount();
    return row;
}

function appendLoadedAuditRows(items) {
    if (!Array.isArray(items) || items.length === 0) return;
    items.forEach(function(item) {
        const html = buildAuditRowHtml(auditItemIndex, item);
        const row = $(html);
        $('#audit-item-tbody').append(row);
        if (item.asset_condition) {
            row.find('.asset-condition-input').val(item.asset_condition);
        }
        auditItemIndex++;
    });
    updateRowNumbers();
    updateItemCount();
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
        const btn = $('#load-assets-by-department');
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span> กำลังโหลด...').prop('disabled', true);

        $.getJSON(loadAssetsUrl, { department_id: departmentId })
            .done(function(res) {
                if (res && res.success) {
                    $('#audit-item-tbody').empty();
                    auditItemIndex = 0;
                    appendLoadedAuditRows(res.items || []);
                    if ((res.items || []).length === 0) {
                        appendAuditRow();
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'โหลดสำเร็จ',
                            text: 'โหลดครุภัณฑ์หน่วยงาน "' + (res.department ? res.department.name : '') + '" จำนวน ' + (res.count || 0) + ' รายการ',
                            icon: 'success',
                            timer: 2500,
                            showConfirmButton: false,
                        });
                    }
                } else if (res && res.message && typeof Swal !== 'undefined') {
                    Swal.fire('ผิดพลาด', res.message, 'error');
                }
            })
            .fail(function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดทะเบียนทรัพย์สินได้', 'error');
                }
            })
            .always(function() {
                btn.html('<i class="bi bi-cloud-download me-1"></i> โหลดจากหน่วยงาน').prop('disabled', false);
            });
    };

    if ($('#audit-item-tbody tr.audit-item-row').length > 0) {
        Swal.fire({
            title: 'โหลดทะเบียนทรัพย์สิน?',
            text: 'รายการเดิมจะถูกล้างออกทั้งหมด',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'โหลด',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#dc3545',
        }).then(function(result) {
            if (result.isConfirmed) doLoad();
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

$(document).on('blur', '.asset-code-input', function() {
    lookupAuditAsset($(this).closest('tr'));
});

$(document).on('click', '.remove-audit-item', function() {
    const rows = $('#audit-item-tbody tr.audit-item-row');
    if (rows.length <= 1) {
        rows.first().find('input[type="text"], input[type="hidden"]').val('');
        rows.first().find('select').val('');
        return;
    }
    $(this).closest('tr').remove();
    updateRowNumbers();
    updateItemCount();
});

$('#asset-audit-form').on('beforeSubmit', function(e) {
    if (auditSubmitConfirmed) {
        auditSubmitConfirmed = false;
        return true;
    }
    e.preventDefault();
    const form = $(this);
    const itemCount = $('#audit-item-tbody tr.audit-item-row').length;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'ยืนยันบันทึกใบตรวจนับ?',
            html: 'จำนวน <strong>' + itemCount + '</strong> รายการ<br><small class="text-muted">กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึก</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-lg me-1"></i> บันทึก',
            cancelButtonText: 'กลับไปตรวจสอบ',
            confirmButtonColor: '#0d6efd',
        }).then(function(result) {
            if (result.isConfirmed) {
                auditSubmitConfirmed = true;
                form.trigger('submit');
            }
        });
    } else if (window.confirm('ยืนยันบันทึกใบตรวจนับ?')) {
        auditSubmitConfirmed = true;
        form.trigger('submit');
    }
    return false;
});

$(document).on('select2:select', '#audit-department', function() {
    try { $(this).select2('close'); } catch (e) {}
});
JS;

$conditionJs = '';
foreach ($conditionOptions as $condId => $condName) {
    $conditionJs .= 'conditionHtml += \'<option value="' . addslashes(Html::encode((string) $condId)) . '">\''
        . ' + escapeHtml(' . json_encode(Html::encode((string) $condName)) . ') + \'</option>\';' . "\n    ";
}

$script = str_replace(
    ['__INITIAL_INDEX__', '__LOOKUP_URL__', '__LOAD_URL__', '__CONDITION_OPTIONS__'],
    [(string) $initialIndex, json_encode($lookupUrl), json_encode($loadAssetsUrl), $conditionJs],
    $script
);
$this->registerJs($script);
?>
