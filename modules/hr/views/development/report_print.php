<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var int $year */
/** @var array $info ข้อมูลหน่วยงาน (SiteHelper::getInfo) */
/** @var string|null $directorName ชื่อ-สกุล ผอ. */
/** @var array $report DevelopmentReport::orgSummary */
/** @var array $activityType ['series'=>[],'labels'=>[]] */
/** @var array $monthly listSummaryMonth */
/** @var array $byDepartment */
/** @var array $followup */
/** @var array $benefitRegister */
/** @var array $idpCoverage */

$nf = static fn($n) => number_format((float) $n);
$money = static fn($n) => number_format((float) $n, 2);
$today = ThaiDateHelper::formatThaiDate(date('Y-m-d'));
$org = $info['company_name'] ?? 'โรงพยาบาล';
$sm = $report['summary'];

// เดือนตามปีงบ (ต.ค. - ก.ย.)
$fiscalMonths = [10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.', 1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.'];
$typeTotal = array_sum($activityType['series'] ?: [0]);
$zeroDepts = array_values(array_filter($byDepartment, static fn($r) => $r['developed'] === 0));
$suggestions = array_values(array_filter($benefitRegister, static fn($r) => trim((string) $r['suggestion']) !== ''));
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>รายงานการพัฒนาบุคลากร ปีงบ <?= $year ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "TH Sarabun New", "Sarabun", "Tahoma", sans-serif; font-size: 15px; color: #111; margin: 24px; }
        h1 { font-size: 20px; margin: 0 0 2px; text-align: center; }
        .sub { text-align: center; color: #444; font-size: 14px; margin-bottom: 2px; }
        h2 { font-size: 16px; margin: 20px 0 6px; padding-bottom: 4px; border-bottom: 2px solid #333; }
        .muted { color: #555; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #999; padding: 4px 7px; font-size: 13px; vertical-align: top; }
        th { background: #f0f0f0; text-align: left; }
        td.num, th.num { text-align: right; }
        td.c, th.c { text-align: center; }
        .kpi-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .kpi { border: 1px solid #ccc; border-radius: 6px; padding: 6px 12px; min-width: 120px; }
        .kpi .l { font-size: 12px; color: #555; }
        .kpi .v { font-size: 18px; font-weight: 700; }
        .kpi .t { font-size: 11px; color: #888; }
        .warn { color: #b30000; }
        .ok { color: #0a7d2c; }
        .note-box { border: 1px dashed #999; border-radius: 6px; min-height: 54px; padding: 8px; margin-top: 6px; }
        .sign { margin-top: 42px; display: flex; justify-content: space-around; text-align: center; }
        .sign div { width: 42%; }
        .toolbar { text-align: right; margin-bottom: 12px; }
        .toolbar button { padding: 6px 16px; font-size: 14px; cursor: pointer; }
        @media print {
            .toolbar { display: none; }
            body { margin: 0; }
            h2 { page-break-after: avoid; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <button onclick="window.print()">🖨️ พิมพ์ / บันทึก PDF</button>
</div>

<h1>รายงานสรุปผลการพัฒนาบุคลากร ประจำปีงบประมาณ <?= $year ?></h1>
<div class="sub"><?= Html::encode($org) ?></div>
<div class="sub muted">ตามมาตรฐานโรงพยาบาลคุณภาพ (HA) ตอนที่ I-5.2 การพัฒนาบุคลากร &nbsp;|&nbsp; พิมพ์เมื่อ <?= Html::encode($today) ?></div>

<!-- 1. ภาพรวมเชิงปริมาณ -->
<h2>๑. ภาพรวมเชิงปริมาณ</h2>
<div class="kpi-row">
    <div class="kpi"><div class="l">กิจกรรมการพัฒนา</div><div class="v"><?= $nf($report['activities']) ?></div><div class="t">ครั้ง</div></div>
    <div class="kpi"><div class="l">บุคลากรที่ได้รับการพัฒนา</div><div class="v"><?= $nf($report['persons_developed']) ?>/<?= $nf($report['active_staff']) ?></div><div class="t">ครอบคลุม <?= $report['coverage_percent'] ?>%</div></div>
    <div class="kpi"><div class="l">คน-ครั้ง (โอกาสพัฒนา)</div><div class="v"><?= $nf($report['person_times']) ?></div><div class="t">ผู้ขอ + คณะเดินทาง</div></div>
    <div class="kpi"><div class="l">อัตราส่งสรุปผล</div><div class="v"><?= $sm['percent'] ?>%</div><div class="t">เชิงคุณภาพ</div></div>
</div>

<!-- 2. งบประมาณ แผน vs ผล -->
<h2>๒. งบประมาณ แผน–ผล การพัฒนาบุคลากร</h2>
<div class="muted">งบที่ตั้งไว้อ้างอิงแผนการเงิน (แผนพัฒนาบุคลากร) · งบใช้จริงจากใบเดินทางไปราชการ</div>
<table>
    <tr><th style="width:34%">รายการ</th><th class="num">จำนวนเงิน (บาท)</th><th>หมายเหตุ</th></tr>
    <tr><td>งบที่ตั้งไว้ (แผน)</td><td class="num"><?= $money($report['planned_budget']) ?></td>
        <td class="muted"><?php foreach ($report['planned_by_item'] as $it): ?><?= Html::encode($it['code']) ?> <?= Html::encode($it['title']) ?> = <?= $money($it['amount']) ?>; <?php endforeach; ?></td></tr>
    <tr><td>ใช้จริง</td><td class="num"><?= $money($report['actual_spend']) ?></td>
        <td class="muted"><?php foreach ($report['actual_by_component'] as $c): ?><?= Html::encode($c['label']) ?> <?= $money($c['amount']) ?>; <?php endforeach; ?></td></tr>
    <tr><td>คงเหลือ / ใช้ไป</td>
        <td class="num <?= $report['budget_remaining'] < 0 ? 'warn' : 'ok' ?>"><?= $money($report['budget_remaining']) ?></td>
        <td>ใช้ไป <?= $report['budget_used_percent'] ?>% ของงบที่ตั้งไว้</td></tr>
</table>

<!-- 3. การกระจายตามประเภท -->
<h2>๓. การกระจายตามประเภทการพัฒนา</h2>
<table>
    <tr><th>ประเภทการพัฒนา</th><th class="num">จำนวน (ครั้ง)</th><th class="num">สัดส่วน</th></tr>
    <?php foreach ($activityType['labels'] as $i => $label): $cnt = (int) ($activityType['series'][$i] ?? 0); ?>
        <tr><td><?= Html::encode($label) ?></td><td class="num"><?= $nf($cnt) ?></td><td class="num"><?= $typeTotal > 0 ? round($cnt / $typeTotal * 100, 1) : 0 ?>%</td></tr>
    <?php endforeach; ?>
    <tr><th>รวม</th><th class="num"><?= $nf($typeTotal) ?></th><th class="num">100%</th></tr>
</table>

<!-- 4. การกระจายรายเดือน -->
<h2>๔. การกระจายกิจกรรมรายเดือน</h2>
<table>
    <tr><th>ประเภท</th><?php foreach ($fiscalMonths as $lbl): ?><th class="c"><?= $lbl ?></th><?php endforeach; ?><th class="num">รวม</th></tr>
    <?php foreach ($monthly as $row): $sum = 0; ?>
        <tr>
            <td><?= Html::encode($row['title']) ?></td>
            <?php foreach (array_keys($fiscalMonths) as $m): $v = (int) ($row['m' . $m] ?? 0); $sum += $v; ?>
                <td class="c"><?= $v ?: '' ?></td>
            <?php endforeach; ?>
            <td class="num"><?= $sum ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- 5. ความครอบคลุมรายหน่วยงาน -->
<h2>๕. ความครอบคลุมการพัฒนา รายหน่วยงาน</h2>
<?php if (!empty($zeroDepts)): ?>
    <div class="muted warn">หน่วยงานที่ยังไม่มีการพัฒนาเลย (<?= count($zeroDepts) ?> หน่วย): <?= Html::encode(implode(', ', array_map(static fn($r) => $r['name'], $zeroDepts))) ?></div>
<?php endif; ?>
<table>
    <tr><th>หน่วยงาน</th><th class="num">บุคลากร</th><th class="num">พัฒนาแล้ว</th><th class="num">ครอบคลุม</th><th class="num">คน-ครั้ง</th><th class="num">งบใช้จริง</th></tr>
    <?php foreach ($byDepartment as $r): ?>
        <tr>
            <td><?= Html::encode($r['name']) ?></td>
            <td class="num"><?= $nf($r['staff']) ?></td>
            <td class="num"><?= $nf($r['developed']) ?></td>
            <td class="num <?= $r['coverage_percent'] < 30 ? 'warn' : '' ?>"><?= $r['coverage_percent'] ?>%</td>
            <td class="num"><?= $nf($r['person_times']) ?></td>
            <td class="num"><?= $money($r['actual_spend']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- 6. เชิงคุณภาพ -->
<h2>๖. การติดตามผลและการนำไปใช้ประโยชน์ (เชิงคุณภาพ)</h2>
<div class="muted">
    ปิด loop สรุปผล <b><?= $followup['percent'] ?>%</b> — รับทราบแล้ว <?= $nf($followup['acknowledged']) ?> ·
    รอรับทราบ <?= $nf($followup['submitted']) ?> · ฉบับร่าง <?= $nf($followup['draft']) ?> ·
    <span class="warn">ยังไม่สรุปผล <?= $nf($followup['none']) ?></span> จากทั้งหมด <?= $nf($followup['total']) ?> รายการ
</div>
<div class="muted" style="margin-top:4px;">
    บุคลากรที่มีแผนพัฒนารายบุคคล (IDP): <?= $nf($idpCoverage['with_idp']) ?>/<?= $nf($idpCoverage['active_staff']) ?>
    (<?= $idpCoverage['percent'] ?>%) — การพัฒนาควรเชื่อมโยงช่องว่างสมรรถนะ (competency gap) ผ่าน IDP
</div>

<h3 style="font-size:14px;margin:12px 0 4px;">คลังการนำไปใช้ประโยชน์</h3>
<?php if (empty($benefitRegister)): ?>
    <div class="muted">— ยังไม่มีการรายงานผลการนำไปใช้ประโยชน์ในปีงบนี้ —</div>
<?php else: ?>
    <table>
        <tr><th style="width:30%">หัวข้อ / ผู้รายงาน</th><th>การนำไปใช้ประโยชน์</th><th>ข้อเสนอแนะ</th></tr>
        <?php foreach ($benefitRegister as $r): ?>
            <tr>
                <td><?= Html::encode($r['topic']) ?><br><span class="muted"><?= Html::encode($r['requester'] ?: '-') ?><?= $r['dept'] ? ' · ' . Html::encode($r['dept']) : '' ?></span></td>
                <td><?= $r['benefit'] ? nl2br(Html::encode($r['benefit'])) : '—' ?></td>
                <td><?= $r['suggestion'] ? nl2br(Html::encode($r['suggestion'])) : '—' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php if (!empty($suggestions)): ?>
    <h3 style="font-size:14px;margin:12px 0 4px;">ข้อเสนอแนะเพื่อการพัฒนา (สู่การพัฒนาคุณภาพต่อเนื่อง)</h3>
    <ul class="muted" style="margin:4px 0;">
        <?php foreach ($suggestions as $r): ?>
            <li><?= Html::encode($r['suggestion']) ?> <span style="color:#999">— <?= Html::encode($r['topic']) ?></span></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- 7. บทวิเคราะห์/ข้อเสนอ -->
<h2>๗. บทวิเคราะห์และข้อเสนอของผู้จัดทำ</h2>
<div class="note-box"></div>

<div class="sign">
    <div>
        ลงชื่อ..............................................<br>
        (..............................................)<br>
        ผู้จัดทำ / งานพัฒนาทรัพยากรบุคคล
    </div>
    <div>
        ลงชื่อ..............................................<br>
        (<?= Html::encode($directorName ?: '..............................................') ?>)<br>
        <?= Html::encode($info['director_position'] ?? 'ผู้อำนวยการ') ?>
    </div>
</div>

</body>
</html>
