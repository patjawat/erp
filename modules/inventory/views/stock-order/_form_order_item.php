<?php

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

<div class="row">
    <div class="col-12">

                <?php

try {
    $initProduct =  Product::find()->where(['code' => $model->asset_item])->one()->Avatar(false);
} catch (\Throwable $th) {
    $initProduct = '';
}
        echo $form->field($model, 'asset_item')->widget(Select2::classname(), [
            'options' => ['placeholder' => 'เลือก ...'],
            'data' => ArrayHelper::map(Stock::find()->where(['warehouse_id' => $model->warehouse_id])
                        ->andWhere(['>','qty',0])
                        ->all(),'asset_item',function($model){
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
        <?= $form->field($model, 'data_json[req_qty]')->textInput(['type' => 'number', 'maxlength' => 2])->label('จำนวนเบิก'); ?>
    </div>
</div>
<?= $form->field($model, 'warehouse_id')->hiddenInput()->label(false);?>
<?= $form->field($model, 'code')->hiddenInput()->label(false); ?>
<?php
$js = <<< JS

    console.log($("#StockEvent-auto_lot").val())
    if($("#StockEvent-auto_lot").val()){
    $( "#StockEvent-auto_lot" ).prop( "checked", localStorage.getItem('lot_auto') == 1 ? true : false );
    $('#StockEvent-lot_number').prop('disabled',localStorage.getItem('lot_auto') == 1 ? true : false );

    if(localStorage.getItem('fsn_auto') == true)
    {
        $('#StockEvent-lot_number').val('สร้างล็อตผลิตอัตโนมัติ')
    }

    }

    $("#StockEvent-auto_lot").change(function() {
        //ตั้งค่า Run Lot Auto
        if(this.checked) {
            console.log('lot_auto');
            localStorage.setItem('lot_auto',1);
            $('#StockEvent-lot_number').prop('disabled',this.checked);
            $('#StockEvent-lot_number').val('สร้างล็อตผลิตอัตโนมัติ')

            $('#StockEvent-lot_number').prop('disabled',this.checked);
            $('#StockEvent-lot_number').val('สร้างล็อตผลิตอัตโนมัติ')

        }else{
            localStorage.setItem('lot_auto',0);
            $('#StockEvent-lot_number').prop('disabled',this.checked);
            $('#StockEvent-lot_number').val('')

            $('#StockEvent-lot_number').prop('disabled',this.checked);
            $('#StockEvent-lot_number').val('')

            console.log('lot_manual');
        }
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