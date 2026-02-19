<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

// ลงทะเบียน Tom-select (ถ้ายังไม่ได้ลงใน Asset)
$this->registerCssFile('https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js');
$this->title = 'ดำเนินการจ่ายพัสดุ (Issue Process) - ' . $model->order_no;
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-box-seam fs-4"></i>
                <h5 class="mb-0 text-white">บันทึกการจ่ายพัสดุ: <?= Html::encode($model->order_no) ?></h5>
            </div>
            <div>
                <button type="button" class="btn btn-light btn-sm fw-bold shadow-sm" id="btnAddItem">
                    <i class="bi bi-plus-circle-fill text-primary"></i> เพิ่มพัสดุอื่นเพิ่มเติม
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4 bg-light p-3 rounded mx-0 border">
                <div class="col-md-4">
                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem;">หน่วยงานที่เบิก</small>
                    <strong class="text-primary"><?= Html::encode($model->subWarehouse->warehouse_name ?? '-') ?></strong>
                </div>
                <div class="col-md-4 text-center border-start border-end">
                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem;">อ้างอิง</small>
                    <strong><?= Html::encode($model->source_type ?? '-') ?></strong>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem;">คลังต้นทาง</small>
                    <span class="badge bg-secondary"><?= Html::encode($model->mainWarehouse->warehouse_name ?? 'คลังหลัก') ?></span>
                </div>
            </div>

            <form id="issue-process-form">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle border" id="issueTable">
                        <thead class="table-dark text-center">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%" class="text-start">รายการพัสดุ</th>
                                <th width="10%">ขอเบิก</th>
                                <th width="12%">จ่ายจริง</th>
                                <th width="25%">ตัดจาก Lot (คลังหลัก)</th>
                                <th width="12%">รวมมูลค่า</th>
                                <th width="8%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($model->stockDetails as $index => $detail): ?>
                                <?php 
                                    $availableLots = \app\modules\inventoryV2\models\StockDetail::find()
                                        ->joinWith('stockOrder')
                                        ->where([
                                            'stock_detail.item_code' => $detail->item_code,
                                            'stock_order.main_warehouse_id' => $model->main_warehouse_id,
                                            'stock_order.order_type' => 'IN'
                                        ])
                                        ->andWhere(['>', 'remain_qty', 0])
                                        ->all();
                                ?>
                                <tr class="item-row" data-index="<?= $index ?>">
                                    <td class="text-center text-muted"><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= Html::encode($detail->item->item_name) ?></strong><br>
                                        <small class="text-muted">Code: <?= Html::encode($detail->item_code) ?></small>
                                        <input type="hidden" name="Issue[<?= $index ?>][detail_id]" value="<?= $detail->id ?>">
                                        <input type="hidden" name="Issue[<?= $index ?>][item_code]" value="<?= $detail->item_code ?>">
                                    </td>
                                    <td class="text-center fw-bold text-secondary"><?= number_format($detail->qty, 2) ?></td>
                                    <td>
                                        <input type="number" name="Issue[<?= $index ?>][qty_issued]" 
                                               class="form-control text-center fw-bold border-primary qty-issued" 
                                               value="<?= $detail->qty ?>" min="0" max="<?= $detail->qty ?>" step="0.01">
                                    </td>
                                    <td>
                                        <select name="Issue[<?= $index ?>][lot_number]" class="form-select border-warning lot-selector">
                                            <?php foreach ($availableLots as $lotIn): ?>
                                                <option value="<?= $lotIn->lot_number ?>" data-stock="<?= $lotIn->remain_qty ?>" data-price="<?= $lotIn->unit_price ?>">
                                                    LOT: <?= $lotIn->lot_number ?> (เหลือ <?= number_format($lotIn->remain_qty, 2) ?>) [@<?= number_format($lotIn->unit_price, 2) ?>]
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="text-end fw-bold text-primary"><span class="row-total">0.00</span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-cancel-item"><i class="bi bi-trash"></i></button>
                                        <button type="button" class="btn btn-link btn-sm btn-restore-item d-none">คืน</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="5" class="text-end fw-bold text-uppercase">Grand Total:</td>
                                <td class="text-end fw-bold text-danger fs-5" id="grand-total">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>

            <div class="text-end mt-4 pt-3 border-top">
                <?= Html::a('กลับ', ['index'], ['class' => 'btn btn-light border px-4 me-2']) ?>
                <button type="button" class="btn btn-success btn-lg px-5 shadow" id="btnSubmitIssue">
                    <i class="bi bi-check-all"></i> บันทึกการจ่ายพัสดุ
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// URL สำหรับดึง Lot เมื่อเลือกสินค้าใหม่ (ต้องไปสร้าง Action นี้ใน Controller)
$getLotUrl = Url::to(['get-available-lots', 'warehouse_id' => $model->main_warehouse_id]);
// URL สำหรับค้นหาสินค้า (แนะนำใช้ Select2 หรือดึงรายการสินค้ามาพักไว้)
$itemsJson = json_encode(\app\modules\inventoryV2\models\StockItem::find()->select(['item_code', 'item_name'])->asArray()->all());

