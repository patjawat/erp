<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;
use app\modules\inventory\models\Warehouse;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['/inventory/withdraw/product-list'],
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
                                        'data' => ArrayHelper::map(Warehouse::find()->all(),'id','warehouse_name'),
                                        'options' => ['placeholder' => 'เลือกรายการพัสดุ'],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 
                                                $.ajax({
                                                    type: 'get',
                                                    url: '".Url::to(['/inventory/warehouse/clear-select-warehouse'])."',
                                                    dataType: 'json',
                                                    success: function (res) {
                                                            //   $.pjax.reload({container:'#inventory', history:false});
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
