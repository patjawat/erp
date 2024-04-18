<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

?>
<style>
.form-group {
    margin-bottom: 0rem;
}
</style>

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'q', [
        'inputTemplate' => 
        '<div class="input-group">{input}'.Html::submitButton('<i class="fas fa-search"></i>', 
        ['class' => 'btn btn-light']).'&nbsp;'
        // .Html::a('<i class="fas fa-plus"></i>', ['create'], ['class' => 'btn btn-success']).'&nbsp;'
        .Html::a('<i class="fas fa-redo"></i>', [''], ['class' => 'btn btn-secondary']).'</div>',
    ])->textInput(['class' => 'form-control float-righ','placeholder' => 'ค้นหา', 'autofocus' => 'autofocus'])->label(false); ?>
    <?php ActiveForm::end(); ?>

<?php
$js = <<< JS
$('#usersearch-q').select();
JS;
$this->registerJS($js);
?>