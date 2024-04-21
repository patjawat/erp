<?php
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use app\modules\am\models\Asset;
use app\components\CategoriseHelper;
use yii\helpers\Html;
use app\modules\am\models\AssetItem;
use yii\helpers\Url;
use yii\widgets\Pjax;
$model_form = new AssetItem()
?>
<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>

<?php $form = ActiveForm::begin(['id' => 'form-ma']); ?>
<div class="card-body">
      <div class="table-responsive">
      <?= $form->field($model_form, 'name')->hiddenInput(['value'=>'ma'])->label(false) ?>
      <?= $form->field($model_form, 'category_id')->hiddenInput(['value'=>$model->code])->label(false) ?>

                <?php

echo $form->field($model_form,'data_json')->widget(MultipleInput::class,[
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
            'name'  => 'date',
            'type'  => \kartik\date\DatePicker::className(),
            'headerOptions' => [
                'class' => 'table-light', 
            ],
            'title' => 'วันที่',
        ],
        [
            'name'  => 'checker',
            'headerOptions' => [
                'class' => 'table-light',
            ],
            'title' => 'ผู้ตรวจเช็คอุปกรณ์',
        ],
        [
            'name'  => 'endorsee',
            'headerOptions' => [
                'class' => 'table-light',
            ],
            'title' => 'หัวหน้ารับรอง',
        ],
        [
            'name'  => 'items',
            'type' => Select2::class,
            'headerOptions' => [
                'class' => 'table-light', 
            ],
            'title' => 'รายการที่ตรวจเช็ค',
            'options' => [
                'data' => CategoriseHelper::Id($id_category)->one()->data_json["ma_items"]
            ]
        ],
        [
            'name'  => 'ma_status',
            'type'  => 'dropDownList',
            'headerOptions' => [
                'class' => 'table-light',
                'style' => 'width: 70px;',
            ],
            'defaultValue' => 1,
            'items' => [
                0 => 'สูง',
                1 => 'ปกติ',
                2 => 'ต่ำ'
            ],
            'title' => 'สถานะ',
        ], 
        [
            'name'  => 'comment',
            'headerOptions' => [
                'class' => 'table-light', 
            ],
            'title' => 'หมายเหตุ',
        ], 
        
 	
        
        
    ]

])->label(false);

?>
                </div>
                    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ดำเนินการต่อ', ['class' => 'btn btn-success waves-effect waves-light',"id"=>"summit"]) ?>

                </div>

<?php ActiveForm::end(); ?>
<?php Pjax::end();?>
<?php

$url = Url::to(['/am/asset-detail']);

$js = <<< JS


$('#form-ma').on('beforeSubmit', function (e) {
    var form = $(this);
    return 
    $.ajax({
        url: "$url" + "/test",
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                console.log(response.data);
                //closeModal()
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