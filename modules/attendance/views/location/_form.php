<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$isNew = $model->isNewRecord;
?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row g-3">
            <div class="col-12">
                <?= $form->field($model, 'name')->textInput(['class' => 'form-control', 'placeholder' => 'ชื่อจุด/บริเวณ'])->label('ชื่อจุด <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'lat')->textInput(['type' => 'number', 'step' => 'any', 'class' => 'form-control', 'placeholder' => '13.7563'])->label('Latitude') ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'lng')->textInput(['type' => 'number', 'step' => 'any', 'class' => 'form-control', 'placeholder' => '100.5018'])->label('Longitude') ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'radius_m')->textInput(['type' => 'number', 'min' => 0, 'max' => 100000, 'class' => 'form-control'])->label('รัศมีอนุญาตลงเวลา (เมตร)') ?>
                <p class="form-text text-muted small mb-0">ระยะห่างสูงสุดจากจุดศูนย์กลาง (Lat/Lng) ที่ยอมรับเมื่อลงเวลาด้วย GPS — ใส่ 0 = ไม่ใช้รัศมีกับจุดนี้ (ถ้าทุกจุดเป็น 0 ระบบจะไม่บังคับตรวจพิกัด)</p>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'qr_token')->textInput(['class' => 'form-control', 'placeholder' => 'เว้นว่างให้ระบบสร้างอัตโนมัติ'])->label('ค่า QR (ถ้ามี)') ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'active')->dropdownList([1 => 'เปิดใช้งาน', 0 => 'ปิด'], ['class' => 'form-select'])->label('สถานะ') ?>
            </div>
            <div class="col-12">
                <?= Html::submitButton($isNew ? 'เพิ่มจุดลงเวลา' : 'บันทึก', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary ms-2']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
