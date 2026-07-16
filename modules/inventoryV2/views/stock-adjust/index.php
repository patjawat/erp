<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var array{warehouse_id:?int, item_code:?string, qty:?string, current_qty:?string, target_qty:?string, lot_number:string, note:string, source:string} $prefill */
$prefill = $prefill ?? [
    'warehouse_id' => null,
    'item_code' => null,
    'qty' => null,
    'current_qty' => null,
    'target_qty' => null,
    'lot_number' => '',
    'note' => '',
    'source' => '',
];

$this->title = 'ปรับยอด stock สินค้า';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-wrench-adjustable fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">เพิ่มหรือลดยอดคงเหลือในคลังโดยตรง (ใช้เมื่อตรวจนับแล้วพบยอดต่าง หรือแก้ไขยอดผิด)</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<a href="<?= Url::to(['/inventory-v2/stock-adjust/reset-warehouse']) ?>" class="btn btn-outline-warning btn-sm me-2">
    <i class="bi bi-arrow-counterclockwise me-1"></i> ล้างยอดคลัง (สำหรับทดสอบ)
</a>
<a href="<?= Url::to(['/inventory-v2/default/index']) ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i> เมนูย้อนกลับ
</a>
<?php $this->endBlock(); ?>

<?php
use app\widgets\TomSelectWidget;
?>

