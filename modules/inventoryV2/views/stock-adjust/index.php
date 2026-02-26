<?php
use yii\helpers\Html;
use yii\helpers\Url;

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
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">คลัง <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                            <?php foreach ($warehouses as $wid => $wname): ?>
                                <option value="<?= $wid === '' ? '' : (int)$wid ?>"><?= Html::encode($wname) ?></option>
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
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">ยอดคงเหลือปัจจุบัน</label>
                        <div class="form-control-plaintext fw-bold text-primary" id="current_balance">—</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">จำนวนที่ปรับ <span class="text-danger">*</span></label>
                        <input type="number" name="adjustment_qty" id="adjustment_qty" class="form-control" step="any" placeholder="บวก = เพิ่ม, ลบ = ลด" required>
                        <small class="text-muted">เช่น 10 = เพิ่ม 10 หน่วย, -5 = ลด 5 หน่วย</small>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">หมายเหตุ</label>
                        <input type="text" name="note" id="note" class="form-control" placeholder="เหตุผลการปรับ (ถ้ามี)">
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
$this->registerJs(<<<JS
(function(){
    var getBalanceUrl = $getBalanceUrl;
    var saveUrl = $saveUrl;
    var itemSelect = window.itemSelect;

    if (itemSelect) {
        itemSelect.on('change', function(value) {
            $('#current_balance').text('\u2014').removeClass('text-danger');
            if (value && $('#warehouse_id').val()) loadBalance();
        });
    }

    $('#warehouse_id').on('change', function() {
        if (itemSelect) itemSelect.clear();
        $('#current_balance').text('\u2014').removeClass('text-danger');
    });

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
                    $('#current_balance').text(res.error).addClass('text-danger');
                } else {
                    $('#current_balance').text(parseFloat(res.balance).toLocaleString('th-TH', { minimumFractionDigits: 2 })).removeClass('text-danger');
                }
            })
            .fail(function() {
                $('#current_balance').text('โหลดไม่สำเร็จ').addClass('text-danger');
            });
    }

    $('#btn-load-balance').on('click', loadBalance);

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
        var msg = 'ยืนยันการปรับยอด?' + String.fromCharCode(10) + 'พัสดุ: ' + name + String.fromCharCode(10) + 'จำนวน: ' + qty + String.fromCharCode(10) + '(บวก=เพิ่ม, ลบ=ลด)';
        if (!confirm(msg)) return;

        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: {
                warehouse_id: wh,
                item_code: code,
                adjustment_qty: qty,
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
