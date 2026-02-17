<?php
use app\models\Item;
use app\modules\inventory\models\Warehouse;
use app\modules\inventoryV2\models\StockItem;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

// 1. ดึงคลังหลัก (สมมติ ID=1 คือคลังหลัก)
$mainWarehouse = Warehouse::findOne(1);
// 2. ดึงรายการคลังย่อยทั้งหมด (สำหรับเลือกคลังผู้เบิก)
$subWarehouses = ArrayHelper::map(Warehouse::find()->where(['!=', 'id', 1])->all(), 'id', 'warehouse_name');
// 3. รายการพัสดุ
$items = ArrayHelper::map(StockItem::find()->all(), 'id', 'item_name');
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
                    <?= $form->field($model, 'from_warehouse_id')->hiddenInput(['value' => 1])->label(false) ?>
                    <label class="control-label">เบิกจากคลัง</label>
                    <input type="text" class="form-control" value="<?= $mainWarehouse->warehouse_name ?>" readonly>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'to_warehouse_id')->dropDownList($subWarehouses, ['prompt' => '--- เลือกคลังผู้เบิก ---']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'order_date')->textInput(['type' => 'date', 'value' => date('Y-m-d')]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>รายการวัสดุที่ต้องการเบิก</h6>
            <button type="button" id="add-item" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="bi bi-plus-lg"></i> เพิ่มวัสดุ
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="item-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60%">ชื่อวัสดุ</th>
                        <th style="width: 25%">จำนวนที่ขอเบิก</th>
                        <th style="width: 15%" class="text-center">ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>

    <div class="form-group mt-4 d-flex justify-content-end gap-2">
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-light px-4']) ?>
        <?= Html::submitButton('ส่งคำขอเบิก', ['class' => 'btn btn-primary px-4']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>


<table class="d-none">
    <tbody id="row-template">
        <tr>
            <td>
                <select name="StockDetail[{idx}][item_code]" class="form-control tom-select-ajax" required>
                    <option value="">พิมพ์ชื่อวัสดุเพื่อค้นหา...</option>
                </select>
            </td>
            <td>
                <input type="number" name="StockDetail[{idx}][qty]" class="form-control qty-input" min="1" step="0.01" required placeholder="0.00">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-link text-danger remove-item"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    </tbody>
</table>
<?php
// ลงทะเบียน Assets
$this->registerCssFile('https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js', [
    'depends' => [\yii\web\JqueryAsset::class]
]);

$script = <<< JS
let idx = 0;


// แก้ไขส่วนการส่งข้อมูลผ่าน AJAX
$('#requisition-form').on('beforeSubmit', function(e) {
    let form = $(this);
    
    // ตรวจสอบว่ามีการเพิ่มรายการวัสดุอย่างน้อย 1 รายการหรือไม่
    if ($('#item-table tbody tr').length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'คำเตือน',
            text: 'กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ',
            confirmButtonColor: '#3085d6',
        });
        return false;
    }

    Swal.fire({
        title: 'ยืนยันการส่งใบขอเบิก?',
        text: "เมื่อส่งแล้ว จะต้องรอคลังหลักอนุมัติเพื่อตัดสต็อก",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="bi bi-check-lg"></i> ยืนยันส่งข้อมูล',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // แสดง Loading ขณะรอ Server ตอบกลับ
            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.post(form.attr('action'), form.serialize())
                .done(function(res) {
                    if(res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'บันทึกสำเร็จ!',
                            text: 'ใบขอเบิกถูกส่งไปยังคลังหลักแล้ว',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // window.location.href = res.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: res.message,
                        });
                    }
                })
                .fail(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้',
                    });
                });
        }
    });

    return false; // บล็อกการ Submit ปกติของ HTML
});

function initTomSelect(elementId) {
    let ts = new TomSelect('#' + elementId, {
        valueField: 'item_code',
        labelField: 'item_name',
        searchField: ['item_name', 'item_code'], // ค้นหาได้ทั้งชื่อและรหัส
        placeholder: 'พิมพ์ชื่อหรือรหัสวัสดุ...',
        dropdownParent: 'body',
        load: function(query, callback) {
            if (!query.length) return callback();
            let url = '/inventory-v2/stock-item/item-list?q=' + encodeURIComponent(query);
            
            fetch(url)
                .then(response => response.json())
                .then(json => {
                    // ปรับตรงนี้ให้ดึงจาก json.results ตามที่ Controller ส่งมา
                    callback(json.results); 
                }).catch(() => {
                    callback();
                });
        },
        render: {
            option: function(item, escape) {
                return '<div class="py-2 d-flex">' +
                    '<div>' +
                        '<div class="mb-0 fw-bold">' + escape(item.item_name) + '</div>' +
                        '<small class="text-muted">รหัส: ' + escape(item.item_code) + '</small>' +
                    '</div>' +
                '</div>';
            },
            item: function(item, escape) {
                return '<div>' + escape(item.item_name) + ' (' + escape(item.item_code) + ')</div>';
            }
        },
        onChange: function(value) {
            if(value) {
                $(this.wrapper).closest('tr').find('.qty-input').focus().select();
            }
        }
    });
    return ts;
}

function addItem() {
    let template = $('#row-template').html();
    let rowHtml = template.replace(/{idx}/g, idx);
    let newRow = $(rowHtml).attr('id', 'row-' + idx);
    
    $('#item-table tbody').append(newRow);
    
    let selectId = 'select-item-' + idx;
    newRow.find('.tom-select-ajax').attr('id', selectId);
    
    let ts = initTomSelect(selectId);
    
    setTimeout(() => { ts.focus(); }, 50);
    idx++;
}

addItem();

$(document).on('keydown', '.qty-input', function(e) {
    if (e.which == 13) { 
        e.preventDefault();
        if ($(this).val() > 0) {
            addItem();
        }
    }
});

$(document).on('click', '.remove-item', function() {
    if ($('#item-table tbody tr').length > 1) {
        $(this).closest('tr').remove();
    }
});
JS;
$this->registerJs($script);
?>