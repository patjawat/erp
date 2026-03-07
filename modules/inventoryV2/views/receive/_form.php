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
            <div class="row g-3 mb-4 p-3 rounded border">
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
                <div class="col-md-2">
                    <?= $form->field($model, 'contact_id')->widget(TomSelectWidget::class, [
                        'items' => $listVendors,
                        'options' => ['class' => 'form-select', 'id' => 'stockorder-contact_id'],
                        'clientOptions' => [
                            'placeholder' => 'เลือกผู้ขาย (ไม่บังคับ)',
                            'allowEmptyOption' => true,
                        ],
                    ])->label('ผู้ขาย') ?>
                </div>
                <div class="col-md-2">
                    <?= Html::activeHiddenInput($model, 'order_type', ['value' => 'IN']) ?>
                    <?= $form->field($model, 'order_no')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'เว้นว่างได้ถ้าไม่ใช่การจัดซื้อ',
                        'id' => 'stockorder-order_no',
                    ])->label('เลขที่ใบรับเข้า')->hint('กรณีไม่ใช่การจัดซื้อ ไม่มีเลขใบรับสินค้า สามารถเว้นว่างได้ ระบบจะสร้างเลขที่ให้อัตโนมัติ', ['class' => 'form-text text-muted small']) ?>
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
                    <small class="text-info"><i class="bi bi-info-circle"></i> รูปแบบ CSV: รหัสพัสดุ,ชื่อพัสดุ,หน่วยนับ,จำนวน,ราคา/หน่วย,Lot Number,วันหมดอายุ (YYYY-MM-DD)</small>
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

            <div class="table-responsive mt-4">
                <table class="table table-hover align-middle mb-0" id="inboundTable">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 4%;">#</th>
                            <th style="width: 18%;">รายการวัสดุ</th>
                            <th style="width: 10%;">หน่วยนับ</th>
                            <th style="width: 12%;">ประเภทวัสดุ</th>
                            <th style="width: 12%;">Lot Number</th>
                            <th style="width: 12%;">วันหมดอายุ</th>
                            <th style="width: 8%;">จำนวน</th>
                            <th style="width: 10%;">ราคา/หน่วย</th>
                            <th style="width: 12%;">รวม</th>
                            <th style="width: 4%;"></th>
                        </tr>
                    </thead>
                    <tbody id="detail-body" class="align-middle table-group-divider">
                        <?php if (!empty($items) && is_array($items)): ?>
                            <?php foreach ($items as $index => $item): ?>
                                <?php
                                // กรณีหน้า Create รุ่นใหม่จะเป็น Model เปล่า item_code จะไม่มีค่า
                                if (!isset($item) || !is_object($item) || empty($item->item_code)) continue;
                                ?>
                                <tr class="item-row">
                                    <td class="text-center text-muted"><?= $index + 1 ?></td>
                                    <td>
                                        <input type="hidden" name="StockDetail[<?= $index ?>][item_code]" value="<?= $item->item_code ?>">
                                        <strong><?= $item->item->item_name ?? 'ไม่พบชื่อวัสดุ' ?></strong>
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
                                    <td><input type="number" name="StockDetail[<?= $index ?>][qty]" class="form-control text-center qty-input" value="<?= $item->qty ?>" min="0.01" step="0.01"></td>
                                    <td><input type="number" name="StockDetail[<?= $index ?>][unit_price]" class="form-control text-end price-input" value="<?= $item->unit_price ?>" step="0.01"></td>
                                    <td class="text-end fw-bold row-total"><?= number_format($item->qty * $item->unit_price, 2) ?></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove border-0"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <tr id="emptyRow" style="<?= (!empty($items) && is_array($items) && count($items) > 0 && isset($items[0]) && is_object($items[0]) && !empty($items[0]->item_code)) ? 'display:none' : '' ?>">
                            <td colspan="10" class="text-center py-4 text-muted">ยังไม่มีรายการที่ถูกเพิ่ม</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold text-end">
                        <tr>
                            <td colspan="8">ยอดรวมสุทธิ</td>
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
$csvHeadersJson = json_encode(['รหัสพัสดุ', 'ชื่อพัสดุ', 'หน่วยนับ', 'จำนวน', 'ราคา/หน่วย', 'Lot Number', 'วันหมดอายุ']);
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
                input.attr('placeholder', 'เว้นว่างได้ถ้าไม่ใช่การจัดซื้อ');
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
                });
                itemTypeTomSelect.setValue(prevItemType, true);
                return;
            }
            prevItemType = newVal;
            if (typeof itemSelect !== 'undefined' && itemSelect) {
                if (newVal) { itemSelect.enable(); itemSelect.placeholder = 'พิมพ์ชื่อหรือรหัสพัสดุ...'; } else { itemSelect.disable(); itemSelect.placeholder = 'กรุณาเลือกประเภทวัสดุก่อน'; }
            }
        }
    });

    function loadItemTypesByWarehouse(warehouseId, preferredItemType) {
        var url = itemTypeListUrl + (warehouseId ? '?warehouse_id=' + encodeURIComponent(warehouseId) : '');
        itemTypeTomSelect.placeholder = warehouseId ? 'เลือกประเภทวัสดุ' : 'กรุณาเลือกคลังก่อน';
        if (typeof itemSelect !== 'undefined' && itemSelect) { itemSelect.disable(); itemSelect.placeholder = 'กรุณาเลือกประเภทวัสดุก่อน'; }
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
                if (typeof itemSelect !== 'undefined' && itemSelect) {
                    itemSelect.enable();
                    itemSelect.placeholder = 'พิมพ์ชื่อหรือรหัสพัสดุ...';
                }
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
        $(this).find('td:first').text(index + 1);
    });
}

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
                    text: 'บันทึกไว้ก่อน ยังไม่รับเข้าคลังและยอดสต็อกไม่เปลี่ยนแปลง',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#6c757d',
                    cancelButtonColor: '#0d6efd',
                    confirmButtonText: 'ใช่, บันทึกฉบับร่าง',
                    cancelButtonText: {$msgCancel},
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
                cancelButtonText: {$msgCancel},
                reverseButtons: true // ให้ปุ่มยกเลิกอยู่ซ้าย
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
        '<td class="text-center text-muted"></td>' +
        '<td><input type="hidden" name="StockDetail[' + currentIndex + '][item_code]" value="' + (itemId || '') + '"><strong>' + (itemData.item_name || '') + '</strong></td>' +
        '<td><input type="hidden" name="StockDetail[' + currentIndex + '][unit_name]" value="' + (unitValue || '-') + '"><span class="text-muted">' + (unitValue || '-') + '</span></td>' +
        '<td class="text-muted small">' + typeLabel + '</td>' +
        '<td><input type="text" name="StockDetail[' + currentIndex + '][lot_number]" class="form-control lot-number-input" value="' + (lotVal || '') + '" placeholder="กรอกหรือกำหนดเอง"></td>' +
        '<td><input type="text" id="expiry-date-' + currentIndex + '" name="StockDetail[' + currentIndex + '][expiry_date]" class="form-control expiry-date-thai" placeholder="วว/ดด/พ.ศ." autocomplete="off"></td>' +
        '<td><input type="number" name="StockDetail[' + currentIndex + '][qty]" class="form-control text-center qty-input" value="1" min="1" step="0.01"></td>' +
        '<td><input type="number" name="StockDetail[' + currentIndex + '][unit_price]" class="form-control text-end price-input" value="0.00" step="0.01"></td>' +
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
        });

        // 4. ฟังก์ชันอัปโหลด CSV
        $('#btnImportCSV').on('click', function() {
            $('#csvFileInput').click();
        });

        // ดาวน์โหลด template ตัวอย่างนำเข้า CSV
        $('#btnDownloadCsvTemplate').on('click', function() {
            var headers = {$csvHeadersJson};
            var headerLine = headers.join(',');
            var exampleRow1 = 'ITEM001,ตัวอย่างพัสดุ 1,ชิ้น,10,5.50,LOT2024001,2025-12-31';
            var exampleRow2 = 'ITEM002,ตัวอย่างพัสดุ 2,กล่อง,5,120.00,LOT2024002,2026-06-30';
            var csvContent = '\\uFEFF' + headerLine + '\\n' + exampleRow1 + '\\n' + exampleRow2;
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

            var headers = lines[0].split(',').map(function(h) { return h.trim(); });
            var expectedHeaders = {$csvHeadersJson};
            var hasAllHeaders = expectedHeaders.every(function(h) { return headers.indexOf(h) !== -1; });
            
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
                title: 'กำลังประมวลผล...',
                text: 'กำลังตรวจสอบและสร้างพัสดุจาก CSV',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            var items = [];
            for (var i = 1; i < lines.length; i++) {
                var values = lines[i].split(',').map(function(v) { return v.trim(); });
                if (values.length < 4) continue;
                
                var itemCode = values[0] || '';
                var itemName = values[1] || '';
                var unitName = values[2] || '';
                var qty = parseFloat(values[3]) || 0;
                var unitPrice = parseFloat(values[4]) || 0;
                var lotNumber = values[5] || '';
                var expiryDate = values[6] || '';

                if (!itemCode || !itemName || qty <= 0) continue;

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

            importCSVItems(items, warehouseId, categoryId);
        }

        function importCSVItems(items, warehouseId, categoryId) {
            var importUrl = {$importCsvUrlJson};
            fetch(importUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    items: items,
                    warehouse_id: warehouseId,
                    category_id: categoryId
                })
            })
            .then(function(response) {
                var ct = response.headers.get('Content-Type') || '';
                if (!ct.includes('application/json')) {
                    return response.text().then(function(text) {
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
            })
            .then(function(result) {
                if (result.success) {
                    var added = result.added || [];
                    var created = result.created || [];
                    var errors = result.errors || [];
                    
                    var message = 'นำเข้าสำเร็จ ' + added.length + ' รายการ';
                    if (created.length > 0) {
                        message += "\\nสร้างพัสดุใหม่ " + created.length + " รายการ: " + created.join(", ");
                    }
                    if (errors.length > 0) {
                        message += "\\nมีข้อผิดพลาด " + errors.length + " รายการ";
                    }

                    Swal.fire({$msgImportSuccess}, message, 'success').then(function() {
                        addCSVItemsToTable(result.items || []);
                    });
                } else {
                    Swal.fire({$msgError}, result.message || {$msgImportError}, 'error');
                }
            })
            .catch(function(error) {
                Swal.fire({$msgError}, ({$msgConnectionError}) + (error.message || ''), 'error');
            });
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
                var row = '<tr class="item-row">' +
                    '<td class="text-center text-muted"></td>' +
                    '<td><input type="hidden" name="StockDetail[' + currentIndex + '][item_code]" value="' + (item.item_code || '') + '"><strong>' + (item.item_name || '') + '</strong></td>' +
                    '<td><input type="hidden" name="StockDetail[' + currentIndex + '][unit_name]" value="' + (unitValue || '-') + '"><span class="text-muted">' + (unitValue || '-') + '</span></td>' +
                    '<td class="text-muted small">' + (item.category_title || '-') + '</td>' +
                    '<td><input type="text" name="StockDetail[' + currentIndex + '][lot_number]" class="form-control lot-number-input" value="' + (lotVal || '') + '" placeholder="กรอกหรือกำหนดเอง"></td>' +
                    '<td><input type="text" id="expiry-date-' + currentIndex + '" name="StockDetail[' + currentIndex + '][expiry_date]" class="form-control expiry-date-thai" value="' + (expiryDisplay || '') + '" placeholder="วว/ดด/พ.ศ." autocomplete="off"></td>' +
                    '<td><input type="number" name="StockDetail[' + currentIndex + '][qty]" class="form-control text-center qty-input" value="' + (item.qty || 1) + '" min="0.01" step="0.01"></td>' +
                    '<td><input type="number" name="StockDetail[' + currentIndex + '][unit_price]" class="form-control text-end price-input" value="' + (item.unit_price || 0) + '" step="0.01"></td>' +
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
?>