<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-search">

    <?php $form = ActiveForm::begin([
        'action' => ['product-list', 'order_id' => $order->id],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div>
<div class="d-flex flex-row gap-3">
  <div class="w-50">
  
  <?php echo $form->field($model, 'title')->textInput(['placeholder' => 'ค้นหา...'])->label('คำค้นหา') ?>
  </div>
  <div class="w-50">
  <?php
  
        echo $form->field($model, 'category_id')->widget(Select2::classname(), [
            'data' => $model->ListProductType(),
            'options' => ['placeholder' => ($order->category_id ?  $order->data_json['order_type_name'] : 'ระบุประเภท'),
            'disabled' => ($order->category_id ?  true : false)
        ],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
            'pluginEvents' => [
                'select2:select' => "function(result) { 
                          $(this).submit()
                        }",
            ]
        ])->label('ประเภท');
        ?>
  </div>
  <div class="p-2">

  </div>
</div>


    <?php
        // echo $form->field($model, 'q_category')->checkboxList(
        //     $model->ListProductType(),
        //     ['custom' => true, 'id' => 'custom-checkbox-list']
        // )->label(false);
    ?>
</div>
    <?php ActiveForm::end(); ?>

</div>
