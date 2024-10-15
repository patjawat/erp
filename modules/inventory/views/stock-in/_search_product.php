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
        'action' => ['product-list', 'id' => $model->id],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div>
<div class="d-flex flex-row gap-3">
  <div class="w-50">
  
  <?php echo $form->field($searchModel, 'q')->textInput(['placeholder' => 'ค้นหา...'])->label('คำค้นหา') ?>
  </div>
  <div class="w-50">
  <?php
  
        echo $form->field($searchModel, 'data_json[asset_type]')->widget(Select2::classname(), [
            'data' => $searchModel->ListProductType(),
            'options' => ['placeholder' => ($model->data_json['asset_type'] ?  $model->data_json['asset_type_name'] : 'ระบุประเภท'),
            'disabled' => ($model->data_json['asset_type'] ?  true : false)
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
