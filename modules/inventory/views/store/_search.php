<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\modules\inventory\models\Warehouse;


?>

<div class="order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['/inventory/store/index'],
        'method' => 'get',
        'id' => 'form-search',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="row justify-content-end">
    <div class="col-6">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label('การค้นหา') ?>
    </div>
    <div class="col-6">
    <?= $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
                                        'data' => ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(),'id','warehouse_name'),
                                        'options' => ['placeholder' => 'เลือกรายการพัสดุ'],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 
                                                $.ajax({
                                                    type: 'get',
                                                    url: '".Url::to(['/inventory/warehouse/clear-select-warehouse'])."',
                                                    dataType: 'json',
                                                    success: function (res) {
                                                              $.pjax.reload({container:'#inventory', history:false});
                                                    }
                                                });
                                            }",
                                            "select2:select" => "function() {
                                                // console.log($(this).val());
                                                $.ajax({
                                                    type: 'get',
                                                    url: '".Url::to(['/inventory/store/select-warehouse'])."',
                                                    data: {id: $(this).val()},
                                                    dataType: 'json',
                                                    success: function (res) {
                                                            //   $.pjax.reload({container:'#inventory', history:false});
                                                              $('#form-search').submit()
                                                    }
                                                });
                                        }",],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        ],
                                    ])->label('คลัง');
                                    
                                    ?>

</div>
</div>


    <!-- <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div> -->

    <?php ActiveForm::end(); ?>

</div>
