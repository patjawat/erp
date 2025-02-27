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

<?php 
$id = isset($model->id) ? intval($model->id) : 0;
$ref = isset($ref) ? Html::encode($ref) : '';

$form = ActiveForm::begin([
    'id' => 'form-fsn',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']],
]); 
?>

<?= $form->field($model, 'ref')->hiddenInput(['value' => $ref, 'maxlength' => true])->label(false) ?>

<div class="asset-type-form">
    <div class="row">
        <div class="col-8">
            <?= $this->render('_form_' . $model->name, ['model' => $model, 'form' => $form]) ?>
        </div>
        <div class="col-4">
            <input type="file" id="my_file" style="display: none;" />
            <div class="d-flex justify-content-center">
                <a href="#" class="select-material-photo">
                    <?= Html::img($model->ShowImg(), ['class' => 'avatar-material object-fit-cover rounded', 'style' => 'max-width:100%;']) ?>
                </a>
            </div>
        </div>
    </div>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => "submit"]) ?>
    </div>

<?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
console.log("Model ID: $id");

function getIMG() {
    if ($id > 0) {
        $.ajax({
            type: "GET",
            url: "/filemanager/uploads/show?id=$id",
            dataType: "json",
            success: function (res) {
                console.log('get Avatar success', res);
            }
        });
    }
}
getIMG();

$(".select-material-photo").click(function() {
    $("#my_file").click();
});

$("#my_file").on("change", function() {
    var fileInput = $(this)[0];
    if (fileInput.files && fileInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $(".avatar-material").attr("src", e.target.result);
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
});

$('#form-fsn').on('beforeSubmit', function (e) {
    e.preventDefault();
    let form = $(this);

    Swal.fire({
        title: 'ยืนยันการบันทึก?',
        text: "คุณต้องการบันทึกข้อมูลหรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, บันทึกเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                beforeSend: async function () {
                    await uploadFile();
                },
                success: async function (response) {
                    form.yiiActiveForm('updateMessages', response, true);
                    if (response.status === 'success') {
                        closeModal();
                        await Swal.fire({
                            title: 'บันทึกสำเร็จ!',
                            text: 'ข้อมูลของคุณถูกบันทึกเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 2000, // ปิดอัตโนมัติใน 3 วินาที
                            confirmButtonText: 'ตกลง'
                        });
                        await $.pjax.reload({ container: response.container, history: false, replace: false, timeout: false });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง',
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                }
            });
        }
    });

    return false;
});


 function uploadFile(){
    formdata = new FormData();
    var checkFile = $("input[id='my_file']").prop('files').length;
    console.log(checkFile);
    
    
    if($("input[id='my_file']").prop('files').length > 0)
    {
		file = $("input[id='my_file']").prop('files')[0];
        formdata.append("asset_type", file);
        formdata.append("id", 1);
        formdata.append("ref", '$ref');
        formdata.append("name", 'asset_type');
        

        console.log(file);
		$.ajax({
			url: '/filemanager/uploads/single',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (res) {
                return true;
			}
		});
    }
}

JS;

$this->registerJS($js);
?>
