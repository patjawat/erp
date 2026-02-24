<?= $form->field($model, 'date_start')->widget(\app\widgets\datepicker\DatepickerThai::class, [
    'options' => ['id' => 'dateStart', 'placeholder' => 'เริ่มจากวันที่'],
])->label(false); ?>

<?php
$js = <<< JS
    $("#dateStart").on('change', function() {
        $('#thaiYear').val(null).trigger('change');
        $('#dateFilter').val(null).trigger('change');
    });
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>