 <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control', 'id' => 'dateEnd', 'placeholder' => 'ถึงวันที่'])->label(false); ?>
 <?php
$js = <<< JS
    thaiDatepicker('#dateEnd')
    $("#dateEnd").on('change', function() {
        $('#thaiYear').val(null).trigger('change');
        $('#dateFilter').val(null).trigger('change');
    });
JS;
$this->registerJS($js, yii\web\View::POS_END);
?>