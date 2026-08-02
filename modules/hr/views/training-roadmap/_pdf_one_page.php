<?php

use app\modules\hr\models\EmployeeTrainingPlan;
use yii\helpers\Html;

$results = [];
foreach ($model->results as $result) {
    $results[(int) $result->activity_id] = $result;
}
$statusLabels = EmployeeTrainingPlan::statusOptions();
$resultLabels = [
    'pending' => 'ยังไม่เริ่ม',
    'in_progress' => 'กำลังดำเนินการ',
    'passed' => 'ผ่าน',
    'completed' => 'ครบถ้วน',
    'failed' => 'ไม่ผ่าน',
];
$activityNo = 0;
?>
<style>
body{font-family:thsarabunnew;font-size:9pt;line-height:1.02;color:#172033}
table{width:100%;border-collapse:collapse}
.header td{border:1px solid #334155;padding:1mm 1.8mm;vertical-align:middle}.title{text-align:center;font-size:16pt;font-weight:bold}.subtitle{text-align:center;font-size:9pt;color:#475569}.code{width:45mm;font-size:8.5pt}
.info{margin-top:1.2mm}.info td{border:1px solid #64748b;padding:.7mm 1.5mm}.label{width:21mm;background:#eef2f7;font-weight:bold}.value{width:auto}
.summary{margin-top:1.2mm}.summary td{border:1px solid #64748b;padding:.6mm 1.5mm;text-align:center}.summary b{font-size:10.5pt}.summary span{display:block;color:#475569;font-size:8pt}
.activities{margin-top:1.2mm;table-layout:fixed}.activities th,.activities td{border:1px solid #64748b;padding:.45mm 1mm;vertical-align:top}.activities th{background:#dfe7f1;text-align:center;font-weight:bold}.num{width:7mm;text-align:center}.phase{width:27mm}.activity{width:auto}.requirement{width:25mm}.status{width:23mm;text-align:center}.result{width:48mm}.date{width:22mm;text-align:center}
.footer{margin-top:1mm}.footer td{border:1px solid #64748b;padding:.7mm 1.5mm;vertical-align:top}.footer .label{width:24mm}.muted{color:#64748b}.nowrap{white-space:nowrap}
</style>

<table class="header">
    <tr>
        <td class="code"><b>รหัส TRM:</b> <?= Html::encode($model->roadmap->code) ?><br><b>แผนเลขที่:</b> <?= (int) $model->id ?></td>
        <td><div class="title">Training Roadmap (TRM)</div><div class="subtitle">แผนพัฒนาบุคลากรรายบุคคล แบบหน้าเดียว</div></td>
        <td class="code"><b>สถานะ:</b> <?= Html::encode($statusLabels[$model->status] ?? $model->status) ?><br><b>พิมพ์เมื่อ:</b> <?= date('d/m/Y H:i') ?></td>
    </tr>
</table>

<table class="info">
    <tr><td class="label">ชื่อบุคลากร</td><td class="value"><?= Html::encode($model->employee->fullname) ?></td><td class="label">หน่วยงาน</td><td class="value"><?= Html::encode($model->employee->departmentName() ?: '-') ?></td></tr>
    <tr><td class="label">ชื่อ Roadmap</td><td class="value"><?= Html::encode($model->roadmap->title) ?></td><td class="label">ระยะเวลา</td><td class="value"><?= Html::encode($model->start_date) ?> ถึง <?= Html::encode($model->target_end_date ?: 'ไม่กำหนด') ?></td></tr>
</table>

<table class="summary">
    <tr>
        <td><b><?= number_format((float) $model->progress_percent, 0) ?>%</b><span>ความก้าวหน้า</span></td>
        <td><b><?= count($model->roadmap->phases) ?></b><span>ระยะพัฒนา</span></td>
        <td><b><?= count($model->results) ?></b><span>กิจกรรมทั้งหมด</span></td>
        <td><b><?= Html::encode($model->mentor?->fullname ?? '-') ?></b><span>ผู้ดูแล/พี่เลี้ยง</span></td>
        <td><b><?= Html::encode($model->assessor?->fullname ?? '-') ?></b><span>ผู้ประเมิน</span></td>
    </tr>
</table>

<table class="activities">
    <thead><tr><th class="num">ลำดับ</th><th class="phase">ระยะ</th><th class="activity">กิจกรรมพัฒนา</th><th class="requirement">เกณฑ์</th><th class="status">สถานะ</th><th class="result">ผลการพัฒนา/ข้อเสนอแนะ</th><th class="date">วันที่ประเมิน</th></tr></thead>
    <tbody>
    <?php foreach ($model->roadmap->phases as $phase): ?>
        <?php foreach ($phase->activities as $activity): $result = $results[(int) $activity->id] ?? null; $activityNo++; ?>
            <tr>
                <td class="num"><?= $activityNo ?></td>
                <td><?= Html::encode($phase->title) ?></td>
                <td><b><?= Html::encode($activity->title) ?></b></td>
                <td><?= Html::encode($activity->requirement_type ?: '-') ?></td>
                <td class="status"><?= Html::encode($resultLabels[$result->status ?? 'pending'] ?? ($result->status ?? '-')) ?></td>
                <?php $resultText = $result ? ($result->result_text ?: ($result->result_value !== null ? (string) $result->result_value : '-')) : '-'; ?>
                <td><?= Html::encode(mb_strimwidth((string) $resultText, 0, 90, '...')) ?></td>
                <td class="date"><?= Html::encode($result->assessed_at ?? '-') ?></td>
            </tr>
        <?php endforeach ?>
    <?php endforeach ?>
    <?php if ($activityNo === 0): ?><tr><td colspan="7" style="text-align:center">ยังไม่มีกิจกรรมใน Training Roadmap</td></tr><?php endif ?>
    </tbody>
</table>

<table class="footer">
    <tr><td class="label">หมายเหตุแผน</td><td><?= Html::encode(mb_strimwidth((string) ($model->note ?: '-'), 0, 140, '...')) ?></td><td class="label">ผลสำเร็จจริง</td><td class="nowrap"><?= Html::encode($model->actual_end_date ?: '-') ?></td></tr>
</table>
