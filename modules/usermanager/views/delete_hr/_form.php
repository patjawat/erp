<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

?>

<style>
.form-group>label {
        text-align: end;
        font-size: 15px;
    }

.help-block {
    display: block;
    margin-top: 0px;
    margin-bottom: 0px;
    color: #737373;
}

.form-group {
    margin-bottom: 5px;
}

.card-top {
    width: 100%;
    display: inline-block;
    border-radius: 5px;
    padding: 10px 30px;
    border-top: 2px solid var(--color-blue-d);
    box-shadow: 0px 6px 6px 0px rgba(0, 0, 0, 0.15);
    text-align: left;
    color: var(--color-gray-xd);
    text-decoration: none;
    margin-bottom: 1rem;
}
</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-usermanager',
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'label' => 'col-lg-4 col-md-4 col-sm-3',
            'wrapper' => 'col-lg-8 col-md-6 col-sm-9',
        ],
    ],
    'layout' => 'horizontal',
]);?>

<div class="row" class="shadow">
    <div class="col-xs-6 col-sm-12 col-md-8 col-lg-6">
        <?= $form->field($model, 'username')->textInput(['maxlength' => true])->label('ชื่อเข้าใช้งาน') ?>
        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true])->label('รหัสผ่าน') ?>
        <?= $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true])->label('ยืนยันรหัสผ่าน');?>
        <?= $form->field($model, 'fullname')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'doctor_id')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
    </div>
</div>

<div class="row" class="shadow">
<div class="col-md-4 offset-md-2">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '<i class="fas fa-check"></i> บันทึก') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
</div>


<div hidden="true">
    <?= $form->field($model, 'roles')->checkboxList($model->getAllRoles(),['value' => 'doctor']) ?>
    <?= $form->field($model, 'status')->inline()->radioList($model->getItemStatus()) ?>
</div>


<?php ActiveForm::end(); ?>