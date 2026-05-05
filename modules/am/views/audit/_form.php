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

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="receipt-text" class="me-2"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>


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
                <?= $form->field($model, 'thai_year')->dropDownList($yearOptions, [
                    'class' => 'form-select',
                ]) ?>
            </div>
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-end gap-2">
                    <div class="flex-grow-1">
                        <?= $form->field($model, 'department')->widget(TreeViewInput::className(), [
                            'query' => Organization::find()->addOrderBy('root, lft'),
                            'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                            'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                            'fontAwesome' => true,
                            'asDropdown' => true,
                            'multiple' => false,
                            'dropdownConfig' => [
                                'input' => [
                                    'placeholder' => '--หน่วยงานทั้งหมด--',
                                ],
                            ],
                            'options' => ['class' => 'form-select', 'id' => 'audit-department'],
                            'pluginEvents' => [
                                'treeview:change' => new JsExpression('function() {
                                    var $container = $(this).closest(".kv-tree-dropdown-container");
                                    setTimeout(function() {
                                        var $toggle = $container.find(".kv-tree-input");
                                        $toggle.removeClass("show open").attr("aria-expanded", "false");
                                        $container.find(".kv-tree-dropdown").removeClass("show open");
                                        if (window.bootstrap && bootstrap.Dropdown && $toggle.length) {
                                            try {
                                                var instance = bootstrap.Dropdown.getInstance($toggle[0]) || bootstrap.Dropdown.getOrCreateInstance($toggle[0]);
                                                if (instance) {
                                                    instance.hide();
                                                }
                                            } catch (e) {}
                                        }
                                    }, 0);
                                }'),
                            ],
                        ])->label('หน่วยงานที่ตรวจนับ') ?>
                    </div>
                    <button type="button" class="btn btn-outline-secondary flex-shrink-0" id="clear-audit-department">
                        <i class="fa-solid fa-eraser me-1"></i> ล้าง
                    </button>
                </div>
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
            <div class="col-12 col-md-6">
                <?= $this->render('@app/components/ui/input_emp', [
                    'form' => $form,
                    'model' => $model,
                    'fieldName' => 'emp_id',
                    'label' => 'ผู้ตรวจนับ',
                    'placeholder' => 'เลือกผู้ตรวจนับ',
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
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="mb-0">รายการที่ตรวจนับ</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                        ทั้งหมด <span class="audit-item-total-count">0</span> รายการ
                    </span>
                </div>
                <div class="text-muted small">หนึ่งใบตรวจนับสามารถมีได้หลายรายการ และโหลดจากหน่วยงานที่เลือกได้</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" id="delete-selected-audit-items" class="btn btn-outline-danger d-none">
                    <i class="bi bi-trash me-1"></i> ลบรายการที่เลือก <span class="selected-audit-item-count">(0)</span>
                </button>
                <button type="button" id="add-audit-item" class="btn btn-outline-primary">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มรายการ
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="audit-item-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 44px;" class="text-center">
                            <div class="form-check d-inline-flex align-items-center justify-content-center m-0">
                                <input class="form-check-input audit-select-all" type="checkbox" id="audit-select-all">
                            </div>
                        </th>
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
                            <td class="text-center align-middle">
                                <div class="form-check d-inline-flex align-items-center justify-content-center m-0">
                                    <input class="form-check-input audit-item-select" type="checkbox" name="selected_items[]" value="<?= (int) $i ?>">
                                </div>
                            </td>
                            <td>
                                <input type="hidden"
                                    name="AssetAuditItem[<?= $i ?>][asset_id]"
                                    class="asset-id-input"
                                    value="<?= Html::encode($item->asset_id ?? '') ?>">

                                <div class="input-group">
                                    <?= Html::textInput("AssetAuditItem[{$i}][asset_code]", $item->asset_code, [
                                        'class' => 'form-control asset-code-input',
                                        'placeholder' => 'รหัสครุภัณฑ์',
                                    ]) ?>

                                    <button type="button" class="btn btn-light lookup-asset-btn">
                                        <i class="fa-solid fa-magnifying-glass"></i>
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
                                    'style' => 'height: 23px;',
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
            <td class="text-center align-middle">
                <div class="form-check d-inline-flex align-items-center justify-content-center m-0">
                    <input class="form-check-input audit-item-select" type="checkbox" name="selected_items[]" value="{idx}">
                </div>
            </td>
            <td>
                <input type="hidden" name="AssetAuditItem[{idx}][asset_id]" class="asset-id-input" value="">
                <div class="input-group">
                    <input type="text" name="AssetAuditItem[{idx}][asset_code]" class="form-control asset-code-input" placeholder="รหัสครุภัณฑ์">
                    <button type="button" class="btn btn-light lookup-asset-btn">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
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
                <textarea name="AssetAuditItem[{idx}][note]" class="form-control asset-note-input" rows="1" style="height: 23px;" placeholder="หมายเหตุ"></textarea>
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
let auditSubmitConfirmed = false;

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

function updateAuditSelectionUI() {
    const $rows = $('#audit-item-table tbody tr.audit-item-row');
    const $selected = $rows.find('.audit-item-select:checked');
    const selectedCount = $selected.length;
    const totalCount = $rows.length;
    const $selectAll = $('#audit-select-all');
    const $deleteSelected = $('#delete-selected-audit-items');

    if ($selectAll.length) {
        $selectAll.prop('checked', totalCount > 0 && selectedCount === totalCount);
        $selectAll.prop('indeterminate', selectedCount > 0 && selectedCount < totalCount);
    }

    if ($deleteSelected.length) {
        $deleteSelected.toggleClass('d-none', selectedCount === 0);
        $deleteSelected.find('.selected-audit-item-count').text('(' + selectedCount + ')');
    }

    $('.audit-item-total-count').text(totalCount);
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
            <td class="text-center align-middle">
                <div class="form-check d-inline-flex align-items-center justify-content-center m-0">
                    <input class="form-check-input audit-item-select" type="checkbox" name="selected_items[]" value="${idx}">
                </div>
            </td>
            <td>
                <input type="hidden" name="AssetAuditItem[${idx}][asset_id]" class="asset-id-input" value="${escapeHtml(item.asset_id || '')}">
                <div class="input-group">
                    <input type="text" name="AssetAuditItem[${idx}][asset_code]" class="form-control asset-code-input" placeholder="รหัสครุภัณฑ์" value="${escapeHtml(item.asset_code || '')}">
                    <button type="button" class="btn btn-light lookup-asset-btn">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
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
                <textarea name="AssetAuditItem[${idx}][note]" class="form-control asset-note-input" rows="1" style="height: 23px;" placeholder="หมายเหตุ">${escapeHtml(item.note || '')}</textarea>
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
    updateAuditSelectionUI();
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
    updateAuditSelectionUI();
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
                    $('#audit-select-all').prop('checked', false).prop('indeterminate', false);
                    updateAuditSelectionUI();
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

$(document).on('change', '#audit-select-all', function() {
    const checked = $(this).is(':checked');
    $('#audit-item-table tbody .audit-item-select').prop('checked', checked);
    updateAuditSelectionUI();
});

$(document).on('change', '.audit-item-select', function() {
    updateAuditSelectionUI();
});

$(document).on('click', '#delete-selected-audit-items', function() {
    const $rows = $('#audit-item-table tbody tr.audit-item-row');
    const $selectedRows = $rows.has('.audit-item-select:checked');
    if ($selectedRows.length === 0) {
        return;
    }

    const removeRows = function() {
        $selectedRows.remove();
        if ($('#audit-item-table tbody tr.audit-item-row').length === 0) {
            appendAuditRow();
        } else {
            updateAuditSelectionUI();
        }
        $('#audit-select-all').prop('checked', false).prop('indeterminate', false);
        updateAuditSelectionUI();
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'ลบรายการที่เลือก?',
            text: 'รายการที่เลือกจะถูกลบออกจากฟอร์ม',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
        }).then(function(result) {
            if (result.isConfirmed) {
                removeRows();
            }
        });
    } else if (window.confirm('ลบรายการที่เลือกใช่หรือไม่?')) {
        removeRows();
    }
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
        row.find('.audit-item-select').prop('checked', false);
        updateAuditSelectionUI();
        return;
    }
    $(this).closest('tr').remove();
    updateAuditSelectionUI();
});

