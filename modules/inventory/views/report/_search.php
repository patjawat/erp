<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\modules\inventory\models\Warehouse;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
$months = [
    10 => "ตุลาคม",
    11 => "พฤศจิกายน",
    12 => "ธันวาคม",
    1 => "มกราคม",
    2 => "กุมภาพันธ์",
    3 => "มีนาคม",
    4 => "เมษายน",
    5 => "พฤษภาคม",
    6 => "มิถุนายน",
    7 => "กรกฎาคม",
    8 => "สิงหาคม",
    9 => "กันยายน"
];
?>

    <?php $form = ActiveForm::begin([
        'action' => ['/inventory/report/index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="d-flex gap-3">
<?= $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(),'id','warehouse_name'),
            // 'data' => ArrayHelper::map(Warehouse::find()->all(),'id','warehouse_name'),
            'options' => ['placeholder' => 'เลือกคลัง'],
            'pluginEvents' => [
                "select2:unselect" => "function() { 
                    $(this).submit()
                            }",
                            "select2:select" => "function() {
                                $(this).submit()
                                }",
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'width' => '300px',
                            ],
                            ])->label('คลัง');
                            
                                    ?>


<?php
echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
    // 'data' => $model->ListGroupYear(),
    'data' => [2567 => '2567',2568 => '2568'],
    'options' => ['placeholder' => 'ปีงบประมาณ'],
    'pluginOptions' => [
        'allowClear' => true,
        'width' => '200px',
    ],
    'pluginEvents' => [
        'select2:select' => "function(result) { 
             $(this).submit()
            }",
            "select2:unselecting" => "function() {
                $(this).submit()
                }",
                ]
                ])->label('ปีงบประมาน');
                ?>

<?=$form->field($model, 'date_start')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('ตั้งแต่วันที่');
                ?>
                <?=$form->field($model, 'date_end')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('ถึงวันที่');
                ?>
                
</div>
    <?php ActiveForm::end(); ?>

<?php
$js = <<< JS

    $('#stockeventsearch-date_start').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
        $(this).submit();
    });
    
    $('#stockeventsearch-date_end').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
        $(this).submit();
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
     
    $("#stockeventsearch-date_start").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });       
    $("#stockeventsearch-date_end").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });  
 
 
JS;
$this->registerJS($js);


?>
