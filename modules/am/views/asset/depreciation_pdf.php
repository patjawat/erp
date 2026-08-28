<?php

use yii\helpers\Html;

/** @var app\modules\am\models\Asset $model */
/** @var string $organizationName */
/** @var string $departmentName */
/** @var string $assetTypeTitle */
/** @var string $assetName */
/** @var string $assetCode */
/** @var string $location */
/** @var array $vendor */
/** @var string $budgetType */
/** @var string $purchaseMethod */
/** @var string $calculationMethodLabel */
/** @var string $docNo */
/** @var string $unit */
/** @var array $rows */

$organizationName = trim((string) ($organizationName ?? '')) ?: '-';
$departmentName = trim((string) ($departmentName ?? '')) ?: '-';
$assetTypeTitle = trim((string) ($assetTypeTitle ?? '')) ?: '-';
$assetName = trim((string) ($assetName ?? '')) ?: '-';
$assetCode = trim((string) ($assetCode ?? '')) ?: '-';
$location = trim((string) ($location ?? '')) ?: '-';
$vendor = is_array($vendor ?? null) ? $vendor : [];
$vendorTitle = trim((string) ($vendor['title'] ?? '')) ?: '-';
$vendorAddress = trim((string) ($vendor['address'] ?? '')) ?: '-';
$vendorPhone = trim((string) ($vendor['phone'] ?? '')) ?: '-';
$budgetType = trim((string) ($budgetType ?? '')) ?: '-';
$purchaseMethod = trim((string) ($purchaseMethod ?? '')) ?: '-';
$calculationMethodLabel = trim((string) ($calculationMethodLabel ?? '')) ?: '-';
$docNo = trim((string) ($docNo ?? '')) ?: '-';
$unit = trim((string) ($unit ?? '')) ?: 'เครื่อง';
$rows = is_array($rows ?? null) ? $rows : [];

$cellText = static function ($value): string {
    $value = trim((string) $value);
    return $value !== '' ? Html::encode($value) : '&nbsp;';
};

