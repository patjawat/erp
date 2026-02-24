<?php

use app\modules\inventoryV2\models\Warehouse;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\web\View;
use Yiisoft\Arrays\ArrayHelper;

/* @var $this yii\web\View */

$mainWarehouseList = ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(), 'id', 'warehouse_name');
$subWarehouseList = ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'SUB'])->all(), 'id', 'warehouse_name');

$this->title = 'สร้างใบเบิกพัสดุใหม่';
?>

<?php \app\assets\TomSelectAsset::register($this); ?>

<style>
    /* ปรับแต่งสไตล์ดั้งเดิมให้รองรับ Tom-Select */
    .select-step {
        border-left: 5px solid #0d6efd;
    }

    .bg-primary-soft {
        background-color: rgba(13, 110, 253, 0.05);
    }

    /* แก้ไขขนาด Tom-Select ให้เข้ากับ Bootstrap control */
    .ts-control {
        border-radius: 0.375rem !important;
        padding: 0.5rem 0.75rem !important;
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
    }

    .ts-wrapper.single .ts-control {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
    }
</style>

<?php $form = ActiveForm::begin(); ?>
<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 text-primary fw-bold">
                <i class="bi bi-cart-plus-fill me-2"></i>สร้างใบเบิกพัสดุใหม่
            </h5>
        </div>
        <div class="card-body p-4">

            <div class="row g-3 mb-4 p-3 bg-primary-soft rounded-4 select-step">
                <div class="col-md-6">
                    <div class="col-md-6">
                        <label class="form-label fw-bold"><i class="bi bi-house-up me-1"></i> 1. เลือกคลังหลัก (เบิกจาก...)</label>
                        <select class="form-select" id="mainWarehouseSelector" placeholder="-- กรุณาเลือกคลังหลักที่มีพัสดุ --">
                            <option value="">-- กรุณาเลือกคลังหลักที่มีพัสดุ --</option>
                            <?php foreach ($mainWarehouseList as $id => $name): ?>
                                <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-house-down me-1"></i> 2. หน่วยงานผู้เบิก (คลังย่อย)</label>
                    <select class="form-select" id="subWarehouse" placeholder="-- เลือกแผนกผู้เบิก --">
                        <option value="">-- เลือกแผนกผู้เบิก --</option>
                           <?php foreach ($subWarehouseList as $id => $name): ?>
                                <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="requestSection" style="display: none;">
                <div class="row g-2 mb-3 align-items-end p-3 bg-light rounded-4 border">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">3. เลือกวัสดุที่ต้องการ</label>
                        <select id="itemSelector" placeholder="-- เลือกวัสดุ --"></select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">จำนวน</label>
                        <input type="number" class="form-control text-center shadow-sm" id="itemQty" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100 py-2 shadow-sm" id="btnAddItem">
                            <i class="bi bi-plus-lg me-1"></i> เพิ่มรายการ
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border rounded-3 overflow-hidden" id="reqTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="ps-3">#</th>
                                <th>รายการพัสดุ</th>
                                <th width="15%" class="text-center">จำนวนที่เบิก</th>
                                <th width="10%">หน่วย</th>
                                <th width="5%" class="pe-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="emptyRow">
                                <td colspan="5" class="text-center py-5 text-muted italic">ยังไม่มีรายการพัสดุในใบเบิกนี้</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <button type="button" class="btn btn-success btn-lg px-5 shadow rounded-3 me-2" id="btnSubmit">
                        ส่งใบเบิกไปที่คลังหลัก
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-4">ยกเลิก</button>
                </div>
            </div>

        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
