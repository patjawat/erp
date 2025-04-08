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
    <div class="d-flex gap-3">
        <?= $form->field($model, 'q')->label(false) ?>
        <?php
  
  echo $form->field($model, 'asset_type')->widget(Select2::classname(), [
      'data' => $model->ListProductType(),
      'options' => ['placeholder' => 'เลือกประเภทวัสดุ',
  ],
      'pluginOptions' => [
          'allowClear' => true,
          'width' => '200px',
        ],
        'pluginEvents' => [
            'select2:select' => "function(result) { 
                $(this).submit()
                }",
                'select2:unselect' => "function(result) { 
                    $(this).submit()
                    }",
            ],
            ])->label(false);
            ?>
            <?php if(isset($warehouse) && $warehouse['warehouse_type'] !== 'MAIN'):?>
             <?=Html::a('<button type="button" class="btn btn-primary">
                    <i class="fa-solid fa-cart-plus"></i> ตะกร้า <span class="badge text-bg-danger" id="totalCount">'.$cart->getCount().'</span>
                    </button>',['/inventory/sub-stock/show-cart'],['class' => 'brn btn-primary shadow open-modal','data' => ['size' => 'modal-xl']])?>
       <?php  endif;?>
       <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-light']) ?>
       


        <div class="form-group">

        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>