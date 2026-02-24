<?= $form->field($model, 'date_end')->widget(\app\widgets\datepicker\DatepickerThai::class, [
    'options' => ['id' => 'dateEnd', 'placeholder' => 'ถึงวันที่'],
])->label(false); ?>

<?php
$js = <<< JS
    $("#dateEnd").on('change', function() {
        $('#thaiYear').val(null).trigger('change');
        $('#dateFilter').val(null).trigger('change');
    });
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>