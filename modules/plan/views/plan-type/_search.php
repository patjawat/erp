<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanItemSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="plan-item-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="row">
        <div class="col-lg-11">
            <?= $form->field($model, 'title')->textInput()->label(false) ?>
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

    <?php ActiveForm::end(); ?>

</div>