<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\StockOrder */
/* @var $form yii\widgets\ActiveForm */
// ใส่ใน View ไฟล์เดิมของคุณ
\app\assets\TomSelectAsset::register($this);
?>

<div class="container-fluid py-4">
    <?php $form = ActiveForm::begin(['id' => 'receipt-form']); ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-download"></i> บันทึกรับวัสดุเข้าคลัง</h5>
            <?= $form->field($model, 'order_type')->hiddenInput(['value' => 'IN'])->label(false) ?>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4 p-3 bg-light rounded border">
                <div class="col-md-3">
                    <?php // เปลี่ยนจาก status เป็น source_type 
                    ?>
                    <?= $form->field($model, 'source_type')->dropDownList([
                        'PO' => 'รับจากการจัดซื้อ (PO)',
                        'DONATE' => 'รับจากใบรับสินค้า/บริจาค',
                        'INITIAL' => 'รับจากยอดยกมา (Initial Stock)',
                    ], ['class' => 'form-select border-primary', 'prompt' => '-- เลือกประเภท --']) ?>
                </div>
                <div class="col-md-3">
                    <?php // ตั้งเป็น Hidden เพราะหน้าจอนี้คือ 'IN' เสมอ 
                    ?>
                    <?= Html::activeHiddenInput($model, 'order_type', ['value' => 'IN']) ?>
                    <?= $form->field($model, 'order_no')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'order_date')->input('datetime-local', [
                        'value' => $model->order_date
                            ? date('Y-m-d\TH:i', strtotime($model->order_date))
                            : date('Y-m-d\TH:i') // ถ้าเป็น null ให้ใช้เวลาปัจจุบัน
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'main_warehouse_id')->dropDownList($listWarehouse, ['prompt' => '-- เลือกคลัง --', 'class' => 'form-select']) ?>
                </div>
            </div>

            <div class="row g-2 mb-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted">ค้นหาพัสดุ</label>
                    <select id="itemSelector" class="form-select" placeholder="พิมพ์รหัสหรือชื่อพัสดุ...">
                        <option value="">-- ค้นหาพัสดุ --</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary w-100" id="btnAddRow">
                        <i class="bi bi-plus-circle"></i> เพิ่มรายการ
                    </button>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered align-middle" id="inboundTable">
                    <thead class="table-primary text-center">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 25%;">รายการวัสดุ</th>
                            <th style="width: 15%;">Lot Number</th>
                            <th style="width: 15%;">วันหมดอายุ</th>
                            <th style="width: 10%;">จำนวน</th>
                            <th style="width: 10%;">ราคา/หน่วย</th>
                            <th style="width: 15%;">รวม</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <!-- <tbody id="detail-body">
                        <tr id="emptyRow">
                            <td colspan="8" class="text-center py-4 text-muted">ยังไม่มีรายการที่ถูกเพิ่ม</td>
                        </tr>
                    </tbody> -->
                    <tbody id="detail-body">
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $index => $item): ?>
                                <?php
                                // กรณีหน้า Create รุ่นใหม่จะเป็น Model เปล่า item_id จะไม่มีค่า
                                if (!$item->item_id) continue;
                                ?>
                                <tr class="item-row">
                                    <td class="text-center text-muted"><?= $index + 1 ?></td>
                                    <td>
                                        <input type="hidden" name="StockDetail[<?= $index ?>][item_id]" value="<?= $item->item_id ?>">
                                        <strong><?= $item->item->item_name ?? 'ไม่พบชื่อวัสดุ' ?></strong>
                                    </td>
                                    <td><input type="text" name="StockDetail[<?= $index ?>][lot_number]" class="form-control form-control-sm" value="<?= $item->lot_number ?>"></td>
                                    <td><input type="date" name="StockDetail[<?= $index ?>][expiry_date]" class="form-control form-control-sm" value="<?= $item->expiry_date ?>"></td>
                                    <td><input type="number" name="StockDetail[<?= $index ?>][qty]" class="form-control form-control-sm text-center qty-input" value="<?= $item->qty ?>" min="0.01" step="0.01"></td>
                                    <td><input type="number" name="StockDetail[<?= $index ?>][unit_price]" class="form-control form-control-sm text-end price-input" value="<?= $item->unit_price ?>" step="0.01"></td>
                                    <td class="text-end fw-bold row-total"><?= number_format($item->qty * $item->unit_price, 2) ?></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove border-0"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <tr id="emptyRow" style="<?= !empty($items) && count($items) > 0 && $items[0]->item_id ? 'display:none' : '' ?>">
                            <td colspan="8" class="text-center py-4 text-muted">ยังไม่มีรายการที่ถูกเพิ่ม</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold text-end">
                        <tr>
                            <td colspan="6">ยอดรวมสุทธิ</td>
                            <td id="grandTotal">0.00</td>
                            <td>บาท</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-end mt-4">
                <?= Html::submitButton('<i class="bi bi-save"></i> บันทึกข้อมูล', ['class' => 'btn btn-success btn-lg px-5 shadow']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php
$itemListUrl = Url::to(['stock-item/item-list']);
$js = <<< JS
    let rowIndex = 0;
    let itemSelect; 

    $(document).ready(function() {

    // --- 2. การดักจับ Event บันทึกข้อมูลด้วย SweetAlert2 ---
        
        $('#receipt-form').on('beforeSubmit', function(e) {
            // เช็คก่อนว่ามีรายการในตารางไหม
            if ($('.item-row').length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ไม่สามารถบันทึกได้',
                    text: 'กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ',
                    confirmButtonColor: '#3085d6',
                });
                return false; // หยุดการทำงาน
            }

            // แสดง Confirmation Dialog
            Swal.fire({
                title: 'ยืนยันการบันทึกข้อมูล?',
                text: "ตรวจสอบความถูกต้องของจำนวนและราคาก่อนยืนยัน",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754', // สีเขียว Success
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="bi bi-check-lg"></i> ใช่, บันทึกเลย!',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true // ให้ปุ่มยกเลิกอยู่ซ้าย
            }).then((result) => {
                if (result.isConfirmed) {
                    // แสดง Loading ระหว่างรอ Server ประมวลผล
                    Swal.fire({
                        title: 'กำลังบันทึก...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // ส่งฟอร์มจริงๆ ไปที่ Controller
                    var form = $('#receipt-form');
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        success: function(response) {
                            // ถ้า Controller ส่งผลลัพธ์กลับมาสำเร็จ
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: 'บันทึกข้อมูลรับเข้าเรียบร้อยแล้ว',
                                timer: 2000,
                                showConfirmButton: true
                            }).then(() => {
                                // ไปหน้า View หรือ Index ตามต้องการ
                                // window.location.href = response.redirect || '/stock-order/index';
                            });
                        },
                        error: function() {
                            Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่', 'error');
                        }
                    });
                }
            });

            return false; // ป้องกันการ Submit แบบปกติ (เพราะเราใช้ AJAX หรือ Confirm ก่อน)
        });



        function calculateTotal() {
            let grandTotal = 0;
            $('.row-total').each(function() {
                let val = $(this).text().replace(/,/g, '');
                grandTotal += parseFloat(val) || 0;
            });
            $('#grandTotal').text(grandTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }

        // 2. ตั้งค่า TomSelect (ใส่โค้ด AJAX กลับเข้าไป)
        itemSelect = new TomSelect('#itemSelector', {
            valueField: 'item_code',
            labelField: 'item_name',
            searchField: ['item_name', 'item_code'],
            load: function(query, callback) {
                if (!query.length) return callback();
                
                // ใช้ตัวแปร $itemListUrl จาก PHP
                var url = '{$itemListUrl}?q=' + encodeURIComponent(query);

                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        // json.results ต้องตรงกับที่ Controller ส่งมา
                        callback(json.results); 
                    }).catch(() => {
                        callback();
                    });
            },
            render: {
                option: function(data, escape) {
                    return `<div class="py-2 d-flex justify-content-between">
                        <div>
                            <span class="fw-bold">\${escape(data.item_name)}</span>
                        </div>
                        <span class="badge bg-secondary">\${escape(data.item_code)}</span>
                    </div>`;
                },
                item: function(data, escape) {
                    return `<div>\${escape(data.item_name)} <small class="text-muted">[\${escape(data.item_code)}]</small></div>`;
                }
            },
            placeholder: 'พิมพ์ชื่อพัสดุ หรือ รหัส...',
            maxOptions: 50,
            shouldLoad: function(query) {
                return query.length >= 2;
            }
        });

        // 3. ปุ่มเพิ่มแถว (เหมือนเดิม)
        $('#btnAddRow').click(function() {
            const itemId = itemSelect.getValue();
            if (!itemId) return alert('กรุณาเลือกวัสดุ');

            const itemData = itemSelect.options[itemId];
            if (!itemData) return alert('ไม่พบข้อมูลวัสดุ');

            $('#emptyRow').hide();

            const row = `
                <tr class="item-row">
                    <td class="text-center text-muted">\${rowIndex + 1}</td>
                    <td>
                        <input type="hidden" name="StockDetail[\${rowIndex}][item_id]" value="\${itemId}">
                        <strong>\${itemData.item_name}</strong>
                    </td>
                    <td><input type="text" name="StockDetail[\${rowIndex}][lot_number]" class="form-control form-control-sm"></td>
                    <td><input type="date" name="StockDetail[\${rowIndex}][expiry_date]" class="form-control form-control-sm"></td>
                    <td><input type="number" name="StockDetail[\${rowIndex}][qty]" class="form-control form-control-sm text-center qty-input" value="1" min="1" step="0.01"></td>
                    <td><input type="number" name="StockDetail[\${rowIndex}][unit_price]" class="form-control form-control-sm text-end price-input" value="0.00" step="0.01"></td>
                    <td class="text-end fw-bold row-total">0.00</td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove border-0"><i class="bi bi-trash"></i></button></td>
                </tr>
            `;

            $('#detail-body').append(row);
            rowIndex++;
            calculateTotal(); 
            itemSelect.clear();
        });

        // 4. Event input และ 5. ปุ่มลบ (เหมือนโค้ดเดิมของคุณ)
        $(document).on('input', '.qty-input, .price-input', function() {
            const row = $(this).closest('tr');
            const qty = parseFloat(row.find('.qty-input').val()) || 0;
            const price = parseFloat(row.find('.price-input').val()) || 0;
            const total = qty * price;
            
            row.find('.row-total').text(total.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            calculateTotal();
        });

        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            if ($('.item-row').length === 0) $('#emptyRow').show();
            calculateTotal();
        });

    });
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>