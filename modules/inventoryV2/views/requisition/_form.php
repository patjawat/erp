<?php

use app\components\AppHelper;
use app\modules\inventoryV2\models\Warehouse;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

$formatRepoJs = "var formatRepo=function(repo){if(repo.loading)return repo.avatar;return '<div>'+repo.avatar+'</div>';};";
$this->registerJs($formatRepoJs, View::POS_HEAD);
$resultsJs = "function(data,p){p.page=p.page||1;return{results:data.results,pagination:{more:(p.page*30)<(data.total_count||999)}};}";

// คลังหลัก (ต้นทางที่จ่าย) และคลังย่อย (หน่วยงานผู้เบิก) – สมมติ id=1 เป็นคลังหลัก
$mainWarehouseIds = [1,2,3,4,5,6,7];
$warehouseList = ArrayHelper::map(Warehouse::find()->orderBy(['id' => SORT_ASC])->all(), 'id', 'warehouse_name');
$subWarehouses = ArrayHelper::map(Warehouse::find()->where(['not in', 'id', $mainWarehouseIds])->orderBy(['warehouse_name' => SORT_ASC])->all(), 'id', 'warehouse_name');
$mainWarehouses = ArrayHelper::map(Warehouse::find()->where(['id' => $mainWarehouseIds])->orderBy(['warehouse_name' => SORT_ASC])->all(), 'id', 'warehouse_name');
?>

