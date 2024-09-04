<?php

use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\sm\models\Product;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\form\ActiveForm;
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
        'validationUrl' => ['/inventory/stock-order/update-lot-validator']
    ]); ?>


<div class="row">
    <div class="col-12">

                <?php
$lot = ArrayHelper::map(Stock::find()->where(['asset_item' => $model->asset_item])->andWhere(['>','qty',0])->all(),'lot_number',function($model){
    return $model->lot_number.' คงเหลือ : '.$model->qty;
});

        echo $form->field($model, 'lot_number')->widget(Select2::classname(), [
            'options' => ['placeholder' => 'เลือกล็อตผลิต ...'],
            'data' => $lot,
            'size' => Select2::LARGE,
            'pluginOptions' => [
                'dropdownParent' => '#main-modal',
                'allowClear' => true,
            ],
        ])->label('ล็อตผลิต')
    ?>
    </div>
    <div class="col-3">
        <div style="margin-top:-13px">


</div>
    </div>
</div>
<div class="row">
    
    <div class="col-6">
        <?= $form->field($model, 'qty')->textInput(['type' => 'number', 'maxlength' => 2])->label('จำนวน'); ?>
    </div>
</div>


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
                                        console.log(response);
                                        
                                        form.yiiActiveForm('updateMessages', response, true);
                                        if(response.status == 'success') {
                                            closeModal()
                                            success()
                                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                        }
                                    }
                                });
                                return false;
                            });


    $('#StockEvent-qty').keyup(function (e) { 
        
    if (e.keyCode === 8) { // Check if the key pressed is Backspace
        // Your code here
        // $('#StockEvent-data_json-po_qty').val();
        var qty = $('#StockEvent-data_json-po_qty').val();
        $('#StockEvent-qty_check').val(qty)
    }
    });



    JS;
$this->registerJS($js)
?>