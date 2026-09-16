<?php

use app\components\AppHelper;
use app\modules\flowchart\components\FlowchartDocRenderer;
use app\modules\flowchart\components\MermaidBuilder;
use app\modules\flowchart\models\Flowchart;
use yii\helpers\Html;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var Flowchart $model */

$mermaid = MermaidBuilder::build($model);
$colorMap = MermaidBuilder::actorColorMap($model->steps);
$mermaidUrl = Yii::getAlias('@web') . '/vendor/mermaid/mermaid.min.js';
$printedAt = AppHelper::convertToThai(date('Y-m-d'));
$mermaidJson = Json::encode($mermaid);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= Html::encode(($model->code ? $model->code . ' ' : '') . $model->title) ?></title>
<style>
    * { box-sizing: border-box; }
    body { font-family: "TH Sarabun New", "Sarabun", system-ui, -apple-system, "Segoe UI", sans-serif; color:#111; margin:24px; font-size:15px; }
    h1 { font-size:20px; margin:0 0 2px; }
    .muted { color:#555; }
    .doc-head { border-bottom:2px solid #333; padding-bottom:8px; margin-bottom:14px; }
    .doc-meta { font-size:13px; color:#444; margin-top:4px; }
    .doc-meta span { margin-right:16px; }
    .diagram { text-align:center; margin:6px 0 12px; }
    .diagram svg { max-width:100%; height:auto; }
    .fc-cols { display:flex; gap:16px; align-items:flex-start; }
    .fc-col-diagram { flex:0 0 38%; max-width:38%; }
    .fc-col-table { flex:1 1 auto; min-width:0; }
    .fc-col-table table.fc-proc-table { font-size:12px; }
    .fc-col-table table.fc-proc-table th, .fc-col-table table.fc-proc-table td { padding:4px 5px; }
    /* จำกัดความสูงผัง (TD สูง) ให้พอดีหน้ากระดาษ ไม่งั้นดันตารางไปคนละหน้า
       ใช้ px + !important เพื่อชนะ inline style ของ mermaid; แนวนอนพื้นที่สูงน้อยกว่า */
    .fc-col-diagram svg { max-width:100% !important; height:auto !important; max-height:880px !important; }
    body.o-landscape .fc-col-diagram svg { max-height:470px !important; }
    /* ยุบเป็นบน-ล่างเฉพาะจอเล็กจริง ๆ — ห้ามใช้ในตอนพิมพ์ (กระดาษ A4 แนวตั้งกว้าง ~794px) */
    @media screen and (max-width:640px){ .fc-cols{ display:block; } .fc-col-diagram,.fc-col-table{ max-width:100%; } }
    .legend { font-size:12px; margin-top:6px; }
    .legend .item { display:inline-flex; align-items:center; margin-right:12px; }
    .legend .sw { width:11px; height:11px; border-radius:2px; border:1px solid; display:inline-block; margin-right:4px; }
    table.fc-proc-table { width:100%; border-collapse:collapse; font-size:13.5px; }
    table.fc-proc-table th, table.fc-proc-table td { border:1px solid #999; padding:5px 7px; vertical-align:top; }
    table.fc-proc-table th { background:#f0f0f0; text-align:left; }
    .text-center { text-align:center; }
    .text-muted { color:#888; }
    .fw-semibold { font-weight:600; }
    .fc-sym svg { color:#333; }
    .small { font-size:12px; }
    .sign-row { display:flex; justify-content:space-between; margin-top:22px; gap:24px; page-break-inside:avoid; break-inside:avoid; }
    .sign-box { flex:1; text-align:center; font-size:13px; }
    .sign-line { border-top:1px dotted #333; margin:36px 12px 4px; }
    .section-title { font-weight:600; margin:16px 0 6px; font-size:15px; }
    @media print { body { margin:12mm; } .no-print { display:none; } }
    .toolbar { text-align:right; margin-bottom:10px; }
    .btn { display:inline-block; padding:6px 14px; border:1px solid #0d6efd; background:#0d6efd; color:#fff; border-radius:6px; cursor:pointer; font-size:13px; text-decoration:none; }
    .btn-ghost { background:#fff; color:#0d6efd; margin-right:6px; }
</style>
</head>
<body>

<style id="orientStyle">@page{size:A4 portrait;margin:12mm;}</style>
<div class="toolbar no-print">
    <button class="btn btn-ghost" onclick="printAs('portrait')">&#128424; พิมพ์แนวตั้ง</button>
    <button class="btn" onclick="printAs('landscape')">&#128424; พิมพ์แนวนอน</button>
</div>
<script>
function printAs(o){
  var ls = (o === 'landscape');
  document.getElementById('orientStyle').textContent = ls
    ? '@page{size:A4 landscape;margin:10mm;}'
    : '@page{size:A4 portrait;margin:12mm;}';
  document.body.classList.toggle('o-landscape', ls); // จำกัดความสูงผังให้พอดีหน้าตามแนวกระดาษ
  setTimeout(function(){ window.print(); }, 80);
}
</script>

<div class="doc-head">
    <h1><?= Html::encode($model->title) ?></h1>
    <div class="doc-meta">
        <span><strong>รหัส:</strong> <?= Html::encode($model->code ?: '—') ?></span>
        <?php if ($model->unitName() !== ''): ?><span><strong>หน่วยงาน:</strong> <?= Html::encode($model->unitName()) ?></span><?php endif; ?>
        <?php if ($model->categoryLabel()): ?><span><strong>ประเภท:</strong> <?= Html::encode($model->categoryLabel()) ?></span><?php endif; ?>
        <span><strong>สถานะ:</strong> <?= Html::encode($model->statusLabel()) ?></span>
        <span><strong>ปรับปรุงครั้งที่:</strong> <?= (int) $model->revision_no ?></span>
        <span><strong>ปีงบ:</strong> <?= $model->budget_year ?: '—' ?></span>
        <span><strong>พิมพ์เมื่อ:</strong> <?= Html::encode($printedAt) ?></span>
    </div>
    <?php if (trim((string) $model->description) !== ''): ?>
        <div class="doc-meta" style="margin-top:6px;"><strong>วัตถุประสงค์:</strong> <?= nl2br(Html::encode($model->description)) ?></div>
    <?php endif; ?>
</div>

<div class="fc-cols">
    <div class="fc-col-diagram">
        <div class="section-title">ผังกระบวนการ</div>
        <div class="diagram">
            <div id="diag"></div>
            <?php if ($colorMap): ?>
                <div class="legend">
                    <span class="text-muted">ผู้รับผิดชอบ: </span>
                    <?php foreach ($colorMap as $actor => $c): ?>
                        <span class="item"><span class="sw" style="background:<?= $c['fill'] ?>;border-color:<?= $c['stroke'] ?>"></span><?= Html::encode($actor) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="fc-col-table">
        <div class="section-title">รายละเอียดขั้นตอน</div>
        <?= FlowchartDocRenderer::procedureTableHtml($model) ?>
    </div>
</div>

<div class="sign-row">
    <div class="sign-box"><div class="sign-line"></div>ผู้จัดทำ<br>(<?= Html::encode($model->ownerName) ?>)</div>
    <div class="sign-box"><div class="sign-line"></div>ผู้ทบทวน<br>(...........................)</div>
    <div class="sign-box"><div class="sign-line"></div>ผู้อนุมัติ<br>(...........................)</div>
</div>

<script src="<?= Html::encode($mermaidUrl) ?>"></script>
<script>
(function(){
  var code = <?= $mermaidJson ?>;
  function go(){
    if (!window.mermaid){ document.getElementById('diag').innerHTML='(โหลดผังไม่ได้)'; return; }
    mermaid.initialize({ startOnLoad:false, securityLevel:'loose', flowchart:{ htmlLabels:true, useMaxWidth:true }, theme:'default' });
    mermaid.render('m0', code).then(function(out){
      document.getElementById('diag').innerHTML = out.svg;
    }).catch(function(){ document.getElementById('diag').innerHTML='(วาดผังไม่สำเร็จ)'; });
  }
  if (document.readyState !== 'loading') go(); else window.addEventListener('load', go);
})();
</script>
</body>
</html>