<div class="requisition-form">
    <?php $form = ActiveForm::begin(['id' => 'requisition-form']); ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-3">
                    <?= $form->field($model, 'order_no', ['labelOptions' => ['label' => 'เลขที่ใบขอเบิก']])->textInput(['readonly' => true, 'class' => 'form-control', 'placeholder' => 'REQ-AUTO']) ?>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <?= $form->field($model, 'sub_warehouse_id', ['labelOptions' => ['label' => 'หน่วยงานที่รับของ']])->dropDownList($subWarehouses, [
                        'id' => 'sub-warehouse-id',
                        'prompt' => '-- เลือกหน่วยงานที่รับของ --',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <?= $form->field($model, 'main_warehouse_id', ['labelOptions' => ['label' => 'คลังที่จ่ายของ']])->dropDownList($mainWarehouses ?: $warehouseList, [
                        'id' => 'main-warehouse-id',
                        'prompt' => '-- เลือกคลังที่จ่ายของ --',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <?= $form->field($model, 'order_date', ['labelOptions' => ['label' => 'วันที่ขอเบิก']])->widget(\app\widgets\datepicker\DatepickerThai::class, [
                        'options' => [
                            'id' => 'requisition-order_date',
                            'placeholder' => 'เลือกวันที่',
                            'value' => $model->order_date ? AppHelper::convertToThai($model->order_date) : '',
                        ],
                    ]) ?>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label d-flex align-items-center gap-2 flex-wrap">
                            เหตุผล/วัตถุประสงค์การเบิก
                            <div class="dropdown">
                                <button class="btn btn-light border rounded-3 dropdown-toggle btn-sm" type="button" id="dropdownIssueReason" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-list-ul me-1"></i> ตัวเลือก
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownIssueReason">
                                    <?php
                                    $reasonOptions = [
                                        'ใช้ในงานประจำ',
                                        'ซ่อมบำรุง',
                                        'เติมสต็อกคลังย่อย',
                                        'โครงการ/กิจกรรม',
                                        'เบิกทดแทนของเสียหาย',
                                        'อื่นๆ (ระบุเพิ่มเติม)',
                                    ];
                                    foreach ($reasonOptions as $text):
                                    ?>
                                    <li><a class="dropdown-item issue-reason-option" href="#" data-reason="<?= Html::encode($text) ?>"><?= Html::encode($text) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </label>
                        <textarea name="issue_reason" id="issue_reason" class="form-control" rows="2" placeholder="ระบุเหตุผลหรือวัตถุประสงค์ในการเบิกวัสดุ"><?= Html::encode($model->getIssueReason()) ?></textarea>
                        <div class="form-text">เช่น เบิกเพื่อใช้ในหน่วยงาน, เติมสต็อกคลังย่อย หรือกดตัวเลือกเพื่อเลือกเหตุผลที่ใช้บ่อย</div>
                    </div>
                </div>
                <?php
                $approverSig = $model->getIssueSignature('approver');
                $approverEmpId = '';
                $dj = $model->data_json;
                if (is_string($dj)) {
                    $dj = json_decode($dj, true) ?: [];
                }
                if (!empty($dj['issue_approver']['emp_id'])) {
                    $approverEmpId = $dj['issue_approver']['emp_id'];
                }
                ?>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">ผู้เห็นชอบ (หัวหน้า) — เลือกพนักงาน (ดึงจากผังโครงสร้างองค์กร <a href="<?= \yii\helpers\Url::to(['/hr/organization/diagram']) ?>" target="_blank" class="small">ตั้งค่า <i class="bi bi-box-arrow-up-right"></i></a> หรือเลือกด้านล่าง)</label>
                        <input type="hidden" name="approver_name" id="approver_name" value="<?= Html::encode($approverSig['name']) ?>">
                        <input type="hidden" name="approver_position" id="approver_position" value="<?= Html::encode($approverSig['position']) ?>">
                        <?= Select2::widget([
                            'name' => 'approver_emp_id',
                            'value' => $approverEmpId,
                            'initValueText' => $approverSig['name'] ?: '— เลือกพนักงาน —',
                            'options' => ['placeholder' => 'พิมพ์ชื่อเพื่อค้นหา...', 'id' => 'approver_emp_id_select'],
                            'pluginEvents' => [
                                'select2:select' => new JsExpression('function(e) {
                                    var d = $(this).select2("data")[0];
                                    if (d && d.id) {
                                        $("#approver_name").val(d.fullname || "");
                                        $("#approver_position").val(d.position_name || d.position_name_text || "");
                                    }
                                }'),
                                'select2:unselect' => new JsExpression('function() {
                                    $("#approver_name").val("");
                                    $("#approver_position").val("");
                                }'),
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 0,
                                'ajax' => [
                                    'url' => Url::to(['/depdrop/employee-by-id']),
                                    'dataType' => 'json',
                                    'delay' => 250,
                                    'data' => new JsExpression('function(params) { return {q: params.term || "", page: params.page || 1}; }'),
                                    'processResults' => new JsExpression($resultsJs),
                                    'cache' => true,
                                ],
                                'escapeMarkup' => new JsExpression('function(m) { return m; }'),
                                'templateSelection' => new JsExpression('function(item) { return item.fullname || item.text || item.id || ""; }'),
                                'templateResult' => new JsExpression('formatRepo'),
                            ],
                        ]) ?>
                        <div class="form-text">ตำแหน่งจะดึงจากระบบพนักงานอัตโนมัติ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" id="card-below-max" style="display: none;">
        <div class="card-header bg-light border-bottom py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 small fw-bold text-secondary"><i class="bi bi-graph-down-arrow me-1"></i>รายการที่หน่วยงานรับของเหลือต่ำกว่า Min (เติมให้ถึง Max)</h6>
            <button type="button" id="btn-load-below-max" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-repeat me-1"></i> โหลดรายการที่ต่ำกว่า Max
            </button>
        </div>
        <div class="card-body p-3">
            <p class="text-muted small mb-2">คำนวณจาก<strong>ยอดคงเหลือที่หน่วยงานที่รับของ</strong> (คลังย่อย) — ถ้าเหลือต่ำกว่า Min จะแสดงในรายการ เบิกให้พอดี = เติมจนหน่วยงานรับของมีครบ Max ไม่ต้องค้นหาทีละรายการ</p>
            <div id="below-max-placeholder" class="text-muted text-center py-4 small">กรุณาเลือกคลังที่จ่ายของ และหน่วยงานที่รับของ แล้วกด "โหลดรายการที่ต่ำกว่า Max"</div>
            <div id="below-max-table-wrap" style="display: none;">
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-hover align-middle mb-0" id="below-max-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="below-max-checkall" title="เลือกทั้งหมด"></th>
                                <th>รายการวัสดุ</th>
                                <th class="text-center">หน่วย</th>
                                <th class="text-end">คงเหลือที่หน่วยงานรับของ</th>
                                <th class="text-end">Min / Max</th>
                                <th class="text-end">เบิกให้พอดี</th>
                            </tr>
                        </thead>
                        <tbody id="below-max-tbody"></tbody>
                    </table>
                </div>
                <button type="button" id="btn-add-below-max" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> เพิ่มรายการที่เลือกเข้าใบเบิก
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="min-height: 400px;">
        <div class="card-header bg-primary-gradient text-white py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="text-white mb-0 small fw-normal"><i class="bi bi-box-seam me-1"></i>รายการวัสดุที่ต้องการเบิก</h6>
            <button type="button" id="add-item" class="btn btn-light btn-sm">
                <i class="bi bi-plus-lg me-1"></i> เพิ่มวัสดุ
            </button>
        </div>
            <table class="table table-hover align-middle mb-0" id="item-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50%">รายการวัสดุ</th>
                        <th style="width: 12%" class="text-center">หน่วยนับ</th>
                        <th style="width: 23%" class="text-center">จำนวนที่ขอเบิก</th>
                        <th style="width: 15%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                <?php
                if (!$model->isNewRecord && !empty($model->stockDetails)) {
                    foreach ($model->stockDetails as $i => $detail) {
                        $unitText = $detail->item ? ($detail->item->getUnitName() ?: '-') : '-';
                        echo '<tr class="item-row">';
                        echo '<td><input type="hidden" name="StockDetail[' . $i . '][item_code]" value="' . Html::encode($detail->item_code) . '"><span class="item-name-display">' . Html::encode($detail->item->item_name ?? $detail->item_code) . '</span></td>';
                        echo '<td class="text-center unit-cell text-muted small align-middle">' . Html::encode($unitText) . '</td>';
                        echo '<td><input type="number" name="StockDetail[' . $i . '][qty]" class="form-control text-center qty-input fw-bold" min="0.01" step="0.01" value="' . (float)$detail->qty . '" required placeholder="0.00"></td>';
                        echo '<td class="text-center"><button type="button" class="btn btn-outline-danger border-0 remove-item"><i class="bi bi-trash"></i></button></td>';
                        echo '</tr>';
                    }
                }
                ?>
                </tbody>
            </table>
    </div>

    <div class="form-group mt-4 d-flex justify-content-end gap-2">
        <?= Html::submitButton($model->isNewRecord ? '<i class="bi bi-send-fill me-1"></i> ส่งคำขอเบิก' : '<i class="bi bi-check-lg me-1"></i> บันทึกการแก้ไข', ['class' => 'btn btn-primary me-2']) ?>
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<table class="d-none">
    <tbody id="row-template">
        <tr class="item-row">
            <td>
                <select name="StockDetail[{idx}][item_code]" class="item-select-ajax form-select" required></select>
            </td>
            <td class="text-center unit-cell text-muted small align-middle"></td>
            <td>
                <input type="number" name="StockDetail[{idx}][qty]" class="form-control text-center qty-input fw-bold" min="0.01" step="0.01" required placeholder="0.00">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger border-0 remove-item"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    </tbody>
    <tbody id="row-template-prefilled">
        <tr class="item-row">
            <td><input type="hidden" name="StockDetail[{idx}][item_code]" value="{item_code}"><span class="item-name-display">{item_name}</span></td>
            <td class="text-center unit-cell text-muted small align-middle">{unit_name}</td>
            <td><input type="number" name="StockDetail[{idx}][qty]" class="form-control text-center qty-input fw-bold" min="0.01" step="0.01" value="{qty}" required placeholder="0.00"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger border-0 remove-item"><i class="bi bi-trash"></i></button></td>
        </tr>
    </tbody>
</table>

<?php
\app\assets\TomSelectAsset::register($this);
$this->registerCss(<<<CSS
/* dropdown ถูก append ที่ body จึงใช้ class นี้ (ไม่มี parent .requisition-form) */
.ts-dropdown.requisition-item-dropdown {
    z-index: 1060 !important;
    position: fixed !important;
    border: 1px solid rgba(0,0,0,.15) !important;
    border-radius: 0.375rem !important;
    background: #fff !important;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15) !important;
    max-height: 280px;
    overflow-y: auto;
}
.requisition-form #item-table td:first-child { overflow: visible; position: relative; }
.requisition-form #item-table .ts-wrapper { position: relative; }
.requisition-form #item-table .ts-control { min-height: 38px; }
CSS
);

$getItemInWhUrl = Url::to(['/inventory-v2/stock-item/get-items-by-warehouse']);
$itemsBelowMaxUrl = Url::to(['/inventory-v2/requisition/items-below-max']);
$initialIdx = !$model->isNewRecord && !empty($model->stockDetails) ? count($model->stockDetails) : 0;

$script = <<< JS
let idx = {$initialIdx};

// รหัสวัสดุที่ถูกเลือกแล้วในแถวอื่น (ไม่รวมแถวที่ส่งเข้า)
function getSelectedItemCodes(excludeRow) {
    var codes = [];
    $('#item-table tbody tr').each(function() {
        if (excludeRow && this !== excludeRow[0]) {
            var v = $(this).find('select[name*="[item_code]"]').val();
            if (v) codes.push(v);
        }
    });
    return codes;
}

// 1. ฟังก์ชันสร้าง TomSelect (กรองรายการที่เลือกแล้วในแถวอื่น)
function initItemSelect(elementId) {
    var selfRef = null;
    return new TomSelect('#' + elementId, {
        dropdownParent: document.body,
        valueField: 'item_code',
        labelField: 'item_name',
        searchField: ['item_name', 'item_code'],
        placeholder: 'พิมพ์ชื่อหรือรหัสวัสดุ...',
        preload: true,
        onDropdownOpen: function() {
            var wrapper = this.wrapper;
            var dropdown = this.dropdown;
            if (!wrapper || !dropdown) return;
            var rect = wrapper.getBoundingClientRect();
            dropdown.style.position = 'fixed';
            dropdown.style.left = rect.left + 'px';
            dropdown.style.top = rect.bottom + 'px';
            dropdown.style.width = Math.max(rect.width, 280) + 'px';
            dropdown.style.minWidth = rect.width + 'px';
            dropdown.classList.add('requisition-item-dropdown');
        },
        load: function(query, callback) {
            var currentWhId = $('#main-warehouse-id').val();
            if (!currentWhId) return callback();
            var excludeRow = $(this.input).closest('tr');
            var selectedCodes = getSelectedItemCodes(excludeRow);
            var q = (typeof query === 'string') ? query : '';
            q = q.replace(/^["'\s]+|["'\s]+$/g, '');
            var url = '$getItemInWhUrl' + '?warehouse_id=' + currentWhId + '&q=' + encodeURIComponent(q);
            fetch(url)
                .then(function(response) {
                    if (!response.ok) return [];
                    var ct = response.headers.get('Content-Type') || '';
                    if (ct.indexOf('json') === -1) return [];
                    return response.json();
                })
                .then(function(json) {
                    var list = Array.isArray(json) ? json : (json && (json.results || json.data)) || [];
                    list = list.filter(function(item) {
                        return item && item.item_code && selectedCodes.indexOf(item.item_code) === -1;
                    });
                    callback(list);
                })
                .catch(function() { callback([]); });
        },
        render: {
            option: function(item, escape) {
                var unit = (item.unit_name && item.unit_name !== '-') ? ' <span class="text-muted">(' + escape(item.unit_name) + ')</span>' : '';
                return '<div class="py-1"><div class="fw-bold">' + escape(item.item_name) + unit + '</div><small class="text-muted">รหัส: ' + escape(item.item_code) + '</small></div>';
            }
        },
        onChange: function(value) {
            if (value) {
                var opt = this.options[value];
                var unitText = (opt && opt.unit_name) ? opt.unit_name : '-';
                $(this.wrapper).closest('tr').find('.unit-cell').text(unitText);
                $(this.wrapper).closest('tr').find('.qty-input').focus();
            }
        }
    });
}

// 2. ฟังก์ชันเพิ่มแถว (คืนค่า TomSelect ของแถวใหม่)
function addItem() {
    var whId = $('#main-warehouse-id').val();
    if (!whId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('คำเตือน', 'กรุณาเลือกคลังที่จ่ายของก่อนเพิ่มรายการ', 'warning');
        }
        $('#main-warehouse-id').addClass('is-invalid').focus();
        return null;
    }

    var template = $('#row-template').html();
    var rowHtml = template.replace(/{idx}/g, idx);
    var newRow = $(rowHtml);

    $('#item-table tbody').append(newRow);

    var selectId = 'select-item-' + idx;
    newRow.find('.item-select-ajax').attr('id', selectId);

    var ts = initItemSelect(selectId);
    idx++;
    return ts;
}

// 3. Event Listeners
$(document).on('click', '#add-item', function(e) {
    e.preventDefault();
    var ts = addItem();
    if (ts) ts.open();
});

// Enter ที่ช่องจำนวน = เพิ่มแถวใหม่ แล้วโฟกัสที่ช่องค้นหาวัสดุ
$(document).on('keydown', '.qty-input', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        var ts = addItem();
        if (ts) {
            ts.open();
        }
    }
});

function refreshBelowMaxCard() {
    var whId = $('#main-warehouse-id').val();
    var subId = $('#sub-warehouse-id').val();
    $('#card-below-max').toggle(!!whId);
    $('#below-max-table-wrap').hide();
    $('#below-max-placeholder').show().html(whId && subId ? 'กด "โหลดรายการที่ต่ำกว่า Max" เพื่อดึงรายการที่หน่วยงานรับของเหลือต่ำกว่า Min' : 'กรุณาเลือกคลังที่จ่ายของ และหน่วยงานที่รับของ แล้วกด "โหลดรายการที่ต่ำกว่า Max"');
}
// เมื่อเปลี่ยนคลังต้นทาง หรือหน่วยงานที่รับของ ให้ล้างตารางโหลดรายการ
$('#main-warehouse-id, #sub-warehouse-id').on('change', function() {
    $(this).removeClass('is-invalid');
    refreshBelowMaxCard();
    if ($(this).attr('id') === 'main-warehouse-id' && $('#item-table tbody tr').length > 0) {
        Swal.fire({
            title: 'ยืนยันการเปลี่ยนคลัง?',
            text: "รายการวัสดุเดิมจะถูกล้างออกทั้งหมด เนื่องจากวัสดุถูกจำกัดตามคลัง",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ตกลง',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#item-table tbody').empty();
            }
        });
    }
});
if ($('#main-warehouse-id').val()) refreshBelowMaxCard();

// โหลดรายการวัสดุต่ำกว่า Max (คำนวณจากยอดที่หน่วยงานที่รับของ)
$('#btn-load-below-max').on('click', function() {
    var whId = $('#main-warehouse-id').val();
    var subId = $('#sub-warehouse-id').val();
    if (!whId) {
        Swal.fire('คำเตือน', 'กรุณาเลือกคลังที่จ่ายของก่อน', 'warning');
        return;
    }
    if (!subId) {
        Swal.fire('คำเตือน', 'กรุณาเลือกหน่วยงานที่รับของก่อน จะได้คำนวณจากยอดคงเหลือที่หน่วยงานนี้', 'warning');
        return;
    }
    var btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> กำลังโหลด...');
    var url = '$itemsBelowMaxUrl'.replace(/\/$/, '') + '?warehouse_id=' + whId + '&sub_warehouse_id=' + encodeURIComponent(subId);
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(list) {
            var tbody = $('#below-max-tbody').empty();
            if (!list || list.length === 0) {
                $('#below-max-placeholder').show().html('ไม่มีรายการที่หน่วยงานรับของเหลือต่ำกว่า Min (หรือยอดต่ำกว่า Max)').addClass('py-4');
                $('#below-max-table-wrap').hide();
            } else {
                $('#below-max-placeholder').hide();
                $('#below-max-table-wrap').show();
                list.forEach(function(row) {
                    var tr = $('<tr></tr>').data('row', row);
                    tr.append($('<td></td>').html('<input type="checkbox" class="form-check-input below-max-cb" value="1">'));
                    tr.append($('<td></td>').text(row.item_name));
                    tr.append($('<td class="text-center"></td>').text(row.unit_name));
                    tr.append($('<td class="text-end"></td>').text(parseFloat(row.balance_qty).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })));
                    var minMax = (row.min_qty != null ? parseFloat(row.min_qty).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-') + ' / ' + parseFloat(row.max_qty).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    tr.append($('<td class="text-end"></td>').text(minMax));
                    tr.append($('<td class="text-end fw-bold text-success"></td>').text(parseFloat(row.qty_to_reach_max).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })));
                    tbody.append(tr);
                });
            }
        })
        .catch(function() {
            $('#below-max-placeholder').show().html('<span class="text-danger">โหลดไม่สำเร็จ</span>');
            $('#below-max-table-wrap').hide();
        })
        .finally(function() { btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> โหลดรายการที่ต่ำกว่า Max'); });
});

