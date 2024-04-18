<?php

use yii\helpers\Html;
// use kartik\widgets\ActiveForm;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\EmployeesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
    .field-employeessearch-lname{
        margin-bottom: 0px !important;
    }
</style>
<div class="d-flex justify-content-between">
<div class="btn-group" role="group" aria-label="Basic outlined example">
  <button type="button" class="btn btn-outline-primary">Left</button>
  <button type="button" class="btn btn-outline-primary">Middle</button>
  <button type="button" class="btn btn-outline-primary">Right</button>
</div>
    <div>

        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            // 'type' => ActiveForm::TYPE_FLOATING,
            'options' => [
                'data-pjax' => 1,
            'class' => ''
            ],
        ]); ?>
        <div class="d-flex gap-2">

            
            <?= $form->field($model, 'lname')->textInput()->label(false) ?>
            <button class="btn btn-outline-success" type="submit">Search</button>
        </div>

    
    </div>
</div>



<?php ActiveForm::end(); ?>