<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

$cart = Yii::$app->cartSub;
$warehouse = Yii::$app->session->get('warehouse');
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
    <?= $form->field($model, 'asset_type')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'order_id')->hiddenInput()->label(false) ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-center gap-3 w-100">
            <?= $form->field($model, 'q', [
                'options' => ['class' => 'flex-grow-1', 'style' => 'max-width:400px'] // จำกัดความกว้าง input
            ])->label(false) ?>
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', [
                'class' => 'btn btn-light'
            ]) ?>
        </div>
    </div>
</div>



    <?php ActiveForm::end(); ?>

</div>