<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'บันทึกการจ่ายพัสดุ/การใช้งาน';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'Dashboard คลังย่อย', 'url' => ['/inventory-v2/sub-stock/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$subWarehouses = $subWarehouses ?? [];
$usageHistory = $usageHistory ?? [];
$currentWarehouseId = $currentWarehouseId ?? null;
$getLotsUrl = Url::to(['/inventory-v2/sub-stock/get-available-lots']);
$saveUrl = Url::to(['/inventory-v2/sub-stock/save-usage']);
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-box-arrow-up-right fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">บันทึกการตัดจ่ายพัสดุที่คลังย่อย (งานคลินิก ซ่อมบำรุง บริหาร ฯลฯ) — เลือกคลังย่อยแล้วเลือกพัสดุและจำนวน</p>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm border-top border-primary border-3">
        <div class="card-header bg-primary-gradient text-white py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="text-white mb-0 fw-normal"><i class="bi bi-box-seam me-1"></i>ตัดจ่ายพัสดุที่คลังย่อย</h6>
            <span class="badge text-bg-light text-dark">คลังที่ดำเนินการ: <span id="currentWarehouse">— เลือกคลังด้านล่าง</span></span>
        </div>
        <div class="card-body">
            <?php if (empty($subWarehouses)): ?>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ไม่พบคลังย่อยที่คุณรับผิดชอบ — ติดต่อผู้ดูแลเพื่อกำหนดสิทธิ์ หรือ
                    <?= Html::a('กลับ Dashboard คลังย่อย', ['/inventory-v2/sub-stock/dashboard'], ['class' => 'alert-link']) ?>
                </div>
            <?php else: ?>
                <div class="row g-3 mb-4 p-3 rounded border">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold">คลังย่อย</label>
                        <select class="form-select" id="warehouseSelect" required>
                            <option value="">-- เลือกคลังย่อย --</option>
                            <?php foreach ($subWarehouses as $w): ?>
                                <option value="<?= (int)$w->id ?>" <?= $currentWarehouseId && (int)$w->id === (int)$currentWarehouseId ? 'selected' : '' ?>>
                                    <?= Html::encode($w->warehouse_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold">ประเภทงาน/การเบิก</label>
                        <select class="form-select" id="jobType">
                            <option value="patient">งานคลินิก (ตัดจ่ายรายคนไข้)</option>
                            <option value="maintenance">งานซ่อมบำรุง/ไอที (ตัดจ่ายตาม Job)</option>
                            <option value="office">งานบริหาร/บัญชี (เบิกใช้ในสำนักงาน)</option>
                            <option value="emergency">งานอุบัติเหตุ/ฉุกเฉิน (เบิกเติม Unit Stock)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-bold" id="dynamicLabel">HN / ชื่อคนไข้</label>
                        <input type="text" class="form-control" id="referenceInput" placeholder="ระบุอ้างอิงการเบิก...">
                    </div>
                </div>

                <div class="row g-2 mb-3 align-items-end border-bottom pb-3">
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-bold">พัสดุ / Lot (ที่มีในคลังย่อย)</label>
                        <select class="form-select" id="stockItemSelector" disabled>
                            <option value="">-- เลือกคลังย่อยก่อน --</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label fw-bold">จำนวนที่ใช้</label>
                        <input type="number" class="form-control text-center" id="inputQty" value="1" min="0.01" step="0.01">
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="button" class="btn btn-primary w-100" id="btnAddToList"><i class="bi bi-plus-circle"></i> เพิ่มรายการ</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="disburseTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="40%">รายการพัสดุ</th>
                                <th width="15%">Lot</th>
                                <th width="15%" class="text-center">จำนวน</th>
                                <th width="10%">หน่วย</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <tr id="noDataRow">
                                <td colspan="6" class="text-center py-4 text-muted">ยังไม่มีรายการตัดจ่าย</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <button type="button" class="btn btn-primary px-5 me-2" id="btnSaveFinal"><i class="bi bi-check-lg me-1"></i>ยืนยันการบันทึกรายการ</button>
                    <?= Html::a('ยกเลิก', ['/inventory-v2/sub-stock/dashboard'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
                <?= $this->render('use_history', ['usageHistory' => $usageHistory, 'currentWarehouseId' => $currentWarehouseId]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($subWarehouses)): ?>
    <?php
    $getLotsUrlJson = json_encode($getLotsUrl);
    $saveUrlJson = json_encode($saveUrl);
    $this->registerJs(
        <<<JS
(function(){
    var getLotsUrl = {$getLotsUrlJson};
    var saveUrl = {$saveUrlJson};
    var lotsData = [];
    var rows = [];
    var isInitialWarehouseChange = true;

    $('#warehouseSelect').on('change', function() {
        var wh = $(this).val();
        if (isInitialWarehouseChange) {
            isInitialWarehouseChange = false;
        } else if (rows.length === 0 && wh) {
            // ให้โหลด history ตามคลังที่เลือก (เพราะ usageHistory render จาก server)
            window.location.href = window.location.pathname + '?warehouse_id=' + wh;
            return;
        }
        var \$sel = $('#stockItemSelector');
        \$sel.empty().append('<option value="">-- โหลดรายการ... --</option>').prop('disabled', true);
        $('#currentWarehouse').text(wh ? $('#warehouseSelect option:selected').text() : '— เลือกคลังด้านล่าง');
        if (!wh) { \$sel.html('<option value="">-- เลือกคลังย่อยก่อน --</option>'); return; }
        $.get(getLotsUrl, { warehouse_id: wh }).done(function(data) {
            lotsData = data || [];
            \$sel.empty().append('<option value="">-- เลือกพัสดุ/Lot --</option>');
            lotsData.forEach(function(o) {
                var label = (o.item_name || o.item_code) + ' | Lot: ' + (o.lot_number || '') + ' | เหลือ: ' + (o.balance_qty || 0) + ' ' + (o.unit || '');
                \$sel.append($('<option></option>').attr('value', (o.item_code || '') + '_' + (o.lot_number || '')).data('item', o).text(label));
            });
            \$sel.prop('disabled', false);
        }).fail(function() {
            \$sel.html('<option value="">โหลดไม่สำเร็จ</option>').prop('disabled', false);
        });
    });

    // ถ้าหน้านี้ถูกเปิดมาพร้อม `warehouse_id` จาก server ให้โหลด Lot ทันที
    var initialWh = $('#warehouseSelect').val();
    if (initialWh) {
        $('#warehouseSelect').trigger('change');
    }

    $('#jobType').on('change', function() {
        var labels = { patient: 'HN / ชื่อคนไข้', maintenance: 'เลขที่ใบแจ้งซ่อม (Job)', office: 'รหัสผู้เบิก/โครงการ', emergency: 'อ้างอิงการเบิก' };
        $('#dynamicLabel').text(labels[$(this).val()] || 'อ้างอิง');
    });

    $('#btnAddToList').on('click', function() {
        var item = $('#stockItemSelector option:selected').data('item');
        if (!item) return;
        var qty = parseFloat($('#inputQty').val()) || 0;
        if (qty <= 0) return;
        if (qty > (item.balance_qty || 0)) {
            alert('จำนวนที่ใช้เกินยอดคงเหลือใน Lot นี้');
            return;
        }
        var row = { item_code: item.item_code, item_name: item.item_name, lot_number: item.lot_number, unit: item.unit || '', qty: qty };
        rows.push(row);
        $('#noDataRow').hide();
        var tr = $('<tr class="item-row"></tr>');
        tr.append('<td class="text-center">' + rows.length + '</td>');
        tr.append('<td><strong>' + (item.item_name || item.item_code) + '</strong></td>');
        tr.append('<td class="text-center text-primary">' + (item.lot_number || '') + '</td>');
        tr.append('<td class="text-center">' + qty + '</td>');
        tr.append('<td>' + (item.unit || '') + '</td>');
        tr.append('<td><button type="button" class="btn btn-link text-danger p-0 btn-remove"><i class="bi bi-trash"></i></button></td>');
        $('#disburseTable tbody').append(tr);
    });

    $(document).on('click', '.btn-remove', function() {
        var i = $(this).closest('tr').index() - 1;
        if (i >= 0 && i < rows.length) rows.splice(i, 1);
        $(this).closest('tr').remove();
        $('#disburseTable tbody tr.item-row').each(function(i) { $(this).find('td:first').text(i + 1); });
        if (rows.length === 0) $('#noDataRow').show();
    });

    $('#btnSaveFinal').on('click', function() {
        if (rows.length === 0) { alert('กรุณาเพิ่มรายการพัสดุ'); return; }
        var wh = $('#warehouseSelect').val();
        if (!wh) { alert('กรุณาเลือกคลังย่อย'); return; }
        var jobType = $('#jobType').val();
        var reference = ($('#referenceInput').val() || '').trim() || 'ไม่ได้ระบุ';
        var items = rows.map(function(r) { return { item_code: r.item_code, lot_number: r.lot_number, qty: r.qty }; });
        var \$btn = $(this).prop('disabled', true);
        $.post(saveUrl, { warehouse_id: wh, job_type: jobType, reference: reference, items: items })
            .done(function(res) {
                if (res.success) {
                    if (typeof Swal !== 'undefined') Swal.fire('สำเร็จ', (res.order_no ? 'เลขที่ ' + res.order_no + ' — ' : '') + (res.message || 'บันทึกการตัดจ่ายเรียบร้อย'), 'success').then(function() { location.href = window.location.pathname + '?warehouse_id=' + wh; });
                    else { alert(res.message); location.reload(); }
                } else {
                    alert(res.message || 'เกิดข้อผิดพลาด');
                    \$btn.prop('disabled', false);
                }
            })
            .fail(function() {
                alert('เกิดข้อผิดพลาด');
                \$btn.prop('disabled', false);
            });
    });
})();
JS,
        View::POS_READY
    );
    ?>
<?php endif; ?>