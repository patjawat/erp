<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\modules\inventory\models\Warehouse;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['/me/store-v2/order-in'],
        'method' => 'get',
        'id' => 'form-search',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>


    <div class="row">
        <div class="col-12">

            <?php
       echo $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
                                        'data' => $model->listWareHouseMain(),
                                        'options' => ['placeholder' => 'เลือกคลังที่ต้องการเบิก'],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 

                                            }",
                                            "select2:select" => "function() {
                                               $(this).submit();
                                        }",],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                    ])->label('คลัง');
                                    
                                    ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>