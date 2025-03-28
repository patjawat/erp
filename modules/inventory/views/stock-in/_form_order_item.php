<?php
use yii\web\View;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\sm\models\Product;
use iamsaint\datetimepicker\Datetimepicker;

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
    <?= $form->field($model, 'qty')->textInput(['type' => 'number', 'maxlength' => 5,'step' => '0.01'])->label('จำนวนรับเข้า'); ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'auto_lot')->checkbox(['custom' => true, 'switch' => true,'checked' => true])->label('ล็อตอัตโนมัติ');?>
        <?= $form->field($model, 'lot_number')->textInput()->label(false); ?>
        
        <?= $form->field($model, 'unit_price')->textInput(['type' => 'number','step' => '0.00001',])->label('ราคาต่อหน่วย'); ?>
        <?= $form->field($model, 'code')->hiddenInput()->label(false); ?>
   

    </div>
    <div class="col-12">
    <?php
     echo $form->field($model, 'data_json[item_type]')->radioList(
         ['รายการปกติ' => 'รายการปกติ', 'ยอดยกมา' => 'ยอดยกมา', 'ของแถม' => 'ของแถม','บริจาค' => 'บริจาค'], 
         ['custom' => true, 'inline' => true, 'id' => 'custom-radio-list']
         )->label('ประเภทการรับเข้า');
         ?>

    </div>
</div>


<?php
$js = <<< JS

    console.log($("#stockevent-auto_lot").val())
    if($("#stockevent-auto_lot").val()){
    $( "#stockevent-auto_lot" ).prop( "checked", localStorage.getItem('lot_auto') == 1 ? true : false );
    $('#stockevent-lot_number').prop('disabled',localStorage.getItem('lot_auto') == 1 ? true : false );

    if(localStorage.getItem('fsn_auto') == true)
    {
        $('#stockevent-lot_number').val('สร้างล็อตผลิตอัตโนมัติ')
    }

    }

    $("#stockevent-auto_lot").change(function() {
        //ตั้งค่า Run Lot Auto
        if(this.checked) {
            console.log('lot_auto');
            localStorage.setItem('lot_auto',1);
            $('#stockevent-lot_number').prop('disabled',this.checked);
            $('#stockevent-lot_number').val('สร้างล็อตผลิตอัตโนมัติ')

            $('#stockevent-lot_number').prop('disabled',this.checked);
            $('#stockevent-lot_number').val('สร้างล็อตผลิตอัตโนมัติ')

        }else{
            localStorage.setItem('lot_auto',0);
            $('#stockevent-lot_number').prop('disabled',this.checked);
            $('#stockevent-lot_number').val('')

            $('#stockevent-lot_number').prop('disabled',this.checked);
            $('#stockevent-lot_number').val('')

            console.log('lot_manual');
        }
    });

    $('#stockevent-qty').keyup(function (e) { 
        
    if (e.keyCode === 8) { // Check if the key pressed is Backspace
        // Your code here
        // $('#stockevent-data_json-po_qty').val();
        var qty = $('#stockevent-data_json-po_qty').val();
        $('#stockevent-qty_check').val(qty)
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
     
   
    $("#stockevent-data_json-mfg_date").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    }); 
    
    $("#stockevent-data_json-exp_date").datetimepicker({
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