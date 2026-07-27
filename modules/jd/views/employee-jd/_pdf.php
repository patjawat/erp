<?php

use app\modules\jd\components\RichText;
use app\modules\jd\models\JdEmployee;
use app\modules\jd\models\JdTemplateBlock;
use yii\helpers\Html;

/** @var JdEmployee $jd */
/** @var app\modules\hr\models\Employees $employee */
/** @var array $siteInfo */
/** @var string $logoPath */

$statusLabels = JdEmployee::statusLabels();
$approvalRows = [];
foreach ($jd->approvalRows as $row) {
    $rowData = (array) $row->data_json;
    $approvalRows[(int) $row->emp_id . '|' . ($rowData['role'] ?? '')] = $row;
}
$dash = '<span class="empty-value">-</span>';
?>
<style>
body{font-family:thsarabunnew;font-size:14pt;line-height:1.35;color:#111}
table{width:100%;border-collapse:collapse}
.doc-header td{border:1px solid #111;padding:2.5mm;vertical-align:middle}
.doc-logo{width:19mm;text-align:center}.doc-logo img{width:16mm;height:16mm}
.doc-title{text-align:center}.doc-title strong{display:block;font-size:19pt;line-height:1.15}.doc-title span{font-size:13pt}
.doc-control{width:45mm;font-size:11pt}.doc-control div{margin-bottom:1mm}.doc-control b{display:inline-block;min-width:17mm}
.identity{margin-top:3mm}.identity td{border:1px solid #333;padding:1.8mm 2.5mm;vertical-align:top}.identity .label{width:26mm;background:#f0f2f5;font-weight:bold}.identity .value{width:auto}
.section{margin-top:3mm;page-break-inside:avoid}.section-title{padding:1.5mm 2.5mm;background:#e8edf3;border:1px solid #333;font-weight:bold;font-size:15pt}.section-intro{padding:2mm 2.5mm;border:1px solid #777;border-top:0}.section-intro p{margin:0 0 1.2mm}.section-intro ul,.section-intro ol{margin:0 0 1mm 5mm;padding-left:3mm}
.data-table{table-layout:fixed}.data-table th,.data-table td{border:1px solid #555;padding:1.5mm 2mm;vertical-align:top}.data-table th{background:#f5f6f8;text-align:center;font-weight:bold}.data-table .num{width:9mm;text-align:center}.data-table p{margin:0}.data-table ul,.data-table ol{margin:0 0 0 4mm;padding-left:3mm}
.signature-table{table-layout:fixed}.signature-table td{border:1px solid #555;padding:3mm 2mm;text-align:center;vertical-align:top}.signature-image{display:block;margin:0 auto 1mm;max-width:36mm;max-height:15mm}.signature-space{height:14mm}.signature-name{font-weight:bold}.signature-role{margin-top:1mm}.signature-date{font-size:11pt;color:#444;margin-top:1mm}.empty-value{color:#777}
.print-note{margin-top:3mm;font-size:10pt;color:#555;text-align:right}
</style>

<table class="doc-header">
    <tr>
        <td class="doc-logo"><?= Html::img($logoPath, ['alt' => 'ตรากระทรวงสาธารณสุข', 'width' => 60, 'height' => 60]) ?></td>
        <td class="doc-title">
            <strong>คำบรรยายลักษณะงาน (Job Description)</strong>
            <span><?= Html::encode($siteInfo['company_name'] ?: 'โรงพยาบาล') ?></span>
        </td>
        <td class="doc-control">
            <div><b>รหัสเอกสาร</b> JD-<?= (int) $jd->emp_id ?></div>
            <div><b>Revision</b> <?= (int) $jd->revision_no ?></div>
            <div><b>สถานะ</b> <?= Html::encode($statusLabels[$jd->status] ?? $jd->status) ?></div>
        </td>
    </tr>
</table>

<table class="identity">
    <tr>
        <td class="label">ชื่อผู้ดำรงตำแหน่ง</td>
        <td class="value"><?= Html::encode($employee->fullname) ?></td>
        <td class="label">วันที่มีผล</td>
        <td class="value"><?= $jd->effective_from ? Yii::$app->formatter->asDate($jd->effective_from, 'php:d/m/Y') : $dash ?></td>
    </tr>
    <tr>
        <td class="label">ตำแหน่ง</td>
        <td class="value"><?= Html::encode($jd->position_title ?: strip_tags((string) $employee->positionName())) ?></td>
        <td class="label">หน่วยงาน</td>
        <td class="value"><?= Html::encode($jd->department_title ?: $employee->departmentName()) ?></td>
    </tr>
</table>

<?php foreach ($jd->sections as $section): ?>
    <?php
    $data = $section->getData() + ['intro' => '', 'items' => []];
    $items = is_array($data['items'] ?? null) ? $data['items'] : [];
    ?>
    <div class="section">
        <div class="section-title"><?= Html::encode($section->title) ?></div>
        <?php if (trim((string) ($data['intro'] ?? '')) !== ''): ?>
            <div class="section-intro"><?= RichText::render((string) $data['intro']) ?></div>
        <?php endif; ?>

        <?php if ($section->block_type === 'approval' && $items !== []): ?>
            <table class="signature-table">
                <tr>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $key = (int) ($item['employee_id'] ?? 0) . '|' . ($item['role'] ?? '');
                        $approval = $approvalRows[$key] ?? null;
                        $approvalData = $approval ? (array) $approval->data_json : [];
                        $signedAt = $approvalData['approve_date'] ?? null;
                        $signaturePath = $approval && $approval->status === 'Pass' && $approval->employee
                            ? $approval->employee->SignatureFilePath()
                            : null;
                        ?>
                        <td>
                            <?php if ($signaturePath && is_file($signaturePath)): ?>
                                <?= Html::img($signaturePath, ['class' => 'signature-image', 'alt' => 'ลายมือชื่อ']) ?>
                            <?php else: ?>
                                <div class="signature-space"></div>
                            <?php endif; ?>
                            <div class="signature-name">(<?= Html::encode($item['employee_name'] ?? '................................................') ?>)</div>
                            <div class="signature-role"><?= Html::encode($item['role'] ?? 'ผู้ลงนาม') ?></div>
                            <div class="signature-date"><?= $signedAt ? 'ลงนามเมื่อ ' . Yii::$app->formatter->asDatetime($signedAt, 'php:d/m/Y H:i') : 'วันที่ ........../........../..........' ?></div>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </table>
        <?php elseif ($items !== []): ?>
            <?php $columns = JdTemplateBlock::editorColumns(JdTemplateBlock::editorType($section->block_type ?: 'named_items', $section->section_code)); ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th class="num">ลำดับ</th>
                    <?php foreach ($columns as $label): ?><th><?= Html::encode($label) ?></th><?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td class="num"><?= $index + 1 ?></td>
                        <?php foreach ($columns as $key => $label): ?>
                            <td><?= RichText::render((string) ($item[$key] ?? ($key === 'expectation' ? ($item['measurement'] ?? '') : ''))) ?: $dash ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (trim((string) $section->content) !== ''): ?>
            <div class="section-intro"><?= nl2br(Html::encode($section->content)) ?></div>
        <?php elseif (trim((string) ($data['intro'] ?? '')) === ''): ?>
            <div class="section-intro"><?= $dash ?></div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<div class="print-note">เอกสารพิมพ์จากระบบ ERP เมื่อ <?= Yii::$app->formatter->asDatetime(time(), 'php:d/m/Y H:i') ?></div>
