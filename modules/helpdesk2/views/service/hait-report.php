<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var string $title */
/** @var array $dashboardParams */

$p = $dashboardParams;
$kpi = $p['kpi'];
$range = $p['dateRange'];

$fmtDuration = static function (?int $s): string {
    if ($s === null) {
        return '—';
    }
    if ($s < 3600) {
        return round($s / 60) . ' นาที';
    }
    if ($s < 86400) {
        return round($s / 3600, 1) . ' ชม.';
    }
    return round($s / 86400, 1) . ' วัน';
};
$nf = static fn($n) => number_format((int) $n);
$today = ThaiDateHelper::formatThaiDate(date('Y-m-d'));
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>รายงาน HAIT — <?= Html::encode($title) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "TH Sarabun New", "Sarabun", "Tahoma", sans-serif; font-size: 15px; color: #111; margin: 24px; }
        h1 { font-size: 20px; margin: 0 0 2px; }
        h2 { font-size: 16px; margin: 22px 0 8px; padding-bottom: 4px; border-bottom: 2px solid #333; }
        .muted { color: #555; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #999; padding: 5px 7px; font-size: 13px; }
        th { background: #f0f0f0; text-align: left; }
        td.num, th.num { text-align: right; }
        .kpi-row { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; }
        .kpi { border: 1px solid #ccc; border-radius: 6px; padding: 8px 12px; min-width: 130px; }
        .kpi .l { font-size: 12px; color: #555; }
        .kpi .v { font-size: 18px; font-weight: 700; }
        .note-box { border: 1px dashed #999; border-radius: 6px; min-height: 60px; padding: 8px; margin-top: 6px; }
        .sign { margin-top: 40px; display: flex; justify-content: space-around; text-align: center; }
        .sign div { width: 40%; }
        .toolbar { text-align: right; margin-bottom: 12px; }
        .toolbar button { padding: 6px 16px; font-size: 14px; cursor: pointer; }
        @media print { .toolbar { display: none; } body { margin: 0; } h2 { page-break-after: avoid; } table { page-break-inside: auto; } }
    </style>
</head>
<body>
<div class="toolbar">
    <button onclick="window.print()">🖨️ พิมพ์ / บันทึก PDF</button>
</div>

<h1>รายงานคุณภาพระบบเทคโนโลยีสารสนเทศ (HAIT หมวด 4) — <?= Html::encode($title) ?></h1>
<div class="muted">
    ช่วงข้อมูล: <?= Html::encode($range['start']) ?> ถึง <?= Html::encode($range['end']) ?>
    &nbsp;|&nbsp; พิมพ์เมื่อ: <?= Html::encode($today) ?>
</div>

<div class="kpi-row">
    <div class="kpi"><div class="l">อุบัติการณ์ทั้งหมด</div><div class="v"><?= $nf($kpi['total']) ?></div></div>
    <div class="kpi"><div class="l">เปิดค้าง</div><div class="v"><?= $nf($kpi['open']) ?></div></div>
    <div class="kpi"><div class="l">% ทำได้ตาม SLA</div><div class="v"><?= $kpi['sla_pct'] === null ? '—' : $kpi['sla_pct'] . '%' ?></div></div>
    <div class="kpi"><div class="l">MTTA (มัธยฐาน)</div><div class="v"><?= $fmtDuration($kpi['mtta_median_seconds']) ?></div></div>
    <div class="kpi"><div class="l">MTTR (มัธยฐาน)</div><div class="v"><?= $fmtDuration($kpi['mttr_median_seconds']) ?></div></div>
    <div class="kpi"><div class="l">ความพึงพอใจ</div><div class="v"><?= $kpi['rating_avg'] === null ? '—' : $kpi['rating_avg'] . '/5' ?></div></div>
</div>

<!-- ฉบับที่ 1 -->
<h2>รายงานฉบับที่ 1: ผลการดำเนินการตามข้อตกลงระดับบริการ (SLA)</h2>
<table>
    <thead>
        <tr>
            <th>รายการบริการ</th>
            <th class="num">จำนวน</th>
            <th class="num">ทำได้ตามเวลา</th>
            <th class="num">% สำเร็จ</th>
            <th class="num">เร็วสุด</th>
            <th class="num">ช้าสุด</th>
            <th class="num">เฉลี่ย</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($p['slaByService'] as $s): ?>
            <tr>
                <td><?= Html::encode($s['title']) ?></td>
                <td class="num"><?= $nf($s['count']) ?></td>
                <td class="num"><?= $nf($s['met']) ?></td>
                <td class="num"><?= $s['pct'] === null ? '—' : $s['pct'] . '%' ?></td>
                <td class="num"><?= $fmtDuration($s['min_secs']) ?></td>
                <td class="num"><?= $fmtDuration($s['max_secs']) ?></td>
                <td class="num"><?= $fmtDuration($s['avg_secs']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($p['slaByService'])): ?>
            <tr><td colspan="7" style="text-align:center;color:#777;">ไม่มีข้อมูล</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<p class="muted" style="margin-top:6px;">สรุปผล / ข้อเสนอแนะ:</p>
<div class="note-box"></div>

<!-- ฉบับที่ 2 -->
<h2>รายงานฉบับที่ 2: อุบัติการณ์ที่เกิดขึ้น (Incident Report)</h2>
<div style="display:flex; gap:16px; flex-wrap:wrap;">
    <div style="flex:1; min-width:280px;">
        <strong style="font-size:13px;">ตามประเภทอุปกรณ์</strong>
        <table>
            <thead><tr><th>ประเภท</th><th class="num">จำนวน</th></tr></thead>
            <tbody>
                <?php foreach (array_slice($p['paretoDevice'], 0, 10) as $d): ?>
                    <tr><td><?= Html::encode($d['title']) ?></td><td class="num"><?= $nf($d['cnt']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div style="flex:1; min-width:280px;">
        <strong style="font-size:13px;">ตามหน่วยงาน/สถานที่เกิดเหตุ</strong>
        <table>
            <thead><tr><th>หน่วยงาน</th><th class="num">จำนวน</th></tr></thead>
            <tbody>
                <?php foreach (array_slice($p['paretoDepartment'], 0, 10) as $d): ?>
                    <tr><td><?= Html::encode($d['title']) ?></td><td class="num"><?= $nf($d['cnt']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if (!empty($p['rootCauses'])): ?>
    <strong style="font-size:13px;">สาเหตุรากเหง้ายอดนิยม (Problem Management)</strong>
    <table>
        <thead><tr><th>สาเหตุ</th><th class="num">จำนวน</th></tr></thead>
        <tbody>
            <?php foreach ($p['rootCauses'] as $rc): ?>
                <tr><td><?= Html::encode($rc['cause']) ?></td><td class="num"><?= $nf($rc['cnt']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<p class="muted" style="margin-top:6px;">สรุปผล / ข้อเสนอแนะ:</p>
<div class="note-box"></div>

<!-- ฉบับที่ 3 -->
<h2>รายงานฉบับที่ 3: กิจกรรมการทำงานของฝ่ายเทคโนโลยีสารสนเทศ</h2>
<table>
    <thead>
        <tr><th>เจ้าหน้าที่</th><th class="num">รวมงาน</th><th class="num">เปิดอยู่</th><th class="num">กำลังทำ</th><th class="num">ปิดแล้ว</th></tr>
    </thead>
    <tbody>
        <?php foreach ($p['staffWorkload'] as $st): ?>
            <tr>
                <td><?= Html::encode($st['fullname']) ?></td>
                <td class="num"><?= $nf($st['total']) ?></td>
                <td class="num"><?= $nf($st['open_total']) ?></td>
                <td class="num"><?= $nf($st['in_progress_total']) ?></td>
                <td class="num"><?= $nf($st['success_total']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($p['staffWorkload'])): ?>
            <tr><td colspan="5" style="text-align:center;color:#777;">ไม่มีข้อมูล</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<p class="muted" style="margin-top:6px;">ความพึงพอใจของผู้ใช้บริการ: เฉลี่ย <?= $kpi['rating_avg'] === null ? '—' : $kpi['rating_avg'] . '/5' ?> (ประเมิน <?= $nf($kpi['rating_count']) ?> รายการ)</p>

<div class="sign">
    <div>
        ลงชื่อ ........................................<br>
        (........................................)<br>
        ผู้จัดทำรายงาน
    </div>
    <div>
        ลงชื่อ ........................................<br>
        (........................................)<br>
        หัวหน้ากลุ่มงาน/ผู้รับผิดชอบ
    </div>
</div>

</body>
</html>
