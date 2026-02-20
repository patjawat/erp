<?php

use app\models\Item;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\models\StockItem;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

// 1. ดึงคลังหลัก (สมมติ ID=1 คือคลังหลัก)
$mainWarehouse = Warehouse::findOne(1);
$warehouseList = ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name');
// 2. ดึงรายการคลังย่อยทั้งหมด (สำหรับเลือกคลังผู้เบิก)
$subWarehouses = ArrayHelper::map(Warehouse::find()->where(['!=', 'id', 1])->all(), 'id', 'warehouse_name');
// 3. รายการพัสดุ
$items = ArrayHelper::map(StockItem::find()->all(), 'id', 'item_name');
?>
<?php
// ... (คงส่วน PHP ด้านบนไว้เหมือนเดิม แต่ลบ $items ออกเพราะเราใช้ AJAX) ...
?>

<div class="requisition-form">
    <?php $form = ActiveForm::begin(['id' => 'requisition-form']); ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'order_no')->textInput(['readonly' => true, 'placeholder' => 'REQ-AUTO']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'sub_warehouse_id')->hiddenInput(['value' => 1])->label(false) ?>
                    <label class="control-label">หน่วยงานผู้เบิก</label>
                    <input type="text" class="form-control bg-white" value="<?= $mainWarehouse->warehouse_name ?>" readonly>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'main_warehouse_id')->dropDownList($warehouseList, [
                        'id' => 'main-warehouse-id',
                        'prompt' => '-- เลือกคลังต้นทาง --',
                        'class' => 'form-select fw-bold border-primary shadow-sm'
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'order_date')->textInput(['type' => 'date', 'value' => date('Y-m-d')]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-box-seam me-2"></i>รายการวัสดุที่ต้องการเบิก</h6>
            <button type="button" id="add-item" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg"></i> เพิ่มวัสดุ
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="item-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60%">ชื่อวัสดุ (เฉพาะที่มีในคลังที่เลือก)</th>
                        <th style="width: 25%" class="text-center">จำนวนที่ขอเบิก</th>
                        <th style="width: 15%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>

    <div class="form-group mt-4 d-flex justify-content-end gap-2">
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-light px-4 border']) ?>
        <?= Html::submitButton('<i class="bi bi-send-fill me-1"></i> ส่งคำขอเบิก', ['class' => 'btn btn-primary px-4 shadow-sm']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<table class="d-none">
    <tbody id="row-template">
        <tr class="item-row">
            <td>
                <select name="StockDetail[{idx}][item_code]" class="item-select-ajax" required></select>
            </td>
            <td>
                <input type="number" name="StockDetail[{idx}][qty]" class="form-control text-center qty-input fw-bold" min="0.01" step="0.01" required placeholder="0.00">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-item"><i class="bi bi-trash fs-5"></i></button>
            </td>
        </tr>
    </tbody>
</table>

<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$getItemInWhUrl = Url::to(['/inventory-v2/stock-item/get-items-by-warehouse']);

$script = <<< JS
let idx = 0;

// 1. ฟังก์ชันสร้าง TomSelect
function initItemSelect(elementId) {
    let whId = $('#main-warehouse-id').val();
    
    return new TomSelect('#' + elementId, {
        valueField: 'item_code',
        labelField: 'item_name',
        searchField: ['item_name', 'item_code'],
        placeholder: 'พิมพ์ชื่อหรือรหัสวัสดุ...',
        load: function(query, callback) {
            let currentWhId = $('#main-warehouse-id').val();
            if (!query.length || !currentWhId) return callback();
            
            let url = '$getItemInWhUrl' + '?warehouse_id=' + currentWhId + '&q=' + encodeURIComponent(query);
            fetch(url)
                .then(response => response.json())
                .then(json => {
                    // ปรับตามโครงสร้าง JSON ของคุณ (ถ้า Controller ส่งตรงๆ ไม่ต้อง .results)
                    callback(json); 
                }).catch(() => callback());
        },
        render: {
            option: function(item, escape) {
                return `<div class="py-1">
                            <div class="fw-bold">\${escape(item.item_name)}</div>
                            <small class="text-muted">รหัส: \${escape(item.item_code)}</small>
                        </div>`;
            }
        },
        onChange: function(value) {
            if(value) {
                $(this.wrapper).closest('tr').find('.qty-input').focus();
            }
        }
    });
}

// 2. ฟังก์ชันเพิ่มแถว
function addItem() {
    let whId = $('#main-warehouse-id').val();
    if (!whId) {
        Swal.fire('คำเตือน', 'กรุณาเลือกคลังต้นทางก่อนเพิ่มรายการ', 'warning');
        $('#main-warehouse-id').addClass('is-invalid').focus();
        return;
    }

    let template = $('#row-template').html();
    let rowHtml = template.replace(/{idx}/g, idx);
    let newRow = $(rowHtml);
    
    $('#item-table tbody').append(newRow);
    
    let selectId = 'select-item-' + idx;
    newRow.find('.item-select-ajax').attr('id', selectId);
    
    initItemSelect(selectId);
    idx++;
}

// 3. Event Listeners
$(document).on('click', '#add-item', function(e) {
    e.preventDefault();
    addItem();
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
JS;
$this->registerJs($script);
?>