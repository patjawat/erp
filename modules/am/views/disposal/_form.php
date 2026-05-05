<?php

use app\components\AppHelper;
use app\modules\am\models\AssetDisposal;
use app\modules\am\models\AssetDisposalItem;
use app\widgets\datepicker\DatepickerThai;
use kartik\tree\TreeViewInput;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var AssetDisposal $model */
/** @var AssetDisposalItem[] $items */
/** @var array $conditionOptions */
/** @var array $departmentOptions */
/** @var array $assetTypeOptions */
/** @var array $statusOptions */

$items = $items ?? [new AssetDisposalItem()];
$conditionOptions = $conditionOptions ?? [];
$assetTypeOptions = $assetTypeOptions ?? [];
$statusOptions = $statusOptions ?? AssetDisposal::statusList();
$yearOptions = [];
$currentYear = (int) AppHelper::YearBudget();
for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
    $yearOptions[$y] = $y;
}
$lookupUrl = Url::to(['/am/disposal/lookup-asset']);
$loadAssetsUrl = Url::to(['/am/disposal/load-pending-assets']);
$initialIndex = count($items);
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $form = ActiveForm::begin(['id' => 'asset-disposal-form']); ?>

        <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <?= $form->field($model, 'disposal_no')->textInput([
                    'readonly' => true,
                    'class' => 'form-control fw-semibold',
                    'placeholder' => 'ระบบจะออกเลขให้เอง',
                ])->hint('ตัวอย่าง: จน.002/2568') ?>
            </div>
            <div class="col-12 col-md-3">
                <?= $form->field($model, 'fiscal_year')->dropDownList($yearOptions, ['class' => 'form-select']) ?>
            </div>
            <div class="col-12 col-md-3">
                <?= $form->field($model, 'disposal_date')->widget(DatepickerThai::class, [
                    'options' => [
                        'class' => 'form-control',
                        'placeholder' => 'วว/ดด/พ.ศ.',
                    ],
                ]) ?>
            </div>
            <div class="col-12 col-md-3">
                <?= $form->field($model, 'status')->dropDownList($statusOptions, ['class' => 'form-select']) ?>
            </div>
       
            <div class="col-6">
                <?= $form->field($model, 'disposal_method')->textInput([
                    'class' => 'form-control',
                    'placeholder' => 'เช่น ขายทอดตลาด, โอนออก, ทำลาย, บริจาค',
                ]) ?>
            </div>
                 <div class="col-12 col-md-6">
                <?= $this->render('@app/components/ui/input_emp', [
                    'form' => $form,
                    'model' => $model,
                    'fieldName' => 'responsible_emp_id',
                    'label' => 'ผู้รับผิดชอบ',
                    'placeholder' => 'เลือกผู้รับผิดชอบ',
                ]) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'summary_note')->textarea([
                    'rows' => 3,
                    'class' => 'form-control',
                    'placeholder' => 'หมายเหตุรวมของใบขอจำหน่าย',
                ]) ?>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="mb-0">รายการพัสดุที่ขอจำหน่าย</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                        ทั้งหมด <span class="disposal-item-total-count">0</span> รายการ
                    </span>
                </div>
                <div class="text-muted small">หนึ่งใบขอจำหน่ายสามารถมีหลายรายการ และโหลดจากทรัพย์สินที่มีสถานะรอจำหน่ายได้</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" id="delete-selected-disposal-items" class="btn btn-outline-danger d-none">
                    <i class="bi bi-trash me-1"></i> ลบรายการที่เลือก <span class="selected-disposal-item-count">(0)</span>
                </button>
                <button type="button" id="open-load-assets-modal" class="btn btn-outline-primary">
                    <i class="bi bi-collection me-1"></i> โหลดข้อมูล
                </button>
                <button type="button" id="add-disposal-item" class="btn btn-outline-secondary">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มรายการ
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="disposal-item-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 44px;" class="text-center">
                            <div class="form-check d-inline-flex align-items-center justify-content-center m-0">
                                <input class="form-check-input disposal-select-all" type="checkbox" id="disposal-select-all">
                            </div>
                        </th>
                        <th style="width: 18%">รหัส</th>
                        <th style="width: 26%">ชื่อ</th>
                        <th style="width: 16%">สภาพ</th>
                        <th style="width: 34%">เหตุผล</th>
                        <th style="width: 6%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                        <tr class="disposal-item-row">
                            <td class="text-center align-middle">
                                <div class="form-check d-inline-flex align-items-center justify-content-center m-0">
                                    <input class="form-check-input disposal-item-select" type="checkbox" name="selected_items[]" value="<?= (int) $i ?>">
                                </div>
                            </td>
                            <td>
                                <input type="hidden" name="AssetDisposalItem[<?= $i ?>][asset_id]" class="asset-id-input" value="<?= Html::encode($item->asset_id ?? '') ?>">
                                <div class="input-group">
                                    <?= Html::textInput("AssetDisposalItem[{$i}][asset_code]", $item->asset_code, [
                                        'class' => 'form-control asset-code-input',
                                        'placeholder' => 'รหัสครุภัณฑ์',
                                    ]) ?>
                                    <button type="button" class="btn btn-light lookup-asset-btn">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <?= Html::textInput("AssetDisposalItem[{$i}][asset_name]", $item->asset_name, [
                                    'class' => 'form-control asset-name-input',
                                    'placeholder' => 'ชื่อ',
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::dropDownList("AssetDisposalItem[{$i}][asset_condition]", $item->asset_condition, $conditionOptions, [
                                    'class' => 'form-select asset-condition-input',
                                    'prompt' => '-- เลือกสภาพ --',
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::textarea("AssetDisposalItem[{$i}][reason]", $item->reason, [
                                    'class' => 'form-control asset-reason-input',
                                    'rows' => 1,
                                     'style' => 'height: 23px;',
                                    'placeholder' => 'เหตุผล',
                                ]) ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger remove-disposal-item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <?= Html::submitButton($model->isNewRecord ? 'บันทึกใบขอจำหน่าย' : 'บันทึกการแก้ไข', ['class' => 'btn btn-primary px-4']) ?>
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary px-4']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="modal fade" id="load-assets-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">โหลดข้อมูลทรัพย์สินรอจำหน่าย</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border small mb-3">
                    เลือกตัวกรองได้ หรือปล่อยว่างเพื่อโหลดทรัพย์สินรอจำหน่ายทั้งหมด
                </div>
                <div class="mb-3">
                    <label class="form-label">หน่วยงาน</label>
                    <?= TreeViewInput::widget([
                        'name' => 'load_department_id',
                        'value' => '',
                        'query' => \app\modules\hr\models\Organization::find()->addOrderBy('root, lft'),
                        'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                        'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                        'fontAwesome' => true,
                        'asDropdown' => true,
                        'multiple' => false,
                        'options' => ['class' => 'form-select', 'id' => 'load-department-id'],
                        'pluginOptions' => [
                            'closeOnSelect' => true,
                        ],
                        'pluginEvents' => [
                            'select2:select' => new JsExpression('function() {
                                var $el = $(this);
                                setTimeout(function() {
                                    try { $el.select2("close"); } catch (e) {}
                                }, 50);
                            }'),
                        ],
                    ]) ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">ประเภททรัพย์สิน</label>
                    <?= Select2::widget([
                        'name' => 'load_asset_type_id',
                        'value' => '',
                        'data' => $assetTypeOptions,
                        'options' => [
                            'placeholder' => '-- ไม่เลือกก็ได้ --',
                            'id' => 'load-asset-type-id',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'width' => '100%',
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" id="confirm-load-assets" class="btn btn-primary">โหลดข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<table class="d-none">
    <tbody id="disposal-item-template">
        <tr class="disposal-item-row">
            <td class="text-center align-middle">
                <div class="form-check d-inline-flex align-items-center justify-content-center m-0">
                    <input class="form-check-input disposal-item-select" type="checkbox" name="selected_items[]" value="{idx}">
                </div>
            </td>
            <td>
                <input type="hidden" name="AssetDisposalItem[{idx}][asset_id]" class="asset-id-input" value="">
                <div class="input-group">
                    <input type="text" name="AssetDisposalItem[{idx}][asset_code]" class="form-control asset-code-input" placeholder="รหัสครุภัณฑ์">
                    <button type="button" class="btn btn-light lookup-asset-btn">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </td>
            <td>
                <input type="text" name="AssetDisposalItem[{idx}][asset_name]" class="form-control asset-name-input" placeholder="ชื่อ">
            </td>
            <td>
                <select name="AssetDisposalItem[{idx}][asset_condition]" class="form-select asset-condition-input">
                    <option value="">-- เลือกสภาพ --</option>
                    <?php foreach ($conditionOptions as $condId => $condName): ?>
                        <option value="<?= Html::encode($condId) ?>"><?= Html::encode($condName) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <textarea name="AssetDisposalItem[{idx}][reason]" class="form-control asset-reason-input" rows="2"  style="height: 23px;" placeholder="เหตุผล"></textarea>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger remove-disposal-item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </tbody>
</table>

<?php
$script = <<<'JS'
let disposalItemIndex = __INITIAL_INDEX__;
const lookupAssetUrl = __LOOKUP_URL__;
const loadAssetsUrl = __LOAD_URL__;
let disposalSubmitConfirmed = false;
let loadAssetsModal = null;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function fillDisposalRow(row, data) {
    row.find('.asset-id-input').val(data.asset_id || '');
    row.find('.asset-code-input').val(data.asset_code || '');
    row.find('.asset-name-input').val(data.asset_name || '');
    if (data.asset_condition && !row.find('.asset-condition-input').val()) {
        row.find('.asset-condition-input').val(data.asset_condition);
    }
}

function updateDisposalSelectionUI() {
    const $rows = $('#disposal-item-table tbody tr.disposal-item-row');
    const $selected = $rows.find('.disposal-item-select:checked');
    const selectedCount = $selected.length;
    const totalCount = $rows.length;
    const $selectAll = $('#disposal-select-all');
    const $deleteSelected = $('#delete-selected-disposal-items');

    if ($selectAll.length) {
        $selectAll.prop('checked', totalCount > 0 && selectedCount === totalCount);
        $selectAll.prop('indeterminate', selectedCount > 0 && selectedCount < totalCount);
    }

    if ($deleteSelected.length) {
        $deleteSelected.toggleClass('d-none', selectedCount === 0);
        $deleteSelected.find('.selected-disposal-item-count').text('(' + selectedCount + ')');
    }

    $('.disposal-item-total-count').text(totalCount);
}

function lookupDisposalAsset(row) {
    const code = (row.find('.asset-code-input').val() || '').trim();
    if (!code) {
        row.find('.asset-id-input').val('');
        row.find('.asset-name-input').val('');
        return;
    }

    $.getJSON(lookupAssetUrl, { code: code })
        .done(function(res) {
            if (res && res.success && res.data) {
                fillDisposalRow(row, res.data);
            } else if (res && res.message && typeof Swal !== 'undefined') {
                Swal.fire('ค้นหาไม่พบ', res.message, 'warning');
            }
        })
        .fail(function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire('ผิดพลาด', 'ไม่สามารถค้นหาทรัพย์สินได้', 'error');
            }
        });
}

function buildDisposalRowHtml(idx, item) {
    let conditionHtml = '<option value="">-- เลือกสภาพ --</option>';
    __CONDITION_OPTIONS__

    return `
        <tr class="disposal-item-row">
            <td class="text-center align-middle">
                <div class="form-check d-inline-flex align-items-center justify-content-center m-0">
                    <input class="form-check-input disposal-item-select" type="checkbox" name="selected_items[]" value="${idx}">
                </div>
            </td>
            <td>
                <input type="hidden" name="AssetDisposalItem[${idx}][asset_id]" class="asset-id-input" value="${escapeHtml(item.asset_id || '')}">
                <div class="input-group">
                    <input type="text" name="AssetDisposalItem[${idx}][asset_code]" class="form-control asset-code-input" placeholder="รหัสครุภัณฑ์" value="${escapeHtml(item.asset_code || '')}">
                    <button type="button" class="btn btn-light lookup-asset-btn">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </td>
            <td>
                <input type="text" name="AssetDisposalItem[${idx}][asset_name]" class="form-control asset-name-input" placeholder="ชื่อ" value="${escapeHtml(item.asset_name || '')}">
            </td>
            <td>
                <select name="AssetDisposalItem[${idx}][asset_condition]" class="form-select asset-condition-input">
                    ${conditionHtml}
                </select>
            </td>
            <td>
                <textarea name="AssetDisposalItem[${idx}][reason]" class="form-control asset-reason-input" rows="2" style="height: 23px;" placeholder="เหตุผล">${escapeHtml(item.reason || '')}</textarea>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger remove-disposal-item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
}

function appendDisposalRow() {
    const html = $('#disposal-item-template').html().replace(/\{idx\}/g, disposalItemIndex);
    const row = $(html);
    $('#disposal-item-table tbody').append(row);
    disposalItemIndex++;
    updateDisposalSelectionUI();
    return row;
}

function appendLoadedDisposalRows(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return;
    }
    items.forEach(function(item) {
        const html = buildDisposalRowHtml(disposalItemIndex, item);
        const row = $(html);
        $('#disposal-item-table tbody').append(row);
        if (item.asset_condition) {
            row.find('.asset-condition-input').val(item.asset_condition);
        }
        disposalItemIndex++;
    });
    updateDisposalSelectionUI();
}

function loadPendingAssets() {
    const departmentId = $('#load-department-id').val();
    const assetTypeId = $('#load-asset-type-id').val();

    const doLoad = function() {
        $.getJSON(loadAssetsUrl, {
            department_id: departmentId,
            asset_type_id: assetTypeId,
        })
            .done(function(res) {
                if (res && res.success) {
                    $('#disposal-item-table tbody').empty();
                    disposalItemIndex = 0;
                    appendLoadedDisposalRows(res.items || []);
                    if ((res.items || []).length === 0) {
                        appendDisposalRow();
                    }
                    $('#disposal-select-all').prop('checked', false).prop('indeterminate', false);
                    updateDisposalSelectionUI();
                    if (typeof Swal !== 'undefined') {
                        const deptLabel = res.department ? (' หน่วยงาน ' + res.department.name) : '';
                        const typeLabel = res.asset_type ? (' ประเภท ' + res.asset_type.name) : '';
                        Swal.fire('โหลดแล้ว', 'โหลดทรัพย์สินรอจำหน่าย' + deptLabel + typeLabel + ' จำนวน ' + (res.count || 0) + ' รายการ', 'success');
                    }
                    if (loadAssetsModal && typeof loadAssetsModal.hide === 'function') {
                        loadAssetsModal.hide();
                    }
                } else if (res && res.message && typeof Swal !== 'undefined') {
                    Swal.fire('ผิดพลาด', res.message, 'error');
                }
            })
            .fail(function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
                }
            });
    };

    if ($('#disposal-item-table tbody tr').length > 0) {
        Swal.fire({
            title: 'โหลดข้อมูลทรัพย์สินรอจำหน่าย?',
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

$(document).on('click', '#add-disposal-item', function() {
    const row = appendDisposalRow();
    row.find('.asset-code-input').focus();
});

$(document).on('change', '#disposal-select-all', function() {
    const checked = $(this).is(':checked');
    $('#disposal-item-table tbody .disposal-item-select').prop('checked', checked);
    updateDisposalSelectionUI();
});

$(document).on('change', '.disposal-item-select', function() {
    updateDisposalSelectionUI();
});

$(document).on('click', '#delete-selected-disposal-items', function() {
    const $rows = $('#disposal-item-table tbody tr.disposal-item-row');
    const $selectedRows = $rows.has('.disposal-item-select:checked');
    if ($selectedRows.length === 0) {
        return;
    }

    const removeRows = function() {
        $selectedRows.remove();
        if ($('#disposal-item-table tbody tr.disposal-item-row').length === 0) {
            appendDisposalRow();
        } else {
            updateDisposalSelectionUI();
        }
        $('#disposal-select-all').prop('checked', false).prop('indeterminate', false);
        updateDisposalSelectionUI();
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

$(document).on('click', '#open-load-assets-modal', function() {
    if (window.bootstrap && $('#load-assets-modal').length) {
        loadAssetsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('load-assets-modal'));
        loadAssetsModal.show();
    } else {
        loadPendingAssets();
    }
});

$(document).on('click', '#confirm-load-assets', function() {
    loadPendingAssets();
});

$(document).on('click', '.lookup-asset-btn', function() {
    lookupDisposalAsset($(this).closest('tr'));
});

$(document).on('blur change', '.asset-code-input', function() {
    lookupDisposalAsset($(this).closest('tr'));
});

$(document).on('click', '.remove-disposal-item', function() {
    const rows = $('#disposal-item-table tbody tr');
    if (rows.length <= 1) {
        const row = rows.first();
        row.find('input, textarea').val('');
        row.find('select').val('');
        row.find('.disposal-item-select').prop('checked', false);
        updateDisposalSelectionUI();
        return;
    }
    $(this).closest('tr').remove();
    updateDisposalSelectionUI();
});

$('#asset-disposal-form').on('beforeSubmit', function(e) {
    if (disposalSubmitConfirmed) {
        disposalSubmitConfirmed = false;
        return true;
    }

    e.preventDefault();
    const form = $(this);
    const confirmSubmit = function() {
        disposalSubmitConfirmed = true;
        form.trigger('submit');
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'บันทึกใบขอจำหน่าย?',
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
    } else if (window.confirm('ยืนยันบันทึกใบขอจำหน่าย?')) {
        confirmSubmit();
    }

    return false;
});

$(document).on('select2:select', '#load-department-id', function() {
    try {
        $(this).select2('close');
    } catch (e) {}
});

$(function() {
    updateDisposalSelectionUI();
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
