<?php
use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="development-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="d-flex justify-content-between align-items-center gap-2">
<?=$this->render('@app/components/ui/Search',['form' => $form,'model' => $model])?>
</div>

    <?php ActiveForm::end(); ?>

</div>
<?php

$js = <<< JS

    thaiDatepicker('#developmentsearch-date_start,#developmentsearch-date_end')
    $("#developmentsearch-date_start").on('change', function() {
            $('#developmentsearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });
    $("#developmentsearch-date_end").on('change', function() {
            $('#developmentsearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });


    JS;
$this->registerJS($js, View::POS_END);

?>
