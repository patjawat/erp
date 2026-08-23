<?php
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\models\ServiceProfileApproval;
use app\modules\serviceProfile\services\SectionDefinitionService;
use app\modules\jd\components\RichText;
use yii\helpers\Html;
$labels=ServiceProfile::statusLabels();
$stageLabels=[ServiceProfileApproval::STAGE_QUALITY=>'ผู้แทนคุณภาพเห็นชอบ',ServiceProfileApproval::STAGE_DIRECTOR=>'ผู้อำนวยการอนุมัติ',ServiceProfileApproval::STAGE_HEAD=>'หัวหน้าหน่วยงานรับทราบ'];
?>
<style>
body{font-family:thsarabunnew;font-size:14pt;line-height:1.3;color:#111}table{width:100%;border-collapse:collapse}.header{table-layout:fixed}.header td,.meta td,.data th,.data td,.sign td{border:1px solid #555;padding:1.4mm 2mm;vertical-align:top}.logo{text-align:center;vertical-align:middle!important;padding:2mm!important}.title{text-align:center;vertical-align:middle!important;font-size:19pt;font-weight:bold;line-height:1.15}.title .hospital{font-size:14pt;font-weight:normal}.control{font-size:11pt;line-height:1.45}.meta{margin-top:2.5mm}.meta .label{width:23mm;background:#eef1f4;font-weight:bold}.section{margin-top:2.5mm;page-break-inside:avoid}.section-title{border:1px solid #555;background:#e8edf3;padding:1.4mm 2mm;font-size:15pt;font-weight:bold}.content{border:1px solid #777;border-top:0;padding:2mm}.content p{margin:0 0 1mm}.content ul,.content ol,.data ul,.data ol{margin:0 0 1mm;padding-left:6mm}.content table{width:100%;border-collapse:collapse;margin:1.5mm 0}.content th,.content td{border:1px solid #555;padding:1.2mm 1.8mm;vertical-align:top}.content th{background:#f3f5f7;font-weight:bold}.data{table-layout:fixed;font-size:12pt}.data th{background:#f3f5f7;text-align:center}.sign{margin-top:4mm;table-layout:fixed}.sign td{text-align:center}.space{height:12mm}.small{font-size:11pt;color:#555}
.cover-hero{background:#075b5b;color:#fff;padding:14mm 12mm 12mm;min-height:92mm}.cover-kicker{font-size:13pt;font-weight:bold;letter-spacing:2px}.cover-code{text-align:right;color:#f3d47b;font-size:13pt;font-weight:bold}.cover-title{font-size:28pt;font-weight:bold;line-height:1.08;margin:8mm 0 2mm}.cover-subtitle{font-size:18pt;color:#d8eeee;margin-bottom:5mm}.cover-description{font-size:14pt;line-height:1.42;color:#eefafa}.cover-meta{font-size:13pt;font-weight:bold;margin-top:5mm}.cover-keywords{font-size:12pt;color:#d8eeee;margin-top:4mm}.cover-control-title{font-size:20pt;font-weight:bold;color:#075b5b;border-bottom:1mm solid #075b5b;padding-bottom:1.5mm;margin:8mm 0 3mm}.cover-control{table-layout:fixed;font-size:12.5pt}.cover-control th,.cover-control td{border:1px solid #666;padding:2.2mm 2.5mm;vertical-align:top}.cover-control th{width:42mm;background:#f2f6f6;text-align:left}.page-break{page-break-after:always}
</style>
<?php if ((int) $model->template_revision_snapshot >= 2): ?>
<div class="cover-hero">
    <table style="border:0"><tr><td class="cover-kicker" style="border:0;color:#fff">SERVICE PROFILE</td><td class="cover-code" style="border:0"><?= Html::encode($coverData['document_code']) ?></td></tr></table>
    <div class="cover-title"><?= Html::encode($coverData['owner_name']) ?></div>
    <div class="cover-subtitle"><?= Html::encode($coverData['organization_path']) ?></div>
    <div class="cover-description"><?= Html::encode($coverData['description']) ?></div>
    <div class="cover-meta">รหัสเอกสาร: <?= Html::encode($coverData['document_code']) ?> &nbsp;|&nbsp; ฉบับที่: <?= (int) $model->revision_no ?> &nbsp;|&nbsp; วันที่จัดทำ: <?= Html::encode($coverData['prepared_date']) ?></div>
    <div class="cover-keywords">Service Quality · Patient Safety · Risk Management · KPI · Continuous Improvement</div>
</div>
<div class="cover-control-title">ประวัติและการควบคุมเอกสาร</div>
<table class="cover-control">
    <tr><th>ชื่อเอกสาร</th><td><?= Html::encode($coverData['document_name']) ?></td></tr>
    <tr><th>รหัสเอกสาร</th><td><?= Html::encode($coverData['document_code']) ?></td></tr>
    <tr><th>หน่วยงาน/กลุ่มงาน</th><td><?= Html::encode($coverData['organization_path']) ?></td></tr>
    <tr><th>ผู้จัดทำ</th><td><?= Html::encode($coverData['prepared_by']) ?></td></tr>
    <tr><th>ผู้ทบทวน</th><td><?= Html::encode($coverData['reviewed_by']) ?></td></tr>
    <tr><th>ผู้อนุมัติ</th><td><?= Html::encode($coverData['approved_by']) ?></td></tr>
    <tr><th>ฉบับที่ / วันที่</th><td><?= (int) $model->revision_no ?> / <?= Html::encode($coverData['prepared_date']) ?></td></tr>
    <tr><th>มาตรฐานอ้างอิง</th><td><?= Html::encode($coverData['reference_standard']) ?></td></tr>
    <tr><th>รอบการทบทวน</th><td><?= Html::encode($coverData['review_cycle']) ?></td></tr>
    <tr><th>การเผยแพร่</th><td><?= Html::encode($coverData['distribution']) ?></td></tr>
</table>
<div class="page-break"></div>
<?php endif; ?>
<table class="header"><colgroup><col style="width:17mm"><col><col style="width:38mm"></colgroup><tr><td class="logo"><?= Html::img($logoPath,['alt'=>'ตรากระทรวงสาธารณสุข','width'=>'38','height'=>'38','style'=>'width:10mm;height:10mm']) ?></td><td class="title">Service Profile<br><span class="hospital"><?= Html::encode($siteInfo['company_name']??'โรงพยาบาล') ?></span></td><td class="control">ปีงบประมาณ <?= (int)$model->fiscal_year ?><br>Revision <?= (int)$model->revision_no ?><br><strong><?= Html::encode($labels[$model->status]??$model->status) ?></strong></td></tr></table>
<table class="meta"><tr><td class="label">หน่วยงาน</td><td><?= Html::encode($model->owner_name_snapshot) ?></td><td class="label">Template</td><td>Revision <?= (int)$model->template_revision_snapshot ?></td></tr></table>
<?php foreach($model->sections as $section): $columns=SectionDefinitionService::columns($section->block_type);$items=(array)($section->getData()['items']??[]); ?>
<div class="section"><div class="section-title"><?= Html::encode($section->title) ?></div><?php if($section->content): ?><div class="content"><?= RichText::render($section->content) ?></div><?php endif; ?><?php if($columns&&$items): ?><table class="data"><thead><tr><?php foreach($columns as $label): ?><th><?= Html::encode($label) ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach($items as $item): ?><tr><?php foreach($columns as $key=>$label): ?><td><?= RichText::render((string)($item[$key]??'')) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table><?php elseif(!$section->content): ?><div class="content small">- ไม่มีข้อมูล -</div><?php endif; ?></div>
<?php endforeach; ?>
<?php if($model->approvals): ?><table class="sign"><tr><?php foreach($model->approvals as $row): ?><td><strong><?= Html::encode($stageLabels[$row->stage]??$row->stage) ?></strong><div class="space"></div><div><?= Html::encode($row->employee_name_snapshot?:'-') ?></div><div class="small"><?= $row->acted_at?Yii::$app->formatter->asDate($row->acted_at,'php:d/m/Y'):'รอดำเนินการ' ?></div></td><?php endforeach; ?></tr></table><?php endif; ?>
