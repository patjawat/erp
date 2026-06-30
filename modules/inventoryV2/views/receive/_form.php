<?php

use app\components\AppHelper;
use app\widgets\TomSelectWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use kartik\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\StockOrder */
/* @var $form yii\widgets\ActiveForm */
/* @var $items array รายการ StockDetail สำหรับหน้า update */

// กำหนดค่าเริ่มต้นเพื่อป้องกัน undefined variable
$items = $items ?? [];
$listVendors = $listVendors ?? ['' => '-- เลือกผู้ขาย (ไม่บังคับ) --'];
// หน้าแก้ไข: ถ้าไม่มี $items แต่โมเดลมี stockDetails ให้ใช้จาก relation (ป้องกันรายการไม่โหลด)
if (!$model->isNewRecord && empty($items) && is_array($model->stockDetails)) {
    $items = $model->stockDetails;
}
$initialWarehouseId = isset($model->main_warehouse_id) ? (string) $model->main_warehouse_id : '';
$initialItemType = '';
foreach ($items as $it) {
    if (is_object($it) && isset($it->item) && $it->item) {
        $cid = $it->item->category_id;
        if ($cid !== null && $cid !== '') {
            $initialItemType = (string) $cid;
            break;
        }
    }
}

\app\assets\TomSelectAsset::register($this);
\app\widgets\datepicker\Assets::register($this);
?>

