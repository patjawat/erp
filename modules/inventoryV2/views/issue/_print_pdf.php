<?php
use yii\helpers\Html;

$mainWh = $model->mainWarehouse;
$subWh = $model->subWarehouse;
$unitName = $subWh ? $subWh->warehouse_name : '-';
$orderTime = $model->order_date ? date('H:i', is_numeric($model->order_date) ? $model->order_date : strtotime($model->order_date)) : '';
$thaiDateFormatted = $model->order_date && Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($model->order_date, false, false) : '-';
$thaiDateTime = $model->order_date && Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($model->order_date, true, false) : '-';
$grandTotal = 0;
$recipient = isset($model->data_json['recipient']) ? $model->data_json['recipient'] : 'ผู้อำนวยการโรงพยาบาล';
$sig = function ($role) use ($model) { return $model->getIssueSignature($role); };
$companyAuthorizer = \app\modules\inventoryV2\models\StockOrder::getCompanyAuthorizer();
$authorizer = ($sig('authorizer')['name'] ?? '') !== '' ? $sig('authorizer') : array_merge($sig('authorizer'), $companyAuthorizer);
?>
<div class="issue-print-form" style="font-family: 'TH Sarabun New', 'Sarabun', sans-serif; font-size: 14pt;">
    <div style="text-align: center; font-weight: bold; font-size: 18pt; margin-bottom: 8px;">ใบเบิกวัสดุ</div>
    <div style="text-align: right; margin-bottom: 10px;">ใบเบิกวัสดุเลขที่ : <?= Html::encode($model->order_no) ?></div>

    <table style="width: 100%; margin-bottom: 10px;" cellpadding="0" cellspacing="0">
        <tr><td style="width: 120px; color: #555;">ชื่อแผนก/ฝ่าย</td><td><?= Html::encode($unitName) ?></td></tr>
        <tr><td style="color: #555;">เรียน</td><td><?= Html::encode($recipient) ?></td></tr>
        <tr><td style="color: #555;">วันที่</td><td><?= Html::encode($thaiDateFormatted) ?></td></tr>
    </table>

    <p style="margin-bottom: 10px;">ด้วย <?= Html::encode($unitName) ?> มีความประสงค์ขอเบิกวัสดุ ใช้ในแผนก/ฝ่าย มีรายการดังต่อไปนี้</p>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;" border="1" cellpadding="5" cellspacing="0">
        <tr style="background: #f5f5f5;">
            <th style="width: 30px; text-align: left;">ที่</th>
            <th style="text-align: left;">รายการ</th>
            <th colspan="3" style="text-align: center;">จำนวน</th>
            <th colspan="2" style="text-align: right;">ราคา/</th>
        </tr>
        <tr style="background: #f5f5f5;">
            <th></th>
            <th></th>
            <th style="width: 50px; text-align: center;">หน่วย</th>
            <th style="width: 45px; text-align: center;">เบิก</th>
            <th style="width: 50px; text-align: center;">อนุมัติ</th>
            <th style="width: 60px; text-align: right;">หน่วย</th>
            <th style="width: 65px; text-align: right;">ราคารวม</th>
        </tr>
        <?php foreach ($model->stockDetails as $i => $d): ?>
            <?php
            $total = (float) $d->qty * (float) ($d->unit_price ?? 0);
            $grandTotal += $total;
            $itemName = $d->item ? $d->item->item_name : $d->item_code;
            $unit = $d->item && method_exists($d->item, 'getUnitName') ? ($d->item->getUnitName() ?: '-') : '-';
            ?>
            <tr>
                <td style="text-align: center;"><?= $i + 1 ?></td>
                <td><?= Html::encode($itemName) ?></td>
                <td style="text-align: center;"><?= Html::encode($unit) ?></td>
                <td style="text-align: center;"><?= number_format($d->qty, 0) ?></td>
                <td style="text-align: center;"><?= $model->status === 'CONFIRMED' ? number_format($d->qty, 0) : '' ?></td>
                <td style="text-align: right;"><?= number_format($d->unit_price ?? 0, 2) ?></td>
                <td style="text-align: right;"><?= $model->status === 'CONFIRMED' ? number_format($total, 2) : '0.00' ?></td>
            </tr>
        <?php endforeach; ?>
        <tr style="font-weight: bold;">
            <td colspan="6" style="text-align: right; border: 1px solid #333;">ราคารวม</td>
            <td style="text-align: right; border: 1px solid #333;"><?= number_format($grandTotal, 2) ?></td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 25px;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 50%; padding-right: 20px; vertical-align: top;">
                <div style="border-bottom: 1px dotted #333; padding-bottom: 2px; margin-top: 25px;">ลงชื่อ : <?= $sig('requester')['name'] ? Html::encode($sig('requester')['name']) : '..............................................' ?> ผู้เบิก</div>
                <div style="margin-top: 5px;">(<?= $sig('requester')['name'] ? Html::encode($sig('requester')['name']) : '..............................................' ?>)</div>
                <div style="font-size: 12pt; color: #555;">ตำแหน่ง <?= $sig('requester')['position'] ? Html::encode($sig('requester')['position']) : '.............................................' ?></div>
                <div style="font-size: 12pt; color: #555; margin-top: 3px;"><?= $sig('requester')['date'] ? (Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($sig('requester')['date'], true, false) : $sig('requester')['date']) : Html::encode($thaiDateTime) ?></div>
            </td>
            <td style="width: 50%; padding-left: 20px; vertical-align: top;">
                <div style="border-bottom: 1px dotted #333; padding-bottom: 2px; margin-top: 25px;">ลงชื่อ : <?= $sig('disbursing')['name'] ? Html::encode($sig('disbursing')['name']) : '..............................................' ?> ผู้จ่ายพัสดุ</div>
                <div style="margin-top: 5px;">(<?= $sig('disbursing')['name'] ? Html::encode($sig('disbursing')['name']) : 'ไม่ระบุผู้จ่าย' ?>)</div>
                <div style="font-size: 12pt; color: #555;">ตำแหน่ง <?= $sig('disbursing')['position'] ? Html::encode($sig('disbursing')['position']) : '' ?></div>
                <div style="font-size: 12pt; color: #555; margin-top: 3px;"><?= $sig('disbursing')['date'] ? (Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($sig('disbursing')['date'], true, false) : $sig('disbursing')['date']) : '-' ?></div>
            </td>
        </tr>
        <tr>
            <td style="padding-right: 20px; vertical-align: top;">
                <div style="border-bottom: 1px dotted #333; padding-bottom: 2px; margin-top: 25px;">ลงชื่อ : <?= $sig('approver')['name'] ? Html::encode($sig('approver')['name']) : '..............................................' ?> ผู้เห็นชอบ</div>
                <div style="font-size: 11pt; color: #666; margin-top: 2px;">(หัวหน้า, เจ้าหน้าที่คลังสามารถอนุมัติแทนได้)</div>
                <div style="margin-top: 5px;">(<?= $sig('approver')['name'] ? Html::encode($sig('approver')['name']) : '..............................................' ?>)</div>
                <div style="font-size: 12pt; color: #555;">ตำแหน่ง <?= $sig('approver')['position'] ? Html::encode($sig('approver')['position']) : '.............................................' ?></div>
                <div style="font-size: 12pt; color: #555; margin-top: 3px;"><?= $sig('approver')['date'] ? (Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($sig('approver')['date'], true, false) : $sig('approver')['date']) : '...............................................' ?></div>
            </td>
            <td style="padding-left: 20px; vertical-align: top;">
                <div style="border-bottom: 1px dotted #333; padding-bottom: 2px; margin-top: 25px;">ลงชื่อ : <?= $sig('recipient')['name'] ? Html::encode($sig('recipient')['name']) : '..............................................' ?> ผู้รับวัสดุ</div>
                <div style="margin-top: 5px;">(<?= $sig('recipient')['name'] ? Html::encode($sig('recipient')['name']) : '..............................................' ?>)</div>
                <div style="font-size: 12pt; color: #555;">ตำแหน่ง <?= $sig('recipient')['position'] ? Html::encode($sig('recipient')['position']) : '.............................................' ?></div>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="padding-left: 20px; vertical-align: top;">
                <div style="border-bottom: 1px dotted #333; padding-bottom: 2px; margin-top: 25px;">ลงชื่อ : <?= $authorizer['name'] ? Html::encode($authorizer['name']) : '..............................................' ?> ผู้สั่งจ่าย</div>
                <div style="margin-top: 5px;">(<?= $authorizer['name'] ? Html::encode($authorizer['name']) : '..............................................' ?>)</div>
                <div style="font-size: 12pt; color: #555;">ตำแหน่ง <?= $authorizer['position'] ? Html::encode($authorizer['position']) : '.............................................' ?></div>
            </td>
        </tr>
    </table>
</div>
