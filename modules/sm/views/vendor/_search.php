<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\SupVendorSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => ['data-pjax' => 1],
]); ?>
<div class="row g-2 align-items-end">
    <?php if ((int) ($model->missing_code_only ?? 0) === 1): ?>
    <?= Html::hiddenInput('VendorSearch[missing_code_only]', '1') ?>
    <?php endif; ?>
    <div class="col-12 col-sm-6 col-md-7">
        <?= $form->field($model, 'q')->label(false)->textInput([
            'class' => 'form-control',
            'placeholder' => 'ค้นหา ชื่อผู้แทนจำหน่าย รหัส ที่อยู่ โทรศัพท์...',
        ]) ?>
    </div>
    <div class="col-12 col-sm-4 col-md-3">
        <div class="form-check mb-0">
            <input type="hidden" name="VendorSearch[incomplete_only]" value="0">
            <?= Html::checkbox('VendorSearch[incomplete_only]', (int) ($model->incomplete_only ?? 0) === 1, ['value' => '1', 'id' => 'vendorsearch-incomplete_only', 'class' => 'form-check-input']) ?>
            <label class="form-check-label small" for="vendorsearch-incomplete_only">
                <i class="bi bi-funnel me-1 text-warning"></i> เฉพาะข้อมูลไม่ครบถ้วน
            </label>
        </div>
    </div>
    <div class="col-12 col-sm-2 col-md-2 d-flex gap-2">
        <?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหา', ['class' => 'btn btn-primary w-100']) ?>
        <?= Html::a('<i class="bi bi-arrow-counterclockwise me-1"></i> รีเซ็ต', ['index'], ['class' => 'btn btn-outline-secondary w-100', 'data-pjax' => 0]) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>