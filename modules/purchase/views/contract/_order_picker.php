<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var array[] $items ผลจาก Contract::orderSnapshot() */
/** @var int $year */
/** @var array $years */
/** @var string $q */

$pickerUrl = Url::to(['order-picker']);
?>

<div class="alert alert-info d-flex gap-2 align-items-start">
    <i class="bi bi-info-circle mt-1"></i>
    <div class="small">
        แสดงเฉพาะใบสั่งซื้อที่ออกเลข PO แล้วและยังไม่มีสัญญาผูกอยู่
        เมื่อเลือกแล้วระบบจะคัดลอกคู่สัญญา วงเงิน และวันที่มาเป็นค่าตั้งต้น
        <span class="fw-medium">สัญญาจะเก็บสำเนาของตัวเอง</span>
        การแก้ไขในสัญญาจะไม่กระทบใบสั่งซื้อ
    </div>
</div>

<div class="row g-2 align-items-end mb-3">
    <div class="col-md-6">
        <label class="form-label">ค้นหา</label>
        <input type="text" class="form-control" id="order-picker-q" value="<?= Html::encode($q) ?>"
            placeholder="เลขที่ PO / PR / ชื่อผู้ขาย / ชื่อโครงการ">
    </div>
    <div class="col-md-3">
        <label class="form-label">ปีงบประมาณ</label>
        <select class="form-select" id="order-picker-year">
            <option value="0">ทุกปี</option>
            <?php foreach ($years as $y): ?>
                <option value="<?= (int) $y ?>" <?= (int) $y === (int) $year ? 'selected' : '' ?>><?= (int) $y ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <button type="button" class="btn btn-primary w-100" id="order-picker-search">
            <i class="bi bi-search me-1"></i>ค้นหา
        </button>
    </div>
</div>

<div class="table-responsive" style="max-height:55vh">
    <table class="table table-hover table-sm align-middle mb-0">
        <thead class="table-light position-sticky top-0">
            <tr>
                <th style="min-width:120px">เลขที่ PO</th>
                <th style="min-width:200px">รายการ</th>
                <th style="min-width:170px">ผู้ขาย</th>
                <th style="min-width:110px" class="text-end">วงเงิน</th>
                <th style="min-width:110px">ครบกำหนด</th>
                <th style="min-width:110px">ตรวจรับ</th>
                <th style="width:90px"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><span class="badge text-bg-light border"><?= Html::encode($item['po_number'] ?: '—') ?></span></td>
                    <td class="small"><?= Html::encode($item['title'] ?: '—') ?></td>
                    <td class="small"><?= Html::encode($item['vendor_name'] ?: '—') ?></td>
                    <td class="text-end"><?= number_format((float) $item['budget'], 2) ?></td>
                    <td class="small"><?= $item['end_date'] ? AppHelper::convertToThai($item['end_date']) : '—' ?></td>
                    <td class="small"><?= $item['receive_date'] ? AppHelper::convertToThai($item['receive_date']) : '—' ?></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-success order-pick"
                            data-order="<?= Html::encode(Json::encode($item)) ?>">
                            เลือก
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$items): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        ไม่พบใบสั่งซื้อที่ยังไม่มีสัญญาผูกตามเงื่อนไขที่เลือก
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$js = <<<JS
(function () {
    // ปุ่มเลือก: ส่งข้อมูลใบสั่งซื้อกลับไปให้ฟอร์มสัญญาเติมค่า
    \$(document).off('click.orderPick').on('click.orderPick', '.order-pick', function () {
        var data = \$(this).data('order');
        if (typeof window.contractApplyOrder === 'function') window.contractApplyOrder(data);
    });

    function reload() {
        var q = \$('#order-picker-q').val() || '';
        var year = \$('#order-picker-year').val() || 0;
        \$.get('{$pickerUrl}', { q: q, year: year }, function (r) {
            if (r && r.content) \$('#main-modal .modal-body').html(r.content);
        }, 'json');
    }

    \$('#order-picker-search').off('click').on('click', reload);
    \$('#order-picker-q').off('keypress').on('keypress', function (e) {
        if (e.which === 13) { e.preventDefault(); reload(); }
    });
})();
JS;
$this->registerJs($js, View::POS_READY);
?>
