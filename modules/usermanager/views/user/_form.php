<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var app\modules\usermanager\models\User $model */

$form = ActiveForm::begin(['id' => 'form-usermanager']);
?>
<div class="card border shadow-sm mb-3">
    <div class="card-body">
        <h2 class="h5 mb-3">ข้อมูลผู้ใช้งาน</h2>
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'fullname')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-12 col-lg-6">
                <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'status')->inline()->radioList($model->getItemStatus()) ?>
            </div>
        </div>
    </div>
</div>

<div class="card border shadow-sm mb-3">
    <div class="card-body">
        <?= $this->render('_role_picker', ['model' => $model, 'excludeDirector' => true]) ?>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 justify-content-end mb-4">
    <?= Html::a('<i class="bi bi-x-lg me-1" aria-hidden="true"></i> ยกเลิก', ['/usermanager/user'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="bi bi-check-lg me-1" aria-hidden="true"></i> บันทึก', ['class' => 'btn btn-success']) ?>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs(<<<'JS'
$('#form-usermanager').on('beforeSubmit', function () {
    if ($(this).find('input[name="User[roles][]"][value="doctor"]').is(':checked')
        && !$(this).find('#user-doctor_id').val()) {
        alert('ระบุรหัสแพทย์');
        return false;
    }
    return true;
});
JS);
?>
