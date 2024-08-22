<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
$warehouse = Yii::$app->session->get('search_warehouse_id');
/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
   .field-stocksearch-warehouse_id{
        min-width: 220px;
    }
</style>
<div class="order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['product'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="d-flex justofy-content-between gap-2">
        <?= $form->field($model, 'q')->label('ค้นหา') ?>
        <?= $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
                                        'data' => $model->listWarehouseMe(),
                                        
                                        'options' => ['placeholder' => 'เลือกคลังวัสดุ','value' => $warehouse,'style' => 'width:400px;'],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 
                                                    $.ajax({
                                                        type: 'get',
                                                        url: '".Url::to(['/me/store/remove-warehouse'])."',
                                                        dataType:'json',
                                                        success: function (response) {
                                                            
                                                        }
                                                    });
                                                $(this).submit()
                                            }",
                                            "select2:select" => "function() {
                                               $(this).submit()
                                               
                                        }",],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        'disabled' => $warehouse ? true : false
                                        ],
                                    ])->label('คลังวัสดุ');
                                    
                                    ?>
    </div>

    <!-- <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div> -->

    <?php ActiveForm::end(); ?>

</div>
