<?php

use app\modules\finance\components\BahtText;
use app\modules\finance\components\ThaiDate;
use app\modules\finance\models\FinancePayableBilling;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var FinancePayableBilling $model */
/** @var array $site */

$fmt = fn($v) => number_format((float) $v, 2);
$longDate = function ($d) {
    $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $t = $d ? date_create($d) : null;
    return $t ? ((int) $t->format('j') . ' ' . $months[(int) $t->format('n')] . ' ' . ((int) $t->format('Y') + 543)) : '.....................';
};
$company = trim((string) ($site['company_name'] ?? '')) ?: 'โรงพยาบาล';
$address = trim((string) ($site['address'] ?? ''));
$bills = $model->payables;
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ใบรับวางบิล <?= Html::encode($model->billing_no) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'TH Sarabun New', 'Sarabun', 'Tahoma', sans-serif; font-size: 16pt; color: #000; margin: 0; background: #eee; }
    .toolbar { text-align: center; padding: 10px; background: #fff; border-bottom: 1px solid #ccc; }
    .toolbar button, .toolbar a { font-size: 14pt; padding: 6px 18px; border-radius: 6px; border: 1px solid #888; background: #fff; cursor: pointer; text-decoration: none; color: #000; margin: 0 4px; }
    .page { width: 21cm; min-height: 29.7cm; margin: 16px auto; background: #fff; padding: 1.5cm 2cm; box-sizing: border-box; box-shadow: 0 0 6px rgba(0,0,0,.2); }
    h1 { font-size: 22pt; text-align: center; margin: 0 0 4px; }
    .center { text-align: center; }
    .right { text-align: right; }
    .meta { display: flex; justify-content: space-between; margin: 12px 0; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #000; padding: 2px 6px; font-size: 15pt; }
    th { background: #f2f2f2; }
    .num { text-align: right; white-space: nowrap; }
    .signs { display: flex; justify-content: space-between; margin-top: 2.2rem; }
    .sign { width: 45%; text-align: center; line-height: 1.6; }
    .cancelled { color: #b00; text-align: center; font-weight: bold; border: 2px solid #b00; padding: 4px; margin-bottom: 8px; }
    @media print {
        body { background: #fff; }
        .toolbar { display: none; }
        .page { box-shadow: none; margin: 0; width: auto; min-height: auto; }
        @page { size: A4; margin: 0; }
    }
</style>
</head>
<body>
<div class="toolbar">
    <button onclick="window.print()">🖨️ พิมพ์ / บันทึก PDF</button>
    <a href="<?= Url::to(['view', 'id' => $model->id]) ?>">← กลับ</a>
</div>
<div class="page">
    <?php if ($model->isCancelled()): ?><div class="cancelled">ยกเลิกแล้ว — <?= Html::encode($model->cancel_reason) ?></div><?php endif; ?>
    <div class="center"><strong><?= Html::encode($company) ?></strong><?php if ($address): ?><br><?= Html::encode($address) ?><?php endif; ?></div>
    <h1>ใบรับวางบิล</h1>
    <div class="meta">
        <div>เลขที่ <?= Html::encode($model->billing_no) ?></div>
        <div>วันที่ <?= $longDate($model->billing_date) ?></div>
    </div>
    <div>ได้รับวางบิลจาก <strong><?= Html::encode($model->vendor_name) ?></strong>
        <?php if ($model->vendor_ref): ?> ตามใบวางบิลเลขที่ <?= Html::encode($model->vendor_ref) ?><?php endif; ?>
        จำนวน <?= $model->isCancelled() ? 0 : count($bills) ?> ฉบับ ดังนี้</div>

    <table>
        <thead><tr><th style="width:8%">ลำดับ</th><th>เลขที่ใบแจ้งหนี้ / ใบส่งของ</th><th style="width:18%">ลงวันที่</th><th style="width:18%">ครบกำหนดจ่าย</th><th style="width:20%">จำนวนเงิน (บาท)</th></tr></thead>
        <tbody>
            <?php foreach ($bills as $i => $p): ?>
                <tr>
                    <td class="center"><?= $i + 1 ?></td>
                    <td><?= Html::encode($p->invoice_no ?: ($p->source_document_no ?: $p->payable_no)) ?></td>
                    <td class="center"><?= ThaiDate::date($p->invoice_date) ?></td>
                    <td class="center"><?= ThaiDate::date($p->due_date) ?></td>
                    <td class="num"><?= $fmt($p->net_amount) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" class="center"><strong>รวมเงิน</strong> (<?= Html::encode(BahtText::convert((float) $model->total_amount)) ?>)</td>
                <td class="num"><strong><?= $fmt($model->total_amount) ?></strong></td>
            </tr>
        </tbody>
    </table>
    <?php if ($model->note): ?><div>หมายเหตุ: <?= Html::encode($model->note) ?></div><?php endif; ?>

    <div class="signs">
        <div class="sign">
            ลงชื่อ ....................................... ผู้วางบิล<br>
            (<?= Html::encode($model->deliverer_name ?: '.......................................') ?>)<br>
            <?= Html::encode($model->vendor_name) ?>
        </div>
        <div class="sign">
            ลงชื่อ ....................................... ผู้รับวางบิล<br>
            (<?= Html::encode($model->receiver_name ?: '.......................................') ?>)<br>
            <?= Html::encode($company) ?>
        </div>
    </div>
</div>
</body>
</html>