$('#asset-audit-form').on('beforeSubmit', function(e) {
    if (auditSubmitConfirmed) {
        auditSubmitConfirmed = false;
        return true;
    }

    e.preventDefault();
    const form = $(this);
    const confirmSubmit = function() {
        auditSubmitConfirmed = true;
        form.trigger('submit');
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'บันทึกใบตรวจนับ?',
            text: 'กรุณาตรวจสอบข้อมูลก่อนบันทึก',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก',
        }).then(function(result) {
            if (result.isConfirmed) {
                confirmSubmit();
            }
        });
    } else if (window.confirm('ยืนยันบันทึกใบตรวจนับ?')) {
        confirmSubmit();
    }

    return false;
});

$(document).on('select2:select', '#audit-department', function() {
    try {
        $(this).select2('close');
    } catch (e) {}
});

updateAuditSelectionUI();
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

<?php
$clearDepartmentJs = <<<'JS'
$('#clear-audit-department').on('click', function() {
    const $field = $('.field-assetaudit-department');
    const $input = $field.find('#audit-department, input[name="AssetAudit[department]"]').first();
    const treeInput = $input.data('treeinput');
    const treeView = $input.data('treeview');

    if (!$input.length) {
        return;
    }

    $input.val('');
    $input.trigger('treeview:change', ['', '']);
    $input.trigger('change');

    if (treeView && treeView.$tree) {
        treeView.$tree.find('.kv-selected').removeClass('kv-selected');
        if (typeof treeView.disableToolbar === 'function') {
            treeView.disableToolbar();
        }
    }

    if (treeInput && typeof treeInput.setInput === 'function') {
        treeInput.setInput([]);
    } else if (treeInput && treeInput.$input) {
        treeInput.$input.html(treeInput.caret + treeInput.placeholder);
    }

    const $toggle = $field.find('.kv-tree-input').first();
    if ($toggle.length) {
        $toggle.attr('aria-expanded', 'false');
    }

    const $container = $field.find('.kv-tree-dropdown-container').first();
    if ($container.length) {
        $container.removeClass('show open');
        if (window.bootstrap && bootstrap.Dropdown && $toggle.length) {
            try {
                var instance = bootstrap.Dropdown.getInstance($toggle[0]) || bootstrap.Dropdown.getOrCreateInstance($toggle[0]);
                if (instance) {
                    instance.hide();
                }
            } catch (e) {}
        }
    }
});
JS;
$this->registerJs($clearDepartmentJs);
?>
