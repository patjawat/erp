<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\Categorise $model */
?>
<div class="categorise-form">
    <?php $form = ActiveForm::begin([
        'id' => 'form-item-type',
        'options' => [
            'class' => 'stock-type-form',
            'data' => [
                'confirm-title' => 'ยืนยันการบันทึกข้อมูล?',
                'confirm-text' => 'โปรดตรวจสอบรหัสและชื่อประเภทวัสดุก่อนยืนยัน',
                'confirm-button' => '<i class="fa fa-save"></i> ยืนยันบันทึก',
                'loading-title' => 'กำลังบันทึกข้อมูล',
                'loading-text' => 'ระบบกำลังบันทึกประเภทวัสดุ กรุณารอสักครู่...',
            ],
        ],
    ]); ?>
    <div class="row g-3">
        <div class="col-md-4">
            <?= $form->field($model, 'code')->textInput([
                'maxlength' => true,
                'class' => 'form-control form-control-input',
                'placeholder' => 'เช่น M1-01',
                'autofocus' => true,
            ])->label('รหัสประเภท') ?>
        </div>
        <div class="col-md-8">
            <?= $form->field($model, 'title')->textInput([
                'maxlength' => true,
                'class' => 'form-control form-control-input',
            ])->label('ชื่อประเภทวัสดุ') ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'description')->textarea([
                'rows' => 3,
                'class' => 'form-control form-control-input',
            ])->label('รายละเอียด (ไม่บังคับ)') ?>
        </div>
    </div>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>
    <div class="form-group mt-3 d-flex flex-wrap gap-2">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="bi bi-x-lg"></i> ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<'JS'
handleFormSubmit('#form-item-type', null, async function () {
    location.reload();
});
JS;
$this->registerJs($js);
?>
