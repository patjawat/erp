<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;
use app\modules\inventory\models\Warehouse;
use yii\helpers\ArrayHelper;

$getWarehouse = Yii::$app->session->get('selectMainWarehouse');
/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
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
<div class="row justify-content-end">
    <div class="col-6">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>
    </div>
    <div class="col-6">
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
