<?php

use kartik\widgets\ActiveForm;
?>

<div class="row">
    <div class="col-6">
      <iframe id="preview-frame" 
        src="<?= \yii\helpers\Url::to(['service/print', 'id' => 418]) ?>" 
        width="100%" height="800px">
</iframe>
    </div>
    <div class="col-6">
        <?php $form = ActiveForm::begin(['id' => 'form']); ?>
        <?= $form->field($model, 'name')->textInput()->label(false) ?>
        <label for="">ฝ่ายงนาที่ส่งซ่อม</label>
        <div class="d-flex gap-2">
            <?= $form->field($model, 'data_json[department_x]')->textInput()->label('X') ?>
            <?= $form->field($model, 'data_json[department_y]')->textInput()->label('Y') ?>
        </div>

        <label for="">สถานที่</label>
        <div class="d-flex gap-2">
            <?= $form->field($model, 'data_json[location_x]')->textInput()->label('X') ?>
            <?= $form->field($model, 'data_json[location_y]')->textInput()->label('Y') ?>
        </div>

        <label for="">ความเร่งด่วน</label>
        <div class="d-flex gap-2">
            <?= $form->field($model, 'data_json[urgency_x]')->textInput()->label('X') ?>
            <?= $form->field($model, 'data_json[urgency_y]')->textInput()->label('Y') ?>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-send me-1"></i>
            บันทึก
        </button>
        <?php ActiveForm::end(); ?>
    </div>
</div>




<?php
$js = <<< JS

   handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });

let timer;
$('#form input').on('keyup', function(e) {
    clearTimeout(timer); // เคลียร์ timeout เดิม
    timer = setTimeout(function() {
        var form = $('#form');
        $.ajax({
            type: "post",
            url: form.attr('action'),
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                // โหลด iframe ใหม่หลังบันทึก
                const iframe = document.getElementById('preview-frame');
                if (iframe) {
                    iframe.src = iframe.src.split('?')[0] + '?id=418';
                }
            }
        });
    }, 800); // 0.8 วินาทีหลังพิมพ์หยุด
});
JS;
$this->registerJs($js);
?>

