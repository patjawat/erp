<?php
use app\components\AppHelper;
use yii\helpers\Html;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\select2\Select2;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */
/** @var yii\widgets\ActiveForm $form */
use app\models\Categorise;
use unclead\multipleinput\MultipleInput;
?>
<?php
echo "<pre>";
// print_r($$itemType);
echo "</pre>";
?>


<?php $form = ActiveForm::begin([
    'id' => 'form-fsn',
    'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/sm/asset-item/validator']
    ]); ?>




<?php $form->field($model, 'data_json[title]')->textInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput(['value'=>'asset_item','maxlength' => true])->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput(['value'=>$ref,'maxlength' => true])->label(false) ?>
<div class="row">
    <div class="col-5">
        <input type="file" id="my_file" style="display: none;" />

        <a href="#" class="select-photo">
            <?= Html::img($model->showImg(),['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;']) ?>
        </a>
    </div>
    <div class="col-7">
        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อครุภัณฑ์...'])->label("ชื่อรายการ") ?>
            </div>
            <div class="col-8">
                <?=$form->field($model, 'code')->textInput()->label("รหัสครุภัณฑ์")?>
            </div>
            <div class="col-4">
                <?php 
                    $units = Categorise::findAll(['name' => 'unit']);

                    // สร้าง array เพื่อใช้ใน Select2
                    $unitData = [];
                    foreach ($units as $unit) {
                        $unitData[$unit->id] = $unit->title;
                    }

                    $assets = Categorise::findAll(['name' => 'asset_type', 'category_id'=>[3,4]]);

                    // สร้าง array เพื่อใช้ใน Select2
                    $assetData = [];
                    foreach ($assets as $asset) {
                        $assetData[$asset->id] = $asset->title;
                    }

                ?>
                <?php
                echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
                    'data' => $unitData,
                    'options' => ['placeholder' => 'ระบุ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label("หน่วยนับ")
                ?>

            </div>
            <div class="col-12">
                                <?php
                echo $form->field($model, 'category_id')->widget(Select2::classname(), [
                    'data' => $model->AssetType(),
                    'options' => ['placeholder' => 'ระบุ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label("ทรัพย์สิน")
                ?>
            </div>
            
        </div>
    </div>
</div>
<hr>
<?php

$model->ma = isset($model->data_json['ma_items']) ? $model->data_json['ma_items'] : '';
    echo $form->field($model,'ma')->widget(MultipleInput::className(), [
        
        'allowEmptyList'    => false,
        'enableGuessTitle'  => true,
        'min'               => 1, // should be at least 2 rows
        'addButtonPosition' => MultipleInput::POS_HEADER,
        'addButtonOptions' => [
            'class' => 'btn btn-sm btn-primary',
            'label' => '<i class="fa-solid fa-circle-plus"></i>' // also you can use html code
        ],
        'removeButtonOptions' => [
            'class' => 'btn btn-sm btn-danger',
            'label' => '<i class="fa-solid fa-trash"></i>'
        ],
        'columns' => [
            [
                'name'  => 'code',
                'title' => 'รหัส',
                'enableError' => true,
                'options' => [
                    'class' => 'input-priority'
                ]
            ],
            [
                'name'  => 'item_name',
                'title' => 'แผนบำรุงรักษา',
                'enableError' => true,
                'options' => [
                    'class' => 'input-priority'
                ]
            ],
            
        ]
    ])
    ->label(false);
?>

<div class="form-group mt-3 d-flex justify-content-center">
            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summit"]) ?>
        </div>

        <?php ActiveForm::end(); ?>

<?php
$js = <<< JS

if($("#assetitem-fsn_auto").val()){
    $( "#assetitem-fsn_auto" ).prop( "checked", localStorage.getItem('fsn_auto') == 1 ? true : false );   
    $('#assetitem-code').prop('disabled',localStorage.getItem('fsn_auto') == 1 ? true : false );
    
    if(localStorage.getItem('fsn_auto') == 1)
    {
        $('#assetitem-code').val('อัตโนมัติ')
    }
}

$("#assetitem-fsn_auto").change(function() {
    //ตั้งค่า Run FSN Auto
    if(this.checked) {
        localStorage.setItem('fsn_auto',1);
        $('#assetitem-code').prop('disabled',this.checked);
        $('#assetitem-code').val('อัตโนมัติ')
    }else{
        localStorage.setItem('fsn_auto',0);
        var category_id = $('#assetitem-category_id').val();
        $('#assetitem-code').prop('disabled',this.checked);

        $('#assetitem-code').val('')
    }
});


// เลือก upload รูปภาพ
$(".select-photo").click(function() {
    $("input[id='my_file']").click();
});

$("input[id='my_file']").on("change", function() {
    var fileInput = $(this)[0];
    if (fileInput.files && fileInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
        $(".avatar-profile").attr("src", e.target.result);
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
});

$("button[id='summit']").on('click', function() {
    formdata = new FormData();
    if($("input[id='my_file']").prop('files').length > 0)
    {
		file = $("input[id='my_file']").prop('files')[0];
        formdata.append("asset", file);
        formdata.append("id", 1);
        formdata.append("ref", '$model->ref');
        formdata.append("name", 'asset');
		$.ajax({
			url: '/filemanager/uploads/single',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (res) {
                success('แก้ไขภาพสำเร็จ')
                console.log(res)
			}
		});
    }
})

$('#form-fsn').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            console.log(response);
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
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