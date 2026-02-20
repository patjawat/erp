<?php

use app\components\AppHelper;
use app\modules\inventoryV2\models\Warehouse;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

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
                    <?= $form->field($model, 'order_date', ['labelOptions' => ['label' => 'วันที่ขอเบิก']])->textInput([
                        'class' => 'form-control',
                        'id' => 'requisition-order_date',
                        'placeholder' => 'เลือกวันที่',
                        'value' => $model->order_date ? AppHelper::convertToThai($model->order_date) : '',
                    ]) ?>
                </div>
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
                </tbody>
            </table>
    </div>

    <div class="form-group mt-4 d-flex justify-content-end gap-2">
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::submitButton('<i class="bi bi-send-fill me-1"></i> ส่งคำขอเบิก', ['class' => 'btn btn-primary']) ?>
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
</table>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css');
$this->registerCss(<<<CSS
.ts-dropdown { z-index: 1060 !important; }
.requisition-form #item-table td:first-child { overflow: visible; position: relative; }
.requisition-form #item-table .ts-wrapper { position: relative; }
.requisition-form #item-table .ts-control { min-height: 38px; }
CSS
);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$getItemInWhUrl = Url::to(['/inventory-v2/stock-item/get-items-by-warehouse']);

$script = <<< JS
let idx = 0;

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
        valueField: 'item_code',
        labelField: 'item_name',
        searchField: ['item_name', 'item_code'],
        placeholder: 'พิมพ์ชื่อหรือรหัสวัสดุ...',
        noResults: function() { return 'ไม่มีรายการหรือเลือกครบแล้ว'; },
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

// เมื่อเปลี่ยนคลังต้นทาง ให้ล้างรายการเดิม
$('#main-warehouse-id').on('change', function() {
    $(this).removeClass('is-invalid');
    if ($('#item-table tbody tr').length > 0) {
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
            } else {
                // ย้อนกลับไปค่าเดิม (ถ้าจำเป็น)
            }
        });
    }
});

$(document).on('click', '.remove-item', function() {
    $(this).closest('tr').remove();
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
if (typeof thaiDatepicker === 'function') {
    thaiDatepicker('#requisition-order_date');
}
JS;
$this->registerJs($script);
?>