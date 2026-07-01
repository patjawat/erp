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
                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">ยอดคงเหลือปัจจุบัน</label>
                        <div class="form-control-plaintext fw-bold text-primary" id="current_balance">—</div>
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
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">หมายเหตุ</label>
                        <input type="text" name="note" id="note" class="form-control" placeholder="เหตุผลการปรับ (ถ้ามี)" value="<?= Html::encode($prefill['note']) ?>">
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

    function formatQty(value) {
        var num = parseFloat(value);
        if (isNaN(num)) return '\\u2014';
        return num.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function setCurrentQty(value, isError) {
        if (isError) {
            $('#current_balance').text(value || 'โหลดไม่สำเร็จ').addClass('text-danger');
            $('#current_qty').val('');
            return;
        }
        $('#current_qty').val(value);
        $('#current_balance').text(formatQty(value)).toggleClass('text-danger', parseFloat(value) < 0);
    }

    function recalculateAdjustment() {
        var target = parseFloat($('#target_qty').val());
        var current = parseFloat($('#current_qty').val());
        if (isNaN(target) || isNaN(current)) return;
        var qty = target - current;
        $('#adjustment_qty').val(parseFloat(qty.toFixed(6)));
    }

    if (itemSelect) {
        itemSelect.on('change', function(value) {
            $('#current_balance').text('\u2014').removeClass('text-danger');
            $('#current_qty').val('');
            if (value && $('#warehouse_id').val()) loadBalance();
        });
    }

    $('#warehouse_id').on('change', function() {
        if (itemSelect) itemSelect.clear();
        $('#current_qty').val('');
        $('#current_balance').text('\u2014').removeClass('text-danger');
    });

    $('#target_qty').on('input', recalculateAdjustment);

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
                    recalculateAdjustment();
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
        var msg = 'ยืนยันการปรับยอด?' + String.fromCharCode(10) + 'พัสดุ: ' + name + String.fromCharCode(10) + 'จำนวน: ' + qty + String.fromCharCode(10) + '(บวก=เพิ่ม, ลบ=ลด)';
        if (current !== '' || target !== '') {
            msg += String.fromCharCode(10) + 'ยอดระบบ: ' + (current || '-') + String.fromCharCode(10) + 'ยอดตรวจนับจริง: ' + (target || '-');
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
                lot_number: $('#lot_number').val(),
                source: $('#source').val(),
                note: $('#note').val()
            },
            dataType: 'json'
        }).done(function(res) {
            if (res.success) {
                alert('ปรับยอดสำเร็จ — เลขที่เอกสาร: ' + (res.order_no || ''));
                $('#adjustment_qty').val('');
                $('#note').val('');
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
