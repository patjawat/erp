<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinancePayablePayment $pay */
/** @var array $lines */
/** @var array $site */

$fmt = fn($v) => number_format((float) $v, 2);
$thDate = function ($d) {
    if (!$d) {
        return '.....................';
    }
    $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $t = date_create($d);
    return $t ? ((int) $t->format('j') . ' ' . $months[(int) $t->format('n')] . ' ' . ((int) $t->format('Y') + 543)) : $d;
};

/** แปลงจำนวนเงินเป็นข้อความภาษาไทย */
$bahttext = function ($number) {
    $number = number_format((float) $number, 2, '.', '');
    [$int, $dec] = explode('.', $number);
    $num = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    $dig = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];
    $conv = function ($n) use ($num, $dig, &$conv) {
        $n = (string) (int) $n;
        $len = strlen($n);
        if ($len == 0 || (int) $n === 0) {
            return '';
        }
        if ($len > 7) {
            return $conv(substr($n, 0, $len - 6)) . 'ล้าน' . $conv(substr($n, $len - 6));
        }
        $r = '';
        for ($i = 0; $i < $len; $i++) {
            $d = (int) $n[$i];
            $pos = $len - $i - 1;
            if ($d === 0) {
                continue;
            }
            if ($pos === 0 && $d === 1 && $len > 1) {
                $r .= 'เอ็ด';
            } elseif ($pos === 1 && $d === 2) {
                $r .= 'ยี่' . $dig[$pos];
            } elseif ($pos === 1 && $d === 1) {
                $r .= $dig[$pos];
            } else {
                $r .= $num[$d] . $dig[$pos];
            }
        }
        return $r;
    };
    $baht = $conv($int);
    $out = ($baht === '' ? 'ศูนย์บาท' : $baht . 'บาท');
    $out .= ((int) $dec === 0) ? 'ถ้วน' : ($conv($dec) . 'สตางค์');
    return $out;
};

// ผู้ลงนาม = ผู้อำนวยการตามตั้งค่า (SiteHelper)
$directorName = '';
$d = $site['director'] ?? null;
if (is_object($d)) {
    $directorName = method_exists($d, 'fullname') ? $d->fullname() : (string) ($d->fullname ?? '');
}
$directorPos = trim((string) ($site['director_position'] ?? '')) ?: 'ผู้อำนวยการโรงพยาบาล';
$company = trim((string) ($site['company_name'] ?? '')) ?: 'โรงพยาบาล';
$address = trim((string) ($site['address'] ?? ''));
$phone = trim((string) ($site['phone'] ?? ''));