<div class="container-fluid py-4 receive-form">
    <?php $form = ActiveForm::begin(['id' => 'receipt-form', 'options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary-gradient text-white py-2 px-3 d-flex justify-content-between align-items-center">
            <h6 class="text-white mb-0 small fw-normal"><i class="bi bi-box-arrow-in-down me-1"></i>บันทึกรับวัสดุเข้าคลัง</h6>
            <?= $form->field($model, 'order_type')->hiddenInput(['value' => 'IN'])->label(false) ?>
        </div>

        <div class="card-body">
            <div class="row g-2 mb-4 px-3 rounded border">
                <div class="col-md-3">
                    <?= $form->field($model, 'main_warehouse_id')->dropDownList($listWarehouse, [
                        'prompt' => '-- เลือกคลัง --',
                        'class' => 'form-select',
                        'id' => 'warehouseSelector',
                    ])->label('คลังสินค้า') ?>
                </div>
                <div class="col-md-3">
                    <?php
                    $orderDateDisplay = '';
                    if (!empty($model->order_date)) {
                        $orderDateDisplay = (preg_match('/^\d{4}-\d{2}-\d{2}/', trim($model->order_date)))
                            ? AppHelper::convertToThai($model->order_date)
                            : $model->order_date;
                    }
                    ?>
                    <?= $form->field($model, 'order_date')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                        'options' => [
                            'class' => 'form-control',
                            'id' => 'stockorder-order_date',
                            'placeholder' => 'วัน/เดือน/พ.ศ.',
                            'value' => $orderDateDisplay,
                        ],
                    ])->label('วันที่รับเข้า') ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'source_type')->dropDownList(
                        \app\modules\inventoryV2\models\StockOrder::optsReceiveSourceType(),
                        ['class' => 'form-select border-primary', 'prompt' => '-- เลือกประเภทการรับเข้า --']
                    )->label('ประเภทการรับเข้า') ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeHiddenInput($model, 'order_type', ['value' => 'IN']) ?>
                    <?= $form->field($model, 'order_no')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'เว้นว่างได้ — สร้างเลขที่อัตโนมัติ',
                        'id' => 'stockorder-order_no',
                    ])->label('เลขที่ใบรับเข้า (เว้นว่างได้ — ระบบสร้างเลขที่อัตโนมัติ)') ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'contact_id')->widget(TomSelectWidget::class, [
                        'items' => $listVendors,
                        'options' => ['class' => 'form-select', 'id' => 'stockorder-contact_id'],
                        'clientOptions' => [
                            'placeholder' => 'เลือกผู้ขาย (ไม่บังคับ)',
                            'allowEmptyOption' => true,
                        ],
                    ])->label('ผู้ขาย') ?>
                </div>
                
            </div>

            <div class="row g-2 mb-1 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">ประเภทวัสดุ</label>
                    <select id="itemTypeFilter" class="form-select">
                        <option value="">-- เลือกประเภทวัสดุ --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">ค้นหาพัสดุ</label>
                    <select id="itemSelector" class="form-select" placeholder="พิมพ์รหัสหรือชื่อพัสดุ...">
                        <option value="">-- ค้นหาพัสดุ --</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">&nbsp;</label>
                    <button type="button" class="btn btn-primary w-100" id="btnAddRow">
                        <i class="bi bi-plus-circle"></i> เพิ่มรายการ
                    </button>
                </div>
                <div class="col-md-4">
                    <input type="file" id="csvFileInput" class="form-control" accept=".csv" style="display: none;">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success flex-grow-1" id="btnImportCSV">
                            <i class="bi bi-file-earmark-spreadsheet"></i> อัปโหลด CSV
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnDownloadCsvTemplate" title="ดาวน์โหลดไฟล์ตัวอย่างรูปแบบ CSV">
                            <i class="bi bi-download"></i> Template
                        </button>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <small class="text-muted">ต้องเลือกคลังสินค้า แล้วเลือกประเภทวัสดุก่อน จึงจะค้นหาและเพิ่มรายการพัสดุได้ (รับเข้าได้เฉพาะประเภทที่คลังกำหนด)</small>
                    <br>
                    <small class="text-info d-block"><i class="bi bi-info-circle"></i> รูปแบบ CSV: รหัสวัสดุ,ชื่อวัสดุ,หน่วยนับ,จำนวน,ราคา/หน่วย,Lot Number,วันหมดอายุ (YYYY-MM-DD)</small>
                    <small class="text-success d-block mt-1"><i class="bi bi-stars"></i> หากเว้น <strong>รหัสวัสดุ</strong> ว่างไว้ ระบบจะสร้างวัสดุใหม่ในหมวดที่เลือกให้อัตโนมัติ (ถ้าชื่อตรงกับวัสดุเดิม จะใช้ตัวเดิม)</small>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-12">
                    <span class="me-2 small text-muted">Lot number:</span>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="lotMode" id="lotModeAuto" value="auto" checked>
                        <label class="form-check-label" for="lotModeAuto">กำหนดอัตโนมัติ</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="lotMode" id="lotModeManual" value="manual">
                        <label class="form-check-label" for="lotModeManual">กรอกเอง</label>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 align-items-center mt-4 mb-2" id="selectionToolbar" style="display:none !important;">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    เลือกแล้ว <span id="selectedCount">0</span> รายการ
                </span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnViewSelected">
                    <i class="bi bi-eye"></i> ดูรายการที่เลือก
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnDeleteSelected">
                    <i class="bi bi-trash"></i> ลบรายการที่เลือก
                </button>
                <button type="button" class="btn btn-sm btn-link text-muted" id="btnClearSelection">
                    ล้างการเลือก
                </button>
            </div>

            <div class="table-responsive mt-2">
                <table class="table table-hover align-middle mb-0" id="inboundTable">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 3%;"><input type="checkbox" id="chkAll" class="form-check-input" title="เลือกทั้งหมด"></th>
                            <th style="width: 3%;">#</th>
                            <th style="width: 17%;">รายการวัสดุ</th>
                            <th style="width: 9%;">หน่วยนับ</th>
                            <th style="width: 11%;">ประเภทวัสดุ</th>
                            <th style="width: 11%;">Lot Number</th>
                            <th style="width: 11%;">วันหมดอายุ</th>
                            <th style="width: 8%;">จำนวน</th>
                            <th style="width: 10%;">ราคา/หน่วย</th>
                            <th style="width: 12%;">รวม</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="detail-body" class="align-middle table-group-divider">
                        <?php if (!empty($items) && is_array($items)): ?>
                            <?php foreach ($items as $index => $item): ?>
                                <?php
                                // กรณีหน้า Create รุ่นใหม่จะเป็น Model เปล่า item_code จะไม่มีค่า
                                if (!isset($item) || !is_object($item) || empty($item->item_code)) continue;
                                ?>
                                <?php
                                $rowItemName = $item->item->item_name ?? 'ไม่พบชื่อวัสดุ';
                                $rowItemCode = $item->item_code;
                                $rowImgUrl = '';
                                if ($item->item && !empty($item->item->ref)) {
                                    $rowUpload = \app\modules\filemanager\models\Uploads::find()->where(['ref' => $item->item->ref])->one();
                                    if ($rowUpload) {
                                        $rowImgUrl = \app\modules\filemanager\components\FileManagerHelper::getImg($rowUpload->id);
                                    }
                                }
                                ?>
                                <tr class="item-row">
                                    <td class="text-center"><input type="checkbox" class="form-check-input row-check"></td>
                                    <td class="text-center text-muted"><?= $index + 1 ?></td>
                                    <td>
                                        <input type="hidden" name="StockDetail[<?= $index ?>][item_code]" value="<?= Html::encode($rowItemCode) ?>">
                                        <div class="item-cell">
                                            <?php if ($rowImgUrl): ?>
                                                <img src="<?= Html::encode($rowImgUrl) ?>" alt="" class="item-cell__img" loading="lazy">
                                            <?php else: ?>
                                                <span class="item-cell__img item-cell__img--placeholder"><i class="bi bi-box"></i></span>
                                            <?php endif; ?>
                                            <div class="item-cell__body">
                                                <div class="item-cell__name"><?= Html::encode($rowItemName) ?></div>
                                                <?php if ($rowItemCode): ?>
                                                    <div class="item-cell__code"><?= Html::encode($rowItemCode) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php $currentUnit = $item->item && $item->item->unitName ? $item->item->unitName : '-'; ?>
                                        <input type="hidden" name="StockDetail[<?= $index ?>][unit_name]" value="<?= Html::encode($currentUnit) ?>">
                                        <span class="text-muted"><?= Html::encode($currentUnit) ?></span>
                                    </td>
                                    <td class="text-muted small"><?= $item->item && $item->item->categoryType ? Html::encode($item->item->categoryType->title) : '-' ?></td>
                                    <td><input type="text" name="StockDetail[<?= $index ?>][lot_number]" class="form-control" value="<?= $item->lot_number ?>" placeholder="กรอกหรือกำหนดเอง"></td>
                                    <?php
                                    $expiryDisplay = '';
                                    if (!empty($item->expiry_date)) {
                                        $t = strtotime($item->expiry_date);
                                        $expiryDisplay = date('d/m/', $t) . (date('Y', $t) + 543);
                                    }
                                    ?>
                                    <td><input type="text" id="expiry-date-<?= $index ?>" name="StockDetail[<?= $index ?>][expiry_date]" class="form-control expiry-date-thai" value="<?= Html::encode($expiryDisplay) ?>" placeholder="วว/ดด/พ.ศ." autocomplete="off"></td>
                                    <td><input type="number" name="StockDetail[<?= $index ?>][qty]" class="form-control text-center qty-input" value="<?= $item->qty ?>" min="1" step="1"></td>
                                    <td><input type="number" name="StockDetail[<?= $index ?>][unit_price]" class="form-control text-end price-input" value="<?= $item->unit_price ?>" step="1"></td>
                                    <td class="text-end fw-bold row-total"><?= number_format($item->qty * $item->unit_price, 2) ?></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove border-0"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <tr id="emptyRow" style="<?= (!empty($items) && is_array($items) && count($items) > 0 && isset($items[0]) && is_object($items[0]) && !empty($items[0]->item_code)) ? 'display:none' : '' ?>">
                            <td colspan="11" class="text-center py-4 text-muted">ยังไม่มีรายการที่ถูกเพิ่ม</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold text-end">
                        <tr>
                            <td colspan="9">ยอดรวมสุทธิ</td>
                            <td id="grandTotal">0.00</td>
                            <td>บาท</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-end mt-4">
                <input type="hidden" name="save_as_draft" id="save_as_draft" value="0">
                <?php if ($model->isNewRecord || $model->status === 'DRAFT'): ?>
                <button type="button" class="btn btn-outline-secondary btn-lg px-4 me-2" id="btnSaveDraft">
                    <i class="bi bi-journal-bookmark"></i> บันทึกฉบับร่าง
                </button>
                <?php endif; ?>
                <?= Html::submitButton('<i class="bi bi-save"></i> บันทึกรับเข้าคลัง', ['class' => 'btn btn-success btn-lg px-5 shadow', 'id' => 'btnSubmitReceive']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<div class="modal fade" id="selectedItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary-gradient text-white py-2">
                <h6 class="modal-title text-white mb-0"><i class="bi bi-list-check me-1"></i> รายการที่เลือกไว้ (<span id="modalSelectedCount">0</span> รายการ)</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2"><i class="bi bi-info-circle"></i> ตรวจสอบรายการที่เลือก — หากนำเข้าผิด สามารถกดปุ่ม <span class="text-danger">ลบ</span> ในแต่ละแถว หรือปิด modal แล้วกด "ลบรายการที่เลือก" เพื่อลบทั้งหมด</p>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-center small">
                                <th style="width:4%;">#</th>
                                <th>รายการวัสดุ</th>
                                <th style="width:12%;">หน่วยนับ</th>
                                <th style="width:14%;">Lot</th>
                                <th style="width:10%;">จำนวน</th>
                                <th style="width:12%;">ราคา/หน่วย</th>
                                <th style="width:12%;">รวม</th>
                                <th style="width:6%;"></th>
                            </tr>
                        </thead>
                        <tbody id="selectedItemsBody"></tbody>
                        <tfoot class="table-light fw-bold text-end">
                            <tr>
                                <td colspan="6">ยอดรวมที่เลือก</td>
                                <td id="modalGrandTotal">0.00</td>
                                <td>บาท</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-danger btn-sm" id="btnDeleteSelectedModal">
                    <i class="bi bi-trash"></i> ลบรายการที่เลือกทั้งหมด
                </button>
            </div>
        </div>
    </div>
</div>
<?php
$itemListUrl = Url::to(['stock-item/item-list']);
$itemTypeListUrl = Url::to(['stock-item/item-type-list']);
$importCsvUrl = Url::to(['stock-item/import-csv-items']);
$unitListUrl = Url::to(['stock-item/unit-list']);
$createUnitUrl = Url::to(['stock-item/create-unit']);
$createItemUrl = Url::to(['stock-item/create-item']);
$createItemModalUrl = Url::to(['stock-item/create']);
$itemListUrlJson = json_encode($itemListUrl);
$itemTypeListUrlJson = json_encode($itemTypeListUrl);
$importCsvUrlJson = json_encode($importCsvUrl);
$unitListUrlJson = json_encode($unitListUrl);
$createUnitUrlJson = json_encode($createUnitUrl);
$createItemUrlJson = json_encode($createItemUrl);
$createItemModalUrlJson = json_encode($createItemModalUrl);
$initialWarehouseIdJson = json_encode($initialWarehouseId);
$initialItemTypeJson = json_encode($initialItemType);
$msgChangeWarehouse = json_encode('การเปลี่ยนคลังสินค้าจะล้างรายการที่เลือกไว้ทั้งหมด ต้องการดำเนินการหรือไม่?');
$msgChangeItemType = json_encode('การเปลี่ยนประเภทวัสดุจะล้างรายการที่เลือกไว้ทั้งหมด ต้องการดำเนินการหรือไม่?');
$csvHeadersJson = json_encode(['รหัสวัสดุ', 'ชื่อวัสดุ', 'หน่วยนับ', 'จำนวน', 'ราคา/หน่วย', 'Lot Number', 'วันหมดอายุ']);
// alias สำหรับ backward compat — ไฟล์เก่าที่ใช้ "พัสดุ" ยัง upload ได้
$csvHeaderAliasesJson = json_encode([
    'รหัสพัสดุ' => 'รหัสวัสดุ',
    'ชื่อพัสดุ' => 'ชื่อวัสดุ',
]);
$msgCsvInvalid = json_encode('ไฟล์ CSV ไม่ถูกต้อง (ต้องมีหัวตาราง)');
$msgCsvFormatError = json_encode('รูปแบบ CSV ไม่ถูกต้อง\nต้องมีหัวตาราง: ');
$msgSelectWarehouse = json_encode('กรุณาเลือกคลังสินค้าก่อน');
$msgSelectItemType = json_encode('กรุณาเลือกประเภทวัสดุก่อน');
$msgSelectItem = json_encode('กรุณาเลือกวัสดุ');
$msgCsvNoItems = json_encode('ไม่พบรายการที่ถูกต้องในไฟล์ CSV');
$msgImportSuccess = json_encode('สำเร็จ');
$msgImportError = json_encode('ไม่สามารถนำเข้าข้อมูลได้');
$msgConnectionError = json_encode('เกิดข้อผิดพลาดในการเชื่อมต่อ: ');
$msgAlert = json_encode('แจ้งเตือน');
$msgCannotSave = json_encode('ไม่สามารถบันทึกได้');
$msgAddItemRequired = json_encode('กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ');
$msgConfirmSave = json_encode('ยืนยันการบันทึกข้อมูล?');
$msgCheckData = json_encode('ตรวจสอบความถูกต้องของจำนวนและราคาก่อนยืนยัน');
$msgConfirmYes = json_encode('ใช่, บันทึกเลย!');
$msgCancel = json_encode('ยกเลิก');
$msgSaving = json_encode('กำลังบันทึก...');
$msgSuccess = json_encode('สำเร็จ!');
$msgSaveSuccess = json_encode('บันทึกข้อมูลรับเข้าเรียบร้อยแล้ว');
$msgSaveError = json_encode('ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่');
$msgError = json_encode('ผิดพลาด');
$js = <<< JS
    $(document).ready(function() {
    // วันหมดอายุ: ใช้ Thai datepicker (วว/ดด/พ.ศ.)
    $('.expiry-date-thai').each(function() {
        if (this.id && typeof thaiDatepicker === 'function') thaiDatepicker('#' + this.id);
    });

    // เลขที่ใบรับเข้า: บังคับกรอกเฉพาะเมื่อประเภทการรับเข้า = จัดซื้อ (PO)
    function toggleOrderNoRequired() {
        var st = ($('#stockorder-source_type').val() || '').trim();
        var input = $('#stockorder-order_no');
        if (input.length) {
            input.removeAttr('required');
            if (st === 'PO') {
                input.prop('required', true);
                input.attr('placeholder', '');
            } else {
                input.attr('placeholder', 'เว้นว่างได้ — สร้างเลขที่อัตโนมัติ');
            }
        }
    }
    $(document).on('change', '#stockorder-source_type', toggleOrderNoRequired);
    toggleOrderNoRequired();
    setTimeout(toggleOrderNoRequired, 100);
    setTimeout(toggleOrderNoRequired, 500);

    var initialWarehouseId = {$initialWarehouseIdJson} || '';
    var initialItemType = {$initialItemTypeJson} || '';
    var warehouseSelectEl = document.getElementById('warehouseSelector') || document.querySelector('select[name="StockOrder[main_warehouse_id]"]');
    var prevWarehouse = (warehouseSelectEl && warehouseSelectEl.value) ? warehouseSelectEl.value : initialWarehouseId;
    var prevItemType = '';

    function clearDetailRows() {
        $('#detail-body tr.item-row').remove();
        $('#emptyRow').show();
        if (typeof calculateTotal === 'function') calculateTotal();
        if (typeof itemSelect !== 'undefined' && itemSelect) itemSelect.clear();
    }

    function confirmThenClearRows(message, onConfirm) {
        if ($('.item-row').length === 0) {
            if (typeof onConfirm === 'function') onConfirm();
            return;
        }
        Swal.fire({
            title: {$msgAlert},
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, ล้างรายการ',
            cancelButtonText: {$msgCancel}
        }).then(function(result) {
            if (result.isConfirmed && typeof onConfirm === 'function') onConfirm();
        });
    }

    var warehouseTomSelect = null;
    if (warehouseSelectEl) {
        warehouseTomSelect = new TomSelect(warehouseSelectEl, {
            placeholder: '-- เลือกคลัง --',
            allowEmptyOption: true,
            maxOptions: null,
            render: {
                option: function(data, escape) {
                    return '<div class="py-1">' + escape(data.text) + '</div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                }
            },
            onChange: function(val) {
                var newVal = val || '';
                if (newVal !== prevWarehouse && $('.item-row').length > 0) {
                    confirmThenClearRows({$msgChangeWarehouse}, function() {
                        clearDetailRows();
                        prevWarehouse = newVal;
                        if (warehouseTomSelect) warehouseTomSelect.setValue(newVal, true);
                        loadItemTypesByWarehouse(newVal);
                    });
                    if (warehouseTomSelect) warehouseTomSelect.setValue(prevWarehouse, true);
                    return;
                }
                prevWarehouse = newVal;
                loadItemTypesByWarehouse(newVal);
            }
        });
        if (initialWarehouseId) warehouseTomSelect.setValue(initialWarehouseId, true);
    }

    var itemTypeListUrl = {$itemTypeListUrlJson};
    var itemTypeTomSelect = new TomSelect('#itemTypeFilter', {
        valueField: 'value',
        labelField: 'text',
        searchField: 'text',
        options: [],
        maxItems: 1,
        allowEmptyOption: true,
        placeholder: 'กรุณาเลือกคลังก่อน',
        render: {
            option: function(data, escape) {
                return '<div class="py-1">' + escape(data.text) + '</div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            }
        },
        onChange: function(val) {
            var newVal = val || '';
            if (newVal !== prevItemType && $('.item-row').length > 0) {
                confirmThenClearRows({$msgChangeItemType}, function() {
                    clearDetailRows();
                    prevItemType = newVal;
                    itemTypeTomSelect.setValue(newVal, true);
                    resetItemSelectForCategory(newVal);
                });
                itemTypeTomSelect.setValue(prevItemType, true);
                return;
            }
            prevItemType = newVal;
            resetItemSelectForCategory(newVal);
        }
    });

    /**
     * ล้าง cache + options ของ itemSelect เมื่อสลับประเภทวัสดุหรือคลัง
     * จำเป็นเพราะ TomSelect cache ผล load() ภายใน:
     *   - this.options: เก็บผลการค้นเดิม (ทำให้ dropdown โชว์ของประเภทเก่า)
     *   - this.loadedSearches: tracker ของ query ที่เคยโหลด (ทำให้ไม่ refire เมื่อพิมพ์ query เดิม)
     */
    function resetItemSelectForCategory(cat) {
        if (typeof itemSelect === 'undefined' || !itemSelect) return;
        try { itemSelect.clear(true); } catch(e) {}
        try { itemSelect.clearOptions(); } catch(e) {}
        if (itemSelect.loadedSearches) itemSelect.loadedSearches = {};
        if (typeof itemSelect.clearCache === 'function') {
            try { itemSelect.clearCache(); } catch(e) {}
        }
        if (cat) {
            itemSelect.enable();
            itemSelect.placeholder = 'พิมพ์ชื่อหรือรหัสพัสดุ...';
        } else {
            itemSelect.disable();
            itemSelect.placeholder = 'กรุณาเลือกประเภทวัสดุก่อน';
        }
        try { itemSelect.refreshOptions(false); } catch(e) {}
    }

    function loadItemTypesByWarehouse(warehouseId, preferredItemType) {
        var url = itemTypeListUrl + (warehouseId ? '?warehouse_id=' + encodeURIComponent(warehouseId) : '');
        itemTypeTomSelect.placeholder = warehouseId ? 'เลือกประเภทวัสดุ' : 'กรุณาเลือกคลังก่อน';
        // คลังเปลี่ยน = ล้าง cache ของ itemSelect ทันที (กันค้างของคลัง/ประเภทก่อนหน้า)
        resetItemSelectForCategory('');
        prevItemType = '';
        fetch(url).then(function(r) { return r.json(); }).then(function(res) {
            itemTypeTomSelect.clearOptions();
            if (res.results && res.results.length) {
                itemTypeTomSelect.addOptions(res.results);
            } else {
                itemTypeTomSelect.addOption({ value: '', text: 'ไม่พบประเภทตามคลัง' });
            }
            itemTypeTomSelect.setValue('', true);
            if (preferredItemType && res.results && res.results.some(function(o) { return o.value == preferredItemType; })) {
                itemTypeTomSelect.setValue(preferredItemType, true);
                prevItemType = preferredItemType;
                resetItemSelectForCategory(preferredItemType);
            }
        }).catch(function() {
            itemTypeTomSelect.clearOptions();
            itemTypeTomSelect.addOption({ value: '', text: 'ไม่พบประเภทตามคลัง' });
            itemTypeTomSelect.setValue('', true);
        });
    }

    // โหลดประเภทวัสดุตามคลังที่เลือกไว้ (กรณีมีค่าเริ่มต้น / หน้าแก้ไข) — รอ DOM และ TomSelect พร้อม
    var whVal = initialWarehouseId || (warehouseSelectEl && warehouseSelectEl.value) || '';
    if (whVal) {
        prevWarehouse = whVal;
        setTimeout(function() {
            if (warehouseTomSelect && warehouseTomSelect.setValue) warehouseTomSelect.setValue(whVal, true);
            loadItemTypesByWarehouse(whVal, initialItemType);
        }, 150);
    }
    prevItemType = initialItemType || ((typeof itemTypeTomSelect !== 'undefined' && itemTypeTomSelect) ? (itemTypeTomSelect.getValue() || '') : '');

    let rowIndex = $('.item-row').length;
    let itemSelect;
    
    // ตั้งค่า TomSelect สำหรับหน่วยนับในแถวที่มีอยู่แล้ว (edit mode)
    $('.unit-select').each(function() {
        var selectEl = $(this);
        var selectId = selectEl.attr('id');
        if (!selectId) {
            var index = selectEl.data('index');
            selectId = 'unit-select-existing-' + index;
            selectEl.attr('id', selectId);
        }
        
        if (selectEl.hasClass('ts-hidden-accessible')) {
            return; // ถ้าเป็น TomSelect อยู่แล้วให้ข้าม
        }
        
        var currentValue = selectEl.val();
        var unitTomSelect = new TomSelect('#' + selectId, {
            dropdownParent: document.body,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            options: [],
            maxItems: 1,
            allowEmptyOption: true,
            placeholder: 'เลือกหรือพิมพ์หน่วยนับ',
            create: function(input, callback) {
                fetch({$createUnitUrlJson}, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ unit_name: input })
                })
                .then(function(response) { return response.json(); })
                .then(function(result) {
                    if (result.success) {
                        callback({ value: result.unit_name, text: result.unit_name });
                    } else {
                        Swal.fire({$msgError}, result.message || 'ไม่สามารถสร้างหน่วยนับได้', 'error');
                        callback(null);
                    }
                })
                .catch(function(error) {
                    Swal.fire({$msgError}, 'เกิดข้อผิดพลาดในการสร้างหน่วยนับ', 'error');
                    callback(null);
                });
            },
            createOnBlur: true,
            createFilter: function(input) {
                return input.length > 0;
            },
            load: function(query, callback) {
                var url = {$unitListUrlJson} + (query ? '?q=' + encodeURIComponent(query) : '');
                fetch(url)
                    .then(function(response) { return response.json(); })
                    .then(function(json) {
                        callback(json.results || []);
                    }).catch(function() {
                        callback([]);
                    });
            },
            render: {
                option: function(data, escape) {
                    return '<div class="py-1">' + escape(data.text) + '</div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
                option_create: function(data, escape) {
                    return '<div class="py-1"><strong>สร้างใหม่: ' + escape(data.input) + '</strong></div>';
                }
            },
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
            }
        });
        
        if (currentValue) {
            unitTomSelect.setValue(currentValue, true);
        }
    });
    
    // --- 2. การดักจับ Event บันทึกข้อมูลด้วย SweetAlert2 ---
        
    function reOrder(){
    $('#detail-body tr.item-row').each(function(index) {
        $(this).find('td').eq(1).text(index + 1);
    });
}

    // --- Checkbox + ดูรายการที่เลือก ---
    function updateSelectionUi() {
        var rows = $('#detail-body tr.item-row');
        var checked = rows.find('.row-check:checked');
        var total = rows.length;
        var n = checked.length;
        $('#selectedCount').text(n);
        if (n > 0) {
            $('#selectionToolbar').attr('style', 'display:flex !important;');
        } else {
            $('#selectionToolbar').attr('style', 'display:none !important;');
        }
        var chkAll = document.getElementById('chkAll');
        if (chkAll) {
            chkAll.checked = (total > 0 && n === total);
            chkAll.indeterminate = (n > 0 && n < total);
        }
    }

    $(document).on('change', '#chkAll', function() {
        $('#detail-body tr.item-row .row-check').prop('checked', this.checked);
        updateSelectionUi();
    });

    $(document).on('change', '.row-check', function() {
        updateSelectionUi();
    });

    $(document).on('click', '#btnClearSelection', function() {
        $('#detail-body tr.item-row .row-check').prop('checked', false);
        updateSelectionUi();
    });

    function renderSelectedItemsModal() {
        var \$tbody = $('#selectedItemsBody').empty();
        var grand = 0;
        var rows = $('#detail-body tr.item-row').filter(function() {
            return $(this).find('.row-check').is(':checked');
        });
        rows.each(function(idx) {
            var \$r = $(this);
            var name = \$r.find('td').eq(2).find('strong').text() || '-';
            var code = \$r.find('input[name*="[item_code]"]').val() || '';
            var unit = \$r.find('input[name*="[unit_name]"]').val() || '-';
            var lot = \$r.find('input[name*="[lot_number]"]').val() || '-';
            var qty = parseFloat(\$r.find('.qty-input').val()) || 0;
            var price = parseFloat(\$r.find('.price-input').val()) || 0;
            var sub = qty * price;
            grand += sub;
            var rowHtml = '<tr data-row-key="' + \$r.index() + '">' +
                '<td class="text-center">' + (idx + 1) + '</td>' +
                '<td><strong>' + \$('<div>').text(name).html() + '</strong> <small class="text-muted">[' + \$('<div>').text(code).html() + ']</small></td>' +
                '<td class="text-center">' + \$('<div>').text(unit).html() + '</td>' +
                '<td>' + \$('<div>').text(lot).html() + '</td>' +
                '<td class="text-center">' + qty + '</td>' +
                '<td class="text-end">' + price.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>' +
                '<td class="text-end fw-bold">' + sub.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger border-0 btn-modal-remove" data-code="' + \$('<div>').text(code).html() + '"><i class="bi bi-trash"></i></button></td>' +
                '</tr>';
            \$tbody.append(rowHtml);
        });
        $('#modalSelectedCount').text(rows.length);
        $('#modalGrandTotal').text(grand.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}));
        if (rows.length === 0) {
            \$tbody.append('<tr><td colspan="8" class="text-center text-muted py-4">ยังไม่ได้เลือกรายการ</td></tr>');
        }
    }

    $(document).on('click', '#btnViewSelected', function() {
        renderSelectedItemsModal();
        var modalEl = document.getElementById('selectedItemsModal');
        if (modalEl) {
            var m = bootstrap.Modal.getOrCreateInstance(modalEl);
            m.show();
        }
    });

    $(document).on('click', '.btn-modal-remove', function() {
        var code = $(this).data('code');
        if (!code) return;
        $('#detail-body tr.item-row').each(function() {
            var v = $(this).find('input[name*="[item_code]"]').val();
            if (v === String(code)) $(this).remove();
        });
        reOrder();
        if ($('.item-row').length === 0) $('#emptyRow').show();
        if (typeof calculateTotal === 'function') calculateTotal();
        updateSelectionUi();
        renderSelectedItemsModal();
    });

    function deleteSelectedRows() {
        var checked = $('#detail-body tr.item-row').filter(function() {
            return $(this).find('.row-check').is(':checked');
        });
        if (checked.length === 0) return;
        Swal.fire({
            title: {$msgAlert},
            text: 'ลบ ' + checked.length + ' รายการที่เลือกหรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, ลบ',
            cancelButtonText: {$msgCancel}
        }).then(function(result) {
            if (!result.isConfirmed) return;
            checked.remove();
            reOrder();
            if ($('.item-row').length === 0) $('#emptyRow').show();
            if (typeof calculateTotal === 'function') calculateTotal();
            updateSelectionUi();
            var modalEl = document.getElementById('selectedItemsModal');
            if (modalEl) {
                var m = bootstrap.Modal.getInstance(modalEl);
                if (m) m.hide();
            }
        });
    }

    $(document).on('click', '#btnDeleteSelected, #btnDeleteSelectedModal', deleteSelectedRows);

        $('#btnSaveDraft').on('click', function() {
            $('#save_as_draft').val('1');
            $('#receipt-form').trigger('submit');
        });

        // แปลงวันหมดอายุจาก รูปแบบไทย (วว/ดด/พ.ศ.) เป็น Y-m-d ก่อนส่งฟอร์ม
        function convertExpiryThaiToYmd(val) {
            if (!val || typeof val !== 'string') return '';
            var p = val.trim().split(/[\/\-]/);
            if (p.length !== 3) return val;
            var d = parseInt(p[0], 10); var m = parseInt(p[1], 10); var y = parseInt(p[2], 10) - 543;
            if (isNaN(d) || isNaN(m) || isNaN(y) || y < 1900) return val;
            var pad = function(n) { return n < 10 ? '0' + n : '' + n; };
            return y + '-' + pad(m) + '-' + pad(d);
        }
        function applyExpiryConvert() {
            $('.expiry-date-thai').each(function() {
                var v = $(this).val();
                if (v) $(this).val(convertExpiryThaiToYmd(v));
            });
        }

        $('#receipt-form').on('beforeSubmit', function(e) {
            applyExpiryConvert();
            var isDraft = $('#save_as_draft').val() === '1';
            if (!isDraft) {
                // เช็คก่อนว่ามีรายการในตารางไหม (เฉพาะเมื่อบันทึกรับเข้าจริง)
                if ($('.item-row').length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: {$msgCannotSave},
                        text: {$msgAddItemRequired},
                        confirmButtonColor: '#3085d6',
                    });
                    return false;
                }
            }

            // เช็ค Lot number, จำนวน, ราคาต่อหน่วย (ทั้งฉบับร่างและรับเข้าจริง)
            var invalidRows = [];
            $('.item-row').find('input[name*="[lot_number]"], .qty-input, .price-input').removeClass('is-invalid');
            $('.item-row').each(function(index) {
                var row = $(this);
                var rowNum = index + 1;
                var lotInput = row.find('input[name*="[lot_number]"]');
                var qtyInput = row.find('.qty-input');
                var priceInput = row.find('.price-input');
                var lot = (lotInput.val() || '').trim();
                var qtyVal = qtyInput.val();
                var priceVal = priceInput.val();
                var qty = parseFloat(qtyVal);
                var price = parseFloat(priceVal);
                var itemName = row.find('strong').text().trim() || 'แถวที่ ' + rowNum;
                var err = [];
                if (!lot) {
                    err.push('Lot number');
                    lotInput.addClass('is-invalid');
                }
                if (qtyVal === '' || qtyVal === null || isNaN(qty) || qty <= 0) {
                    err.push('จำนวน (ต้องมากกว่า 0)');
                    qtyInput.addClass('is-invalid');
                }
                if (priceVal === '' || priceVal === null || isNaN(price) || price < 0) {
                    err.push('ราคา/หน่วย (ต้องไม่น้อยกว่า 0)');
                    priceInput.addClass('is-invalid');
                }
                if (err.length) invalidRows.push({ rowEl: row, row: rowNum, name: itemName, fields: err });
            });
            if (invalidRows.length > 0) {
                var firstRow = invalidRows[0].rowEl[0];
                if (firstRow && firstRow.scrollIntoView) {
                    firstRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                var msg = 'กรุณากรอกให้ครบในทุกแถว: Lot number, จำนวน (ต้องมากกว่า 0), ราคา/หน่วย (ต้องไม่น้อยกว่า 0)\\n\\n';
                invalidRows.slice(0, 5).forEach(function(r) {
                    msg += 'แถว ' + r.row + ' (' + r.name.substring(0, 30) + (r.name.length > 30 ? '...' : '') + '): ' + r.fields.join(', ') + '\\n';
                });
                if (invalidRows.length > 5) msg += '... และอีก ' + (invalidRows.length - 5) + ' แถว';
                Swal.fire({
                    icon: 'warning',
                    title: {$msgCannotSave},
                    text: msg,
                    confirmButtonColor: '#3085d6',
                });
                return false;
            }

            // บันทึกฉบับร่าง: ตรวจสอบแล้ว ส่ง AJAX
            if (isDraft) {
                e.preventDefault();
                var form = $('#receipt-form');
                Swal.fire({
                    title: 'บันทึกฉบับร่าง?',
                    html: '<div class="text-start small">'
                        + '<div class="alert alert-warning border-0 mb-3" style="background:#fff8e1">'
                        + '<strong><i class="bi bi-exclamation-triangle-fill me-1"></i> ฉบับร่างยังไม่เข้าคลัง</strong>'
                        + '<ul class="mb-0 mt-2 ps-3">'
                        + '<li>ยอดคงเหลือคลัง<strong>ไม่อัปเดต</strong></li>'
                        + '<li>ใบเบิกที่ใช้วัสดุนี้จะ<strong>จ่ายไม่ได้</strong> (FIFO มองไม่เห็น)</li>'
                        + '<li>ต้องเปิดเอกสารกลับมาแล้วกด<strong>"บันทึกและรับเข้าคลัง"</strong> เพื่อ commit เข้า stock จริง</li>'
                        + '</ul></div>'
                        + '<div class="text-muted">ใช้เมื่อยังเก็บข้อมูลไม่ครบ — ถ้าพร้อมแล้วให้กด <em>"ยกเลิก"</em> และกด <em>"บันทึกและรับเข้าคลัง"</em> แทน</div>'
                        + '</div>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6c757d',
                    cancelButtonColor: '#0d6efd',
                    confirmButtonText: 'บันทึกเป็นร่าง (ยังไม่เข้าคลัง)',
                    cancelButtonText: {$msgCancel},
                    reverseButtons: true,
                    width: 560,
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                $('#save_as_draft').val('0');
                                if (response && response.success === false) {
                                    Swal.fire({$msgError}, response.message || {$msgSaveError}, 'error');
                                    return;
                                }
                                Swal.fire({ icon: 'success', title: 'บันทึกฉบับร่างเรียบร้อย', text: 'สามารถกลับมาเพิ่มรายการและบันทึกรับเข้าคลังภายหลัง' }).then(function() {
                                    if (response && response.redirect) window.location.href = response.redirect;
                                });
                            },
                            error: function(xhr) {
                                $('#save_as_draft').val('0');
                                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : {$msgSaveError};
                                Swal.fire({$msgError}, msg, 'error');
                            }
                        });
                    }
                });
                return false;
            }

            // แสดง Confirmation Dialog (บันทึกรับเข้าคลัง)
            Swal.fire({
                title: {$msgConfirmSave},
                text: {$msgCheckData},
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754', // สีเขียว Success
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="bi bi-check-lg"></i> ' + {$msgConfirmYes},
                cancelButtonText: {$msgCancel}
                // default order: ปุ่มยืนยันซ้าย ปุ่มยกเลิกขวา
            }).then(function(result) {
                if (result.isConfirmed) {
                    // แสดง Loading ระหว่างรอ Server ประมวลผล
                    Swal.fire({
                        title: {$msgSaving},
                        allowOutsideClick: false,
                        didOpen: function() {
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
                            if (response && response.success === false) {
                                Swal.fire({$msgError}, response.message || {$msgSaveError}, 'error');
                                return;
                            }
                            Swal.fire({
                                icon: 'success',
                                title: {$msgSuccess},
                                text: {$msgSaveSuccess},
                                timer: 2000,
                                showConfirmButton: true
                            }).then(function() {
                                if (response && response.redirect) {
                                    window.location.href = response.redirect;
                                }
                            });
                        },
                        error: function(xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : {$msgSaveError};
                            Swal.fire({$msgError}, msg, 'error');
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

        // 2. ตั้งค่า TomSelect รายการพัสดุ (ใช้ได้เมื่อเลือกประเภทวัสดุแล้วเท่านั้น)
        itemSelect = new TomSelect('#itemSelector', {
            dropdownParent: document.body,
            valueField: 'item_code',
            labelField: 'item_name',
            searchField: ['item_name', 'item_code'],
            create: function(input, callback) {
                // เมื่อสร้างพัสดุใหม่ → เปิด modal form
                var cat = (typeof itemTypeTomSelect !== 'undefined' && itemTypeTomSelect) ? itemTypeTomSelect.getValue() : '';
                if (!cat) {
                    Swal.fire({$msgAlert}, {$msgSelectItemType}, 'warning');
                    callback(null);
                    return;
                }
                
                // เปิด modal form สำหรับสร้างพัสดุใหม่
                var modalUrl = {$createItemModalUrlJson} + '?category_id=' + encodeURIComponent(cat) + '&item_name=' + encodeURIComponent(input);
                if (typeof beforLoadModal === 'function') beforLoadModal();
                
                $.ajax({
                    type: 'GET',
                    url: modalUrl,
                    dataType: 'json',
                    success: function(response) {
                        var modal = $('#main-modal');
                        modal.find('#main-modal-label').html(response.title);
                        modal.find('.modal-body').html(response.content);
                        modal.find('.modal-footer').html(response.footer);
                        modal.find('.modal-dialog').removeClass('modal-sm modal-md modal-lg modal-xl modal-xxl')
                             .addClass('modal-lg');
                        modal.modal('show');
                        
                        // ตั้งค่า callback สำหรับเมื่อสร้างสำเร็จ
                        window.itemSelectCallback = callback;
                    },
                    error: function(xhr) {
                        Swal.fire({$msgError}, 'ไม่สามารถเปิดฟอร์มสร้างพัสดุได้', 'error');
                        callback(null);
                    }
                });
                
                // ไม่ต้อง callback ทันที รอให้ modal สร้างเสร็จก่อน
                callback(null);
            },
            createOnBlur: false,
            createFilter: function(input) {
                return input.length > 0;
            },
            load: function(query, callback) {
                var cat = (typeof itemTypeTomSelect !== 'undefined' && itemTypeTomSelect) ? itemTypeTomSelect.getValue() : ($('#itemTypeFilter').val() || '');
                if (!cat) {
                    callback([]);
                    return;
                }
                var url = {$itemListUrlJson} + '?q=' + encodeURIComponent(query || '') + '&category_id=' + encodeURIComponent(cat);
                var wh = (warehouseTomSelect && warehouseTomSelect.getValue()) ? warehouseTomSelect.getValue() : ($(warehouseSelectEl).val() || '');
                if (wh) url += '&warehouse_id=' + encodeURIComponent(wh);

                fetch(url)
                    .then(function(response) { return response.json(); })
                    .then(function(json) {
                        var results = json.results || [];
                        // ไม่แสดงพัสดุที่เลือกไปแล้วในตาราง (ไม่ซ้ำ)
                        var selectedCodes = [];
                        $('.item-row input[name*="[item_code]"]').each(function() {
                            var v = $(this).val();
                            if (v) selectedCodes.push(v);
                        });
                        if (selectedCodes.length > 0) {
                            results = results.filter(function(r) {
                                return selectedCodes.indexOf(r.item_code) === -1;
                            });
                        }
                        callback(results);
                    }).catch(function() {
                        callback([]);
                    });
            },
            render: {
                option: function(data, escape) {
                    return '<div class="py-2 d-flex justify-content-between"><div><span class="fw-bold">' + escape(data.item_name) + '</span></div><span class="badge text-bg-secondary">' + escape(data.item_code) + '</span></div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.item_name) + ' <small class="text-muted">[' + escape(data.item_code) + ']</small></div>';
                }
            },
            placeholder: 'กรุณาเลือกประเภทวัสดุก่อน',
            maxOptions: 50,
            shouldLoad: function(query) {
                var cat = (typeof itemTypeTomSelect !== 'undefined' && itemTypeTomSelect) ? itemTypeTomSelect.getValue() : '';
                return !!cat;
            },
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
            }
        });
        if (!(itemTypeTomSelect.getValue())) itemSelect.disable();
        else { itemSelect.enable(); itemSelect.placeholder = 'พิมพ์ชื่อหรือรหัสพัสดุ...'; }

        if ($('.item-row').length) { reOrder(); calculateTotal(); }
        updateSelectionUi();

        // 3. ปุ่มเพิ่มแถว (เหมือนเดิม)
       // เปลี่ยนจาก $('#btnAddRow').click(function() { ... });
// เป็นการ .off('click') ก่อนทุกครั้ง

$(document).off('click', '#btnAddRow').on('click', '#btnAddRow', function(e) {
    e.preventDefault(); // ป้องกันการส่งฟอร์มโดยไม่ตั้งใจ
    var cat = (typeof itemTypeTomSelect !== 'undefined' && itemTypeTomSelect) ? itemTypeTomSelect.getValue() : '';
    if (!cat) return Swal.fire({$msgAlert}, {$msgSelectItemType}, 'warning');
    const itemId = itemSelect.getValue();
    if (!itemId) return Swal.fire({$msgAlert}, {$msgSelectItem}, 'warning');

    const itemData = itemSelect.options[itemId];
    if (!itemData) return;

    // กันไม่ให้เลือกรายการซ้ำกับที่อยู่ในตารางแล้ว
    var alreadyInTable = false;
    $('.item-row').each(function() {
        var code = $(this).find('input[name*="[item_code]"]').val();
        if (code && code === itemId) {
            alreadyInTable = true;
            return false;
        }
    });
    if (alreadyInTable) {
        Swal.fire({$msgAlert}, 'รายการนี้มีในตารางแล้ว กรุณาเลือกพัสดุอื่น', 'warning');
        return;
    }

    $('#emptyRow').hide();
    
    // คำนวณ Index ล่าสุดจากจำนวนแถวที่มีอยู่จริงในหน้าจอขณะนั้น
    let currentIndex = $('.item-row').length;

    const typeLabel = (itemData.category_title != null && itemData.category_title !== '') ? itemData.category_title : '-';
    // ใช้หน่วยนับจากพัสดุ
    const unitValue = (itemData.unit_name && itemData.unit_name !== '-') ? itemData.unit_name : '-';
    var lotVal = ($('input[name="lotMode"]:checked').val() === 'auto') ? getAutoLotNumber(currentIndex) : '';
    const row = '<tr class="item-row">' +
        '<td class="text-center"><input type="checkbox" class="form-check-input row-check"></td>' +
        '<td class="text-center text-muted"></td>' +
        '<td><input type="hidden" name="StockDetail[' + currentIndex + '][item_code]" value="' + (itemId || '') + '"><strong>' + (itemData.item_name || '') + '</strong></td>' +
        '<td><input type="hidden" name="StockDetail[' + currentIndex + '][unit_name]" value="' + (unitValue || '-') + '"><span class="text-muted">' + (unitValue || '-') + '</span></td>' +
        '<td class="text-muted small">' + typeLabel + '</td>' +
        '<td><input type="text" name="StockDetail[' + currentIndex + '][lot_number]" class="form-control lot-number-input" value="' + (lotVal || '') + '" placeholder="กรอกหรือกำหนดเอง"></td>' +
        '<td><input type="text" id="expiry-date-' + currentIndex + '" name="StockDetail[' + currentIndex + '][expiry_date]" class="form-control expiry-date-thai" placeholder="วว/ดด/พ.ศ." autocomplete="off"></td>' +
        '<td><input type="number" name="StockDetail[' + currentIndex + '][qty]" class="form-control text-center qty-input" value="1" min="1" step="1"></td>' +
        '<td><input type="number" name="StockDetail[' + currentIndex + '][unit_price]" class="form-control text-end price-input" value="0.00" step="1"></td>' +
        '<td class="text-end fw-bold row-total">0.00</td>' +
        '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove border-0"><i class="bi bi-trash"></i></button></td>' +
        '</tr>';

    $('#detail-body').append(row);
    if (typeof thaiDatepicker === 'function') thaiDatepicker('#expiry-date-' + currentIndex);

    reOrder(); // รันเลขลำดับ 1, 2, 3 ใหม่
    calculateTotal();
    itemSelect.clear();
    itemSelect.clearOptions(); // ล้างตัวเลือกเก่า เพื่อให้ค้นครั้งถัดไปโหลดใหม่และไม่แสดงรายการที่เลือกแล้ว
    setTimeout(function() {
        if (itemSelect.control_input) {
            itemSelect.control_input.focus();
        } else if (itemSelect.wrapper) {
            $(itemSelect.wrapper).find('input').first().focus();
        }
    }, 50);
});

        function getAutoLotNumber(rowIndex) {
            var d = new Date();
            var y = d.getFullYear();
            var m = String(d.getMonth() + 1).padStart(2, '0');
            var day = String(d.getDate()).padStart(2, '0');
            var seq = String((rowIndex || 0) + 1).padStart(3, '0');
            return 'LOT-' + y + m + day + '-' + seq;
        }

        function createUnitIfNotExists(unitName, itemCode, itemName, categoryId) {
            // สร้างหน่วยนับใหม่ใน database ถ้ายังไม่มี
            fetch({$createUnitUrlJson}, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ unit_name: unitName })
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                if (result.success) {
                    console.log('สร้างหน่วยนับใหม่: ' + result.unit_name);
                }
            })
            .catch(function(error) {
                console.error('Error creating unit:', error);
            });
        }

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
            row.find('.qty-input, .price-input').removeClass('is-invalid');
        });
        $(document).on('input change', '.lot-number-input', function() {
            $(this).removeClass('is-invalid');
        });

        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            reOrder();
            if ($('.item-row').length === 0) $('#emptyRow').show();
            calculateTotal();
            updateSelectionUi();
        });

        // 4. ฟังก์ชันอัปโหลด CSV
        $('#btnImportCSV').on('click', function() {
            $('#csvFileInput').click();
        });

        // ดาวน์โหลด template ตัวอย่างนำเข้า CSV
        $('#btnDownloadCsvTemplate').on('click', function() {
            var headers = {$csvHeadersJson};
            var headerLine = headers.join(',');
            var exampleRow1 = 'ITEM001,ตัวอย่างวัสดุ 1,ชิ้น,10,5.50,LOT2024001,2025-12-31';
            var exampleRow2 = 'ITEM002,ตัวอย่างวัสดุ 2,กล่อง,5,120.00,LOT2024002,2026-06-30';
            var exampleRow3 = ',ตัวอย่างวัสดุ 3 (รหัสว่าง สร้างใหม่อัตโนมัติ),ขวด,3,80.00,LOT2024003,2026-12-31';
            var csvContent = '\\uFEFF' + headerLine + '\\n' + exampleRow1 + '\\n' + exampleRow2 + '\\n' + exampleRow3;
            var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'template_nam_khao_csv.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        });

        $('#csvFileInput').on('change', function(e) {
            var file = e.target.files[0];
            if (!file) return;
            
            var cat = (typeof itemTypeTomSelect !== 'undefined' && itemTypeTomSelect) ? itemTypeTomSelect.getValue() : '';
            if (!cat) {
                Swal.fire({$msgAlert}, {$msgSelectItemType}, 'warning');
                $(this).val('');
                return;
            }

            var reader = new FileReader();
            reader.onload = function(event) {
                var csv = event.target.result;
                parseAndImportCSV(csv, cat);
            };
            reader.readAsText(file, 'UTF-8');
        });

        function parseAndImportCSV(csv, categoryId) {
            var lines = csv.split("\\n").filter(function(line) { return line.trim(); });
            if (lines.length < 2) {
                Swal.fire({$msgError}, {$msgCsvInvalid}, 'error');
                return;
            }

            var headerAliases = {$csvHeaderAliasesJson};
            // ใช้ parseCsvLine + normalize ตัด BOM (\\uFEFF), white space, ช่องว่างใน "ราคา / หน่วย" → "ราคา/หน่วย"
            var rawHeaderCells = parseCsvLine(lines[0].replace(/^\\uFEFF/, ''));
            var headers = rawHeaderCells.map(function(h) {
                var t = String(h || '').trim().replace(/\\s+/g, '');
                return headerAliases[t] || t;
            });
            var expectedHeaders = {$csvHeadersJson};
            var normalizedExpected = expectedHeaders.map(function(h) { return h.replace(/\\s+/g, ''); });
            // colIndex: { "รหัสวัสดุ": 0, "ชื่อวัสดุ": 1, ... } — lookup ตามชื่อ ไม่ใช่ตำแหน่ง
            var colIndex = {};
            normalizedExpected.forEach(function(h, i) {
                var idx = headers.indexOf(h);
                if (idx !== -1) colIndex[expectedHeaders[i]] = idx;
            });
            var hasAllHeaders = expectedHeaders.every(function(h) { return colIndex[h] !== undefined; });

            if (!hasAllHeaders) {
                Swal.fire({$msgError}, {$msgCsvFormatError} + expectedHeaders.join(', '), 'error');
                return;
            }

            var warehouseId = (warehouseTomSelect && warehouseTomSelect.getValue()) ? warehouseTomSelect.getValue() : ($(warehouseSelectEl).val() || '');
            if (!warehouseId) {
                Swal.fire({$msgAlert}, {$msgSelectWarehouse}, 'warning');
                return;
            }

            Swal.fire({
                title: 'กำลังตรวจสอบไฟล์...',
                text: 'กำลังวิเคราะห์รายการในไฟล์ CSV',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            var items = [];
            var skipped = []; // [{row, reason, name, raw}]
            for (var i = 1; i < lines.length; i++) {
                var rowNum = i + 1; // CSV row number (1-based, header = row 1)
                var raw = lines[i];
                // ไม่ pre-process หลักพัน — regex แยกไม่ออกระหว่าง "6,130" (qty=6, price=130 = 2 คอลัมน์)
                // กับ "6,130" (= 6130 หลักพันคำเดียว) ทำให้แถว qty 1 หลัก + price 3 หลักโดน merge ผิด
                // ปล่อยให้ parseCsvLine ตัดตามไวยากรณ์ CSV จริง แล้ว toNumber ตัด , ราย cell เอง
                // (ถ้าผู้ใช้ต้องการใส่ 1,500 ในเซลล์เดียว ต้องคร่อมด้วย " เช่น "1,500" ตาม RFC 4180)
                var values = parseCsvLine(raw);

                if (values.length < 4) {
                    skipped.push({ row: rowNum, name: values[colIndex['ชื่อวัสดุ']] || '', reason: 'จำนวนคอลัมน์น้อยกว่า 4 (ตรวจ comma ใน CSV)', raw: raw.substring(0, 60) });
                    continue;
                }

                // อ่านค่าจากตำแหน่งคอลัมน์จริง (ตาม header) — ไม่ใช่ตำแหน่งคงที่
                // กันกรณีผู้ใช้สลับคอลัมน์ในไฟล์ CSV เช่น เอา "ราคา/หน่วย" มาก่อน "จำนวน"
                var itemCode = values[colIndex['รหัสวัสดุ']] || '';
                var itemName = values[colIndex['ชื่อวัสดุ']] || '';
                var unitName = values[colIndex['หน่วยนับ']] || '';
                var qty = toNumber(values[colIndex['จำนวน']]);
                var unitPrice = toNumber(values[colIndex['ราคา/หน่วย']]);
                var lotNumber = values[colIndex['Lot Number']] || '';
                var expiryDate = values[colIndex['วันหมดอายุ']] || '';

                // อนุญาตให้รหัสว่างได้ (server จะ match จากชื่อ / สร้างใหม่ให้)
                // แต่ "ชื่อ" และ "จำนวน" ยังจำเป็น
                if (!itemName) {
                    skipped.push({ row: rowNum, name: '', reason: 'ไม่มีชื่อวัสดุ', raw: raw.substring(0, 60) });
                    continue;
                }
                if (qty <= 0) {
                    skipped.push({ row: rowNum, name: itemName, reason: 'จำนวนเป็น 0 หรือไม่ใช่ตัวเลข', raw: raw.substring(0, 60) });
                    continue;
                }

                items.push({
                    item_code: itemCode,
                    item_name: itemName,
                    unit_name: unitName,
                    qty: qty,
                    unit_price: unitPrice,
                    lot_number: lotNumber,
                    expiry_date: expiryDate
                });
            }

            if (items.length === 0) {
                Swal.fire({$msgError}, {$msgCsvNoItems}, 'error');
                return;
            }

            importCSVItems(items, warehouseId, categoryId, skipped);
        }

        // แปลงค่า cell เป็นตัวเลข — ทน comma หลักพัน + space ปะปน
        // เช่น "1,739.00" → 1739, " 12.5 " → 12.5, "" → 0, "abc" → 0
        function toNumber(s) {
            if (s == null) return 0;
            var clean = String(s).replace(/,/g, '').trim();
            if (clean === '') return 0;
            var n = parseFloat(clean);
            return isFinite(n) ? n : 0;
        }

        // ตัด comma หลักพันออกจากตัวเลข (Excel export ชอบใส่)
        // - "ITEM,1,500,..." → "ITEM,1500,..."  (1,500 = หนึ่งพันห้าร้อย ไม่ใช่ 2 column)
        // - "ITEM,1,500.50,..." → "ITEM,1500.50,..."
        // - "ITEM,1,000,000,..." → "ITEM,1000000,..."
        // เงื่อนไข match: 1-3 digit + , + 3 digit (+ optional decimal) ที่ขึ้นต้นด้วย non-digit
        // ใช้ capture group แทน lookbehind (compat กับ Safari เก่า)
        function normalizeThousandsSeparator(line) {
            return line.replace(/(^|[^\d.])(\d{1,3}(?:,\d{3})+(?:\.\d+)?)/g, function(_, prefix, num) {
                return prefix + num.replace(/,/g, '');
            });
        }

        // Proper CSV parser — รองรับ quoted string ที่มี comma ข้างใน
        // เช่น  "Paracetamol, 500mg",ชื่อ,หน่วย,...
        function parseCsvLine(line) {
            var result = [];
            var cur = '';
            var inQuotes = false;
            for (var i = 0; i < line.length; i++) {
                var c = line.charAt(i);
                if (inQuotes) {
                    if (c === '"') {
                        if (line.charAt(i + 1) === '"') { cur += '"'; i++; } // escaped ""
                        else { inQuotes = false; }
                    } else {
                        cur += c;
                    }
                } else {
                    if (c === ',') { result.push(cur); cur = ''; }
                    else if (c === '"' && cur === '') { inQuotes = true; }
                    else { cur += c; }
                }
            }
            result.push(cur);
            return result.map(function(v) { return v.trim(); });
        }

        function importCSVItems(items, warehouseId, categoryId, skipped) {
            skipped = skipped || [];
            // flow:
            // 1) dry_run preview
            // 2) ถ้ามี category_mismatches → mismatch warning dialog (update / keep / cancel)
            // 3) ถ้ามี would_create → confirm dialog (สร้างใหม่ N รายการ)
            // 4) real import (ส่ง update_categories ถ้า user เลือกปรับ)
            callImport(items, warehouseId, categoryId, true, false, [])
                .then(function(preview) {
                    if (!preview || !preview.success) {
                        Swal.fire({$msgError}, (preview && preview.message) || {$msgImportError}, 'error');
                        return;
                    }
                    var wouldCreate = preview.would_create || [];
                    var wouldReuse  = preview.would_reuse || [];
                    var mismatches  = preview.category_mismatches || [];
                    var targetTitle = preview.target_category_title || '';
                    var errors      = preview.errors || [];

                    // Step 2: mismatch dialog (ถ้ามี)
                    var mismatchPromise = (mismatches.length === 0)
                        ? Promise.resolve({ proceed: true, updateCategories: false, codes: [] })
                        : showMismatchDialog(mismatches, targetTitle);

                    mismatchPromise.then(function(decision) {
                        if (!decision || !decision.proceed) return; // user cancelled

                        // Step 3: confirm "สร้างใหม่"
                        var createPromise = (wouldCreate.length === 0)
                            ? Promise.resolve(true)
                            : showCreateConfirmDialog(wouldCreate, wouldReuse, skipped, errors);

                        createPromise.then(function(confirmed) {
                            if (!confirmed) return;
                            // Step 4: real import
                            doRealImport(items, warehouseId, categoryId, skipped, decision.updateCategories, decision.codes);
                        });
                    });
                })
                .catch(function(error) {
                    Swal.fire({$msgError}, ({$msgConnectionError}) + (error.message || ''), 'error');
                });
        }

        // ─── Category mismatch warning dialog ───
        function showMismatchDialog(mismatches, targetTitle) {
            return new Promise(function(resolve) {
                // group by current_category_title
                var grouped = {};
                var codesAll = [];
                mismatches.forEach(function(m) {
                    var key = m.current_category_title || '(ไม่ระบุ)';
                    if (!grouped[key]) grouped[key] = [];
                    grouped[key].push(m);
                    if (m.item_code) codesAll.push(m.item_code);
                });

                var groupsHtml = Object.keys(grouped).map(function(catTitle) {
                    var rows = grouped[catTitle].map(function(m) {
                        return '<li class="mismatch-row">' +
                            '<code class="mismatch-row__code">' + escapeHtml(m.item_code) + '</code>' +
                            '<span class="mismatch-row__name">' + escapeHtml(m.item_name) + '</span>' +
                        '</li>';
                    }).join('');
                    return '<section class="mismatch-group">' +
                        '<header class="mismatch-group__head">' +
                            '<span class="mismatch-group__title">' + escapeHtml(catTitle) + '</span>' +
                            '<span class="mismatch-group__count">' + grouped[catTitle].length + ' รายการ</span>' +
                        '</header>' +
                        '<ul class="mismatch-group__list">' + rows + '</ul>' +
                    '</section>';
                }).join('');

                var html =
                    '<div class="mismatch-dialog text-start">' +
                        '<div class="mismatch-banner">' +
                            '<i class="bi bi-exclamation-triangle-fill"></i>' +
                            '<div>' +
                                '<div class="mismatch-banner__title">วัสดุในไฟล์มีประเภทเดิมไม่ตรงกับที่เลือก</div>' +
                                '<div class="mismatch-banner__caption">ประเภทที่เลือกตอนนี้: <strong>' + escapeHtml(targetTitle) + '</strong></div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="mismatch-help">' +
                            'เลือกได้ว่าจะ <strong>ปรับประเภทของวัสดุในระบบ</strong> ให้เป็น "' + escapeHtml(targetTitle) + '" เพื่อให้รายงานในอนาคตตรงกัน หรือจะ <strong>คงไว้เหมือนเดิม</strong> แล้วบันทึกการรับเข้าต่อไป' +
                        '</div>' +
                        '<div class="mismatch-groups">' + groupsHtml + '</div>' +
                    '</div>';

                Swal.fire({
                    icon: undefined,
                    title: 'พบ ' + mismatches.length + ' รายการที่ประเภทไม่ตรง',
                    html: html,
                    width: 640,
                    showCancelButton: true,
                    showDenyButton: true,
                    showCloseButton: false,
                    confirmButtonText: '<i class="bi bi-arrow-repeat"></i> ปรับประเภทให้เป็น "' + escapeHtml(targetTitle) + '"',
                    denyButtonText: 'คงประเภทเดิม',
                    cancelButtonText: 'ยกเลิกการนำเข้า',
                    confirmButtonColor: '#b45309', // warning ink — destructive-ish
                    denyButtonColor: '#475569',    // muted neutral
                    cancelButtonColor: '#6c757d',
                    reverseButtons: false,
                    focusDeny: true, // ค่า default ที่ปลอดภัยกว่า
                    customClass: { popup: 'mismatch-popup' }
                }).then(function(res) {
                    if (res.isConfirmed) {
                        resolve({ proceed: true, updateCategories: true, codes: codesAll });
                    } else if (res.isDenied) {
                        resolve({ proceed: true, updateCategories: false, codes: [] });
                    } else {
                        resolve({ proceed: false });
                    }
                });
            });
        }

        // ─── Confirm "สร้างใหม่" dialog (เดิม แยกออกมาเป็นฟังก์ชัน) ───
        function showCreateConfirmDialog(wouldCreate, wouldReuse, skipped, errors) {
            return new Promise(function(resolve) {
                var listHtml = wouldCreate.map(function(it) {
                    var code = it.item_code ? ' <code class="text-muted small">(' + escapeHtml(it.item_code) + ')</code>' : '';
                    return '<li class="text-start">' + escapeHtml(it.item_name) + code + '</li>';
                }).join('');

                var subInfo = '';
                if (wouldReuse.length > 0) {
                    subInfo += '<div class="text-muted small mb-2">ใช้วัสดุเดิม ' + wouldReuse.length + ' รายการ (ไม่สร้างซ้ำ)</div>';
                }
                if (skipped.length > 0) {
                    subInfo += '<div class="text-warning small mb-2">มีรายการในไฟล์ที่ไม่ผ่านการอ่าน ' + skipped.length + ' แถว — ดูรายละเอียดใน "สรุปหลังบันทึก"</div>';
                }
                if (errors.length > 0) {
                    subInfo += '<div class="text-danger small mb-2">ข้อผิดพลาดเชิง validation ' + errors.length + ' รายการ — จะไม่บันทึกรายการเหล่านั้น</div>';
                }

                Swal.fire({
                    icon: 'question',
                    title: 'จะสร้างวัสดุใหม่ ' + wouldCreate.length + ' รายการ',
                    html:
                        '<div class="text-start">' +
                            subInfo +
                            '<div class="fw-semibold mb-1">รายชื่อที่จะสร้าง:</div>' +
                            '<ul class="mb-0 small" style="max-height: 240px; overflow-y: auto;">' + listHtml + '</ul>' +
                        '</div>',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันบันทึก',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#0d6efd',
                    focusCancel: true,
                }).then(function(res) {
                    resolve(!!res.isConfirmed);
                });
            });
        }

        function escapeHtml(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
            });
        }

        function callImport(items, warehouseId, categoryId, dryRun, updateCategories, applyCategoryToCodes) {
            var importUrl = {$importCsvUrlJson};
            return fetch(importUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    items: items,
                    warehouse_id: warehouseId,
                    category_id: categoryId,
                    dry_run: !!dryRun,
                    update_categories: !!updateCategories,
                    apply_category_to_codes: applyCategoryToCodes || []
                })
            })
            .then(function(response) {
                var ct = response.headers.get('Content-Type') || '';
                if (!ct.includes('application/json')) {
                    return response.text().then(function() {
                        var msg = response.status === 403 || response.status === 401
                            ? 'ไม่มีสิทธิ์หรือกรุณาเข้าสู่ระบบใหม่'
                            : 'เซิร์ฟเวอร์ส่งกลับข้อมูลที่ไม่ใช่ JSON (รหัส ' + response.status + ')';
                        throw new Error(msg);
                    });
                }
                if (!response.ok) {
                    return response.json().then(function(err) {
                        throw new Error(err.message || 'รหัส ' + response.status);
                    }).catch(function() {
                        throw new Error('รหัส ' + response.status);
                    });
                }
                return response.json();
            });
        }

        function doRealImport(items, warehouseId, categoryId, skipped, updateCategories, applyCategoryToCodes) {
            skipped = skipped || [];
            Swal.fire({
                title: 'กำลังบันทึก...',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });
            return callImport(items, warehouseId, categoryId, false, updateCategories, applyCategoryToCodes)
                .then(function(result) {
                    if (!result.success) {
                        Swal.fire({$msgError}, result.message || {$msgImportError}, 'error');
                        return;
                    }
                    // ใส่ skipped เข้าไปใน result ก่อนแสดง summary
                    result.skipped = skipped;
                    showImportSummary(result);
                })
                .catch(function(error) {
                    Swal.fire({$msgError}, ({$msgConnectionError}) + (error.message || ''), 'error');
                });
        }

        function showImportSummary(result) {
            var added       = result.added || [];
            var createdInfo = result.created_info || (result.created || []).map(function(c) { return { item_code: c, item_name: '-' }; });
            var reusedInfo  = result.reused_info  || (result.reused  || []).map(function(c) { return { item_code: c, item_name: '-' }; });
            var categoryUpdated = result.category_updated || [];
            var errors      = result.errors || [];
            var skipped     = result.skipped || [];

            var totalProcessed = added.length + skipped.length + errors.length;
            var hasIssues = skipped.length > 0 || errors.length > 0;

            // Summary stats inline
            var stats =
                '<div class="d-flex flex-wrap gap-4 justify-content-center mb-3 pb-3 border-bottom text-center">' +
                    statCell('รายการในไฟล์', totalProcessed, 'text-body') +
                    statCell('นำเข้าสำเร็จ', added.length, 'text-success') +
                    (createdInfo.length > 0 ? statCell('สร้างใหม่', createdInfo.length, 'text-success') : '') +
                    (reusedInfo.length  > 0 ? statCell('ใช้เดิม',   reusedInfo.length,  'text-primary') : '') +
                    (categoryUpdated.length > 0 ? statCell('ปรับประเภท', categoryUpdated.length, 'text-warning') : '') +
                    (hasIssues          ? statCell('ไม่ผ่าน', skipped.length + errors.length, 'text-danger') : '') +
                '</div>';

            var sections = '';
            if (categoryUpdated.length > 0) {
                var updRows = categoryUpdated.map(function(u) {
                    return '<tr>' +
                        '<td class="font-monospace text-muted" style="white-space:nowrap;">' + escapeHtml(u.item_code) + '</td>' +
                        '<td>' + escapeHtml(u.item_name) + '</td>' +
                        '<td class="small text-muted">' + escapeHtml(u.from_title || '-') + '</td>' +
                        '<td class="small text-muted text-center" aria-hidden="true">→</td>' +
                        '<td class="small text-warning fw-semibold">' + escapeHtml(u.to_title || '-') + '</td>' +
                    '</tr>';
                }).join('');
                sections += '<div class="mb-3">' +
                    '<div class="fw-semibold mb-2 text-warning">ปรับประเภทวัสดุในระบบ (' + categoryUpdated.length + ' รายการ)</div>' +
                    '<div class="border rounded" style="max-height: 240px; overflow-y: auto;">' +
                        '<table class="table table-sm table-hover mb-0 align-middle">' +
                            '<thead class="table-light position-sticky top-0">' +
                                '<tr>' +
                                    '<th style="width: 110px;">รหัส</th>' +
                                    '<th>ชื่อวัสดุ</th>' +
                                    '<th style="width: 130px;">จากประเภท</th>' +
                                    '<th style="width: 28px;"></th>' +
                                    '<th style="width: 130px;">เป็นประเภท</th>' +
                                '</tr>' +
                            '</thead>' +
                            '<tbody>' + updRows + '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>';
            }
            if (createdInfo.length > 0) {
                sections += buildSection('สร้างวัสดุใหม่ (' + createdInfo.length + ')', createdInfo, 'success');
            }
            if (reusedInfo.length > 0) {
                sections += buildSection('ใช้วัสดุเดิม (match จากชื่อ) — ' + reusedInfo.length + ' รายการ', reusedInfo, 'primary');
            }

            // รายการที่ไม่ผ่าน: skipped (ฝั่ง client) + errors (ฝั่ง server)
            // เปิด default ถ้ามี — เพราะ user ต้องเห็นว่า "อะไรหายไป"
            if (skipped.length > 0 || errors.length > 0) {
                var rows = '';
                skipped.forEach(function(s) {
                    rows += '<tr>' +
                        '<td class="text-muted small">' + s.row + '</td>' +
                        '<td>' + escapeHtml(s.name || '-') + '</td>' +
                        '<td class="small text-warning">' + escapeHtml(s.reason) + '</td>' +
                    '</tr>';
                });
                errors.forEach(function(e) {
                    rows += '<tr>' +
                        '<td class="text-muted small">—</td>' +
                        '<td colspan="2" class="small text-danger">' + escapeHtml(e) + '</td>' +
                    '</tr>';
                });
                sections +=
                    '<details class="mb-2" open>' +
                        '<summary class="fw-semibold text-danger mb-2" style="cursor:pointer;">' +
                            'รายการที่ไม่ผ่าน — ' + (skipped.length + errors.length) + ' แถว (กดเพื่อย่อ)' +
                        '</summary>' +
                        '<div class="border rounded" style="max-height: 240px; overflow-y: auto;">' +
                            '<table class="table table-sm table-hover mb-0 align-middle">' +
                                '<thead class="table-light position-sticky top-0">' +
                                    '<tr>' +
                                        '<th style="width: 60px;">แถวที่</th>' +
                                        '<th>ชื่อ / รหัส</th>' +
                                        '<th>เหตุผล</th>' +
                                    '</tr>' +
                                '</thead>' +
                                '<tbody>' + rows + '</tbody>' +
                            '</table>' +
                        '</div>' +
                    '</details>';
            }

            Swal.fire({
                icon: added.length > 0 && !hasIssues ? 'success' : (added.length > 0 ? 'warning' : 'error'),
                title: 'นำเข้าสำเร็จ ' + added.length + ' / ' + totalProcessed + ' รายการ',
                html: '<div class="text-start" style="font-size:0.875rem;">' + stats + sections + '</div>',
                width: 720,
                confirmButtonText: 'ตกลง',
            }).then(function() {
                addCSVItemsToTable(result.items || []);
            });
        }

        function statCell(label, value, toneCls) {
            return '<div>' +
                '<div class="text-muted small mb-1">' + label + '</div>' +
                '<div class="fs-5 fw-semibold ' + toneCls + '">' + Number(value).toLocaleString() + '</div>' +
            '</div>';
        }

        function buildSection(title, rows, tone) {
            var body = rows.map(function(r) {
                return '<tr>' +
                    '<td class="font-monospace text-muted" style="white-space:nowrap;">' + escapeHtml(r.item_code) + '</td>' +
                    '<td>' + escapeHtml(r.item_name) + '</td>' +
                '</tr>';
            }).join('');
            return '<div class="mb-3">' +
                '<div class="fw-semibold mb-2 text-' + tone + '">' + escapeHtml(title) + '</div>' +
                '<div class="border rounded" style="max-height: 240px; overflow-y: auto;">' +
                    '<table class="table table-sm table-hover mb-0 align-middle">' +
                        '<thead class="table-light position-sticky top-0">' +
                            '<tr>' +
                                '<th style="width: 110px;">รหัส</th>' +
                                '<th>ชื่อวัสดุ</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>' + body + '</tbody>' +
                    '</table>' +
                '</div>' +
            '</div>';
        }

        function ymdToThaiDisplay(ymd) {
            if (!ymd || ymd.length < 10) return '';
            var y = parseInt(ymd.substring(0, 4), 10);
            var m = parseInt(ymd.substring(5, 7), 10);
            var d = parseInt(ymd.substring(8, 10), 10);
            var pad = function(n) { return n < 10 ? '0' + n : '' + n; };
            return pad(d) + '/' + pad(m) + '/' + (y + 543);
        }
        function addCSVItemsToTable(items) {
            $('#emptyRow').hide();
            var currentIndex = $('.item-row').length;
            var useAutoLot = $('input[name="lotMode"]:checked').val() === 'auto';

            items.forEach(function(item, i) {
                var expiryDateInput = item.expiry_date ? item.expiry_date.substring(0, 10) : '';
                var expiryDisplay = expiryDateInput ? ymdToThaiDisplay(expiryDateInput) : '';
                var unitValue = item.unit_name || '-';
                var lotVal = useAutoLot ? getAutoLotNumber(currentIndex + i) : (item.lot_number || '');
                var imgUrl = item.image_url || '';
                var itemCode = escapeHtml(item.item_code || '');
                var itemName = escapeHtml(item.item_name || '');
                var itemCell =
                    '<div class="item-cell">' +
                        (imgUrl
                            ? '<img src="' + escapeHtml(imgUrl) + '" alt="" class="item-cell__img" loading="lazy" onerror="this.style.display=\\'none\\'">'
                            : '<span class="item-cell__img item-cell__img--placeholder"><i class="bi bi-box"></i></span>') +
                        '<div class="item-cell__body">' +
                            '<div class="item-cell__name">' + itemName + '</div>' +
                            (itemCode ? '<div class="item-cell__code">' + itemCode + '</div>' : '') +
                        '</div>' +
                    '</div>';
                var row = '<tr class="item-row">' +
                    '<td class="text-center"><input type="checkbox" class="form-check-input row-check"></td>' +
                    '<td class="text-center text-muted"></td>' +
                    '<td><input type="hidden" name="StockDetail[' + currentIndex + '][item_code]" value="' + (item.item_code || '') + '">' + itemCell + '</td>' +
                    '<td><input type="hidden" name="StockDetail[' + currentIndex + '][unit_name]" value="' + (unitValue || '-') + '"><span class="text-muted">' + (unitValue || '-') + '</span></td>' +
                    '<td class="text-muted small">' + (item.category_title || '-') + '</td>' +
                    '<td><input type="text" name="StockDetail[' + currentIndex + '][lot_number]" class="form-control lot-number-input" value="' + (lotVal || '') + '" placeholder="กรอกหรือกำหนดเอง"></td>' +
                    '<td><input type="text" id="expiry-date-' + currentIndex + '" name="StockDetail[' + currentIndex + '][expiry_date]" class="form-control expiry-date-thai" value="' + (expiryDisplay || '') + '" placeholder="วว/ดด/พ.ศ." autocomplete="off"></td>' +
                    '<td><input type="number" name="StockDetail[' + currentIndex + '][qty]" class="form-control text-center qty-input" value="' + (item.qty || 1) + '" min="1" step="1"></td>' +
                    '<td><input type="number" name="StockDetail[' + currentIndex + '][unit_price]" class="form-control text-end price-input" value="' + (item.unit_price || 0) + '" step="1"></td>' +
                    '<td class="text-end fw-bold row-total">' + ((item.qty || 0) * (item.unit_price || 0)).toFixed(2) + '</td>' +
                    '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove border-0"><i class="bi bi-trash"></i></button></td>' +
                    '</tr>';
                $('#detail-body').append(row);
                if (typeof thaiDatepicker === 'function') thaiDatepicker('#expiry-date-' + currentIndex);
                currentIndex++;
            });

            reOrder();
            calculateTotal();
            $('#csvFileInput').val('');
        }

    });
