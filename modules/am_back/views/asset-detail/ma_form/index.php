<?php
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use app\modules\am\models\Asset;
use app\components\CategoriseHelper;
use yii\helpers\Html;
use app\modules\am\models\AssetDetail;
use yii\helpers\Url;
use yii\widgets\Pjax;

$model_form = new AssetDetail()
?>
<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>


<?php 
$category = CategoriseHelper::Id($id_category)->one();
$items = $category->data_json["ma_items"];
?>

<?php 
     $list = array_map(function ($asset) use ($items) {
        return $items[$asset]["item_name"];
    },range(0, count($items) - 1));
 ?>
<?php $form = ActiveForm::begin(['id' => 'form-ma']); ?>
<?= $form->field($model_form, 'code')->textInput(['maxlength' => true])->label("หัวหน้ารับรอง") ?>

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
      <?=$form->field($model_form, 'date_start')->widget(\yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('วันที่');             ?>  
    </div>
    <div class="col-xs-6 col-sm-4 col-md-4">
        <?= $form->field($model_form, 'data_json[checker]')->textInput(['maxlength' => true])->label("ผู้ตรวจเช็คอุปกรณ์") ?>
    </div>
    <div class="col-xs-12 col-sm-4 col-md-4">
        <?= $form->field($model_form, 'data_json[endorsee]')->textInput(['maxlength' => true])->label("หัวหน้ารับรอง") ?>
    </div>
</div>
      <?= $form->field($model_form, 'name')->hiddenInput(['value'=>'ma'])->label(false) ?>
      <?= $form->field($model_form, 'code')->hiddenInput(['value'=>$model_asset->code])->label(false) ?>
<div class="card-body mt-3">
      <div class="table-responsive">
      

      
                <?php

echo $form->field($model_form,'ma')->widget(MultipleInput::class,[
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
<div class="d-flex justify-content-center">
       <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ดำเนินการต่อ', ['class' => 'btn btn-success waves-effect waves-light',"id"=>"summit"]) ?>
</div>
                </div>

<?php ActiveForm::end(); ?>
<?php Pjax::end();?>
<?php

$url = Url::to(['/am/asset-detail']);

$js = <<< JS


$('#form-ma').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: "$url" + "/test",
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                console.log(response.data);
                closeModal()
                success()
                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
            }
        }
    });
    return false;
});
JS;
$this->registerJs($js);
?>