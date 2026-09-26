<?php

use app\modules\finance\services\FinanceLoanMonthlyRegister as Register;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $report  ผลจาก FinanceLoanMonthlyRegister::build() */
/** @var array $site */
/** @var array $signers */

$fmt = static fn(float $v) => $v != 0 ? number_format($v, 2) : '';
$fmtTotal = static fn(float $v) => number_format($v, 2);
$company = trim((string) ($site['company_name'] ?? ''));
$province = trim((string) ($site['province'] ?? ''));
if ($province !== '' && mb_strpos($province, 'จังหวัด') !== 0) {
    $province = 'จังหวัด' . $province;
}
$monthLabel = Register::monthLabel($report['month']);
$m = $report['month_total'];
$y = $report['ytd_total'];
$dots = str_repeat('.', 60);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ทะเบียนคุมเงินยืม <?= Html::encode($monthLabel) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'TH Sarabun New', 'Sarabun', 'Tahoma', sans-serif; font-size: 14pt; color: #000; margin: 0; background: #eee; }
    .toolbar { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 8px; padding: 10px; background: #fff; border-bottom: 1px solid #ccc; font-size: 13pt; }
    .toolbar select, .toolbar button, .toolbar a { font: inherit; padding: 5px 14px; border-radius: 6px; border: 1px solid #888; background: #fff; cursor: pointer; text-decoration: none; color: #000; }
    .toolbar .primary { background: #0d6efd; border-color: #0d6efd; color: #fff; }
    .page { width: 29.7cm; min-height: 21cm; margin: 16px auto; background: #fff; padding: 1cm 1.2cm; box-sizing: border-box; box-shadow: 0 0 6px rgba(0,0,0,.2); }
    .head { text-align: center; font-weight: bold; line-height: 1.35; margin-bottom: 6px; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border: 1px solid #000; padding: 1px 4px; vertical-align: top; line-height: 1.2; }
    th { font-size: 12pt; text-align: center; vertical-align: middle; font-weight: bold; }
    td { font-size: 13pt; }
    .c { text-align: center; }
    .n { text-align: right; white-space: nowrap; }
    .nowrap { white-space: nowrap; }
    tfoot td { font-weight: bold; }
    .empty { text-align: center; padding: 16px; color: #555; }
    .signs { display: flex; justify-content: space-around; margin-top: 28px; text-align: center; line-height: 1.5; page-break-inside: avoid; }
    .signs > div { width: 40%; }
    .note { max-width: 29.7cm; margin: 0 auto 16px; font-size: 12pt; color: #444; padding: 0 4px; }
    @media print {
        body { background: #fff; }
        .toolbar, .note { display: none; }
        .page { box-shadow: none; margin: 0; width: auto; min-height: auto; padding: 0; }
        @page { size: A4 landscape; margin: 10mm 10mm 12mm; }
        tr { page-break-inside: avoid; }
    }
</style>
</head>
<body>
<form class="toolbar" method="get" action="<?= Url::to(['monthly-register']) ?>">
    <label for="mr-month">ประจำเดือน</label>
    <?= Html::dropDownList('month', $report['month'], Register::monthOptions(), ['id' => 'mr-month', 'onchange' => 'this.form.submit()']) ?>
    <button type="button" class="primary" onclick="window.print()">🖨️ พิมพ์ / บันทึก PDF</button>
    <a href="<?= Url::to(['monthly-register', 'month' => $report['month'], 'format' => 'xlsx']) ?>">⬇️ Excel</a>
    <a href="<?= Url::to(['index']) ?>">← ทะเบียนเงินยืม</a>
</form>

<div class="page">
    <div class="head">
        <div>ส่วนราชการ <?= Html::encode(trim($company . ' ' . $province)) ?></div>
        <div>ทะเบียนคุมเอกสารแทนตัวเงิน สัญญารับรองการยืมเงิน ประจำเดือน <?= Html::encode($monthLabel) ?></div>
    </div>

    <table>
        <colgroup>
            <col style="width:7.5%"><col style="width:7.5%"><col style="width:27%"><col style="width:8%"><col style="width:8%">
            <col style="width:12.5%"><col style="width:7.5%"><col style="width:7%"><col style="width:7.5%"><col style="width:7.5%">
        </colgroup>
        <thead>
            <tr>
                <th>วัน/เดือน/ปี<br>ที่ยืม</th>
                <th>เลขที่เอกสาร</th>
                <th>รายการ</th>
                <th>ยอดคงเหลือ<br>ยกมา</th>
                <th>จำนวนเงิน</th>
                <th>ชื่อผู้ยืม</th>
                <th>วันครบกำหนด<br>ส่งคืน</th>
                <th>วันที่ส่งคืน</th>
                <th>จำนวนเงิน</th>
                <th>ลูกหนี้<br>คงเหลือ</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$report['rows']): ?>
            <tr><td colspan="10" class="empty">ไม่มีลูกหนี้เงินยืมคงค้าง ณ สิ้นเดือนนี้</td></tr>
        <?php endif; ?>
        <?php foreach ($report['rows'] as $row): ?>
            <?php $loan = $row['loan']; ?>
            <tr>
                <td class="c nowrap"><?= Register::shortDate($loan->borrowed_at) ?></td>
                <td class="c nowrap"><?= Html::encode($loan->contract_no) ?></td>
                <td><?= Html::encode($loan->purpose) ?></td>
                <td class="n"><?= $fmt($row['brought']) ?></td>
                <td class="n"><?= $fmt($row['borrowed']) ?></td>
                <td><?= Html::encode($loan->borrower_name) ?></td>
                <td class="c nowrap"><?= Register::shortDate($loan->due_at) ?></td>
                <td class="c nowrap"><?= implode('<br>', array_map([Register::class, 'shortDate'], $row['return_dates'])) ?></td>
                <td class="n"><?= $fmt($row['returned']) ?></td>
                <td class="n"><?= $fmtTotal($row['balance']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="c">รวมเดือนนี้</td>
                <td class="n"><?= $fmtTotal($m['brought']) ?></td>
                <td class="n"><?= $fmtTotal($m['borrowed']) ?></td>
                <td colspan="3"></td>
                <td class="n"><?= $fmtTotal($m['returned']) ?></td>
                <td class="n"><?= $fmtTotal($m['balance']) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="c">รวมตั้งแต่ต้นปี</td>
                <td class="n"><?= $fmtTotal($y['brought']) ?></td>
                <td class="n"><?= $fmtTotal($y['borrowed']) ?></td>
                <td colspan="3"></td>
                <td class="n"><?= $fmtTotal($y['returned']) ?></td>
                <td class="n"><?= $fmtTotal($y['balance']) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="signs">
        <div>
            ผู้จัดทำ<br><br>
            <?= $dots ?><br>
            (<?= Html::encode($signers['preparer']['name'] ?: str_repeat('.', 40)) ?>)<br>
            <?= Html::encode($signers['preparer']['position']) ?>
        </div>
        <div>
            <br><br>
            <?= $dots ?><br>
            (<?= Html::encode($signers['director']['name'] ?: str_repeat('.', 40)) ?>)<br>
            <?= Html::encode($signers['director']['position']) ?>
        </div>
    </div>
</div>

<p class="note">
    แสดงเฉพาะใบยืมที่ยังคืนไม่ครบ ณ สิ้นเดือน (ค้างยกมาจากเดือนก่อน ๆ และยืมใหม่ในเดือนนี้) นับทุกสถานะยกเว้นยกเลิก ·
    “รวมตั้งแต่ต้นปี” ช่องยอดยกมาคือยอดค้างจากปีงบก่อน ณ 1 ต.ค., ช่องจำนวนเงินคือยอดยืมสะสม, ช่องส่งคืนคือยอดส่งคืนสะสม
    ลูกหนี้คงเหลือจึงเท่ากับยอดของเดือนนี้
</p>
</body>
</html>
