<?php

use app\modules\housing\models\Building;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['id' => 'housing-user-request-form']);
?>
<div class="mb-3">
    <div class="small text-muted">เลขคำขอ</div>
    <div class="fw-semibold"><?= Html::encode($model->request_no) ?></div>
</div>
<?= $form->field($model, 'preferred_building_type')->dropDownList(
    Building::typeOptions() + ['any' => 'บ้านพักหรือแฟลต'],
    ['prompt' => 'ยังไม่ระบุ']
) ?>
<?= $form->field($model, 'reason')->textarea([
    'rows' => 5,
    'placeholder' => 'อธิบายเหตุผลและความจำเป็น เพื่อประกอบการพิจารณา',
]) ?>
<div class="rounded-3 p-3 mb-3 small" style="background:#f5f8fb;border:1px solid #dce6f0">
    เมื่อส่งคำร้องแล้ว เจ้าหน้าที่จะตรวจสอบข้อมูลและแจ้งสถานะผ่านหน้านี้
</div>
<div class="d-flex justify-content-end gap-2">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('ส่งคำร้อง', ['class' => 'btn btn-primary px-4', 'name' => 'submit', 'value' => '1']) ?>
</div>
<?php
ActiveForm::end();
$this->registerJs(<<<'JS'
handleFormSubmit('#housing-user-request-form', null, function(response){
    if (response && response.redirect) location.href = response.redirect;
});
JS);
?>
