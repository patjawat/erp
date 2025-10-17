<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
    <?php $form = ActiveForm::begin([
        'action' => ['/inventory/default/dashboard'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="row  align-items-end">
         <div class="col-5 ms-auto">
            <?php
            echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
                'data' => $model->ListGroupYear(),
                'options' => ['placeholder' => 'ปีงบประมาณ'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'pluginEvents' => [
                    'select2:select' => "function(result) { 
                  $(this).submit()
                  }",
                    "select2:unselecting" => "function() {
                    $(this).submit()
                }",
                ]
            ])->label(false);
            ?>
        </div>
        <!-- <div class="col-4">
            <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
        </div>
        <div class="col-4">
            <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
        </div> -->
        <div class="col-2">
<?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
        <?php ActiveForm::end(); ?>