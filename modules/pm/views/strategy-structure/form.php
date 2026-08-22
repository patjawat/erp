<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\db\ActiveRecord $model @var string $type @var app\modules\pm\models\StrategyPlan $plan */

app\assets\FormGuardAsset::register($this);
$labels = [
    'mission' => 'พันธกิจ', 'issue' => 'ประเด็นยุทธศาสตร์', 'goal' => 'เป้าประสงค์',
    'indicator' => 'ตัวชี้วัดหลัก', 'sub-indicator' => 'ตัวชี้วัดรอง',
    'tactic' => 'กลยุทธ์', 'project' => 'โครงการ', 'activity' => 'แผนงาน/กิจกรรม',
];
// ตัวชี้วัดและโครงการกรอกที่นี่แค่รหัสกับชื่อ รายละเอียดไปทำที่หน้าเฉพาะทาง
$hints = [
    'indicator' => 'กำหนดเฉพาะรหัสและชื่อ · นิยาม ค่าเป้าหมาย และเกณฑ์คะแนน ไปกรอกที่หน้าตัวชี้วัด',
    'sub-indicator' => 'ตัวชี้วัดย่อยภายใต้ตัวชี้วัดหลัก · รายละเอียดไปกรอกที่หน้าตัวชี้วัด',
    'project' => 'กำหนดเฉพาะรหัสและชื่อ · หลักการเหตุผล งบประมาณ และผู้รับผิดชอบ ไปเขียนที่หน้าโครงการ',
    'activity' => 'งานที่ไม่ใช้งบประมาณหรือใช้เพียงเล็กน้อย · กำหนดเฉพาะรหัสและชื่อ รายละเอียดไปกรอกที่หน้าโครงการ',
];
$isWork = in_array($type, ['project', 'activity'], true);
$isBrief = in_array($type, ['indicator', 'sub-indicator'], true) || $isWork;
$this->title = ($model->isNewRecord ? 'เพิ่ม' : 'แก้ไข') . $labels[$type];
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'strategy']) ?><?php $this->endBlock();
// หน่วยงานเจ้าของโครงการ — ต้องมีเสมอ ตั้งต้นจากหน่วยงานของผู้สร้างไว้แล้ว
// ส่งค่าที่เลือกไว้เดิมเข้าไปด้วย ของเก่าจะได้ไม่หายจากรายการแม้หน่วยนั้นถูกปิดใช้ไปแล้ว
$ouItems = [];
$ouHint = null;
if ($isWork) {
    $ouItems = \app\modules\settings\models\OrgUnit::groupedForSelect((int) $model->thai_year, $model->org_unit_id ? (int) $model->org_unit_id : null);
    $ouYearUsed = \app\modules\settings\models\OrgUnit::yearWithData((int) $model->thai_year);
    if ($ouYearUsed !== (int) $model->thai_year) {
        $ouHint = 'ยังไม่ได้ตั้งค่าทะเบียนหน่วยงานของปี ' . (int) $model->thai_year . ' จึงแสดงรายการของปี ' . $ouYearUsed . ' แทน';
    }
}
// ตัวชี้วัดในชุดแผนนี้ จัดกลุ่มตามเป้าประสงค์ และเยื้องตัวชี้วัดรองให้เห็นลำดับชั้น
$indicatorItems = [];
if ($type === 'tactic') {
    foreach ($plan->missions as $mi) foreach ($mi->issues as $is) foreach ($is->goals as $go) {
        $group = trim($go->code . ' ' . $go->name);
        foreach ($go->indicators as $ind) {
            if ($ind->parent_id) continue;
            $indicatorItems[$group][$ind->id] = $ind->code . ' — ' . $ind->name;
            foreach ($ind->children as $ch) {
                $indicatorItems[$group][$ch->id] = "\u{00A0}\u{00A0}\u{00A0}" . $ch->code . ' — ' . $ch->name;
            }
        }
    }
}
$form = ActiveForm::begin();
?>
<div class="mb-4">
    <h1 class="h3 mb-1"><?= Html::encode($this->title) ?></h1>
    <p class="text-muted mb-0"><?= Html::encode($plan->name) ?></p>
    <?php if (isset($hints[$type])): ?><p class="small text-muted mb-0 mt-1"><i data-lucide="info" class="me-1" style="width:14px;height:14px"></i><?= Html::encode($hints[$type]) ?></p><?php endif; ?>
</div>
<?php /* สรุปข้อผิดพลาดไว้บนสุด กันกรณีที่ error ตกอยู่ในฟิลด์ที่ฟอร์มย่อไม่ได้แสดง */ ?>
<?= $form->errorSummary($model, ['class' => 'alert alert-danger', 'header' => '<div class="fw-semibold mb-1">บันทึกไม่สำเร็จ</div>']) ?>
<div class="card border-0 shadow-sm"><div class="card-body p-4"><div class="row g-3">
<?php if ($isWork): ?>
    <div class="col-12 col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'เว้นว่าง = ออกอัตโนมัติ']) ?></div>
    <div class="col-12 col-md-3"><?= $form->field($model, 'thai_year')->input('number', ['min' => 2500, 'max' => 2600]) ?></div>
    <div class="col-12 col-md-5"><?= $form->field($model, 'org_unit_id')->dropDownList($ouItems, ['prompt' => '-- เลือกหน่วยงาน --'])->label('หน่วยงานเจ้าของ')->hint($ouHint) ?></div>
    <div class="col-12"><?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('ชื่อ' . $labels[$type]) ?></div>
<?php else: ?>
    <div class="col-12 col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?></div>
    <div class="col-12 col-md-2"><?= $form->field($model, 'sort_order')->textInput(['type' => 'number', 'min' => 0]) ?></div>
    <?php if ($type === 'tactic'): ?>
        <div class="col-12 col-md-6"><?= $form->field($model, 'indicator_id')->dropDownList($indicatorItems, ['prompt' => '-- เลือกตัวชี้วัด --'])->hint('กลยุทธ์ต้องผูกกับตัวชี้วัดหลักหรือตัวชี้วัดรอง') ?></div>
    <?php endif; ?>
    <div class="col-12"><?= $form->field($model, 'name')->textarea(['rows' => $isBrief ? 2 : 4]) ?></div>
    <div class="col-12"><?= $form->field($model, 'is_active')->checkbox() ?></div>
<?php endif; ?>
</div></div><div class="card-footer bg-body d-flex justify-content-between p-3">
<?= Html::a('ยกเลิก', ['/pm/strategy-plan/view', 'id' => $plan->id], ['class' => 'btn btn-outline-secondary']) ?>
<?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary']) ?>
</div></div><?php ActiveForm::end(); ?>
