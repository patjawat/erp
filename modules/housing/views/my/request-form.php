<?php

use app\modules\housing\models\Building;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'ยื่นคำขอเข้าพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm"><div class="card-body">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'request_no')->textInput(['readonly' => true]) ?>
        <?= $form->field($model, 'preferred_building_type')->dropDownList(Building::typeOptions(), ['prompt' => 'ไม่ระบุ']) ?>
        <?= $form->field($model, 'reason')->textarea(['rows' => 5, 'placeholder' => 'ระบุเหตุผลและข้อมูลที่ต้องการให้คณะกรรมการพิจารณา']) ?>
        <div class="d-flex justify-content-end gap-2"><?= Html::submitButton('บันทึกร่าง', ['class' => 'btn btn-outline-secondary']) ?><?= Html::submitButton('ส่งคำขอ', ['class' => 'btn btn-primary', 'name' => 'submit', 'value' => '1']) ?></div>
        <?php ActiveForm::end(); ?>
    </div></div>
</div>
