<?php

use app\components\ThaiDateHelper;

/** @var app\modules\am\models\AmAssetDepreciationMonthly[] $records */
/** @var float $totalDepreciation */
/** @var string $orgName */
/** @var string $periodLabel */
/** @var string $printDate */
/** @var array $summaryByType */
/** @var int $fiscalYear */
/** @var int $month */
$summaryByType = $summaryByType ?? [];
$fiscalYear = $fiscalYear ?? (int) date('Y');
$month = $month ?? (int) date('n');
?>
<style>
    body { font-family: thsarabun, sans-serif; font-size: 11pt; }
    .header { text-align: center; margin-bottom: 12px; border-bottom: 1px solid #333; padding-bottom: 8px; }
    .header h1 { font-size: 16pt; margin: 0 0 4px 0; }
    .header .org { font-size: 12pt; }
    .header .meta { font-size: 10pt; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #333; padding: 4px 6px; }
    th { background: #e9ecef; font-weight: bold; font-size: 10pt; }
    td { font-size: 10pt; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .fw-bold { font-weight: bold; }
    tfoot td { font-weight: bold; background: #f8f9fa; }
    .signature { margin-top: 24px; }
    .signature table { border: none; }
    .signature td { border: none; padding: 8px 24px 0 0; vertical-align: top; width: 33%; }
    .signature .line { border-bottom: 1px solid #333; margin-top: 32px; padding-top: 4px; font-size: 10pt; text-align: center; }
</style>

<div class="header">
    <div class="org"><?= htmlspecialchars($orgName) ?></div>
    <h1>รายงานค่าเสื่อมรายเดือน (Monthly Depreciation Report)</h1>
    <div class="meta">เดือน / ปีงบประมาณ: <?= htmlspecialchars($periodLabel) ?> &nbsp;|&nbsp; วันที่พิมพ์: <?= htmlspecialchars($printDate) ?></div>
</div>

<?php if (!empty($summaryByType)): ?>
<p style="margin: 8px 0 4px 0; font-weight: bold; font-size: 10pt;">รวมมูลค่าแยกตามประเภทครุภัณฑ์</p>
<table style="margin-bottom: 12px;">
    <thead>
        <tr>
            <th style="width:5%;" class="text-center">ลำดับ</th>
            <th style="width:32%;">ประเภทครุภัณฑ์</th>
            <th style="width:11%;" class="text-center">จำนวนรายการ</th>
            <th style="width:16%;" class="text-right">มูลค่าต้นเดือน</th>
            <th style="width:16%;" class="text-right">ค่าเสื่อมเดือน</th>
            <th style="width:16%;" class="text-right">ค่าเสื่อมสะสม</th>
            <th style="width:16%;" class="text-right">มูลค่าปลายเดือน</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($summaryByType as $typeName => $row): ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($typeName) ?></td>
                <td class="text-center"><?= (int) $row['count'] ?></td>
                <td class="text-right fw-bold"><?= number_format($row['beginning_value'], 2) ?></td>
                <td class="text-right fw-bold"><?= number_format($row['depreciation_amount'], 2) ?></td>
                <td class="text-right fw-bold"><?= number_format($row['accumulated_depreciation'], 2) ?></td>
                <td class="text-right fw-bold"><?= number_format($row['remaining_value'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th style="width:3%;">ลำดับ</th>
            <th style="width:9%;">รหัสครุภัณฑ์</th>
            <th style="width:14%;">ชื่อครุภัณฑ์</th>
            <th style="width:12%;">ประเภทครุภัณฑ์</th>
            <th style="width:9%;">วันที่รับเข้า</th>
            <th style="width:6%;" class="text-center">อายุการใช้งาน (ปี)</th>
            <th style="width:6%;" class="text-center">ปีที่ใช้มาแล้ว</th>
            <th style="width:9%;" class="text-right">มูลค่าต้นเดือน</th>
            <th style="width:5%;" class="text-center">วันใช้</th>
            <th style="width:10%;" class="text-right">ค่าเสื่อมเดือน</th>
            <th style="width:10%;" class="text-right">ค่าเสื่อมสะสม</th>
            <th style="width:10%;" class="text-right">มูลค่าปลายเดือน</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($records as $r): ?>
            <?php $a = $r->asset; ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($a->code ?? '') ?></td>
                <td><?= htmlspecialchars($a->asset_name ?? $a->AssetitemName() ?? '') ?></td>
                <td><?= htmlspecialchars($a->assetType->title ?? $a->AssetTypeName() ?? '-') ?></td>
                <td><?= $a->receive_date ? ThaiDateHelper::formatThaiDate($a->receive_date) : '-' ?></td>
                <td class="text-center"><?= (int) ($a->useful_life ?? 0) ?></td>
                <td class="text-center"><?php
                    $yearsUsed = '-';
                    if (!empty($a->receive_date)) {
                        $reportEnd = new \DateTime($fiscalYear . '-' . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '-01');
                        $reportEnd->modify('last day of this month');
                        $receive = new \DateTime($a->receive_date);
                        $yearsUsed = ($receive <= $reportEnd) ? (int) $receive->diff($reportEnd)->y : 0;
                    }
                    echo $yearsUsed;
                ?></td>
                <td class="text-right fw-bold"><?= number_format((float) $r->beginning_value, 2) ?></td>
                <td class="text-center"><?= (int) $r->days_used ?></td>
                <td class="text-right fw-bold"><?= number_format((float) $r->depreciation_amount, 2) ?></td>
                <td class="text-right fw-bold"><?= number_format((float) $r->accumulated_depreciation, 2) ?></td>
                <td class="text-right fw-bold"><?= number_format((float) $r->remaining_value, 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="9" class="text-right">รวมค่าเสื่อมประจำเดือน</td>
            <td class="text-right"><?= number_format($totalDepreciation, 2) ?></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

<div class="signature">
    <table>
        <tr>
            <td>
                <div class="line">ผู้จัดทำ (Prepared By)</div>
            </td>
            <td>
                <div class="line">ผู้ตรวจสอบ (Checked By)</div>
            </td>
            <td>
                <div class="line">ผู้อนุมัติ (Approved By)</div>
            </td>
        </tr>
    </table>
</div>
