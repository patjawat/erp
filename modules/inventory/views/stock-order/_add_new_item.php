<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\modules\inventory\models\Stock;
use app\modules\sm\models\Product;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\web\View;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);


?>
   <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/inventory/stock-in/create-validator']
    ]); ?>

<div class="row">
    <div class="col-12">

                <?php

try {
    $initProduct =  Product::find()->where(['code' => $model->asset_item])->one()->Avatar(false);
} catch (\Throwable $th) {
    $initProduct = '';
}

$datas = [];
foreach($order->getItems() as $item){
    $data[] = $item->lot_number;
}

$query = Stock::find()->where(['warehouse_id' => $order->warehouse_id])->where(['not in', 'lot_number', $data])->all();


        echo $form->field($model, 'lot_number')->widget(Select2::classname(), [
            'options' => ['placeholder' => 'เลือก ...'],
            'data' => ArrayHelper::map($query,'lot_number',function($model){
                        $product =  Product::findOne(['code' => $model->asset_item]);
                return $product->title.' ('.$model->lot_number.') เหลือ '.$model->qty;
               
            }),
            'size' => Select2::LARGE,
            'pluginOptions' => [
                'dropdownParent' => '#main-modal',
                'allowClear' => true,
            ],
        ])->label('วัสดุ')
    ?>
    </div>

</div>
<div class="row">
    <div class="col-6">
        <?= $form->field($model, 'qty')->textInput(['type' => 'number', 'maxlength' => 2])->label('จำนวนเบิก'); ?>
    </div>
</div>
<?= $form->field($model, 'warehouse_id')->hiddenInput()->label(false);?>
<?= $form->field($model, 'code')->hiddenInput()->label(false); ?>


<div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    </div>

    <?php ActiveForm::end(); ?>


    <?php
$js = <<< JS

                            \$('#form').on('beforeSubmit', function (e) {
                                var form = \$(this);
                                \$.ajax({
                                    url: form.attr('action'),
                                    type: 'post',
                                    data: form.serialize(),
                                    dataType: 'json',
                                    success: async function (response) {
                                        form.yiiActiveForm('updateMessages', response, true);
                                        if(response.status == 'error'){
                                            Swal.fire({
                                            icon: "warning",
                                            title: "เกินจำนวน",
                                            showConfirmButton: false,
                                            timer: 1500,
                                        });
                                        return false;
                }

                                        if(response.status == 'success') {
                                            closeModal()
                                            success()
                                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                        }
                                    }
                                });
                                return false;
                            });
            JS;
$this->registerJS($js)
?>