$('#below-max-checkall').on('change', function() {
    $('#below-max-tbody .below-max-cb').prop('checked', this.checked);
});

$('#btn-add-below-max').on('click', function() {
    var selected = $('#below-max-tbody tr').has('input.below-max-cb:checked');
    if (selected.length === 0) {
        Swal.fire('คำเตือน', 'กรุณาเลือกอย่างน้อย 1 รายการ', 'warning');
        return;
    }
    var added = 0, updated = 0;
    selected.each(function() {
        var row = $(this).data('row');
        if (!row) return;
        var code = (row.item_code || '').toString();
        var existing = $('#item-table tbody tr').filter(function() {
            var el = $(this).find('input[name*="[item_code]"], select[name*="[item_code]"]');
            return el.length && (el.val() === code || (el.find('option:selected').val() === code));
        });
        if (existing.length) {
            existing.find('.qty-input').val(row.qty_to_reach_max);
            updated++;
        } else {
            var template = $('#row-template-prefilled').html();
            var html = template
                .replace(/{idx}/g, idx)
                .replace(/{item_code}/g, (row.item_code || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'))
                .replace(/{item_name}/g, (row.item_name || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'))
                .replace(/{unit_name}/g, (row.unit_name || '-').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'))
                .replace(/{qty}/g, row.qty_to_reach_max);
            $('#item-table tbody').append(html);
            idx++;
            added++;
        }
    });
    if (added || updated) {
        Swal.fire('เพิ่มแล้ว', 'เพิ่ม ' + added + ' รายการ, อัปเดตจำนวน ' + updated + ' รายการ', 'success', { timer: 1500 });
    }
});

$(document).on('click', '.remove-item', function() {
    $(this).closest('tr').remove();
});

// เลือกเหตุผลจาก dropdown แล้วใส่ในช่องเหตุผล/วัตถุประสงค์การเบิก
$(document).on('click', '.issue-reason-option', function(e) {
    e.preventDefault();
    var reason = $(this).data('reason');
    if (reason) $('#issue_reason').val(reason);
});

// Submit Form ด้วย AJAX
$('#requisition-form').on('beforeSubmit', function(e) {
    let form = $(this);
    if ($('#item-table tbody tr').length === 0) {
        Swal.fire('คำเตือน', 'กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ', 'warning');
        return false;
    }

    Swal.fire({
        title: 'ส่งใบขอเบิก?',
        text: "ยืนยันการส่งข้อมูลไปยังคลังที่เลือก",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.post(form.attr('action'), form.serialize())
                .done(function(res) { return res; });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value.success) {
            Swal.fire('สำเร็จ', 'บันทึกข้อมูลเรียบร้อย', 'success')
                .then(() => { window.location.href = result.value.redirect; });
        } else if (result.value) {
            Swal.fire('ผิดพลาด', result.value.message, 'error');
        }
    });
    return false;
});
JS;
$this->registerJs($script);
?>