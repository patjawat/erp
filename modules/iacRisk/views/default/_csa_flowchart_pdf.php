<?php
use yii\helpers\Html;
$hospital=trim((string)($siteInfo['company_name']??'โรงพยาบาล'))?:'โรงพยาบาล';
?>
<style>
body{font-family:thsarabunnew;font-size:16pt;color:#172033;line-height:1.25}h1{font-size:24pt;text-align:center;margin:0 0 2mm}h2{font-size:19pt;margin:0}.meta{text-align:center;color:#526071;margin-bottom:6mm}.objective{border:0.4mm solid #7b8ba5;background:#f3f6fa;padding:3mm 4mm;margin-bottom:6mm}.step{page-break-inside:avoid;border:0.5mm solid #3569a8;border-radius:2mm;padding:3mm 4mm;background:#fff}.step-no{display:inline-block;background:#3569a8;color:#fff;padding:0.5mm 2.5mm;border-radius:3mm;font-weight:bold}.detail{margin-top:2mm}.facts{margin-top:2mm;color:#526071;font-size:14pt}.control{margin-top:2mm;padding:2mm 3mm;background:#eef5ff;border-left:1.2mm solid #3569a8}.risk{margin-top:2mm;padding:2mm 3mm;background:#fff3f3;border-left:1.2mm solid #b64343}.arrow{text-align:center;font-size:14pt;font-weight:bold;line-height:0.8;color:#3569a8;height:8mm}.legend{margin-top:6mm;border-top:0.3mm solid #aab4c2;padding-top:3mm;font-size:13pt;color:#526071}.page-title{font-size:13pt;text-align:right;color:#526071}
</style>
<div class="page-title">ปีงบประมาณ <?= (int)$model->fiscal_year ?> · CSA Revision <?= (int)$model->revision_no ?></div>
<h1>แผนผังกระบวนงาน (Flow chart)</h1><div class="meta"><?= Html::encode($hospital) ?><br><?= Html::encode($model->process_name_snapshot) ?></div>
<div class="objective"><strong>วัตถุประสงค์:</strong> <?= nl2br(Html::encode($model->objective_snapshot?:'—')) ?></div>
<?php foreach($model->steps as $index=>$step): ?>
<?php if($index>0): ?><div class="arrow">|<br>v</div><?php endif; ?>
<div class="step"><span class="step-no">ขั้นตอนที่ <?= $index+1 ?></span>&nbsp; <strong><?= Html::encode($step->name) ?></strong><?php if($step->detail): ?><div class="detail"><?= nl2br(Html::encode($step->detail)) ?></div><?php endif; ?><div class="facts">ผู้รับผิดชอบ: <?= Html::encode($step->responsible?:'—') ?><?php if($step->duration): ?> | ระยะเวลา: <?= Html::encode($step->duration) ?><?php endif; ?></div><?php if($step->control_point): ?><div class="control"><strong>จุดควบคุม:</strong> <?= nl2br(Html::encode($step->control_point)) ?></div><?php endif; ?><?php foreach($step->risks as $risk): ?><div class="risk"><strong>ความเสี่ยง:</strong> <?= Html::encode($risk->name) ?><?php if($risk->controls): ?><br><strong>การควบคุม:</strong> <?= Html::encode(implode('; ',array_map(static fn($control)=>(string)$control->description,$risk->controls))) ?><?php endif; ?></div><?php endforeach; ?><?php if(!$step->risks): ?><div class="facts">ไม่พบความเสี่ยงจากการวิเคราะห์ขั้นตอนนี้</div><?php endif; ?></div>
<?php endforeach; ?>
<?php if(!$model->steps): ?><div class="step" style="text-align:center;color:#526071">ยังไม่มีขั้นตอนการปฏิบัติงาน</div><?php endif; ?>
<div class="legend">เอกสารสร้างจากข้อมูล CSA วันที่ <?= date('d/m/Y H:i') ?> | แสดงทุกขั้นตอน รวมขั้นตอนที่ไม่มีความเสี่ยง</div>
