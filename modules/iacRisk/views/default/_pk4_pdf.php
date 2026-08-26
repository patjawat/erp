<?php use yii\helpers\Html; ?>
<style>
body{font-family:thsarabunnew;font-size:16pt;line-height:1.15;color:#000}.title{text-align:center;font-weight:bold;font-size:18pt;margin:0}.subtitle{text-align:center;margin:0 0 8mm}.report{width:100%;border-collapse:collapse;table-layout:fixed}.report th,.report td{border:.3mm solid #000;padding:2.2mm;vertical-align:top}.report th{text-align:center;font-weight:bold}.component{width:32%;font-weight:bold}.summary-title{font-weight:bold;margin-top:7mm}.signature{text-align:right;margin-top:12mm;padding-right:12mm}.signature div{margin-top:2mm}
</style>
<div class="title"><?= Html::encode($model->orgUnit?->name?:'หน่วยงาน') ?></div>
<div class="title">รายงานผลการประเมินองค์ประกอบของการควบคุมภายใน (แบบ ปค.4)</div>
<div class="subtitle">สำหรับปีสิ้นสุดวันที่ 30 กันยายน <?= (int)$model->fiscal_year ?></div>
<table class="report"><thead><tr><th class="component">องค์ประกอบการควบคุมภายใน<br>(1)</th><th>ผลการประเมิน / ข้อสรุป<br>(2)</th></tr></thead><tbody>
<?php foreach($model->items as $item): ?><tr><td class="component"><?= nl2br(Html::encode($item->component_name)) ?></td><td><?= nl2br(Html::encode($item->evaluation_summary?:'')) ?></td></tr><?php endforeach; ?>
</tbody></table>
<div class="summary-title">สรุปผลการประเมิน</div><div><?= nl2br(Html::encode($model->summary?:'')) ?></div>
<div class="signature">
<?php if(!empty($signatureDataUri)): ?><div><img src="<?= Html::encode($signatureDataUri) ?>" style="max-width:42mm;max-height:18mm" alt="ลายเซ็น"></div><?php else: ?><div>ลงชื่อ ..............................................................</div><?php endif; ?>
<div>(<?= Html::encode($model->signer_name?:'หัวหน้าหน่วยงาน') ?>)</div><div><?= Html::encode($model->signer_position?:'หัวหน้าหน่วยงาน') ?></div><div>วันที่ ..............................................................</div>
</div>