JS;
$this->registerJs($js, \yii\web\View::POS_END);

$this->registerCss(<<<CSS
.receive-form .item-cell {
    display: flex; align-items: center; gap: 0.6rem;
    min-width: 0;
}
.receive-form .item-cell__img {
    width: 40px;
    height: 40px;
    min-width: 40px;
    max-width: 40px;
    max-height: 40px;
    border-radius: 8px;
    object-fit: cover;
    display: block;
    background: #f1f5f9;
    border: 1px solid rgba(15, 23, 42, 0.08);
    flex: 0 0 40px;
}
.receive-form .item-cell__img--placeholder {
    display: inline-flex; align-items: center; justify-content: center;
    color: #94a3b8;
    background: #f1f5f9;
    font-size: 1.1rem;
}
.receive-form .item-cell__body { min-width: 0; flex: 1; }
.receive-form .item-cell__name {
    font-weight: 600; color: #1a202c;
    line-height: 1.25;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.receive-form .item-cell__code {
    font-size: 0.72rem; color: #718096;
    font-family: ui-monospace, SFMono-Regular, "JetBrains Mono", monospace;
    line-height: 1.2; margin-top: 0.1rem;
}

/* ─── Category mismatch dialog ─── */
/* Strategy: ตัด popup ทั้งก้อนให้ไม่เกิน 90vh, แล้วบังคับให้ html-container เป็น scroll ตัวเดียว
   ปุ่ม actions ของ SweetAlert จะอยู่ใต้ html-container เสมอ (grid layout) ไม่หลุดขอบ */
.mismatch-popup {
    max-height: 90vh !important;
    width: 640px !important;
    max-width: 95vw !important;
}
.mismatch-popup .swal2-html-container {
    margin: 0.5rem 1rem 0 !important;
    padding: 0 0.25rem 0.25rem !important;
    max-height: 62vh !important;
    overflow-y: auto !important;
    overscroll-behavior: contain;
    text-align: left !important;
}
.mismatch-popup .swal2-html-container::-webkit-scrollbar { width: 8px; }
.mismatch-popup .swal2-html-container::-webkit-scrollbar-thumb {
    background: rgba(15,23,42,0.22); border-radius: 999px;
}
.mismatch-popup .swal2-html-container::-webkit-scrollbar-thumb:hover { background: rgba(15,23,42,0.35); }
.mismatch-popup .swal2-html-container::-webkit-scrollbar-track { background: transparent; }
.mismatch-popup .swal2-actions { gap: 0.4rem; flex-wrap: wrap; }
.mismatch-popup .swal2-confirm,
.mismatch-popup .swal2-deny,
.mismatch-popup .swal2-cancel {
    font-size: 0.85rem !important;
    padding: 0.5rem 0.85rem !important;
}
.mismatch-dialog { font-size: 0.86rem; color: #1a202c; }
.mismatch-banner {
    display: flex; align-items: flex-start; gap: 0.65rem;
    background: rgba(180, 83, 9, 0.08);
    border: 1px solid rgba(180, 83, 9, 0.18);
    border-radius: 10px;
    padding: 0.75rem 0.85rem;
    margin-bottom: 0.75rem;
}
.mismatch-banner i {
    color: #b45309; font-size: 1.15rem; margin-top: 0.1rem;
    flex-shrink: 0;
}
.mismatch-banner__title { font-weight: 700; color: #1a202c; line-height: 1.3; }
.mismatch-banner__caption { font-size: 0.78rem; color: #4a5568; margin-top: 0.1rem; }
.mismatch-help {
    font-size: 0.8rem; color: #4a5568;
    background: #f7f9fc;
    border-left: 1px solid rgba(15,23,42,0.08); /* hairline only — not a side stripe */
    border-radius: 8px;
    padding: 0.6rem 0.75rem;
    margin-bottom: 0.85rem;
    line-height: 1.5;
}
.mismatch-help strong { color: #1a202c; }
/* ไม่ใส่ max-height ที่นี่ — ปล่อยให้ .swal2-html-container เป็นตัว scroll
   วิธีนี้กัน double-scroll-container และให้ปุ่ม sweetalert อยู่กับที่เสมอ */
.mismatch-groups {
    display: block;
}
.mismatch-group + .mismatch-group { margin-top: 0.6rem; }
.mismatch-group:first-child { margin-top: 0; }
.mismatch-group {
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 10px;
    overflow: hidden;
}
.mismatch-group__head {
    display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
    background: #f7f9fc;
    padding: 0.45rem 0.7rem;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}
.mismatch-group__title { font-weight: 600; color: #1a202c; font-size: 0.85rem; }
.mismatch-group__count {
    background: #eef2f7;
    color: #4a5568;
    font-size: 0.7rem; font-weight: 600;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
}
.mismatch-group__list {
    list-style: none; margin: 0; padding: 0.35rem 0;
}
.mismatch-row {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.35rem 0.75rem;
    font-size: 0.82rem;
}
.mismatch-row + .mismatch-row {
    border-top: 1px solid rgba(15, 23, 42, 0.04);
}
.mismatch-row__code {
    background: #eef2f7;
    color: #4a5568;
    font-family: ui-monospace, SFMono-Regular, "JetBrains Mono", monospace;
    font-size: 0.72rem;
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
    flex-shrink: 0;
    min-width: 5.5rem; text-align: center;
}
.mismatch-row__name {
    color: #1a202c;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    min-width: 0;
}

@media (prefers-reduced-motion: reduce) {
    .mismatch-popup, .mismatch-popup * { animation: none !important; transition: none !important; }
}
CSS
);
?>
