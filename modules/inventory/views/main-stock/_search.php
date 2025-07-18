<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\modules\inventory\models\Warehouse;

$getWarehouse = Yii::$app->session->get('selectMainWarehouse');
/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */

$warehouse = Yii::$app->session->get('warehouse');
$warehouseModel = app\modules\inventory\models\Warehouse::findOne($warehouse['warehouse_id']);
$item = $warehouseModel->data_json['item_type'];
$product = ArrayHelper::map(Categorise::find()
->where(['name' => 'asset_type','category_id' => 4])
->andWhere(['IN', 'code', $item])
->all(), 'code', 'title');

// echo "<pre>";
// print_r($item);
// echo "</pre>";

?>

<div class="order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['/inventory/main-stock/store'],
        'method' => 'get',
        'id' => 'form-search',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="d-flex gap-2">
    <div>
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>
    </div>
    <div>
    <?php
  
  echo $form->field($model, 'asset_type')->widget(Select2::classname(), [
      'data' => $product,
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
            ],
            ])->label(false);
            ?>
    </div>
    <div>
        <?= $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(),'id','warehouse_name'),
            'options' => ['placeholder' => 'เลือกคลัง'],
            'disabled' => ($getWarehouse ?  true : false),
            'pluginEvents' => [
                "select2:unselect" => "function() { 
                    $(this).submit()
                    // $.ajax({
                        //     type: 'get',
                        //     url: '".Url::to(['/inventory/main-stock/clear-warehouse'])."',
                        //     dataType: 'json',
                        //     success: function (res) {
                            //               $.pjax.reload({container:'#inventory-container', history:false});
                            //     }
                            // });
                            }",
                            "select2:select" => "function() {
                                $(this).submit()
                                
                                }",
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'width' => '200px',
                            ],
                            ])->label(false);
                            
                                    ?>

</div>
</div>


    <!-- <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div> -->

    <?php ActiveForm::end(); ?>

</div>