$this->title = 'หนังสือนำส่ง ' . $pay->vendor_name_snapshot;
$hasWht = (float) $pay->wht_total > 0.005;
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= Html::encode($this->title) ?></title>
<style>
    * { box-sizing: border-box; }
    body { font-family: 'TH Sarabun New', 'Sarabun', 'Tahoma', sans-serif; font-size: 16pt; color: #000; margin: 0; background: #eee; }
    .toolbar { text-align: center; padding: 12px; }
    .toolbar button, .toolbar a { font-size: 14pt; padding: 6px 18px; border-radius: 6px; border: 1px solid #888; background: #fff; cursor: pointer; text-decoration: none; color: #000; }
    .toolbar button { background: #0d6efd; color: #fff; border-color: #0d6efd; }
    .page { width: 21cm; min-height: 29.7cm; padding: 2cm 2.2cm; margin: 0 auto 16px; background: #fff; box-shadow: 0 0 6px rgba(0,0,0,.2); }
    .center { text-align: center; }
    .right { text-align: right; }
    .head { text-align: center; margin-bottom: .4rem; }
    .head .org { font-weight: bold; }
    .row-line { margin: .2rem 0; }
    table.bills { width: 100%; border-collapse: collapse; margin: .5rem 0; }
    table.bills th, table.bills td { border: 1px solid #000; padding: 3px 8px; }
    table.bills th { text-align: center; }
    .num { text-align: right; }
    .indent { text-indent: 2.5em; }
    .sign { margin-top: 1.2rem; text-align: center; width: 60%; margin-left: auto; }
    .muted { color: #333; }
    @media print {
        body { background: #fff; font-size: 16pt; }
        .toolbar { display: none; }
        .page { box-shadow: none; margin: 0; width: auto; min-height: auto; padding: 1.5cm 2cm; }
        @page { size: A4; margin: 0; }
    }
</style>
</head>
<body>
<div class="toolbar">
    <button onclick="window.print()">🖨️ พิมพ์ / บันทึก PDF</button>
    <?php if (!empty($cheque)): ?>
        <a href="<?= \yii\helpers\Url::to(['/finance/cheque/print', 'id' => $cheque->id]) ?>" target="_blank">🧾 พิมพ์เช็ค</a>
    <?php endif; ?>
    <a href="<?= \yii\helpers\Url::to(['pay']) ?>">← กลับหน้าจ่ายชำระ</a>
</div>

<div class="page">
    <div class="head">
        <div class="row-line right">ที่ <?= Html::encode($pay->doc_no ?: (($site['doc_number'] ?? '') . '/')) ?></div>
        <div class="org"><?= Html::encode($company) ?></div>
        <?php if ($address): ?><div><?= Html::encode($address) ?></div><?php endif; ?>
    </div>

    <div class="row-line center"><?= $thDate($pay->pay_date) ?></div>

    <div class="row-line"><b>เรื่อง</b>&nbsp;&nbsp;<?= Html::encode($pay->subject ?: 'ชำระเงินค่าสินค้า/บริการ') ?></div>
    <div class="row-line"><b>เรียน</b>&nbsp;&nbsp;ผู้จัดการ <?= Html::encode($pay->vendor_name_snapshot) ?></div>

    <div class="row-line"><b>สิ่งที่ส่งมาด้วย</b>&nbsp;&nbsp;1. เช็คธนาคาร<?= Html::encode($pay->bank_name ?: '') ?> จำนวน 1 ฉบับ
        <?php if ($hasWht): ?><br><span style="margin-left:6.5em">2. ใบรับรองการหักภาษี ณ ที่จ่าย จำนวน 1 ฉบับ</span><?php endif; ?>
    </div>

    <div class="row-line indent" style="margin-top:.6rem"><?= Html::encode($company) ?> ขอชำระเงินตามรายละเอียดใบส่งของ ดังนี้</div>

    <table class="bills">
        <thead><tr><th style="width:8%">ลำดับ</th><th>เลขที่ใบส่งของ</th><th style="width:22%">จำนวนเงิน</th><th style="width:20%">หมายเหตุ</th></tr></thead>
        <tbody>
            <?php $i = 1;
            foreach ($lines as $ln): $p = $ln['payable']; ?>
                <tr>
                    <td class="center"><?= $i++ ?></td>
                    <td><?= Html::encode($p->invoice_no ?: $p->payable_no) ?></td>
                    <td class="num"><?= $fmt($p->gross_amount) ?></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2" class="right"><b>รวมเป็นเงินขั้นต้น</b></td>
                <td class="num"><?= $fmt($pay->gross_total) ?></td>
                <td></td>
            </tr>
            <?php if ($hasWht): ?>
            <tr><td colspan="2" class="right">หัก ภาษี ณ ที่จ่าย</td><td class="num"><?= $fmt($pay->wht_total) ?></td><td></td></tr>
            <?php endif; ?>
            <tr>
                <td colspan="2" class="right"><b>คงเหลือจ่ายเป็นเช็ค (<?= $bahttext($pay->net_total) ?>)</b></td>
                <td class="num"><b><?= $fmt($pay->net_total) ?></b></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="row-line">โดยชำระเป็นเช็คธนาคาร<?= Html::encode($pay->bank_name ?: '.............') ?>
        สาขา<?= Html::encode($pay->bank_branch ?: '.............') ?>
        เลขที่ <?= Html::encode($pay->cheque_no ?: '.......................') ?>
        ลงวันที่ <?= $thDate($pay->pay_date) ?></div>

    <div class="row-line" style="margin-top:.4rem">เมื่อได้รับเงินแล้ว กรุณาดำเนินการดังต่อไปนี้</div>
    <div class="row-line" style="margin-left:2em">( ✓ ) ส่งใบเสร็จรับเงินให้<?= Html::encode($company) ?></div>
    <div class="row-line" style="margin-left:2em">( ✓ ) แจ้งให้โรงพยาบาลฯ ทราบต่อไป</div>

    <div class="row-line indent" style="margin-top:.5rem">จึงเรียนมาเพื่อโปรดพิจารณาดำเนินการต่อไป</div>

    <div class="sign">
        <div>ขอแสดงความนับถือ</div>
        <div style="margin-top:2.8rem">( <?= Html::encode($directorName ?: '.....................................') ?> )</div>
        <div><?= Html::encode($directorPos) ?></div>
    </div>

    <div style="margin-top:1.5rem" class="muted">
        <div>ฝ่ายบริหารทั่วไป</div>
        <?php if ($phone): ?><div>โทร. <?= Html::encode($phone) ?></div><?php endif; ?>
    </div>
</div>
</body>
</html>