$getLotUrl = Url::to(['get-available-lots', 'warehouse_id' => $model->main_warehouse_id]);
// เตรียมข้อมูลสินค้าสำหรับ Tom-select
$items = \app\modules\inventoryV2\models\StockItem::find()
    ->select(['item_code as value', 'item_name as text'])
    ->asArray()
    ->all();

$itemsJson = json_encode($items);
$js = <<< JS
$(document).ready(function() {
    let itemIndex = $('.item-row').length;
    const allItems = $itemsJson;
    const itemList = $itemsJson;


// ฟังก์ชันสำหรับสร้าง Tom-select ให้กับแถวใหม่
    function initTomSelect(index) {
        new TomSelect(`#select-item-\${index}`, {
            options: itemList,
            placeholder: "พิมพ์ชื่อหรือรหัสพัสดุ...",
            allowEmptyOption: true,
            create: false,
            onChange: function(itemCode) {
                if(!itemCode) return;
                loadLots(itemCode, index);
            }
        });
    }

    // ฟังก์ชันดึง Lot เมื่อเลือกสินค้า
    function loadLots(itemCode, index) {
        let lotSelect = $(`#lot-select-\${index}`);
        lotSelect.html('<option>กำลังโหลด...</option>').prop('disabled', true);

        $.ajax({
            url: '$getLotUrl',
            data: { item_code: itemCode },
            success: function(data) {
                lotSelect.empty();
                if (data.length > 0) {
                    data.forEach(lot => {
                        lotSelect.append(`<option value="\${lot.lot_number}" data-stock="\${lot.remain_qty}" data-price="\${lot.unit_price}">
                            LOT: \${lot.lot_number} (เหลือ \${lot.remain_qty}) [@\${lot.unit_price}]
                        </option>`);
                    });
                    lotSelect.prop('disabled', false);
                } else {
                    lotSelect.append('<option value="">ของหมดสต็อก</option>');
                }
                calculateTotal();
            }
        });
    }

    // --- กดปุ่มเพิ่มรายการใหม่ ---
    $(document).off('click', '#btnAddItem').on('click', '#btnAddItem', function(e) {
        let newRow = `
        <tr class="item-row table-info" id="row-\${itemIndex}">
            <td class="text-center text-primary fw-bold">NEW</td>
            <td>
                <select id="select-item-\${itemIndex}" name="Issue[\${itemIndex}][item_code]" class="ts-select"></select>
                <input type="hidden" name="Issue[\${itemIndex}][detail_id]" value="new">
            </td>
            <td class="text-center text-muted">-</td>
            <td>
                <input type="number" name="Issue[\${itemIndex}][qty_issued]" class="form-control text-center fw-bold border-primary qty-issued" value="1" min="0.01" step="0.01">
            </td>
            <td>
                <select id="lot-select-\${itemIndex}" name="Issue[\${itemIndex}][lot_number]" class="form-select border-warning lot-selector" required>
                    <option value="">-- เลือกพัสดุก่อน --</option>
                </select>
            </td>
            <td class="text-end fw-bold text-primary"><span class="row-total">0.00</span></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
        
        $('#issueTable tbody').append(newRow);
        initTomSelect(itemIndex); // เรียกใช้ Tom-select ทันทีที่สร้างแถว
        itemIndex++;
    });

    // การลบแถว
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        calculateTotal();
    });

    function calculateTotal() {
        let grandTotal = 0;
        $('.item-row').each(function() {
            let row = $(this);
            let qtyInput = row.find('.qty-issued');
            if (!qtyInput.prop('disabled')) {
                let qty = parseFloat(qtyInput.val()) || 0;
                let option = row.find('.lot-selector option:selected');
                let price = parseFloat(option.data('price')) || 0;
                let total = qty * price;
                row.find('.row-total').text(total.toLocaleString(undefined, {minimumFractionDigits: 2}));
                grandTotal += total;
            } else {
                row.find('.row-total').text('0.00');
            }
        });
        $('#grand-total').text(grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2}));
    }

    calculateTotal();
    $(document).on('input', '.qty-issued', calculateTotal);
    $(document).on('change', '.lot-selector', calculateTotal);

    // --- เพิ่มรายการใหม่ ---
    $('#btnAddItem').click(function() {
        let options = allItems.map(item => `<option value="\${item.item_code}">\${item.item_name}</option>`).join('');
        
        let newRow = `
        <tr class="item-row table-info" data-index="\${itemIndex}">
            <td class="text-center text-primary fw-bold">NEW</td>
            <td>
                <select name="Issue[\${itemIndex}][item_code]" class="form-select new-item-select shadow-sm border-primary">
                    <option value="">-- เลือกพัสดุ --</option>
                    \${options}
                </select>
                <input type="hidden" name="Issue[\${itemIndex}][detail_id]" value="new">
            </td>
            <td class="text-center text-muted">-</td>
            <td>
                <input type="number" name="Issue[\${itemIndex}][qty_issued]" class="form-control text-center fw-bold border-primary qty-issued" value="1" min="0.01" step="0.01">
            </td>
            <td>
                <select name="Issue[\${itemIndex}][lot_number]" class="form-select border-warning lot-selector" required>
                    <option value="">-- เลือกพัสดุเพื่อดู Lot --</option>
                </select>
            </td>
            <td class="text-end fw-bold text-primary"><span class="row-total">0.00</span></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
        
        $('#issueTable tbody').append(newRow);
        itemIndex++;
        calculateTotal();
    });

    // --- เมื่อเลือกพัสดุใหม่ ให้ไปดึง Lot มาโชว์ ---
    $(document).on('change', '.new-item-select', function() {
        let itemCode = $(this).val();
        let row = $(this).closest('tr');
        let lotSelect = row.find('.lot-selector');

        if (!itemCode) return;

        $.ajax({
            url: '$getLotUrl',
            data: { item_code: itemCode },
            success: function(data) {
                lotSelect.empty();
                if (data.length > 0) {
                    data.forEach(lot => {
                        lotSelect.append(`<option value="\${lot.lot_number}" data-stock="\${lot.remain_qty}" data-price="\${lot.unit_price}">
                            LOT: \${lot.lot_number} (เหลือ \${lot.remain_qty}) [@\${lot.unit_price}]
                        </option>`);
                    });
                    lotSelect.prop('disabled', false);
                } else {
                    lotSelect.append('<option value="">ของหมดสต็อก</option>').prop('disabled', true);
                }
                calculateTotal();
            }
        });
    });

    $(document).on('click', '.btn-remove-row', function() { $(this).closest('tr').remove(); calculateTotal(); });

    $(document).on('click', '.btn-cancel-item', function() {
        let row = $(this).closest('tr');
        row.addClass('table-danger opacity-50').find('td').css('text-decoration', 'line-through');
        row.find('.qty-issued, .lot-selector').prop('disabled', true).val(0);
        $(this).addClass('d-none');
        row.find('.btn-restore-item').removeClass('d-none');
        calculateTotal();
    });

    $(document).on('click', '.btn-restore-item', function() {
        let row = $(this).closest('tr');
        row.removeClass('table-danger opacity-50').find('td').css('text-decoration', 'none');
        row.find('.qty-issued, .lot-selector').prop('disabled', false);
        $(this).addClass('d-none');
        row.find('.btn-cancel-item').removeClass('d-none');
        calculateTotal();
    });

    $('#btnSubmitIssue').click(function() {
        Swal.fire({
            title: 'ยืนยันการบันทึกการจ่าย?',
            text: "ระบบจะตัดสต็อกและบันทึกรายการพัสดุตามหน้าจอนี้",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(window.location.href, $('#issue-process-form').serialize(), function(res) {
                    if (res.success) {
                        Swal.fire('สำเร็จ', 'บันทึกการจ่ายเรียบร้อยแล้ว', 'success').then(() => { window.location.href = 'index'; });
                    } else {
                        Swal.fire('ผิดพลาด', res.message, 'error');
                    }
                });
            }
        });
    });
});
JS;
$this->registerJS($js, View::POS_READY);
?>