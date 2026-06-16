<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListPurchase $model */
?>

<div class="list-setting-form list-purchase-form">
    <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation' => true,
        'validationUrl' => $model->isNewRecord ? ['/am/purchase/validator'] : ['/am/purchase/validator', 'id' => $model->id],
        'options' => [
            'data-confirm-title' => 'ยืนยันการบันทึก?',
            'data-confirm-text' => 'คุณต้องการบันทึกวิธีการได้มานี้ใช่หรือไม่',
            'data-confirm-button' => '<i class="fa-solid fa-check me-1"></i> ใช่, บันทึก',
        ],
    ]); ?>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <?= $form->field($model, 'code')->textInput([
                'maxlength' => true,
                'placeholder' => 'เช่น 1',
                'autocomplete' => 'off',
            ]) ?>
        </div>
        <div class="col-12 col-md-8">
            <?= $form->field($model, 'title')->textInput([
                'maxlength' => true,
                'placeholder' => 'เช่น ตกลงราคา',
                'autocomplete' => 'off',
            ]) ?>
        </div>

        <div class="col-12">
            <?= $form->field($model, 'description')->textarea([
                'rows' => 2,
                'placeholder' => 'อธิบายเพิ่มเติม (ถ้ามี)',
            ]) ?>
        </div>

        <div class="col-6">
            <?= $form->field($model, 'sort')->input('number', ['min' => 0, 'step' => 1, 'placeholder' => '1, 2, 3 ...']) ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'active')->dropDownList([
                1 => 'ใช้งาน',
                0 => 'ปิดใช้',
            ]) ?>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="fa-solid fa-xmark me-1"></i> ยกเลิก
        </button>
        <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึก', [
            'class' => 'btn btn-warning text-white px-3',
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
handleFormSubmit('#form', null, async function(response) {
    if (response && response.container) {
        if (!erpReloadPjax(response.container)) {
            location.reload();
        }
    } else {
        location.reload();
    }
});
JS;
$this->registerJs($js);
?>
