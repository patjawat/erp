<?php

use yii\helpers\Html;
use app\components\SiteHelper;

$mainWh = $model->mainWarehouse;
$subWh = $model->subWarehouse;
$unitName = $subWh ? $subWh->warehouse_name : '-';
$orderTime = $model->order_date ? date('H:i', is_numeric($model->order_date) ? $model->order_date : strtotime($model->order_date)) : '';
$thaiDateFormatted = $model->order_date && Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($model->order_date, false, false) : '-';
$thaiDateTime = $model->order_date && Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($model->order_date, true, false) : '-';
$grandTotal = 0;
$companyName = trim((string) (SiteHelper::getInfo()['company_name'] ?? ''));
$recipient = isset($model->data_json['recipient']) && trim((string) $model->data_json['recipient']) !== ''
    ? $model->data_json['recipient']
    : 'ผู้อำนวยการ' . ($companyName !== '' ? $companyName : 'โรงพยาบาล');
$sig = function ($role) use ($model) {
    return $model->getIssueSignature($role);
};
$companyAuthorizer = \app\modules\inventoryV2\models\StockOrder::getCompanyAuthorizer();
$authorizer = ($sig('authorizer')['name'] ?? '') !== '' ? $sig('authorizer') : array_merge($sig('authorizer'), $companyAuthorizer);
?>


<h3 class="text-center" style="margin-bottom: 50px;">ใบเบิกวัสดุ</h3>

<table class="table">
    <tr>
        <td>ชื่อหน่วยงาน <?= $unitName ?></td>
        <td> ใบเบิกวัสดุเลขที่ <?= Html::encode($model->order_no) ?></td>
    </tr>
    <tr>
        <td>เรียน <?= Html::encode($recipient) ?></td>
        <td>วันที่ <?= Html::encode($thaiDateFormatted) ?></td>
    </tr>
</table>

<p style="margin-left: 80px;">
    ด้วย <?= Html::encode($unitName) ?> มีความประสงค์ขอเบิกวัสดุ ใช้ในหน่วยงาน
</p>
<p>
    มีรายการดังต่อไปนี้
</p>

<table class="table table-sm table-bordered item-table">
    <thead>
        <tr>
            <th rowspan="2" style="width: 40px;">ที่</th>
            <th rowspan="2" class="text-center">รายการ</th>
            <th colspan="3" class="text-center">จำนวน</th>
            <th colspan="2" class="text-center">ราคา</th>
        </tr>
        <tr>
            <th style="width: 60px;">หน่วย</th>
            <th style="width: 55px;">เบิก</th>
            <th style="width: 65px;">อนุมัติ</th>
            <th style="width: 80px;">หน่วยละ</th>
            <th style="width: 90px;">ราคารวม</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($model->stockDetails as $i => $d): ?>
            <?php
            $total = (float) $d->qty * (float) ($d->unit_price ?? 0);
            $grandTotal += $total;
            $itemName = $d->item ? $d->item->item_name : $d->item_code;
            $unit = $d->item && method_exists($d->item, 'getUnitName') ? ($d->item->getUnitName() ?: '-') : '-';
            ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td><?= Html::encode($itemName) ?></td>
                <td class="text-center"><?= Html::encode($unit) ?></td>
                <td class="text-center fw-bold"><?= number_format($d->qty, 0) ?></td>
                <td class="text-center"><?= $model->status === 'CONFIRMED' ? number_format($d->qty, 0) : '' ?></td>
                <td class="text-right"><?= number_format($d->unit_price ?? 0, 2) ?></td>
                <td class="text-right"><?= $model->status === 'CONFIRMED' ? number_format($total, 2) : '0.00' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr style="font-weight: bold; background-color: #f9f9f9;">
            <td colspan="6" class="text-right">รวมเป็นเงินทั้งสิ้น</td>
            <td class="text-right"><?= number_format($grandTotal, 2) ?></td>
        </tr>
    </tfoot>
</table>
<?php
// ฟังก์ชันสำหรับสร้างจุดไข่ปลา
function generateDottedLine()
{
    $totalDots = 60;
    $dots = '';
    for ($i = 0; $i < $totalDots; $i++) {
        $dots .= '.';
    }
    return $dots;
}
?>
<!-- <table class="table border-1" style="width: 100%;"> -->
<table class="table table-sm item-table issue-signature-footer">
    <tr class="signature-row--large">
        <td class="text-center">
            <div class="signature-block">
                <div class="signature-line">ลงชื่อ <span class="signature-dots"><?= generateDottedLine() ?></span> ผู้เบิก</div>
                <div class="signature-name"><?= $sig('requester')['name'] ? Html::encode($sig('requester')['name']) : '&nbsp;' ?></div>
                <div class="signature-position">ตำแหน่ง <?= Html::encode($sig('requester')['position'] ?: '-') ?></div>
                <div class="signature-date sign-subtext">วันที่ <?= $sig('requester')['date'] ? (Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($sig('requester')['date'], true, false) : $sig('requester')['date']) : Html::encode($thaiDateTime) ?></div>
            </div>
        </td>
        <td class="text-center">
            <div class="signature-block">
                <div class="signature-line">ลงชื่อ <span class="signature-dots"><?= generateDottedLine() ?></span> ผู้จ่ายพัสดุ</div>
                <div class="signature-name">(<span class="dot-line"><?= $sig('disbursing')['name'] ? Html::encode($sig('disbursing')['name']) : '&nbsp;' ?></span>)</div>
                <div class="signature-position">ตำแหน่ง <?= Html::encode($sig('disbursing')['position'] ?: '-') ?></div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="text-center">
            <div class="signature-block">
                <div class="signature-line">ลงชื่อ <span class="signature-dots"><?= generateDottedLine() ?></span> ผู้เห็นชอบ</div>
                <div class="signature-name">(<span class="dot-line"><?= $sig('approver')['name'] ? Html::encode($sig('approver')['name']) : '&nbsp;' ?></span>)</div>
                <div class="signature-position sign-subtext">ตำแหน่ง <?= Html::encode($sig('approver')['position'] ?: '-') ?></div>
            </div>
        </td>
        <td class="text-center">
            <div class="signature-block">
                <div class="signature-line">ลงชื่อ <span class="signature-dots"><?= generateDottedLine() ?></span> ผู้รับวัสดุ</div>
                <div class="signature-name">(<span class="dot-line"><?= $sig('recipient')['name'] ? Html::encode($sig('recipient')['name']) : '&nbsp;' ?></span>)</div>
                <div class="signature-position sign-subtext">ตำแหน่ง <?= Html::encode($sig('recipient')['position'] ?: '-') ?></div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="signature-cell--blank"></td>
        <td class="text-center">
            <div class="signature-block">
                <div class="signature-line">ลงชื่อ <span class="signature-dots"><?= generateDottedLine() ?></span> ผู้สั่งจ่าย</div>
                <div class="signature-name">(<span class="dot-line"><?= Html::encode($authorizer['name']) ?></span>)</div>
                <div class="signature-position sign-subtext">ตำแหน่ง <?= Html::encode($authorizer['position'] ?: '-') ?></div>
            </div>
        </td>
    </tr>
</table>