<?php
$js = <<< JS
$(document).ready(function() {
    
    // --- 0. ตั้งค่า Tom-Select ---
    const config = { 
        create: false, 
        allowEmptyOption: true,
        render: {
            no_results: function(data, escape) {
                return '<div class="no-results">ไม่พบข้อมูลที่ค้นหา...</div>';
            }
        }
    };

    const mainWHSelect = new TomSelect('#mainWarehouseSelector', config);
    const subWHSelect = new TomSelect('#subWarehouse', config);
    const itemSelect = new TomSelect('#itemSelector', config);

    // 1. เมื่อเลือกคลังหลัก
    $('#mainWarehouseSelector').change(function() {
        const whId = $(this).val();
        const whName = $(this).find('option:selected').text();

        if (whId !== "") {
            $('#requestSection').slideDown();
            
            // ล้าง Options ใน Tom-Select สินค้า
            itemSelect.clear();
            itemSelect.clearOptions();
            
            // จำลองการดึงสินค้าจากคลังหลัก
            let items = [];
            if(whId === "WH01") {
                items = [
                    {value: "1", text: "ถุงมือตรวจโรค (คงเหลือ 500)", name: "ถุงมือตรวจโรค", unit: "กล่อง"},
                    {value: "2", text: "หน้ากาก N95 (คงเหลือ 1,200)", name: "หน้ากาก N95", unit: "ชิ้น"}
                ];
            } else if(whId === "WH02") {
                items = [
                    {value: "3", text: "กระดาษ A4 (คงเหลือ 200)", name: "กระดาษ A4", unit: "รีม"},
                    {value: "4", text: "ปากกาลูกลื่น (คงเหลือ 50)", name: "ปากกาลูกลื่น", unit: "ด้าม"}
                ];
            }
            
            itemSelect.addOption(items);
            itemSelect.refreshOptions(false);
            
            // ล้างรายการในตารางถ้ามีการเปลี่ยนคลัง
            $('#reqTable tbody .item-row').remove();
            $('#emptyRow').show();
        } else {
            $('#requestSection').slideUp();
        }
    });

    // 2. เมื่อกดปุ่มเพิ่มรายการ
    $('#btnAddItem').click(function() {
        const id = itemSelect.getValue();
        const selectedOption = itemSelect.options[id];

        if (!id) {
            Swal.fire('แจ้งเตือน', 'กรุณาเลือกวัสดุก่อนครับ', 'warning');
            return;
        }

        const name = selectedOption.name;
        const unit = selectedOption.unit;
        const qty = $('#itemQty').val();

        $('#emptyRow').hide();
        
        const rowCount = $('#reqTable tbody tr.item-row').length + 1;
        // เพิ่ม data-id เพื่อใช้ตอนคืนค่าเข้า Dropdown
        const row = `
            <tr class="item-row" data-id="\${id}" data-name="\${name}" data-unit="\${unit}">
                <td class="ps-3">\${rowCount}</td>
                <td class="fw-bold">\${name}</td>
                <td><input type="number" class="form-control form-control-sm text-center mx-auto" style="width:80px" value="\${qty}"></td>
                <td>\${unit}</td>
                <td class="pe-3"><button class="btn btn-sm btn-outline-danger btn-del border-0"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        $('#reqTable tbody').append(row);
        
        // --- ส่วนที่เพิ่มเข้ามา: ลบรายการที่เลือกแล้วออกจากตัวเลือก ---
        itemSelect.removeOption(id); 
        itemSelect.clear(); // ล้างค่าหน้าจอ
        
        $('#itemQty').val(1);
    });

    // 3. เมื่อลบแถว
    $(document).on('click', '.btn-del', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const name = row.data('name');
        const unit = row.data('unit');

        // --- ส่วนที่เพิ่มเข้ามา: คืนค่ารายการกลับเข้าสู่ Tom-Select ---
        itemSelect.addOption({
            value: id,
            text: `\${name} (คืนค่าจากรายการที่ลบ)`,
            name: name,
            unit: unit
        });
        itemSelect.refreshOptions(false);

        row.remove();
        
        if ($('#reqTable tbody tr.item-row').length === 0) {
            $('#emptyRow').show();
        }
        
        // อัปเดตเลขลำดับ #
        $('#reqTable tbody tr.item-row').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    });

    // 4. บันทึกใบเบิก
    $('#btnSubmit').click(function() {
        if ($('#reqTable tbody tr.item-row').length === 0) {
            Swal.fire('ข้อผิดพลาด', 'ยังไม่มีรายการเบิกครับ', 'error');
            return;
        }

        const sourceWH = $('#mainWarehouseSelector option:selected').text();
        const targetDept = $('#subWarehouse option:selected').text();

        Swal.fire({
            title: 'ยืนยันการส่งใบเบิก?',
            html: `เบิกจาก: <b>\${sourceWH}</b><br>ไปยัง: <b>\${targetDept}</b>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'ยืนยันส่งใบเบิก',
            cancelButtonText: 'แก้ไขข้อมูล'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('ส่งใบเบิกสำเร็จ!', 'ระบบส่งรายการให้คลังหลักเพื่อรอจ่ายของแล้ว', 'success')
                .then(() => location.reload());
            }
        });
    });

    // 5. เมื่อกด Enter ที่ช่องจำนวน (#itemQty)
    $('#itemQty').on('keypress', function(e) {
        if (e.which === 13) { // 13 คือปุ่ม Enter
            e.preventDefault(); // ป้องกันการ Submit Form จริง
            $('#btnAddItem').click(); // สั่งให้ไปรัน Logic ของปุ่มเพิ่มรายการ
            
            // ส่งโฟกัสกลับไปที่ช่องเลือกวัสดุ (Tom-Select)
            itemSelect.focus(); 
        }
    });

    // 6. เสริมนิดหน่อย: เมื่อเลือกพัสดุเสร็จ ให้กระโดดไปที่ช่องจำนวนทันที
    itemSelect.on('change', function() {
        if (this.getValue() !== "") {
            $('#itemQty').focus().select(); // focus และคลุมดำตัวเลขเดิมให้พิมพ์ทับได้เลย
        }
    });


});
JS;
$this->registerJS($js, View::POS_READY);
?>