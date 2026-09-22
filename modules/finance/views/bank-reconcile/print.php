<?php

use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\finance\models\FinanceBankReconcileItem;
use yii\helpers\Html;

/** @var app\modules\finance\models\FinanceBankReconcile $model */
/** @var FinanceBankReconcileItem[] $items */

$money = fn ($v) => number_format((float) $v, 2);
$months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
$info = SiteHelper::getInfo();
$company = $info['company_name'] ?? 'โรงพยาบาล';

$bank = array_filter($items, fn ($i) => $i->side === 'bank');
$book = array_filter($items, fn ($i) => $i->side === 'book');
$period = ($model->period_month ? 'ประจำเดือน' . $months[$model->period_month] . ' ' : '') . 'ปีงบประมาณ ' . $model->fiscal_year;
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>งบพิสูจน์ยอดเงินฝาก</title>
<style>
    body { font-family: "TH Sarabun New", "Sarabun", sans-serif; font-size: 16px; color: #000; margin: 2rem; }
    h3, h4 { text-align: center; margin: 0.2rem 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    td, th { padding: 4px 8px; vertical-align: top; }
    .num { text-align: right; white-space: nowrap; }
    .line td { border-bottom: 1px solid #999; }
    .tot td { border-top: 2px solid #000; font-weight: bold; }
    .sub { color: #444; }
    .foot { margin-top: 2rem; display: flex; justify-content: space-around; text-align: center; }
    @media print { body { margin: 0.5rem; } .noprint { display: none; } }
</style>
</head>
<body>
<div class="noprint" style="text-align:right;margin-bottom:1rem;">
    <button onclick="window.print()">พิมพ์</button>
</div>
<h3><?= Html::encode($company) ?></h3>
<h4>งบพิสูจน์ยอดเงินฝากธนาคาร</h4>
<div style="text-align:center;"><?= Html::encode(($model->account ? $model->account->label() : '') . ' — ' . $period) ?></div>
<?php if ($model->statement_date): ?>
    <div style="text-align:center;" class="sub">ณ วันที่ <?= Html::encode(AppHelper::convertToThai($model->statement_date)) ?></div>
<?php endif; ?>

<table>
    <tr class="line"><td colspan="2"><strong>ด้านธนาคาร (Statement)</strong></td></tr>
    <tr><td>ยอดคงเหลือตาม statement ธนาคาร</td><td class="num"><?= $money($model->statement_balance) ?></td></tr>
    <?php foreach ($bank as $it): ?>
        <tr><td class="sub">&nbsp;&nbsp;<?= $it->signedLabel() ?> <?= Html::encode($it->typeLabel()) ?><?= $it->description ? ' — ' . Html::encode($it->description) : '' ?><?= $it->ref ? ' (' . Html::encode($it->ref) . ')' : '' ?></td><td class="num"><?= $it->signedLabel() ?><?= $money($it->amount) ?></td></tr>
    <?php endforeach; ?>
    <tr class="tot"><td>ยอดคงเหลือที่ถูกต้อง</td><td class="num"><?= $money($model->adjustedBank()) ?></td></tr>
</table>

<table>
    <tr class="line"><td colspan="2"><strong>ด้านบัญชีโรงพยาบาล (Book)</strong></td></tr>
    <tr><td>ยอดคงเหลือตามบัญชีโรงพยาบาล</td><td class="num"><?= $money($model->book_balance) ?></td></tr>
    <?php foreach ($book as $it): ?>
        <tr><td class="sub">&nbsp;&nbsp;<?= $it->signedLabel() ?> <?= Html::encode($it->typeLabel()) ?><?= $it->description ? ' — ' . Html::encode($it->description) : '' ?><?= $it->ref ? ' (' . Html::encode($it->ref) . ')' : '' ?></td><td class="num"><?= $it->signedLabel() ?><?= $money($it->amount) ?></td></tr>
    <?php endforeach; ?>
    <tr class="tot"><td>ยอดคงเหลือที่ถูกต้อง</td><td class="num"><?= $money($model->adjustedBook()) ?></td></tr>
</table>

<div style="margin-top:1rem;text-align:center;">
    <?php if ($model->isMatched()): ?>
        <strong>ยอดกระทบตรงกัน</strong>
    <?php else: ?>
        <strong style="color:#c00;">ยอดยังไม่ตรงกัน — ผลต่าง <?= $money($model->difference()) ?> บาท</strong>
    <?php endif; ?>
</div>

<div class="foot">
    <div>ลงชื่อ ..............................<br>ผู้จัดทำ</div>
    <div>ลงชื่อ ..............................<br>ผู้ตรวจสอบ</div>
</div>
</body>
</html>