$itemText = static function ($value): string {
    $value = trim((string) $value);
    return $value !== '' ? nl2br(Html::encode($value)) : '&nbsp;';
};
?>
<style>
    @page {
        margin: 8mm 8mm 12mm 8mm;
    }

    body {
        font-family: thsarabun, sans-serif;
        font-size: 11.5pt;
        color: #000;
        margin: 0;
        padding: 0;
    }

    body,
    table,
    tr,
    td,
    th,
    div,
    span {
        font-family: thsarabun, sans-serif;
    }

    .sheet {
        width: 100%;
    }

    .title {
        text-align: center;
        font-size: 18pt;
        line-height: 1.1;
        margin: 0 0 8mm 0;
        padding: 0;
    }

    .top-wrap {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8mm;
    }

    .top-wrap td {
        vertical-align: top;
        padding: 0;
    }

    .top-left {
        width: 70%;
        padding-right: 5mm;
    }

    .top-right {
        width: 30%;
    }

    .meta-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .meta-table td {
        padding: 1.1mm 2mm 1.1mm 0;
        vertical-align: top;
        font-size: 12pt;
        line-height: 1.15;
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    /* คอลัมน์ซ้าย: เว้นช่องว่างขวาให้พ้นคอลัมน์ที่สอง (กันข้อความไทยที่ไม่ตัดบรรทัดล้นไปทับ) */
    .meta-table td.col-l {
        padding-right: 8mm;
    }

    /* คอลัมน์ขวา: ขยับเข้าไปทางขวาให้ห่างจากคอลัมน์ซ้าย */
    .meta-table td.col-r {
        padding-left: 4mm;
    }

    .meta-table .meta-label {
        display: inline-block;
        /* min-width: 18mm; */
        font-size: 20px;
        white-space: nowrap;
        padding-right: 1.5mm;
        font-weight: bold;
    }

    .meta-table .meta-value {
        word-break: break-word;
    }

    .right-panel {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-left: auto;
        margin-right: 0;
    }

    .right-panel td {
        font-size: 12pt;
        line-height: 1.18;
        padding: 0.8mm 0;
        vertical-align: top;
        word-break: break-word;
    }

    .right-panel .section-title {
        text-align: center;
        font-size: 13.5pt;
        padding-bottom: 2mm;
    }

    .ledger {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .ledger th,
    .ledger td {
        border: 1px solid #222;
        padding: 2mm 1.5mm;
        vertical-align: middle;
        font-size: 11pt;
        line-height: 1.15;
    }

    .ledger th {
        text-align: center;
        font-weight: normal;
        padding-top: 2.5mm;
        padding-bottom: 2.5mm;
    }

    .ledger td {
        word-break: break-word;
    }

    .center {
        text-align: center;
    }

    .right {
        text-align: right;
    }

    .item-cell {
        white-space: normal;
        line-height: 1.2;
    }

    .blank-row td {
        height: 9mm;
    }

    .no-data {
        text-align: center;
        padding: 8mm 0;
        color: #444;
    }
</style>

<div class="sheet">
    <div class="title">ทะเบียนคุมทรัพย์สิน</div>

    <table class="top-wrap">
        <tr>
            <td class="top-left">
                <table class="meta-table">
                    <colgroup>
                        <col style="width:50%;">
                        <col style="width:50%;">
                    </colgroup>
                    <tr>
                        <td class="col-l"><span style="font-size: 18px;font-weight: bold;">ประเภทครุภัณฑ์&nbsp;</span><span style="font-size: 18px;"><?= Html::encode($assetTypeTitle) ?></span></td>
                        <td class="col-r"><span style="font-size: 18px;font-weight: bold;">รหัส&nbsp;</span><span style="font-size: 18px;"><?= Html::encode($assetCode) ?></span></td>
                    </tr>
                    <tr>
                        <td class="col-l"><span style="font-size: 18px;font-weight: bold;">สถานที่ตั้ง/หน่วยงานที่รับผิดชอบ&nbsp;</span><span style="font-size: 18px;"><?= Html::encode($location) ?></span></td>
                        <td class="col-r"><span style="font-size: 18px;font-weight: bold;">ชื่อผู้ขาย/ผู้รับจ้าง/ผู้บริจาค&nbsp;</span><span style="font-size: 18px;"><?= Html::encode($vendorTitle) ?></span></td>
                    </tr>
                    <tr>
                        <td class="col-l"><span style="font-size: 18px;font-weight: bold;">ที่อยู่&nbsp;</span><span style="font-size: 18px;"><?= Html::encode($vendorAddress) ?></span></td>
                        <td class="col-r"><span style="font-size: 18px;font-weight: bold;">โทรศัพท์&nbsp;</span><span style="font-size: 18px;"><?= Html::encode($vendorPhone) ?></span></td>
                    </tr>
                    <tr>
                        <td class="col-l"><span style="font-size: 18px;font-weight: bold;">ประเภทเงิน&nbsp;</span><span style="font-size: 18px;"><?= Html::encode($budgetType) ?></span></td>
                        <td class="col-r"><span style="font-size: 18px;font-weight: bold;">วิธีการได้มา&nbsp;</span><span style="font-size: 18px;"><?= Html::encode($purchaseMethod) ?></span></td>
                    </tr>
                </table>
            </td>
            <td class="top-right">
                <table class="right-panel">
                    <tr>
                        <td><span style="font-size: 18px;font-weight: bold;">ส่วนราชการ</span> <?= Html::encode($organizationName) ?></td>
                    </tr>
                    <tr>
                        <td><span style="font-size: 18px;font-weight: bold;">หน่วยงาน</span> <?= Html::encode($departmentName) ?></td>
                    </tr>
                    <tr>
                        <td><span style="font-size: 18px;font-weight: bold;">แบบรุ่น</span> <?= Html::encode($assetName) ?></td>
                    </tr>
                    <tr>
                        <td><span style="font-size: 18px;font-weight: bold;">วิธีคำนวณ</span> <?= Html::encode($calculationMethodLabel) ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width:18mm;">วัน เดือน ปี</th>
                <th style="width:15mm;">ที่เอกสาร</th>
                <th style="width:44mm;">รายการ</th>
                <th style="width:20mm;">จำนวน<br>หน่วย</th>
                <th style="width:23mm;">ราคาต่อ<br>หน่วย/ชุด</th>
                <th style="width:23mm;">มูลค่ารวม</th>
                <th style="width:17mm;">อายุใช้งาน</th>
                <th style="width:20mm;">อัตราค่า<br>เสื่อมราคา</th>
                <th style="width:25mm;">ค่าเสื่อมราคา<br>ประจำปี</th>
                <th style="width:23mm;">ค่าเสื่อม<br>ราคาสะสม</th>
                <th style="width:25mm;">มูลค่าสุทธิ</th>
                <th style="width:18mm;">หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)): ?>
            <?php foreach ($rows as $row): ?>
                    <?php $isBlank = ($row['type'] ?? '') === 'blank'; ?>
                    <tr class="<?= $isBlank ? 'blank-row' : '' ?>">
                        <td class="center"><?= $cellText($row['date'] ?? '') ?></td>
                        <td class="center"><?= $cellText($row['doc_no'] ?? '') ?></td>
                        <td class="item-cell"><?= $itemText($row['item'] ?? '') ?></td>
                        <td class="center"><?= $cellText($row['qty'] ?? '') ?></td>
                        <td class="right"><?= $cellText($row['unit_price'] ?? '') ?></td>
                        <td class="right"><?= $cellText($row['total'] ?? '') ?></td>
                        <td class="center"><?= $cellText($row['life'] ?? '') ?></td>
                        <td class="center"><?= $cellText($row['rate'] ?? '') ?></td>
                        <td class="right"><?= $cellText($row['annual'] ?? '') ?></td>
                        <td class="right"><?= $cellText($row['accumulated'] ?? '') ?></td>
                        <td class="right"><?= $cellText($row['net'] ?? '') ?></td>
                        <td class="center"><?= $cellText($row['note'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="12" class="no-data">ไม่พบข้อมูลค่าเสื่อม</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
