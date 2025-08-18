<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\Plan $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="plan-form card card-body shadow-sm">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'plan_type')->dropDownList([
                'material' => 'แผนคำขอพัสดุ',
                'personnel' => 'แผนคำขอบุคลากร',
                'expense' => 'แผนคำขอค่าใช้สอย'
            ], ['prompt' => 'เลือกประเภทแผน']) ?>
        </div>
        <div class="col-md-8">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'ชื่อแผน']) ?>
        </div>
    </div>

    <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'รายละเอียดแผน']) ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'start_date')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'end_date')->textInput() ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'budget_total')->input('number', ['step' => '0.01', 'placeholder' => 'งบประมาณรวม']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'budget_used')->input('number', ['step' => '0.01', 'placeholder' => 'งบที่ใช้ไปแล้ว']) ?>
        </div>
    </div>
    
    <div class="form-group mt-3">
        <?= Html::submitButton($model->isNewRecord ? 'บันทึกแผน' : 'บันทึกการแก้ไข', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-light']) ?>
    </div>
    
    <?= $form->field($model, 'emp_id')->textInput(['value' => 1]) ?>
    <?php ActiveForm::end(); ?>

</div>
