<?php

use yii\helpers\Html;
use kartik\select2\Select2;

use kartik\widgets\ActiveForm;
?>
<style>
.col-form-label {
    text-align: end;
}
</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-fsn',
    'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL],
        'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']],
    ]); ?>
<?= $form->field($model, 'ref')->hiddenInput(['value'=>$ref,'maxlength' => true,'placeholder'=>'ระบุชื่อครุภัณฑ์'])->label(false) ?>
<div class="asset-type-form">
<div class="row">
<div class="col-8">
            <?=$this->render('_form_'.$model->name,['model' => $model,'form' => $form])?>
        </div>
        <div class="col-4">
            <input type="file" id="material_file" style="display: none;" />
            <div class="d-flex justify-content-center">
                <a href="#" class="select-material-photo">
                    <?=Html::img($model->ShowImg(),['class' => 'avatar-material object-fit-cover rounded','style' =>'max-width:100%;'])?>
                </a>
            </div>
        </div>
    </div>
    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summit"]) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php
$js = <<<JS
 console.log("$model->id");

function getIMG(){
    $.ajax({
        type: "get",
        url: "/filemanager/uploads/show?id=$model->id",
        dataType: "json",
        success: function (res) {
        console.log('get Avatar success');
        console.log(res);
        }
    })
};
getIMG()



$(".select-material-photo").click(function() {
    $("input[id='material_file']").click();
});

$("input[id='material_file']").on("change", function() {
    var fileInput = $(this)[0];
    if (fileInput.files && fileInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
        $(".avatar-material").attr("src", e.target.result);
        };
        reader.readAsDataURL(fileInput.files[0]);
    }

});

$("button[id='summit']").on('click', function() {
    formdata = new FormData();
    if($("input[id='material_file']").prop('files').length > 0)
    {
		file = $("input[id='material_file']").prop('files')[0];
        formdata.append("avatar", file);
        formdata.append("id", 1);
        formdata.append("ref", '$ref');
        formdata.append("name", 'avatar');

        console.log(file);
		$.ajax({
			url: '/filemanager/uploads/single',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (res) {
                // success('แก้ไขภาพ')
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