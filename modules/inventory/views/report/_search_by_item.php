<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>


<div class="row">

   <div class="col-5">
        <?php echo $form->field($model, 'q')->textInput(['class' => 'form-control', 'placeholder' => 'ค้นหา...'])->label(false); ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_filter', [
            'form' => $form,
            'model' => $model,
        ])
        ?>

    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model]) ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model]) ?>
    </div>

    <div class="col-1">
        <div class="d-flex flex-row align-items-center gap-2">
            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter">
                <i class="fa-solid fa-filter"></i>
            </button>
        </div>
    </div>

</div>


<div class="collapse mt-3" id="collapseFilter">


</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<< JS
 
JS;
$this->registerJS($js);
?>