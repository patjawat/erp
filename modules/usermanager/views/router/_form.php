<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
?>

<?php $form = ActiveForm::begin([
    'id' => 'router-form',
]);?>
<div class="row">
    <div class="col-6">
        <?=$form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Url เช่น /site/*'])->label(false);?>
        <?=$form->field($model, 'type')->hiddenInput(['maxlength' => true, 'value' => '2'])->label(false);?>
    </div>
    <div class="col-6 ">
        <?=$model->isNewRecord ? Html::submitButton('<i class="fas fa-check"></i> บันทึก', ['class' => 'btn btn-success']) : Html::submitButton('<i class="fas fa-edit"></i> แก้ไข', ['class' => 'btn btn-warning'])?>
        <?=Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-secondary loading-page'])?>
    </div>
</div>
</div>
<?php ActiveForm::end();?>


<?php
$js = <<< JS

$("#router-form").on('beforeSubmit',function(e) {
    var form = $(this);
    $.ajax({
          url: form.attr('action'),
          type: 'post',
          data: form.serialize(),
          success: function (response) {
               console.log(response);
               $.pjax.reload({container:"#grid-router"});
            loadFormCreate();
          }
     });

return false;
});
JS;
$this->registerJs($js, View::POS_READY);
?>