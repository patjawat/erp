<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var app\modules\hr\models\Employees $employee */
/** @var app\modules\jobdescription\models\JdEmployee $jd */
/** @var app\modules\jobdescription\models\JdEmployeeSection $section */
$this->title = 'แก้ไขหัวข้อ — ' . $section->title;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees/index']];
$this->params['breadcrumbs'][] = ['label' => $employee->fullname, 'url' => ['/hr/employees/view', 'id' => $employee->id]];
$this->params['breadcrumbs'][] = ['label' => 'JD', 'url' => ['view', 'emp_id' => $employee->id]];
$this->params['breadcrumbs'][] = 'แก้ไขหัวข้อ';
?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal"><?= Html::encode($this->title) ?></h6>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($section, 'title')->textInput(['maxlength' => true, 'class' => 'form-control'])->label('หัวข้อ') ?>
        <?= $form->field($section, 'content')->textarea(['rows' => 6, 'class' => 'form-control'])->label('เนื้อหา') ?>
        <?= $form->field($section, 'sort_order')->textInput(['type' => 'number', 'class' => 'form-control'])->label('ลำดับ') ?>
        <div class="mt-3">
            <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('ยกเลิก', ['view', 'emp_id' => $employee->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
