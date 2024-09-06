<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;
use app\modules\inventory\models\Warehouse;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
$cart = \Yii::$app->cart;
?>

<div class="order-search w-25">

    <?php $form = ActiveForm::begin([
        'action' => ['/inventory/store/index'],
        'method' => 'get',
        'id' => 'form-search',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="row justify-content-end">
    <div class="col-8">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>
    </div>
</div>


    <!-- <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div> -->

    <?php ActiveForm::end(); ?>

</div>
