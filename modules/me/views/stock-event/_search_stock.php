<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
$cart = Yii::$app->cartSub;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-search">

    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="d-flex gap-3">
        <?= $form->field($model, 'q')->label(false) ?>
       

        <div class="form-group">

        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>