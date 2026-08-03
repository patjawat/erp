<?php
use yii\helpers\Html;

$items = $model->template->items;
$evaluations = [];
foreach ($round->evaluations as $evaluation) {
    $evaluations[$evaluation->role] = $evaluation;
}
$scores = [];
foreach ($evaluations as $role => $evaluation) {
    foreach ($evaluation->scores as $score) {
        $scores[$role][(int) $score->template_item_id] = $score->score;
    }
}
$categories = [];
foreach ($items as $item) {
    $categories[$item->category][] = $item;
}
$fmtScore = static function ($value): string {
    return $value === null ? '' : number_format((float) $value, 0);
};
$submittedDate = static function ($evaluation): string {
    return $evaluation && $evaluation->submitted_at
        ? Yii::$app->formatter->asDate($evaluation->submitted_at, 'php:d/m/Y')
        : '......../......../..........';
};
$department = $model->employee->empDepartment?->name
    ?? $model->employee->empDepartment?->title
    ?? '-';
$position = $model->employee->employeePosition?->title
    ?? $model->employee->position_name
    ?? '-';
$isSameLeader = (int) $model->supervisor_employee_id === (int) $model->group_head_employee_id;
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<style>
    body { font-family: thsarabunnew; font-size: 13pt; line-height: 1.12; color: #111; }
    h1, h2, p { margin: 0; }
    .title { text-align: center; margin-bottom: 7mm; }
    .title h1 { font-size: 20pt; }
    .title h2 { font-size: 16pt; margin-top: 1mm; }
    .title p { font-size: 13pt; margin-top: 1mm; }
    .section-title { font-weight: bold; background: #dbe8f2; border: 0.3mm solid #222; padding: 1.2mm 2mm; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 0.3mm solid #222; padding: 1.1mm 1.5mm; vertical-align: middle; }
    th { font-weight: bold; text-align: center; background: #eaf1f6; }
    .info td { height: 6mm; }
    .rubric { margin-top: 4mm; }
    .rubric td, .rubric th { text-align: center; }
    .assessment { margin-top: 4mm; font-size: 11.2pt; }
    .assessment .category td { background: #eaf1f6; font-weight: bold; }
    .assessment .number, .assessment .score { text-align: center; }
    .assessment .number { width: 6mm; }
    .assessment .score { width: 12mm; }
    .assessment .note { width: 24mm; }
    .summary { margin-top: 3mm; }
    .summary td { height: 8mm; }
    .small { font-size: 10.5pt; }
    .center { text-align: center; }
    .right { text-align: right; }
    .page-break { page-break-before: always; }
    .comment td { height: 20mm; vertical-align: top; }
    .signatures { margin-top: 5mm; table-layout: fixed; }
    .signatures td { height: 29mm; text-align: center; vertical-align: bottom; }
    .ack td { height: 13mm; vertical-align: top; }
    .decision td { height: 20mm; vertical-align: top; }
</style>
</head>
<body>
<div class="title">
    <h1>แบบประเมินผลการปฏิบัติงานช่วงทดลองงาน</h1>
    <h2>โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย</h2>
    <p><?= Html::encode($model->template->name) ?> | การประเมินครั้งที่ <?= (int) $round->month_no ?> เดือน</p>
</div>

<div class="section-title">ข้อมูลผู้รับการประเมิน</div>
<table class="info">
    <tr><td width="18%">ชื่อ-สกุล</td><td width="32%"><?= Html::encode($model->employee->fullname) ?></td><td width="18%">ตำแหน่ง</td><td><?= Html::encode($position) ?></td></tr>
    <tr><td>วันที่เริ่มงาน</td><td><?= Yii::$app->formatter->asDate($model->start_date, 'php:d/m/Y') ?></td><td>ครั้งที่ประเมิน</td><td>เดือนที่ <?= (int) $round->month_no ?></td></tr>
    <tr><td>หน่วยงาน</td><td><?= Html::encode($department) ?></td><td>วันที่ครบกำหนด</td><td><?= Yii::$app->formatter->asDate($round->due_date, 'php:d/m/Y') ?></td></tr>
</table>

<table class="rubric">
    <tr><th>ระดับ</th><th>ดีมาก</th><th>ดี</th><th>พอใช้</th><th>ต้องปรับปรุง</th><th>ไม่ผ่าน</th></tr>
    <tr><th>คะแนน</th><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td></tr>
    <tr class="small"><th>ความหมาย</th><td>ปฏิบัติได้ดีเยี่ยม</td><td>ปฏิบัติได้ดี</td><td>ปฏิบัติได้ตามที่กำหนด</td><td>ยังต้องพัฒนา</td><td>ไม่สามารถปฏิบัติงานได้ตามมาตรฐาน</td></tr>
</table>

<table class="assessment">
    <thead><tr><th class="number">ที่</th><th>หัวข้อการประเมิน</th><th class="score">ตนเอง</th><?php if ($isSameLeader): ?><th class="score">หัวหน้างาน / หัวหน้ากลุ่มงาน</th><?php else: ?><th class="score">หัวหน้างาน</th><th class="score">หัวหน้ากลุ่มงาน</th><?php endif ?><th class="note">หมายเหตุ</th></tr></thead>
    <tbody>
    <?php $number = 1; foreach ($categories as $category => $categoryItems): ?>
        <tr class="category"><td></td><td colspan="<?= $isSameLeader ? 4 : 5 ?>"><?= Html::encode($category) ?></td></tr>
        <?php foreach ($categoryItems as $item): ?>
            <tr>
                <td class="number"><?= $number++ ?></td>
                <td><?= Html::encode($item->question) ?></td>
                <td class="score"><?= $fmtScore($scores['self'][(int) $item->id] ?? null) ?></td>
                <td class="score"><?= $fmtScore($scores['supervisor'][(int) $item->id] ?? null) ?></td>
                <?php if (!$isSameLeader): ?><td class="score"><?= $fmtScore($scores['group_head'][(int) $item->id] ?? null) ?></td><?php endif ?>
                <td></td>
            </tr>
        <?php endforeach ?>
    <?php endforeach ?>
        <tr><td colspan="2" class="right"><strong>คะแนนร้อยละ</strong></td><td class="score"><?= isset($evaluations['self']) && $evaluations['self']->percent_score !== null ? number_format($evaluations['self']->percent_score, 2) : '' ?></td><td class="score"><?= isset($evaluations['supervisor']) && $evaluations['supervisor']->percent_score !== null ? number_format($evaluations['supervisor']->percent_score, 2) : '' ?></td><?php if (!$isSameLeader): ?><td class="score"><?= isset($evaluations['group_head']) && $evaluations['group_head']->percent_score !== null ? number_format($evaluations['group_head']->percent_score, 2) : '' ?></td><?php endif ?><td></td></tr>
    </tbody>
</table>

<table class="summary">
    <tr><th colspan="2">สรุปผลการประเมินเดือนที่ <?= (int) $round->month_no ?></th></tr>
    <?php if ((int) $round->month_no === 3 && $model->decision): ?>
        <tr><td width="44%">คะแนนเฉลี่ยผู้บังคับบัญชา <?= $isSameLeader ? '(ผู้ประเมินคนเดียว)' : '(หัวหน้างานและหัวหน้ากลุ่มงาน)' ?></td><td><strong><?= number_format($model->decision->average_percent, 2) ?>%</strong> | เกณฑ์ผ่าน <?= number_format($model->decision->threshold_percent, 0) ?>%</td></tr>
        <tr><td>ข้อเสนอการจ้าง</td><td><?= $model->decision->recommendation === 'hire' ? '[X] จ้างต่อ  [ ] ไม่จ้างต่อ' : '[ ] จ้างต่อ  [X] ไม่จ้างต่อ' ?></td></tr>
    <?php else: ?>
        <tr><td width="44%">ผลรอบนี้</td><td>ใช้สำหรับติดตามและให้ข้อเสนอแนะ ยังไม่ใช้ตัดสินการจ้าง</td></tr>
    <?php endif ?>
</table>

<div class="page-break"></div>
<div class="section-title">ความเห็นและข้อเสนอแนะ</div>
<table class="comment"><tr><td><?= (int) $round->month_no === 3 && $model->decision ? nl2br(Html::encode($model->decision->summary_comment)) : '' ?></td></tr></table>

<div class="section-title" style="margin-top:5mm">ผู้ประเมิน</div>
<table class="signatures">
    <tr>
        <td><strong>ผู้รับการประเมิน</strong><br><br>ลงชื่อ ................................................<br>(<?= Html::encode($model->employee->fullname) ?>)<br>วันที่ <?= $submittedDate($evaluations['self'] ?? null) ?></td>
        <td><strong><?= $isSameLeader ? 'หัวหน้างาน / หัวหน้ากลุ่มงาน' : 'หัวหน้างาน' ?></strong><br><br>ลงชื่อ ................................................<br>(<?= Html::encode($model->supervisor->fullname) ?>)<br>วันที่ <?= $submittedDate($evaluations['supervisor'] ?? null) ?></td>
        <?php if (!$isSameLeader): ?><td><strong>หัวหน้ากลุ่มงาน</strong><br><br>ลงชื่อ ................................................<br>(<?= Html::encode($model->groupHead->fullname) ?>)<br>วันที่ <?= $submittedDate($evaluations['group_head'] ?? null) ?></td><?php endif ?>
    </tr>
</table>

<div class="section-title" style="margin-top:5mm">ผู้อำนวยการรับทราบ</div>
<table class="ack"><tr><td>
    <?= $round->acknowledgement ? 'รับทราบผลการประเมินแล้ว เมื่อ '.Yii::$app->formatter->asDatetime($round->acknowledgement->acknowledged_at, 'php:d/m/Y H:i') : '[ ] รับทราบผลการประเมิน' ?>
    <div class="center"><br>ลงชื่อ ................................................ ผู้รับทราบ<br>(<?= Html::encode($model->director->fullname) ?>)</div>
</td></tr></table>
</body>
</html>
