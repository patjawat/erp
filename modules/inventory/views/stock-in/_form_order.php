<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
/** @var yii\widgets\ActiveForm $form */

?>



<?php
     echo $form->field($model, 'vendor_id')->widget(Select2::classname(), [
                                        'data' => $model->listVendor(),
                                        'options' => ['placeholder' => 'เลือกผู้จำหน่วย/บริจาค'],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 
                                              
                                            }",
                                            "select2:select" => "function() {
                                                 var data = $(this).select2('data')[0]
                                                $('#stockevent-data_json-vendor_name').val(data.text)
                                        }",],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            'dropdownParent' => '#main-modal',
                                        ],
                                    ])->label();
                                    
                                    ?>

<div class="row">
    <div class="col-6">

        <?php

echo $form->field($model, 'data_json[asset_type]')->widget(Select2::classname(), [
    'data' => $model->ListAssetType(),
    //   'options' => ['placeholder' => ($model->category_id ?  $model->data_json['order_type_name'] : 'ระบุประเภท'),
    'options' => ['placeholder' => 'ระบุประเภท',
    'disabled' => ($model->category_id ?  true : false)
],
'pluginOptions' => [
    'allowClear' => true,
    'dropdownParent' => '#main-modal',
],
'pluginEvents' => [
    "select2:select" => "function(result) { 
        var data = $(this).select2('data')[0]
        $('#stockevent-data_json-asset_type_name').val(data.text)
        }",
        ]
        ])->label('ประเภทวัดุ');
        ?>

    </div>
    <div class="col-6">

        <?php

echo $form->field($model, 'data_json[item_type]')->widget(Select2::classname(), [
    'data' =>  ['รายการปกติ' => 'รายการปกติ', 'ยอดยกมา' => 'ยอดยกมา', 'ของแถม' => 'ของแถม','บริจาค' => 'บริจาค'], 
    'options' => ['placeholder' => 'ระบุประเภทการรับเข้า',
    'disabled' => ($model->category_id ?  true : false)
],
'pluginOptions' => [
    'allowClear' => true,
    'dropdownParent' => '#main-modal',
],
'pluginEvents' => []
        ])->label('ประเภทการรับเข้า');
        ?>


    </div>

</div>
<?= $form->field($model, 'data_json[do_number]')->textInput()->label('เลขที่ส่งสินค้า');?>

<?=$form->field($model, 'data_json[receive_date]')->textInput()->label('วันที่รับเข้า');?>

<?= $form->field($model, 'auto_lot')->checkbox(['custom' => true, 'switch' => true,'checked' => true])->label('ล็อตอัตโนมัติ');?>


<?php
$js = <<< JS

    thaiDatepicker('#stockevent-data_json-receive_date')
    console.log($("#stockevent-auto_lot").val())
    if($("#stockevent-auto_lot").val()){
    $( "#stockevent-auto_lot" ).prop( "checked", localStorage.getItem('lot_auto') == 1 ? true : false );



    }


    $("#stockevent-auto_lot").change(function() {
        //ตั้งค่า Run Lot Auto
        if(this.checked) {
            console.log('lot_auto');
            localStorage.setItem('lot_auto',1);
         

        }else{
            localStorage.setItem('lot_auto',0);
          
        }
    });


    JS;
$this->registerJS($js)
?>