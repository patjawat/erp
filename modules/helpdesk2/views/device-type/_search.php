<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\DeviceTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="device-type-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'code')->textInput(['placeholder' => 'รหัส'])->label(false) ?>
        </div>
        <div class="col-lg-5">
            <?= $form->field($model, 'title')->textInput(['placeholder' => 'ชื่อรายการ'])->label(false) ?>
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

</div>