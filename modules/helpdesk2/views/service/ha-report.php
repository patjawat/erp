<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var string $title */
/** @var array $dashboardParams */
/** @var string $haContext 'medical' | 'utility' */

$haContext = $haContext ?? 'medical';
$isMedical = $haContext === 'medical';

$p = $dashboardParams;
$kpi = $p['kpi'];
$range = $p['dateRange'];
$ha = $p['haMetrics'] ?? [];
$rd = $ha['readiness'] ?? null;
$cal = $ha['calibration'] ?? null;
$pm = $ha['pm'] ?? null;
$targets = $ha['targets'] ?? ['ready' => 95, 'calibration' => 100, 'pm' => 90, 'sla' => 90];

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
$pctText = static fn($v) => $v === null ? '—' : $v . '%';
$today = ThaiDateHelper::formatThaiDate(date('Y-m-d'));
$stdTitle = $isMedical
    ? 'รายงานคุณภาพเครื่องมือและอุปกรณ์การแพทย์ (HA ฉบับ 6 หมวด II-3)'
    : 'รายงานคุณภาพระบบสาธารณูปโภคและงานซ่อมบำรุง (HA ฉบับ 6 หมวด II-3)';
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>รายงาน HA — <?= Html::encode($title) ?></title>
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
        .kpi .t { font-size: 11px; color: #888; }
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

<h1><?= Html::encode($stdTitle) ?> — <?= Html::encode($title) ?></h1>
<div class="muted">
    ช่วงข้อมูล: <?= Html::encode($range['start']) ?> ถึง <?= Html::encode($range['end']) ?>
    &nbsp;|&nbsp; พิมพ์เมื่อ: <?= Html::encode($today) ?>
</div>

<!-- ฉบับที่ 1: ตัวชี้วัดคุณภาพ HA II-3 -->
<h2>รายงานฉบับที่ 1: ตัวชี้วัดคุณภาพตามมาตรฐาน HA II-3</h2>
<div class="kpi-row">
    <?php if ($rd !== null): ?>
        <div class="kpi"><div class="l">เครื่องมือพร้อมใช้</div><div class="v"><?= $pctText($rd['ready_pct']) ?></div><div class="t">เป้า ≥ <?= (int) $targets['ready'] ?>% · <?= $nf($rd['ready']) ?>/<?= $nf($rd['total']) ?></div></div>
    <?php endif; ?>
    <?php if ($cal !== null): ?>
        <div class="kpi"><div class="l">สอบเทียบตามแผน</div><div class="v"><?= $pctText($cal['compliance_pct']) ?></div><div class="t">ทำจริง <?= $nf($cal['performed'] ?? 0) ?> · เกินกำหนด <?= $nf($cal['overdue']) ?></div></div>
    <?php endif; ?>
    <?php if ($pm !== null): ?>
        <div class="kpi"><div class="l">บำรุงรักษาเชิงป้องกัน</div><div class="v"><?= $pctText($pm['compliance_pct']) ?></div><div class="t">ทำจริง <?= $nf($pm['performed'] ?? 0) ?> · เกินกำหนด <?= $nf($pm['overdue']) ?></div></div>
    <?php endif; ?>
    <div class="kpi"><div class="l">% ทำได้ตาม SLA</div><div class="v"><?= $pctText($kpi['sla_pct']) ?></div><div class="t">เป้า ≥ <?= (int) $targets['sla'] ?>%</div></div>
    <div class="kpi"><div class="l">MTTR (มัธยฐาน)</div><div class="v"><?= $fmtDuration($kpi['mttr_median_seconds']) ?></div><div class="t">เวลาซ่อมเสร็จ</div></div>
    <div class="kpi"><div class="l">ความพึงพอใจ</div><div class="v"><?= $kpi['rating_avg'] === null ? '—' : $kpi['rating_avg'] . '/5' ?></div><div class="t">ประเมิน <?= $nf($kpi['rating_count']) ?> ราย</div></div>
</div>
<p class="muted" style="margin-top:6px;">สรุปผล / ข้อเสนอแนะเชิงคุณภาพ:</p>
<div class="note-box"></div>

<!-- ฉบับที่ 2: SLA ตามระบบงาน -->
<?php $slaRows = $p['slaBySystem'] ?? []; ?>
<h2>รายงานฉบับที่ 2: ผลการดำเนินการตาม SLA (ตามระบบงาน)</h2>
<table>
    <thead>
        <tr>
            <th>ระบบงาน</th>
            <th class="num">จำนวน</th>
            <th class="num">ทำได้ตามเวลา</th>
            <th class="num">% สำเร็จ</th>
            <th class="num">เร็วสุด</th>
            <th class="num">ช้าสุด</th>
            <th class="num">เฉลี่ย</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($slaRows as $s): ?>
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
        <?php if (empty($slaRows)): ?>
            <tr><td colspan="7" style="text-align:center;color:#777;">ไม่มีข้อมูล</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- ฉบับที่ 3: งานซ่อม/อุบัติการณ์ -->
<h2>รายงานฉบับที่ 3: งานซ่อมและอุบัติการณ์</h2>
<div style="display:flex; gap:16px; flex-wrap:wrap;">
    <?php
    $atp = $ha['assetTypePareto'] ?? null;
    $useGsn = $isMedical && $atp && !empty($atp['rows']);
    $devHead = $useGsn ? 'ตามชนิดเครื่องมือ (จากทะเบียนครุภัณฑ์)' : ($isMedical ? 'ตามระบบงาน' : 'ตามประเภทงาน/ระบบ');
    $devRows = $useGsn ? array_slice($atp['rows'], 0, 10) : array_slice($p['paretoDevice'], 0, 10);
    ?>
    <div style="flex:1; min-width:280px;">
        <strong style="font-size:13px;"><?= $devHead ?></strong>
        <?php if ($useGsn): ?>
            <div class="muted" style="font-size:11px;">ผูกครุภัณฑ์ <?= $nf($atp['linked']) ?>/<?= $nf($atp['total']) ?> ใบ<?= $atp['total'] > 0 ? ' (' . round($atp['linked'] / $atp['total'] * 100) . '%)' : '' ?> · นับเฉพาะใบที่ผูกครุภัณฑ์</div>
        <?php endif; ?>
        <table>
            <thead><tr><th>ประเภท</th><th class="num">จำนวน</th></tr></thead>
            <tbody>
                <?php foreach ($devRows as $d): ?>
                    <tr><td><?= Html::encode($d['title']) ?></td><td class="num"><?= $nf($d['cnt']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div style="flex:1; min-width:280px;">
        <strong style="font-size:13px;">ตามหน่วยงาน/สถานที่</strong>
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
