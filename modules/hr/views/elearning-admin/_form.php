<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\Select2;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\ElearningCourse $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="elearning-course-form card border-0 shadow-sm">
    <div class="card-body p-4">
        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-md-8">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'ระบุชื่อหลักสูตรอบรม (เช่น จริยธรรมและวินัยข้าราชการ)'])->label('ชื่อหลักสูตร <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'passing_score_percent')->textInput(['type' => 'number', 'min' => 0, 'max' => 100])->label('เกณฑ์คะแนนผ่านสอบหลังเรียน (%)') ?>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <?= $form->field($model, 'description')->textarea(['rows' => 4, 'placeholder' => 'ระบุรายละเอียดหลักสูตร จุดประสงค์ หรือคำแนะนำสั้นๆ สำหรับผู้เรียน'])->label('รายละเอียดเนื้อหาของหลักสูตร') ?>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-8">
                <?= $form->field($model, 'target_departments')->widget(Select2::classname(), [
                    'data' => Employees::ListDepartment(),
                    'options' => [
                        'placeholder' => 'เลือกแผนก/หน่วยงานที่บังคับเรียน (เว้นว่างไว้เพื่อเปิดเป็นหลักสูตรทั่วไป)', 
                        'multiple' => true
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('แผนกเป้าหมาย (กลุ่มเป้าหมายบังคับเรียน)') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'is_active')->dropDownList([
                    1 => 'เปิดใช้งานหลักสูตร',
                    0 => 'ปิดใช้งานหลักสูตร (ซ่อนไว้ชั่วคราว)',
                ])->label('สถานะเปิดสอน') ?>
            </div>
        </div>

        <div class="form-group mt-4 d-flex justify-content-end gap-2">
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-light rounded-pill px-4']) ?>
            <?= Html::submitButton('บันทึกข้อมูลหลักสูตร', ['class' => 'btn btn-primary rounded-pill px-4']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
