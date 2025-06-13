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

?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>

<?php $form = ActiveForm::begin([
    'id' => 'form-fsn',
    'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/am/fsn/validator']
    ]); ?>

<?= $form->field($model, 'data_json[title]')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'category_id')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'data_json[asset_type]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[asset_type_text]')->hiddenInput()->label(false) ?>

<input type="file" id="my_file" style="display: none;" />
<div class="row">
    <div class="<?=$model->name == 'asset_group' ? 'col-4' : 'col-5' ?>">

        <a href="#" class="select-photo">
            <?php if($model->showImg() != false):?>
            <?= Html::img($model->showImg(),['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;']) ?>
            <?php else:?>
            <?=Html::img('@web/img/placeholder-img.jpg',['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;'])?>
            <?php endif;?>
        </a>
    </div>
    <div class="<?=$model->name == 'asset_group' ? 'col-8' : 'col-7' ?>">

        <div class="row">
        
            <?php if($model->name == "asset_name"):?>
                <div class="col-12">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'เครื่องกลสำนักงานและอุปกรณ์กรรมวิธีบันทึกและลงข้อมูล'])->label("ชื่อรายการ") ?>
        </div>
                <div class="col-12">
                        <?=$form->field($model, 'code')->widget(MaskedInput::className(),['mask'=>'9999-999-9999'])->label("รหัสครุภัณฑ์")?>
                        <?php else:?>
                    </div>
                    
                    <?php endif;?>
                    <?php if($model->name == "asset_group"):?>
                        <div class="row">
                            <div class="col-8">
                                <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'เครื่องกลสำนักงานและอุปกรณ์กรรมวิธีบันทึกและลงข้อมูล'])->label("ชื่อรายการ") ?>
                                <?= $form->field($model, 'data_json[depreciation]')->textInput(['placeholder' => "33.33"])->label("อัตราค่าเสื่อมราคาจ่อปี (ร้อยละ)") ?>
                            </div>
                            <div class="col-4">
                <?=$form->field($model, 'code')->widget(MaskedInput::className(),['mask'=>'9999-999'])->label("รหัสครุภัณฑ์")?>
                <?= $form->field($model, 'data_json[service_life]')->textInput(['placeholder' => "3"])->label("อายุการใช้งาน (ปี)") ?>
            </div>
        </div>
        <?php endif;?>

    </div>
</div>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summit"]) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS

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
$this->registerJS($js)
?>