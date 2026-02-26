<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\widgets\TomSelectWidget;

$this->title = 'Stock Card | บัตรควบคุมพัสดุ';
?>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form id="stock-card-filter">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-primary">1. เลือกรายการพัสดุ</label>
                    <?= TomSelectWidget::widget([
                        'name' => 'item_code',
                        'id' => 'select-item',
                        'options' => ['class' => 'form-select border-0 bg-light rounded-3 shadow-none', 'required' => true],
                        'items' => ['' => 'ค้นหาพัสดุ...'],
                        'loadUrl' => Url::to(['/inventory-v2/stock-item/item-list']),
                        'clientOptions' => [
                            'valueField' => 'item_code',
                            'labelField' => 'item_name',
                            'searchField' => ['item_name', 'item_code'],
                        ],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-primary">2. คลังสินค้า</label>
                    <select name="warehouse_id" id="select-warehouse" class="form-select border-0 bg-light rounded-3">
                        <option value="">ทุกคลัง (รวม)</option>
                        <?php foreach ($warehouses as $id => $name): ?>
                            <option value="<?= $id ?>"><?= Html::encode($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-primary">3. ช่วงเวลา</label>
                    <div class="input-group">
                        <input type="date" name="start_date" id="start-date" class="form-control border-0 bg-light" value="<?= date('Y-m-01') ?>">
                        <span class="input-group-text border-0 bg-light">-</span>
                        <input type="date" name="end_date" id="end-date" class="form-control border-0 bg-light" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold shadow-sm">
                        <i class="bi bi-search me-2"></i>แสดงข้อมูล
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-3 h-100">
            <small class="text-white-50">ยอดยกมา (Brought Forward)</small>
            <h4 class="fw-bold mb-0" id="stat-brought-forward">0</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 border-start border-primary border-4">
            <small class="text-muted">ความเคลื่อนไหวช่วงที่เลือก</small>
            <h4 class="fw-bold mb-0">
                <span class="text-success" id="stat-in">0</span> / <span class="text-danger" id="stat-out">0</span>
            </h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-3 h-100">
            <small class="text-white-50">คงเหลือสะสมสุทธิ (Total Balance)</small>
            <h4 class="fw-bold mb-0 text-warning" id="stat-balance">0</h4>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="stockCardTable">
            <thead class="bg-secondary text-white text-center">
                <tr>
                    <th rowspan="2" class="align-middle">วัน/เวลา</th>
                    <th rowspan="2" class="align-middle">เลขที่เอกสาร</th>
                    <th rowspan="2" class="align-middle">ราคา/หน่วย</th>
                    <th colspan="2">จำนวน (Units)</th>
                    <th colspan="2" class="bg-dark bg-opacity-25">คงเหลือ (Balance)</th>
                    <th rowspan="2" class="align-middle">Lot</th>
                </tr>
                <tr>
                    <th class="bg-success bg-opacity-75">รับ (+)</th>
                    <th class="bg-danger bg-opacity-75">จ่าย (-)</th>
                    <th class="bg-dark">จำนวน</th>
                    <th class="bg-primary">มูลค่ารวม</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<?php
$getStockCardUrl = Url::to(['/inventory-v2/stock-card/get-stock-data']);
$js = <<< JS

$(document).ready(function() {
    // โหลดข้อมูล Stock Card
    $('#stock-card-filter').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();
        console.log("ข้อมูลที่ส่งไป:", formData);
        // แสดงสถานะ Loading...
        $('#stockCardTable tbody').html('<tr><td colspan="7" class="py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> กำลังคำนวณยอดสะสม...</td></tr>');

        $.getJSON('$getStockCardUrl', formData, function(res) {
            // อัปเดตตัวเลข Stats
            $('#stat-brought-forward').text(res.qtyBF);
            $('#stat-in').text('+' + res.totalIn);
            $('#stat-out').text('-' + res.totalOut);
            $('#stat-balance').text(res.currentQty);


            // สร้างแถวตาราง
            let html = '';
                // 1. แถวยอดยกมา (ตัวตั้งต้น)
                html += '<tr class="table-light italic text-muted">' +
                    '<td>' + $('#start-date').val() + '</td>' +
                    '<td colspan="2" class="text-center fw-bold text-dark">ยอดยกมาสะสม (Brought Forward)</td>' +
                    '<td class="text-center">-</td>' +
                    '<td class="text-center">-</td>' +
                    '<td class="text-center fw-bold text-dark bg-light">' + res.qtyBF + '</td>' +
                    '<td class="text-end fw-bold text-dark bg-light">' + res.valueBF + '</td>' +
                    '<td>-</td>' +
                '</tr>';

                // 2. วนลูปแสดงธุรกรรมพร้อมยอดสะสมที่คำนวณมาแล้วจาก Controller
                if (res.transactions.length > 0) {
                    res.transactions.forEach(function(row) {
                        html += '<tr>' +
                            '<td class="text-center">' + row.date + '</td>' +
                            '<td class="text-center"><span class="badge text-bg-light text-dark border">' + row.order_no + '</span></td>' +
                            '<td class="text-end">' + row.price + '</td>' +
                            '<td class="text-success fw-bold text-center">' + row.in_qty + '</td>' +
                            '<td class="text-danger fw-bold text-center">' + row.out_qty + '</td>' +
                            '<td class="fw-bold text-center bg-dark bg-opacity-10">' + row.balance_qty + '</td>' +
                            '<td class="fw-bold text-primary text-end bg-primary bg-opacity-10">' + row.balance_value + '</td>' +
                            '<td class="text-center small text-muted">' + row.lot + '</td>' +
                        '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="8" class="text-center py-4 text-muted">ไม่พบข้อมูลความเคลื่อนไหวในช่วงเวลาที่เลือก</td></tr>';
                }

                $('#stockCardTable tbody').html(html);
                });
    });
});

JS;
$this->registerJs($js, View::POS_END);
?>