<?php

use yii\helpers\Html;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var \app\modules\inventory\models\Stock $model */
/** @var array  $card */
/** @var string $dateFrom */
/** @var string $dateTo */
/** @var string $whName */

$fmtQty = static function ($v) { return number_format((float)($v ?? 0)); };
$fmtVal = static function ($v) { return number_format((float)($v ?? 0), 2); };

$balanceQty = (float) $card['opening']['qty'];
$balanceVal = (float) $card['opening']['value'];

$siteName = Yii::$app->name ?? 'หน่วยงาน';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>สต๊อกการ์ด — <?= Html::encode($card['item_info']['code']) ?></title>
<style>
@page { size: A4; margin: 12mm; }
body { font-family: 'TH Sarabun New', 'Sarabun', sans-serif; font-size: 14pt; color: #000; margin: 0; }
.header { text-align: center; margin-bottom: 8px; }
.header h2 { margin: 0; font-size: 18pt; }
.header .sub { font-size: 12pt; color: #555; }
.info { width: 100%; margin: 8px 0; font-size: 12pt; }
.info td { padding: 2px 6px; vertical-align: top; }
.info .label { font-weight: bold; width: 110px; }
table.card { width: 100%; border-collapse: collapse; margin-top: 6px; }
table.card th, table.card td { border: 1px solid #000; padding: 3px 4px; font-size: 11pt; }
table.card thead th { background: #d0e5ff; text-align: center; }
table.card .num { text-align: right; }
table.card .ctr { text-align: center; }
.opening, .closing { background: #fff3cd; font-weight: bold; }
.adjustment { background: #ffe0e0; }
.signature { margin-top: 30px; width: 100%; }
.signature td { width: 33%; padding: 25px 6px 6px; text-align: center; vertical-align: top; font-size: 12pt; }
.signature .line { border-top: 1px dotted #000; margin: 0 8px 4px; padding-top: 4px; }
.signature .role { color: #444; font-size: 11pt; }
@media print { .no-print { display: none; } }
.no-print { text-align: right; margin: 8px 0; }
.no-print button { padding: 6px 14px; font-size: 13pt; cursor: pointer; }
</style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨 พิมพ์เอกสาร</button>
    <button onclick="window.close()">ปิด</button>
</div>

<div class="header">
    <h2><?= Html::encode($siteName) ?></h2>
    <div class="sub">บัญชีสินค้า (Stock Card)</div>
</div>

<table class="info">
    <tr>
        <td class="label">รหัสสินค้า:</td>
        <td><?= Html::encode($card['item_info']['code']) ?></td>
        <td class="label">หน่วยนับ:</td>
        <td><?= Html::encode($card['item_info']['unit'] ?: '-') ?></td>
    </tr>
    <tr>
        <td class="label">ชื่อสินค้า:</td>
        <td><?= Html::encode($card['item_info']['title']) ?></td>
        <td class="label">คลังสินค้า:</td>
        <td><?= Html::encode($whName ?: '-') ?></td>
    </tr>
    <tr>
        <td class="label">ช่วงวันที่:</td>
        <td colspan="3">
            <?= AppHelper::convertToThai($dateFrom) ?> &nbsp;ถึง&nbsp;
            <?= AppHelper::convertToThai($dateTo) ?>
        </td>
    </tr>
</table>

<table class="card">
    <thead>
        <tr>
            <th style="width:9%">วันที่</th>
            <th>เลขที่เอกสาร / รายการ</th>
            <th style="width:9%">ล็อต</th>
            <th style="width:7%">รับเข้า</th>
            <th style="width:7%">จ่าย รพ.</th>
            <th style="width:7%">จ่าย รพ.สต.</th>
            <th style="width:8%">ราคา/หน่วย</th>
            <th style="width:9%">คงเหลือ qty</th>
            <th style="width:10%">คงเหลือ มูลค่า</th>
        </tr>
    </thead>
    <tbody>
        <!-- OPENING -->
        <tr class="opening">
            <td class="ctr"><?= AppHelper::convertToThai($dateFrom) ?></td>
            <td colspan="6">ยอดยกมา
                (<?= $card['opening']['source'] === 'monthly_close' ? 'จากปิดเดือนก่อน' : 'จากประวัติ' ?>)
            </td>
            <td class="num"><?= $fmtQty($card['opening']['qty']) ?></td>
            <td class="num"><?= $fmtVal($card['opening']['value']) ?></td>
        </tr>

        <!-- MOVEMENTS -->
        <?php foreach ($card['movements'] as $m): ?>
            <?php
            $qty = (float) $m['qty'];
            $val = (float) $m['value'];
            if ($m['kind'] === 'IN') {
                $balanceQty += $qty; $balanceVal += $val;
            } elseif (in_array($m['kind'], ['OUT', 'OUT_HOSP', 'OUT_BRANCH'])) {
                $balanceQty -= $qty; $balanceVal -= $val;
            }
            $desc = '';
            if ($m['kind'] === 'IN') $desc = 'รับเข้า';
            elseif ($m['kind'] === 'OUT_HOSP') $desc = 'จ่ายให้ รพ.';
            elseif ($m['kind'] === 'OUT_BRANCH') $desc = 'จ่ายให้ รพ.สต.';
            else $desc = $m['transaction_type'];
            ?>
            <tr>
                <td class="ctr"><?= AppHelper::convertToThai(date('Y-m-d', strtotime($m['movement_date']))) ?></td>
                <td>
                    <?= Html::encode($desc) ?> #<?= Html::encode($m['code']) ?>
                    <?php if ($m['note']): ?>— <?= Html::encode($m['note']) ?><?php endif; ?>
                </td>
                <td class="ctr"><?= Html::encode($m['lot_number'] ?: '-') ?></td>
                <td class="num"><?= $m['kind'] === 'IN' ? $fmtQty($qty) : '' ?></td>
                <td class="num"><?= $m['kind'] === 'OUT_HOSP' ? $fmtQty($qty) : '' ?></td>
                <td class="num"><?= $m['kind'] === 'OUT_BRANCH' ? $fmtQty($qty) : '' ?></td>
                <td class="num"><?= $fmtVal($m['unit_price']) ?></td>
                <td class="num"><?= $fmtQty($balanceQty) ?></td>
                <td class="num"><?= $fmtVal($balanceVal) ?></td>
            </tr>
        <?php endforeach; ?>

        <!-- ADJUSTMENTS -->
        <?php foreach ($card['adjustments'] as $a): ?>
            <?php
            $balanceQty += $a['delta_qty'];
            $balanceVal += $a['delta_value'];
            ?>
            <tr class="adjustment">
                <td class="ctr"><?= AppHelper::convertToThai($a['shown_date']) ?></td>
                <td colspan="2">ปรับยอด: <?= Html::encode($a['note'] ?: '-') ?></td>
                <td class="num"><?= $a['delta_qty'] > 0 ? '+' . $fmtQty($a['delta_qty']) : '' ?></td>
                <td class="num" colspan="2"><?= $a['delta_qty'] < 0 ? $fmtQty($a['delta_qty']) : '' ?></td>
                <td></td>
                <td class="num"><?= $fmtQty($balanceQty) ?></td>
                <td class="num"><?= $fmtVal($balanceVal) ?></td>
            </tr>
        <?php endforeach; ?>

        <!-- CLOSING -->
        <tr class="opening">
            <td class="ctr"><?= AppHelper::convertToThai($dateTo) ?></td>
            <td colspan="6">ยอดยกไป (คงเหลือสิ้นช่วง)</td>
            <td class="num"><?= $fmtQty($card['closing']['qty']) ?></td>
            <td class="num"><?= $fmtVal($card['closing']['value']) ?></td>
        </tr>
    </tbody>
</table>

<table class="signature">
    <tr>
        <td>
            <div class="line"></div>
            (............................................)<br>
            <span class="role">ผู้บันทึก</span>
        </td>
        <td>
            <div class="line"></div>
            (............................................)<br>
            <span class="role">หัวหน้าคลัง</span>
        </td>
        <td>
            <div class="line"></div>
            (............................................)<br>
            <span class="role">ผู้ตรวจสอบ</span>
        </td>
    </tr>
</table>

</body>
</html>
