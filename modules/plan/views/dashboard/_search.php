<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use kartik\depdrop\DepDrop;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

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
        <div class="col-lg-12 col-md-12 col-sm-12">
           <?php
        echo $form->field($model, 'thai_year',[
                                    'addon' => [
                                        'append' => [
                                            'content' => Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']), 
                                            'asButton' => true
                                        ]
                                    ]
                                ])->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ปีงบประมาณ'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '200px',
            ],
                        ])->label(false);
                        ?>
        </div>

    </div>

    <?php ActiveForm::end(); ?>

</div>