<?php
use yii\helpers\Html;

$this->title = 'ใบเบิกวัสดุ - ' . $model->order_no;
$mainWh = $model->mainWarehouse;
$subWh = $model->subWarehouse;
$unitName = $subWh ? $subWh->warehouse_name : '-';
$orderDate = $model->order_date ? Yii::$app->formatter->asDate($model->order_date, 'php:d/m/Y') : '-';
$orderTime = $model->order_date ? date('H:i', is_numeric($model->order_date) ? $model->order_date : strtotime($model->order_date)) : '';
$thaiDateFormatted = $model->order_date && Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($model->order_date, false, false) : $orderDate;
$thaiDateTime = $model->order_date && Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($model->order_date, true, false) : ($orderDate . ' ' . $orderTime . ' น.');
$grandTotal = 0;
$recipient = isset($model->data_json['recipient']) ? $model->data_json['recipient'] : 'ผู้อำนวยการโรงพยาบาล';
$sig = function ($role) use ($model) { return $model->getIssueSignature($role); };
$companyAuthorizer = \app\modules\inventoryV2\models\StockOrder::getCompanyAuthorizer();
$authorizer = ($sig('authorizer')['name'] ?? '') !== '' ? $sig('authorizer') : array_merge($sig('authorizer'), $companyAuthorizer);
?>
<style>
.issue-print-form { font-family: "TH Sarabun New", "Sarabun", sans-serif; }
.issue-print-form .doc-title { font-size: 1.35rem; font-weight: bold; text-align: center; margin-bottom: 0.5rem; }
.issue-print-form .doc-no { text-align: right; margin-bottom: 0.75rem; }
.issue-print-form .meta-table { width: 100%; margin-bottom: 0.75rem; }
.issue-print-form .meta-table th { text-align: left; font-weight: normal; color: #555; width: 8rem; padding: 0.15rem 0.5rem 0.15rem 0; }
.issue-print-form .meta-table td { padding: 0.15rem 0; }
.issue-print-form .intro { margin-bottom: 0.75rem; }
.issue-print-form .item-table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
.issue-print-form .item-table th, .issue-print-form .item-table td { border: 1px solid #333; padding: 0.35rem 0.5rem; }
.issue-print-form .item-table th { background: #f5f5f5; font-weight: bold; text-align: center; }
.issue-print-form .item-table th.text-left { text-align: left; }
.issue-print-form .item-table th.text-right { text-align: right; }
.issue-print-form .item-table td.num { text-align: center; }
.issue-print-form .item-table td.right { text-align: right; }
.issue-print-form .item-table .col-no { width: 2.5rem; }
.issue-print-form .item-table .col-item { min-width: 80px; }
.issue-print-form .item-table .col-unit { width: 4rem; }
.issue-print-form .item-table .col-qty { width: 4rem; }
.issue-print-form .item-table .col-approve { width: 4rem; }
.issue-print-form .item-table .col-price { width: 5rem; }
.issue-print-form .item-table .col-total { width: 5rem; }
.issue-print-form .total-row { text-align: right; font-weight: bold; }
.issue-print-form .total-row td { border: 1px solid #333; padding: 0.35rem 0.5rem; }
.issue-print-form .sign-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem 2rem; margin-top: 2rem; }
.issue-print-form .sign-block { min-width: 0; }
.issue-print-form .sign-line { border-bottom: 1px dotted #333; margin-top: 2rem; padding-bottom: 0.2rem; font-size: 0.95rem; }
.issue-print-form .sign-name { margin-top: 0.35rem; font-size: 0.9rem; }
.issue-print-form .sign-pos { font-size: 0.85rem; color: #555; }
.issue-print-form .sign-date { font-size: 0.85rem; color: #555; margin-top: 0.25rem; }
.issue-print-form .sign-note { margin-top: 0.2rem; }
@media print {
    .no-print { display: none !important; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div class="no-print mb-3 d-flex justify-content-between align-items-center">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['process', 'id' => $model->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="bi bi-file-pdf me-1"></i> ดาวน์โหลด PDF', ['pdf', 'id' => $model->id], ['class' => 'btn btn-danger btn-sm', 'target' => '_blank']) ?>
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print();">
            <i class="bi bi-printer me-1"></i> พิมพ์
        </button>
    </div>
</div>

<div class="issue-print-form print-page card shadow-sm border p-4" style="max-width: 210mm;">
    <div class="doc-title">ใบเบิกวัสดุ</div>
    <div class="doc-no">ใบเบิกวัสดุเลขที่ : <?= Html::encode($model->order_no) ?></div>

    <table class="meta-table">
        <tr><th>ชื่อแผนก/ฝ่าย</th><td><?= Html::encode($unitName) ?></td></tr>
        <tr><th>เรียน</th><td><?= Html::encode($recipient) ?></td></tr>
        <tr><th>วันที่</th><td><?= Html::encode($thaiDateFormatted) ?></td></tr>
    </table>

    <div class="intro">
        ด้วย <?= Html::encode($unitName) ?> มีความประสงค์ขอเบิกวัสดุ ใช้ในแผนก/ฝ่าย มีรายการดังต่อไปนี้
    </div>

    <table class="item-table">
        <thead>
            <tr>
                <th class="col-no text-left">ที่</th>
                <th class="col-item text-left">รายการ</th>
                <th colspan="3" class="num">จำนวน</th>
                <th colspan="2" class="text-right">ราคา/</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th class="col-unit num">หน่วย</th>
                <th class="col-qty num">เบิก</th>
                <th class="col-approve num">อนุมัติ</th>
                <th class="col-price text-right">หน่วย</th>
                <th class="col-total text-right">ราคารวม</th>
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
                    <td class="col-no num"><?= $i + 1 ?></td>
                    <td class="col-item"><?= Html::encode($itemName) ?></td>
                    <td class="col-unit num"><?= Html::encode($unit) ?></td>
                    <td class="col-qty num"><?= number_format($d->qty, 0) ?></td>
                    <td class="col-approve num"><?= $model->status === 'CONFIRMED' ? number_format($d->qty, 0) : '' ?></td>
                    <td class="col-price right"><?= number_format($d->unit_price ?? 0, 2) ?></td>
                    <td class="col-total right"><?= $model->status === 'CONFIRMED' ? number_format($total, 2) : '0.00' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6">ราคารวม</td>
                <td class="right"><?= number_format($grandTotal, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="sign-grid">
        <div class="sign-block">
            <div class="sign-line">ลงชื่อ : <?= $sig('requester')['name'] ? Html::encode($sig('requester')['name']) : '..............................................' ?> ผู้เบิก</div>
            <div class="sign-name">(<?= $sig('requester')['name'] ? Html::encode($sig('requester')['name']) : '..............................................' ?>)</div>
            <div class="sign-pos">ตำแหน่ง <?= $sig('requester')['position'] ? Html::encode($sig('requester')['position']) : '.............................................' ?></div>
            <div class="sign-date"><?= $sig('requester')['date'] ? (Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($sig('requester')['date'], true, false) : $sig('requester')['date']) : Html::encode($thaiDateTime) ?></div>
        </div>
        <div class="sign-block">
            <div class="sign-line">ลงชื่อ : <?= $sig('disbursing')['name'] ? Html::encode($sig('disbursing')['name']) : '..............................................' ?> ผู้จ่ายพัสดุ</div>
            <div class="sign-name">(<?= $sig('disbursing')['name'] ? Html::encode($sig('disbursing')['name']) : 'ไม่ระบุผู้จ่าย' ?>)</div>
            <div class="sign-pos">ตำแหน่ง <?= $sig('disbursing')['position'] ? Html::encode($sig('disbursing')['position']) : '' ?></div>
            <div class="sign-date"><?= $sig('disbursing')['date'] ? (Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($sig('disbursing')['date'], true, false) : $sig('disbursing')['date']) : '-' ?></div>
        </div>
        <div class="sign-block">
            <div class="sign-line">ลงชื่อ : <?= $sig('approver')['name'] ? Html::encode($sig('approver')['name']) : '..............................................' ?> ผู้เห็นชอบ</div>
            <div class="sign-note small text-muted">(หัวหน้า, เจ้าหน้าที่คลังสามารถอนุมัติแทนได้)</div>
            <div class="sign-name">(<?= $sig('approver')['name'] ? Html::encode($sig('approver')['name']) : '..............................................' ?>)</div>
            <div class="sign-pos">ตำแหน่ง <?= $sig('approver')['position'] ? Html::encode($sig('approver')['position']) : '.............................................' ?></div>
            <div class="sign-date"><?= $sig('approver')['date'] ? (Yii::$app->has('thaiDate') ? Yii::$app->thaiDate->toThaiDate($sig('approver')['date'], true, false) : $sig('approver')['date']) : '...............................................' ?></div>
        </div>
        <div class="sign-block">
            <div class="sign-line">ลงชื่อ : <?= $sig('recipient')['name'] ? Html::encode($sig('recipient')['name']) : '..............................................' ?> ผู้รับวัสดุ</div>
            <div class="sign-name">(<?= $sig('recipient')['name'] ? Html::encode($sig('recipient')['name']) : '..............................................' ?>)</div>
            <div class="sign-pos">ตำแหน่ง <?= $sig('recipient')['position'] ? Html::encode($sig('recipient')['position']) : '.............................................' ?></div>
        </div>
        <div class="sign-block" style="grid-column: 2;"></div>
        <div class="sign-block">
            <div class="sign-line">ลงชื่อ : <?= $authorizer['name'] ? Html::encode($authorizer['name']) : '..............................................' ?> ผู้สั่งจ่าย</div>
            <div class="sign-name">(<?= $authorizer['name'] ? Html::encode($authorizer['name']) : '..............................................' ?>)</div>
            <div class="sign-pos">ตำแหน่ง <?= $authorizer['position'] ? Html::encode($authorizer['position']) : '.............................................' ?></div>
        </div>
    </div>
</div>
