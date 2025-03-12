<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\inventory\models\Warehouse;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['/me/store-v2/index'],
        'method' => 'get',
        'id' => 'form-search',
        'options' => [
            'data-pjax' => 1
        ],
        'fieldConfig' => [
        'options' => ['class' => 'mb-1'], // เพิ่ม margin-bottom 3
    ],
    ]); ?>

    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...','class' => 'form-control form-control-md rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10 is-valid p-2'])->label(false) ?>
    <?php ActiveForm::end(); ?>

</div>
