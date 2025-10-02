 <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control', 'id' => 'dateStart', 'placeholder' => 'เริ่มจากวันที่'])->label(false); ?>

<?php
$js = <<< JS
    thaiDatepicker('#dateStart')
    $("#dateStart").on('change', function() {
            $('#thaiYear').val(null).trigger('change');
            $('#dateFilter').val(null).trigger('change');
        });

JS;
$this->registerJS($js, yii\web\View::POS_END);
?>