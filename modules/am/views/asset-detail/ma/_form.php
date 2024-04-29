<?php

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use app\components\CategoriseHelper;
use app\components\UserHelper;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use unclead\multipleinput\MultipleInput;
use iamsaint\datetimepicker\Datetimepicker;
use unclead\multipleinput\MultipleInputColumn;
/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */




?>
<?php
$user =  UserHelper::getUser();
$emp =  UserHelper::GetEmployee();
echo "<pre>";
// print_r($asset->assetItem->data_json['ma_items']);
echo "</pre>";
/* echo $user->id;
echo '<br>';
echo $emp->fullname; */
?>
<?php 
// $items = CategoriseHelper::CategoriseByCodeName($model->asset_item,"asset_item");
// $category = CategoriseHelper::CategoriseByCodeName(substr($model->code, 0, strpos($model->code, '/')),"asset_item");
// if (isset($category->data_json["ma_items"])){
//     $items = $category->data_json["ma_items"];
//     $items = array_combine(array_map(function ($asset) use ($items) {
//         return $items[$asset]["item_name"];
//     },range(0, count($items) - 1)),
//     array_map(function ($asset) use ($items) {
//         return $items[$asset]["item_name"];
//     },range(0, count($items) - 1)));
// }else{
//     $items = [];
// }
?>
    <div class="col-xs-6 col-sm-4 col-md-4">
        </div>
        <div class="col-xs-12 col-sm-4 col-md-4">
            <?php if(!($model->isNewRecord)){?>
                <?= $form->field($model, 'data_json[endorsee]')->hiddenInput(['value' => $emp->user_id])->label(false) ?>
                <?= $form->field($model, 'data_json[endorsee_name]')->hiddenInput(['value' => $emp->fullname])->label(false) ?>
                <?= $form->field($model, 'data_json[checker]')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[checker_name]')->hiddenInput()->label(false) ?>
                <?php }?>
        </div>
    </div>
    <?php if($model->isNewRecord){?>
            <?= $form->field($model, 'data_json[checker]')->hiddenInput(['value' => $emp->user_id])->label(false) ?>
            <?= $form->field($model, 'data_json[checker_name]')->hiddenInput(['value' => $emp->fullname])->label(false) ?>
            <?= $form->field($model, 'data_json[status]')->hiddenInput(['value'=>'รอการตวรจสอบ'])->label(false) ?>
        <?php }?>
<div class="row">
<div class="col-sm-6 col-md-6">
    <?=$form->field($model, 'date_start')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันที่');             ?>  
        <?php if(!($model->isNewRecord)){?>
            <?= $form->field($model, 'data_json[status]')->radioList(['ผ่าน' => 'ผ่าน', 'ไม่ผ่าน' => 'ไม่ผ่าน','รอการตวรจสอบ'=>'รอการตวรจสอบ'],['inline' => true])->label("ผลการตรวจ") ?>
        <?php }?>
    </div>
    <div class="col-sm-6 col-md-6 d-flex justify-content-center align-items-center">
        <?php if($model->isNewRecord){?>
            <i class="bi bi-check2-circle text-primary fs-5" style="margin-right:5px;"></i><span class="fw-semibold" style="margin-right:5px;"> ผู้ตรวจเช็คอุปกรณ์ </span><?= $emp->fullname ?>
        <?php }else{?>
            <i class="bi bi-check2-circle text-primary fs-5" style="margin-right:5px;"></i><span class="fw-semibold" style="margin-right:5px;"> หัวหน้ารับรอง </span><?= $emp->fullname ?>
        <?php }?>
    </div>
</div>
<div >
    <?= $form->field($model, 'data_json[description]')->textarea(['rows' => '3'])->label("หมายเหตุ") ?>
</div>

      <?= $form->field($model, 'name')->hiddenInput(['value'=>'ma'])->label(false) ?>
      <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>
<div class="card-body mt-3">
      <div class="table-responsive">
      

      
                <?php

// echo $form->field($model,'ma')->widget(MultipleInput::class,[
//     'allowEmptyList'    => false,
//     'enableGuessTitle'  => true,
//     'addButtonPosition' => MultipleInput::POS_HEADER,
//     'addButtonOptions' => [
//         'class' => 'btn btn-sm btn-primary',
//         'label' => '<i class="fa-solid fa-circle-plus"></i>' // also you can use html code
//     ],
//     'removeButtonOptions' => [
//         'class' => 'btn btn-sm btn-danger',
//         'label' => '<i class="fa-solid fa-trash"></i>'
//     ],
//     'columns' => [
//         [
//             'name'  => 'item',
//             'type' => Select2::class,
//             'headerOptions' => [
//                 'class' => 'table-light', 
//                 'style' => 'width: 45%;',
//             ],
//             'title' => 'รายการที่ตรวจเช็ค',
//             'options' => [
//                 'data' => $items
//             ]
//         ],
//         [
//             'name'  => 'ma_status',
//             'type'  => 'dropDownList',
//             'headerOptions' => [
//                 'class' => 'table-light',
//                 'style' => 'width: 10%;',
//             ],
//             'defaultValue' => 'ปกติ',
//             'items' => [
//                 'สูง'=> 'สูง',
//                 'ปกติ' => 'ปกติ',
//                 'ต่ำ' => 'ต่ำ'
//             ],
//             'title' => 'สถานะ',
//         ], 
//         [
//             'name'  => 'comment',
//             'headerOptions' => [
//                 'class' => 'table-light', 
//                 'style' => 'width: 45%;',
//             ],
//             'title' => 'หมายเหตุ',
//         ], 
        
 	
        
        
//     ]

// ])->label(false);
?>
                </div>
                </div>
  


                <?php
$js = <<<JS

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
     
    $("#assetdetail-data_json-date_start").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });       
 
   
 
JS;
$this->registerJS($js, View::POS_END)
?>