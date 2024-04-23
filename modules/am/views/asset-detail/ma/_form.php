<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\CategoriseHelper;

use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */




?>

<?php 
$category = CategoriseHelper::CategoriseByCodeName(substr($model->code, 0, strpos($model->code, '/')),"asset_item");
$items = $category->data_json["ma_items"];
?>

<div class="row">
    <div class="col-xs-6 col-sm-4 col-md-4">
    <?php /* echo '<label class="control-label">วันที่</label>';
      echo $form->field($model_form, 'date_start')->widget(\kartik\date\DatePicker::class, [
                        'type' => \kartik\date\DatePicker::TYPE_COMPONENT_PREPEND,
                        'options' => ['placeholder' => 'เลือกวันที่'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                        ]
                        ])->label(false); */ ?>
      <?=$form->field($model, 'date_start')->widget(\yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('วันที่');             ?>  
    </div>
    <div class="col-xs-6 col-sm-4 col-md-4">
        <?= $form->field($model, 'data_json[checker]')->textInput(['maxlength' => true])->label("ผู้ตรวจเช็คอุปกรณ์") ?>
    </div>
    <div class="col-xs-12 col-sm-4 col-md-4">
        <?= $form->field($model, 'data_json[endorsee]')->textInput(['maxlength' => true])->label("หัวหน้ารับรอง") ?>
    </div>
</div>
<div class="mt-2">
    <?php if($model->isNewRecord):?>
    <?= $form->field($model, 'data_json[status]')->hiddenInput(['value'=>'รอการตวรจสอบ'])->label(false) ?>
    <?php else:?>
        <?= $form->field($model, 'data_json[status]')->radioList(['ผ่าน' => 'ผ่าน', 'ไม่ผ่าน' => 'ไม่ผ่าน','รอการตวรจสอบ'=>'รอการตวรจสอบ'])->label("ผลการตรวจ") ?>
    <?php endif;?>
</div>
<div class="mt-2">
    <?= $form->field($model, 'data_json[description]')->textarea(['rows' => '3'])->label("หมายเหตุ") ?>
</div>

      <?= $form->field($model, 'name')->hiddenInput(['value'=>'ma'])->label(false) ?>
      <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>
<div class="card-body mt-3">
      <div class="table-responsive">
      

      
                <?php

echo $form->field($model,'ma')->widget(MultipleInput::class,[
    'allowEmptyList'    => false,
    'enableGuessTitle'  => true,
    'addButtonPosition' => MultipleInput::POS_HEADER,
    'addButtonOptions' => [
        'class' => ' btn-sm btn btn-success',
        'label' => '<i class="bi bi-plus fw-bold" ></i>',
    ],
    'removeButtonOptions' => [
        'class' => 'btn-sm btn btn-danger',
        'label' => '<i class="bi bi-x fs-5 fw-bold"></i>' // also you can use html code
    ],
    'columns' => [
        [
            'name'  => 'item',
            'type' => Select2::class,
            'headerOptions' => [
                'class' => 'table-light', 
                'style' => 'width: 45%;',
            ],
            'title' => 'รายการที่ตรวจเช็ค',
            'options' => [
                'data' => array_combine(array_map(function ($asset) use ($items) {
                    return $items[$asset]["item_name"];
                },range(0, count($items) - 1)),
                array_map(function ($asset) use ($items) {
                    return $items[$asset]["item_name"];
                },range(0, count($items) - 1)))
/*                array_map(function ($asset) {
                    return CategoriseHelper::Id($id_category)->one()->data_json["ma_items"][$asset]["item_name"];
                },range(0, count(CategoriseHelper::Id($id_category)->one()->data_json["ma_items"])-1)) */
                #CategoriseHelper::Id($id_category)->one()->data_json["ma_items"]
            ]
        ],
        [
            'name'  => 'ma_status',
            'type'  => 'dropDownList',
            'headerOptions' => [
                'class' => 'table-light',
                'style' => 'width: 10%;',
            ],
            'defaultValue' => 1,
            'items' => [
                'สูง'=> 'สูง',
                'ปกติ' => 'ปกติ',
                'ต่ำ' => 'ต่ำ'
            ],
            'title' => 'สถานะ',
        ], 
        [
            'name'  => 'comment',
            'headerOptions' => [
                'class' => 'table-light', 
                'style' => 'width: 45%;',
            ],
            'title' => 'หมายเหตุ',
        ], 
        
 	
        
        
    ]

])->label(false);

?>
                </div>
                </div>
  
