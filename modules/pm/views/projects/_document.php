<?php

use yii\helpers\Html;
use app\components\RichText;
use app\components\ThaiDate;
use app\modules\pm\models\Projects;

/**
 * เนื้อหาแบบเสนอโครงการ เรียงหัวข้อตามแม่แบบ "รหัส - โครงการ... ปีงบ 25xx" (13 ข้อ)
 * ใช้ร่วมกันระหว่างหน้ารายละเอียด (view) และหน้าพิมพ์ที่แก้ไขบนจอได้ (print)
 * สไตล์อยู่ในคลาส pdoc-* — หน้าที่เรียกใช้เป็นผู้กำหนด CSS เอง
 *
 * @var yii\web\View $this
 * @var Projects $model
 */

$fmt = Yii::$app->formatter;
$blank = '<div class="pdoc-blank">&nbsp;</div>';

$text = static function ($v) use ($blank) {
    return RichText::isEmpty($v) ? $blank : '<div class="erp-richtext">' . RichText::render($v) . '</div>';
};
$section = static function (string $title, string $body): string {
    return '<div class="pdoc-section"><div class="pdoc-head">' . $title . '</div><div class="pdoc-body">' . $body . '</div></div>';
};

// 2. วัตถุประสงค์
$objBody = $blank;
if ($model->objectives) {
    $objBody = '<ol class="pdoc-list">';
    foreach ($model->objectives as $o) {
        $objBody .= '<li>' . nl2br(Html::encode($o->detail)) . '</li>';
    }
    $objBody .= '</ol>';
}

// 3. เป้าหมาย/ตัวชี้วัด
$indBody = $blank;
if ($model->indicators) {
    $indBody = '<ol class="pdoc-list">';
    foreach ($model->indicators as $ind) {
        $pct = $ind->target_percent !== null ? ' ร้อยละ ' . $fmt->asDecimal($ind->target_percent, 2) : '';
        $indBody .= '<li>' . nl2br(Html::encode($ind->detail)) . $pct . '</li>';
    }
    $indBody .= '</ol>';
}

// 6. ระยะเวลา (แสดงเป็น พ.ศ. — formatter ของระบบให้ปี ค.ศ.)
$duration = '';
if ($model->start_date || $model->end_date) {
    $duration = Html::encode(trim(
        ($model->start_date ? ThaiDate::toThaiDate($model->start_date, false) : '') . ' - ' . ($model->end_date ? ThaiDate::toThaiDate($model->end_date, false) : ''),
        ' -'
    ));
}
if ($model->duration_text) {
    $duration .= ($duration !== '' ? '<br>' : '') . Html::encode($model->duration_text);
}

// 9. งบประมาณ
$budgetBody = '<p>งบประมาณ ' . $fmt->asDecimal($model->budget_total, 2) . ' บาท'
    . ($model->budget_source ? ' (' . Html::encode($model->budget_source) . ')' : '') . '</p>'
    . (RichText::isEmpty($model->budget_detail) ? '' : $text($model->budget_detail));

// 12. ผู้รับผิดชอบ
$respBody = $blank;
if ($model->responsibles) {
    $respBody = '<ol class="pdoc-list">';
    foreach ($model->responsibles as $r) {
        $line = Html::encode($r->fullname);
        if ($r->position) {
            $line .= ' ตำแหน่ง ' . Html::encode($r->position);
        }
        if ($r->phone) {
            $line .= '<br>เบอร์โทรศัพท์ ' . Html::encode($r->phone);
        }
        $respBody .= '<li>' . $line . '</li>';
    }
    $respBody .= '</ol>';
}

// 13. ผู้อนุมัติ — ลายเซ็น 3 ช่อง
$signBody = '';
$signers = $model->signers();
foreach (Projects::signerRoleList() as $role => $label) {
    $s = $signers[$role];
    // แบบฟอร์มขึ้นบรรทัดตำแหน่งผู้เสนอด้วยคำว่า "ตำแหน่ง" ส่วนผู้เห็นชอบ/ผู้อนุมัติใช้ข้อความตามที่กรอก
    if ($role === Projects::SIGNER_PROPOSER && $s['position'] !== '' && mb_strpos($s['position'], 'ตำแหน่ง') !== 0) {
        $s['position'] = 'ตำแหน่ง ' . $s['position'];
    }
    $signBody .= '<div class="pdoc-sign">'
        . '<p>ลงชื่อ........................................................' . Html::encode($label) . '</p>'
        . '<p>(' . ($s['name'] !== '' ? Html::encode($s['name']) : '........................................................') . ')</p>'
        . ($s['position'] !== '' ? '<p>' . nl2br(Html::encode($s['position'])) . '</p>' : '')
        . '</div>';
}
?>
<div class="pdoc">
    <div class="pdoc-title"><?= Html::encode($model->documentTitle()) ?></div>

    <?= $section('1. หลักการและเหตุผล', $text($model->rationale)) ?>
    <?= $section('2. วัตถุประสงค์', $objBody) ?>
    <?= $section('3. เป้าหมาย/ตัวชี้วัดผลสำเร็จของโครงการ', $indBody) ?>
    <?= $section('4. กลุ่มเป้าหมาย', $text($model->target_group)) ?>
    <?= $section('5. วิธีดำเนินการ (งานและกิจกรรม)', $text($model->method)) ?>
    <?= $section('6. ระยะเวลาการดำเนินการ', $duration !== '' ? '<p>' . $duration . '</p>' : $blank) ?>
    <?= $section('7. สถานที่ดำเนินโครงการ', $model->location ? '<p>' . Html::encode($model->location) . '</p>' : $blank) ?>
    <?= $section('8. วิทยากร', $text($model->lecturer)) ?>
    <?= $section('9. งบประมาณ', $budgetBody) ?>
    <p class="pdoc-note">หมายเหตุ ค่าใช้จ่ายทุกรายการสามารถถัวเฉลี่ยจ่ายแทนกันได้</p>
    <?= $section('10. การประเมินผลโครงการ', $text($model->evaluation)) ?>
    <?= $section('11. ผลที่คาดว่าจะได้รับ', $text($model->expected_result)) ?>
    <?= $section('12. ผู้รับผิดชอบโครงการ', $respBody) ?>
    <?= $section('13. ผู้อนุมัติโครงการ', $signBody) ?>
</div>