<div class="container-fluid py-4">
    <div class="card border shadow-sm rounded-3">
        <div class="card-body p-4">
            <form id="form-stock-adjust" class="needs-validation" novalidate>
                <input type="hidden" name="current_qty" id="current_qty" value="<?= $prefill['current_qty'] !== null ? Html::encode($prefill['current_qty']) : '' ?>">
                <input type="hidden" name="lot_number" id="lot_number" value="<?= Html::encode($prefill['lot_number'] ?: 'ADJUST') ?>">
                <input type="hidden" name="source" id="source" value="<?= Html::encode($prefill['source']) ?>">
                <?php if ($prefill['source']): ?>
                    <div class="alert alert-info d-flex align-items-start gap-2 py-2 mb-3" role="status">
                        <i class="bi bi-info-circle mt-1"></i>
                        <div>
                            <div class="fw-semibold">สร้างรายการปรับยอดจากประวัติการเคลื่อนไหววัสดุ</div>
                            <div class="small text-muted">ตรวจสอบยอดนับจริงและเหตุผลก่อนบันทึก ระบบจะสร้างเอกสาร ADJUST ใหม่ ไม่แก้ประวัติเดิม</div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">คลัง <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                            <?php foreach ($warehouses as $wid => $wname): ?>
                                <?php $selected = ($prefill['warehouse_id'] !== null && (int)$wid === (int)$prefill['warehouse_id']) ? ' selected' : ''; ?>
                                <option value="<?= $wid === '' ? '' : (int)$wid ?>"<?= $selected ?>><?= Html::encode($wname) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">พัสดุ <span class="text-danger">*</span></label>
                        <?= TomSelectWidget::widget([
                            'name' => 'item_code',
                            'id' => 'item_code',
                            'options' => ['class' => 'form-select', 'required' => true],
                            'items' => ['' => 'ค้นหาชื่อหรือรหัสพัสดุ...'],
                            'loadUrl' => Url::to(['/inventory-v2/stock-item/item-list']),
                            'loadUrlParamKeys' => ['warehouse_id'],
                            'instanceName' => 'itemSelect',
                            'clientOptions' => [
                                'valueField' => 'item_code',
                                'labelField' => 'item_name',
                                'searchField' => ['item_name', 'item_code'],
                                'placeholder' => 'ค้นหาชื่อหรือรหัสพัสดุ...',
                                'allowEmptyOption' => false,
                            ],
                        ]) ?>
                        <small class="text-muted">พิมพ์ชื่อหรือรหัสพัสดุเพื่อค้นหาและเลือกจากรายการ</small>
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                        <button type="button" id="btn-load-balance" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-repeat me-1"></i> ดึงยอดคงเหลือ
                        </button>
                    </div>
                </div>
                <input type="hidden" name="mode" id="mode" value="recount">

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <label class="form-label fw-semibold d-block">รูปแบบการปรับ</label>
                        <div class="btn-group w-100 w-md-auto" role="group" aria-label="โหมดการปรับยอด">
                            <input type="radio" class="btn-check" name="mode_radio" id="mode_recount" value="recount" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="mode_recount">
                                <i class="bi bi-calculator me-1"></i> ตรวจนับ — คิดมูลค่า
                            </label>
                            <input type="radio" class="btn-check" name="mode_radio" id="mode_qty_only" value="qty_only" autocomplete="off">
                            <label class="btn btn-outline-secondary" for="mode_qty_only">
                                <i class="bi bi-123 me-1"></i> ปรับจำนวนอย่างเดียว
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1" id="mode_hint">
                            เพิ่ม = ตีมูลค่าตามต้นทุน (ค่าเริ่มต้น = ต้นทุนเฉลี่ย) · ลด = คิดต้นทุนตามค่าเฉลี่ย → มูลค่าขยับตามจริง
                        </small>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">ยอดคงเหลือปัจจุบัน</label>
                        <div class="form-control-plaintext fw-bold text-primary" id="current_balance">—</div>
                        <small class="text-muted">มูลค่า: <span id="current_value">—</span> · เฉลี่ย/หน่วย: <span id="avg_cost">—</span></small>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">ยอดตรวจนับจริง</label>
                        <input type="number" name="target_qty" id="target_qty" class="form-control" step="any" placeholder="ยอดที่ต้องการให้คงเหลือ" value="<?= $prefill['target_qty'] !== null ? Html::encode($prefill['target_qty']) : '' ?>">
                        <small class="text-muted">กรอกยอดจริง ระบบจะคำนวณจำนวนที่ปรับให้</small>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">จำนวนที่ปรับ <span class="text-danger">*</span></label>
                        <input type="number" name="adjustment_qty" id="adjustment_qty" class="form-control" step="any" placeholder="บวก = เพิ่ม, ลบ = ลด" value="<?= $prefill['qty'] !== null ? Html::encode($prefill['qty']) : '' ?>" required>
                        <small class="text-muted">เช่น 10 = เพิ่ม 10 หน่วย, -5 = ลด 5 หน่วย</small>
                    </div>
                    <div class="col-12 col-md-3" id="unit_price_wrap">
                        <label class="form-label fw-semibold">ต้นทุน/หน่วย (ของที่เพิ่ม)</label>
                        <input type="number" name="unit_price" id="unit_price" class="form-control" step="any" min="0" placeholder="ค่าเริ่มต้น = ต้นทุนเฉลี่ย">
                        <small class="text-muted">ใช้ตอน<strong>เพิ่ม</strong>ยอด · เว้นว่าง = ใช้ต้นทุนเฉลี่ย</small>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">วันที่ปรับยอด</label>
                        <?= \app\widgets\datepicker\DatepickerThai::widget([
                            'name' => 'order_date',
                            'value' => \app\components\AppHelper::convertToThai(date('Y-m-d')),
                            'options' => ['id' => 'order_date', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.'],
                        ]) ?>
                        <div class="form-text">มีผลต่อการปิดเดือน — รายการจะถูกนับในงวดตามวันที่นี้ (ค่าเริ่มต้น = วันนี้)</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">หมายเหตุ</label>
                        <input type="text" name="note" id="note" class="form-control" placeholder="เหตุผลการปรับ (ถ้ามี)" value="<?= Html::encode($prefill['note']) ?>">
                    </div>
                </div>

                <div class="row mt-3" id="preview_wrap" style="display:none;">
                    <div class="col-12">
                        <div class="border rounded-3 p-3 bg-body-tertiary">
                            <div class="fw-semibold mb-2"><i class="bi bi-eye me-1"></i> ตรวจสอบก่อนบันทึก</div>
                            <div class="row g-3 text-center">
                                <div class="col-4">
                                    <div class="small text-muted">ก่อนปรับ</div>
                                    <div class="fw-bold" id="pv_before_qty">—</div>
                                    <div class="small text-muted" id="pv_before_val">—</div>
                                </div>
                                <div class="col-4">
                                    <div class="small text-muted">ผลต่าง</div>
                                    <div class="fw-bold" id="pv_delta_qty">—</div>
                                    <div class="small" id="pv_delta_val">—</div>
                                </div>
                                <div class="col-4">
                                    <div class="small text-muted">หลังปรับ</div>
                                    <div class="fw-bold text-primary" id="pv_after_qty">—</div>
                                    <div class="small text-primary" id="pv_after_val">—</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> บันทึกการปรับยอด
                        </button>
                        <a href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>" class="btn btn-outline-secondary ms-2">ดูรายการพัสดุ</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$getBalanceUrl = json_encode(Url::to(['/inventory-v2/stock-adjust/get-balance']));
$saveUrl = json_encode(Url::to(['/inventory-v2/stock-adjust/save']));
$itemListUrl = json_encode(Url::to(['/inventory-v2/stock-item/item-list']));
$prefillItemCode = json_encode($prefill['item_code']);
$prefillWarehouseId = json_encode($prefill['warehouse_id']);
$prefillCurrentQty = json_encode($prefill['current_qty']);
$prefillTargetQty = json_encode($prefill['target_qty']);
$this->registerJs(<<<JS
(function(){
    var getBalanceUrl = $getBalanceUrl;
    var saveUrl = $saveUrl;
    var prefillItemCode = $prefillItemCode;
    var prefillWarehouseId = $prefillWarehouseId;
    var prefillCurrentQty = $prefillCurrentQty;
    var prefillTargetQty = $prefillTargetQty;
    var itemSelect = window.itemSelect;

    var currentValue = 0;   // มูลค่า ledger ปัจจุบัน
    var avgCost = 0;        // ต้นทุนเฉลี่ยต่อหน่วย
    var hasBalance = false;

    function formatQty(value) {
        var num = parseFloat(value);
        if (isNaN(num)) return '\\u2014';
        return num.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function formatMoney(value) {
        var num = parseFloat(value);
        if (isNaN(num)) return '\\u2014';
        return num.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ฿';
    }
    function getMode() { return $('#mode').val(); }

    function setCurrentQty(value, isError) {
        if (isError) {
            $('#current_balance').text(value || 'โหลดไม่สำเร็จ').addClass('text-danger');
            $('#current_qty').val('');
            hasBalance = false;
            updatePreview();
            return;
        }
        $('#current_qty').val(value);
        $('#current_balance').text(formatQty(value)).toggleClass('text-danger', parseFloat(value) < 0);
        hasBalance = true;
    }

    function recalculateAdjustment() {
        var target = parseFloat($('#target_qty').val());
        var current = parseFloat($('#current_qty').val());
        if (isNaN(target) || isNaN(current)) { updatePreview(); return; }
        var qty = target - current;
        $('#adjustment_qty').val(parseFloat(qty.toFixed(6)));
        updatePreview();
    }

    // ราคาที่ใช้คิดมูลค่าตามโหมด/ทิศทาง (ตรงกับ logic ฝั่ง server)
    function effectiveUnitPrice(qty) {
        if (getMode() === 'qty_only') return 0;
        if (qty > 0) {
            var input = parseFloat($('#unit_price').val());
            return (!isNaN(input) && input >= 0) ? input : avgCost;
        }
        return avgCost; // ลด = ต้นทุนเฉลี่ย
    }

    function updatePreview() {
        var qty = parseFloat($('#adjustment_qty').val());
        var current = parseFloat($('#current_qty').val());
        if (!hasBalance || isNaN(qty) || qty === 0 || isNaN(current)) {
            $('#preview_wrap').hide();
            return;
        }
        var price = effectiveUnitPrice(qty);
        var valueDelta = (getMode() === 'qty_only') ? 0 : qty * price;
        var afterQty = current + qty;
        var afterVal = currentValue + valueDelta;

        $('#pv_before_qty').text(formatQty(current));
        $('#pv_before_val').text(formatMoney(currentValue));
        var deltaSign = qty > 0 ? '+' : '';
        $('#pv_delta_qty').text(deltaSign + formatQty(qty))
            .toggleClass('text-success', qty > 0).toggleClass('text-danger', qty < 0);
        var vSign = valueDelta > 0 ? '+' : '';
        $('#pv_delta_val').text(valueDelta === 0 ? 'มูลค่าไม่เปลี่ยน' : (vSign + formatMoney(valueDelta)))
            .toggleClass('text-success', valueDelta > 0).toggleClass('text-danger', valueDelta < 0)
            .toggleClass('text-muted', valueDelta === 0);
        $('#pv_after_qty').text(formatQty(afterQty));
        $('#pv_after_val').text(formatMoney(afterVal));
        $('#preview_wrap').show();
    }

    // แสดง/ซ่อนช่องต้นทุน + hint ตามโหมด/ทิศทาง
    function syncModeUI() {
        var mode = getMode();
        var qty = parseFloat($('#adjustment_qty').val());
        var showPrice = (mode !== 'qty_only') && (isNaN(qty) || qty > 0);
        $('#unit_price_wrap').toggle(showPrice);
        if (mode === 'qty_only') {
            $('#mode_hint').text('ปรับเฉพาะจำนวน — มูลค่าคงเดิม (ใช้เมื่อแก้ตัวเลขล้วน ไม่ใช่เจอของเพิ่ม/หาย)');
        } else {
            $('#mode_hint').text('เพิ่ม = ตีมูลค่าตามต้นทุน (ค่าเริ่มต้น = ต้นทุนเฉลี่ย) · ลด = คิดต้นทุนตามค่าเฉลี่ย → มูลค่าขยับตามจริง');
        }
        updatePreview();
    }

    $('input[name=mode_radio]').on('change', function(){
        $('#mode').val(this.value);
        syncModeUI();
    });

    if (itemSelect) {
        itemSelect.on('change', function(value) {
            $('#current_balance').text('\u2014').removeClass('text-danger');
            $('#current_qty').val('');
            currentValue = 0; avgCost = 0; hasBalance = false;
            $('#current_value').text('—'); $('#avg_cost').text('—');
            $('#preview_wrap').hide();
            if (value && $('#warehouse_id').val()) loadBalance();
        });
    }

    $('#warehouse_id').on('change', function() {
        if (itemSelect) itemSelect.clear();
        $('#current_qty').val('');
        $('#current_balance').text('\u2014').removeClass('text-danger');
        currentValue = 0; avgCost = 0; hasBalance = false;
        $('#current_value').text('\u2014'); $('#avg_cost').text('\u2014');
        $('#preview_wrap').hide();
    });

    $('#target_qty').on('input', recalculateAdjustment);
    $('#adjustment_qty').on('input', syncModeUI);
    $('#unit_price').on('input', updatePreview);

    function loadBalance() {
        var wh = $('#warehouse_id').val();
        var code = itemSelect ? itemSelect.getValue() : ($('#item_code').val() || '').trim();
        if (!wh || !code) {
            $('#current_balance').text('\u2014').removeClass('text-danger');
            return;
        }
        $.get(getBalanceUrl, { warehouse_id: wh, item_code: code })
            .done(function(res) {
                if (res.error) {
                    setCurrentQty(res.error, true);
                } else {
                    setCurrentQty(res.balance, false);
                    currentValue = parseFloat(res.value) || 0;
                    avgCost = parseFloat(res.avg_cost) || 0;
                    $('#current_value').text(formatMoney(currentValue));
                    $('#avg_cost').text(avgCost > 0 ? formatMoney(avgCost) : '—');
                    if (!$('#unit_price').val() && avgCost > 0) $('#unit_price').val(avgCost.toFixed(4));
                    recalculateAdjustment();
                    syncModeUI();
                }
            })
            .fail(function() {
                setCurrentQty('โหลดไม่สำเร็จ', true);
            });
    }

    $('#btn-load-balance').on('click', loadBalance);

    if (prefillCurrentQty !== null && prefillCurrentQty !== '') {
        setCurrentQty(prefillCurrentQty, false);
    }
    if (prefillTargetQty !== null && prefillTargetQty !== '') {
        $('#target_qty').val(prefillTargetQty);
        recalculateAdjustment();
    }
    syncModeUI();

    // Prefill จาก URL query (มาจาก variance banner ในหน้า main-stock/balance)
    if (prefillItemCode && prefillWarehouseId && itemSelect) {
        // TomSelect AJAX โหลด options ตาม warehouse_id — ต้องรอ load เสร็จถึงจะ setValue ได้
        var attempts = 0;
        var tryPreselect = function () {
            attempts++;
            // ลองเรียก loadUrl + ?warehouse_id=X เพื่อให้ TomSelect มี option ของ item_code
            if (typeof itemSelect.load === 'function') {
                itemSelect.load('');  // trigger load with current warehouse_id param
            }
            setTimeout(function () {
                try {
                    itemSelect.addOption({ item_code: prefillItemCode, item_name: prefillItemCode });
                    itemSelect.setValue(prefillItemCode, true);  // silent = true ไม่ trigger change
                    loadBalance();
                } catch (e) {
                    if (attempts < 3) setTimeout(tryPreselect, 200);
                }
            }, 250);
        };
        setTimeout(tryPreselect, 100);
        // focus ที่ adjustment_qty (มี value แล้ว — ให้ user พร้อมแก้/ยืนยัน)
        setTimeout(function () { $('#adjustment_qty').focus().select(); }, 600);
    }

    $('#form-stock-adjust').on('submit', function(e) {
        e.preventDefault();
        var wh = $('#warehouse_id').val();
        var code = itemSelect ? itemSelect.getValue() : ($('#item_code').val() || '').trim();
        var qty = parseFloat($('#adjustment_qty').val());
        if (!wh || !code) {
            alert('กรุณาเลือกคลังและเลือกพัสดุ');
            return;
        }
        if (isNaN(qty) || qty === 0) {
            alert('กรุณาระบุจำนวนที่ปรับ (บวกหรือลบ ไม่ใช่ศูนย์)');
            return;
        }
        var itemLabel = itemSelect ? itemSelect.getItem(code) : null;
        var name = itemLabel && itemLabel.item_name ? itemLabel.item_name : code;
        var current = $('#current_qty').val();
        var target = $('#target_qty').val();
        var NL = String.fromCharCode(10);
        var valueDelta = (getMode() === 'qty_only') ? 0 : qty * effectiveUnitPrice(qty);
        var msg = 'ยืนยันการปรับยอด?' + NL + 'พัสดุ: ' + name + NL + 'จำนวน: ' + (qty > 0 ? '+' : '') + qty + ' (บวก=เพิ่ม, ลบ=ลด)';
        if (getMode() === 'qty_only') {
            msg += NL + 'โหมด: ปรับจำนวนอย่างเดียว (มูลค่าไม่เปลี่ยน)';
        } else {
            msg += NL + 'ผลต่อมูลค่า: ' + (valueDelta >= 0 ? '+' : '') + formatMoney(valueDelta);
        }
        if (current !== '' || target !== '') {
            msg += NL + 'ยอดระบบ: ' + (current || '-') + ' → ยอดจริง: ' + (target || '-');
        }
        if (!confirm(msg)) return;

        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: {
                warehouse_id: wh,
                item_code: code,
                adjustment_qty: qty,
                current_qty: $('#current_qty').val(),
                target_qty: $('#target_qty').val(),
                mode: getMode(),
                unit_price: $('#unit_price').val(),
                source: $('#source').val(),
                note: $('#note').val(),
                order_date: $('#order_date').val()
            },
            dataType: 'json'
        }).done(function(res) {
            if (res.success) {
                var extra = (res.value_delta && parseFloat(res.value_delta) !== 0)
                    ? (' · มูลค่า ' + (parseFloat(res.value_delta) >= 0 ? '+' : '') + formatMoney(res.value_delta))
                    : '';
                alert('ปรับยอดสำเร็จ — เลขที่เอกสาร: ' + (res.order_no || '') + extra);
                if (res.closed_month_warning) { alert(res.closed_month_warning); }
                $('#adjustment_qty').val('');
                $('#target_qty').val('');
                $('#note').val('');
                $('#preview_wrap').hide();
                loadBalance();
            } else {
                alert(res.message || 'เกิดข้อผิดพลาด');
            }
        }).fail(function() {
            alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
        });
    });
})();
JS
);
?>
