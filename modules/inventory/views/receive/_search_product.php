<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['/inventory/receive/list-product'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="d-flex flex-row gap-1">
    <?= $form->field($model, 'title')->label(false) ?>
    <div class="form-group">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary']) ?>
    </div>
</div>
    <?php ActiveForm::end(); ?>
</div>

<?php
use yii\web\View;
$js = <<< JS


JS;
$this->registerJS($js,View::POS_END);
?>
