<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
.right-setting {
    width: 500px !important;
}
</style>
<div class="d-flex justify-content-between align-items-center">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
        'options' => [
            'data-pjax' => 0
        ],
    ]); ?>

    <div class="d-flex justify-content-start gap-2 align-items-center align-middle">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>
        <?=$form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกช่วงวันที่','style' => 'width:150px'])->label(false);?>
        <?=$form->field($model, 'date_end')->textInput(['placeholder' => 'ถึงวันที่','style' => 'width:150px'])->label(false);?>

        <?=$form->field($model, 'order_type_name')->widget(Select2::classname(), [
        'data' => ArrayHelper::map($model->ListItemTypeOrder(),'id','name'),
        'options' => ['placeholder' => 'ประเภททั้งหมด'],
        'pluginOptions' => [
                                        'width' => '400px',
                                        'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        'select2:select' => "function(result) { 
                                            $(this).submit()
                                            }",
                                            'select2:unselecting' => "function(result) { 
                                                $(this).submit()
                                                }",
                                                
                                                ]
                                                ])->label(false);
                                                ?>
        <?=$form->field($model, 'status')->widget(Select2::classname(), [
        'data' => ArrayHelper::map($model->ListStatus(),'code','title'),
        'options' => ['placeholder' => 'สถานะทั้งหมด'],
        'pluginOptions' => [
            'width' => '400px',
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        'select2:select' => "function(result) { 
                                            $(this).submit()
                                            }",
                                            'select2:unselecting' => "function(result) { 
                                                $(this).submit()
                                                }",
                                                
                                                ]
                                                ])->label(false);
                                                ?>
                                                  <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary']);?>
                                                  



   <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter">
                <i class="fa-solid fa-filter"></i>
            </button>
    </div>

    <div class="collapse mt-3" id="collapseFilter">

 <?php
                
                echo $form->field($model, 'date_between')->widget(Select2::classname(), [
                    'data' => [
                        'pr_create_date' => 'วันที่ขอซื้อ',
                        'po_date' => 'วันที่สั่งซื้อ',
                        'gr_date' => 'วันที่ตรวจรับ'
                    ],
                    'options' => ['placeholder' => 'ระบุประเภท'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                    'pluginEvents' => [
                        'select2:select' => "function(result) { 
                            $(this).submit()
                            }",
                            ]
                            ])->label('ประเภท');
                            ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php


$js = <<<JS

thaiDatepicker('#ordersearch-date_start,#ordersearch-date_end')
$(".filter-emp").on("click", function(){
  $("#filter-emp").addClass("show");
  localStorage.setItem('right-setting','show')
})

// $(".filter-emp-close").on("click", function(){
//     $(".right-setting").removeClass("show");
//     localStorage.setItem('right-setting','hide')
// })

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
 


JS;
$this->registerJS($js, View::POS_END)
?>