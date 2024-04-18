<?php

use app\components\SiteHelper;
use yii\bootstrap4\ActiveForm;
?>

<div class="container">

    <h3>ตั้งค่าหน่วยงาน</h3>
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'data_json[name]')->textInput(['maxlength' => true])->label('ชื่อหน่วยงาน') ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'data_json[contact_person]')->textInput(['maxlength' => true])->label('บุคคลที่ติดต่อ') ?>
        </div>
    </div>
    <div class="form-group">
        <?= SiteHelper::BtnSave(); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>