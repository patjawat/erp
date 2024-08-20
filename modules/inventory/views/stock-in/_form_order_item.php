<?php
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


$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
        // console.log(repo);
        var markup =
    '<div class="row">' +
        '<div class="col-12">' +
            '<span>' + repo.avatar + '</span>' +
        '</div>' +
    '</div>';
        if (repo.description) {
          markup += '<p>' + repo.avatar + '</p>';
        }
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) {
        return repo.avatar || repo.avatar;
    }
    JS;

// Register the formatting script
$this->registerJs($formatJs, View::POS_HEAD);

// script to parse the results into the format expected by Select2
$resultsJs = <<< JS
    function (data, params) {
        params.page = params.page || 1;
        return {
            results: data.results,
            pagination: {
                more: (params.page * 30) < data.total_count
            }
        };
    }
    JS;

?>
<style>
.col-form-label {
    text-align: end;
}
    .select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #eaecee !important;
    color: #fff;
}
:not(.form-floating) > .input-lg.select2-container--krajee-bs5 .select2-selection--single, :not(.form-floating) > .input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 12px) !important;
}
.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #eaecee !important;
    color: #3F51B5;
}
</style>


<div class="row">
    <div class="col-12">

                <?php

try {
    $initProduct =  Product::find()->where(['code' => $model->asset_item])->one()->Avatar(false);
} catch (\Throwable $th) {
    $initProduct = '';
}
        echo $form->field($model, 'asset_item')->widget(Select2::classname(), [
            'initValueText' => $initProduct,
            'options' => ['placeholder' => 'เลือกวัสดุ ...'],
            'size' => Select2::LARGE,
            'pluginEvents' => [
                'select2:unselect' => 'function() {
                $("#order-data_json-board_fullname").val("")

         }',
                'select2:select' => 'function() {
                var fullname = $(this).select2("data")[0].fullname;
                var position_name = $(this).select2("data")[0].position_name;
                $("#order-data_json-board_fullname").val(fullname)
                $("#order-data_json-position_name").val(position_name)
               
         }',
            ],
            'pluginOptions' => [
                'dropdownParent' => '#main-modal',
                'allowClear' => true,
                'minimumInputLength' => 1,
                'ajax' => [
                    'url' => Url::to(['/depdrop/product']),
                    'dataType' => 'json',
                    'delay' => 250,
                    'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                    'processResults' => new JsExpression($resultsJs),
                    'cache' => true,
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                'templateResult' => new JsExpression('formatRepo'),
            ],
        ])->label('วัสดุ')
    ?>
                <?php
     echo $form->field($model, 'data_json[item_type]')->radioList(
         ['รายการปกติ' => 'รายการปกติ', 'ยอดยกมา' => 'ยอดยกมา', 'ของแถม' => 'ของแถม','บริจาค' => 'บริจาค'], 
         ['custom' => true, 'inline' => true, 'id' => 'custom-radio-list']
         )->label('ประเภท');
         ?>

    </div>
    <div class="col-3">
        <div style="margin-top:-13px">


</div>
    </div>
</div>
<div class="row">
    <div class="col-6">
<?=$form->field($model, 'data_json[mfg_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันผลิต');
                ?>

            <?=$form->field($model, 'data_json[exp_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันหมดอายุ');
                ?>
     <?= $form->field($model, 'unit_price')->textInput(['type' => 'number', 'maxlength' => 2])->label('ราคาต่อหน่วย'); ?>
    </div>
    <div class="col-6">
    <?= $form->field($model, 'auto_lot')->checkbox(['custom' => true, 'switch' => true,'checked' => true])->label('ล็อตอันโนมัติ');?>
    <?= $form->field($model, 'lot_number')->textInput()->label(false); ?>
        <?= $form->field($model, 'qty')->textInput(['type' => 'number', 'maxlength' => 2])->label('จำนวนรับเข้า'); ?>
        <?= $form->field($model, 'code')->hiddenInput()->label(false); ?>
   

    </div>
</div>


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

        \$('#form-order-item').on('beforeSubmit', function (e) {
                var form = \$(this);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        form.yiiActiveForm('updateMessages', response, true);
                        if(response.status == 'success') {
                            closeModal()
                            success()
                            try {
                                // loadRepairHostory()
                            } catch (error) {
                                
                            }
                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });



            var thaiYear = function (ct) {
        var leap=3;  
        var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];  
        if(ct){  
            var yearL=new Date(ct).getFullYear()-543;  
            leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;  
            if(leap==2){  
                dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];  
            }  
        }              
        this.setOptions({  
            i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,  
        })                
    };    
     
   
    $("#StockEvent-data_json-mfg_date").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    }); 
    
    $("#StockEvent-data_json-exp_date").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });   


    JS;
$this->registerJS($js)
?>