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
        // 'action' => ['/inventory/store/index'],
        'method' => 'get',
        'id' => 'form-search',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
<div class="d-flex gap-2">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>
    <?= $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
                                        'data' => ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'SUB'])->all(),'id','warehouse_name'),
                                        'options' => ['placeholder' => 'เลือกคลังพัสดุ'],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            'width' => '300px',
                                            ],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 
                                                $(this).submit()
                                            }",
                                            "select2:select" => "function() {
                                            $(this).submit()
                                              
                                        }",],
                               
                                    ])->label(false);
                                    
                                    ?>

<?= $form->field($model, 'order_status')->widget(Select2::classname(), [
                                        'data' => $model->listStatus(),
                                        'options' => ['placeholder' => 'เลือกสถานะ'],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            'width' => '150px',
                                            ],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 
                                                $(this).submit()
                                            }",
                                            "select2:select" => "function() {
                                            $(this).submit()
                                              
                                        }",],
                               
                                    ])->label(false);
                                    
                                    ?>

</div>


    <!-- <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div> -->

    <?php ActiveForm::end(); ?>

</div>
