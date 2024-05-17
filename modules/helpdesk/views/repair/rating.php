<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\StarRating
?>
<?php $form = ActiveForm::begin([
        'id' => 'form-repair',
    ]); ?>
<?php
echo $form->field($model, 'rating')->widget(StarRating::classname(), [
    'pluginOptions' => [
        // 'min' => 1,
        // 'max' => 5,
        'step' => 1,
        'size' => 'sm',
        'starCaptions' => $model->listRating(),
        'starCaptionClasses' => [
            1 => 'text-danger',
            2 => 'text-warning',
            3 => 'text-info',
            4 => 'text-success',
            5 => 'text-success'
        ],
    ],
])->label('ให้คะแนน');
?>
<?= $form->field($model, 'data_json[comment_date]')->hiddenInput(['value' => Date("Y-m-d H:i:s")])->label(false) ?>
<?= $form->field($model, 'data_json[comment]')->textArea(['rows' => 5,'placeholder' => 'ระบุความคิดเห็นเพื่อเป็นประโยนช์และใข้ในการปรับปรุง...'])->label('ความคิดเห็น') ?>
<div class="form-group mt-3 d-flex justify-content-center">
            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summit"]) ?>
        </div>
        <?php ActiveForm::end(); ?>


    <?php
$js = <<<JS

$('#form-repair').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                closeModal()
                Swal.fire({
                    title: "ขอคุณสำหรับการให้คะแนนกับเรา!",
                    text: "เราจะนำข้อเสนอแนะไปปรุงแก้ไขให้ดียิ่งขึ้น!",
                    icon: "success"
                    });

                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
            }
        }
    });
    return false;
});

JS;
$this->registerJS($js)
?>