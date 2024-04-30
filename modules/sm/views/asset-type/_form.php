<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use kartik\select2\Select2;
/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetType $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-type-form">

    <?php $form = ActiveForm::begin(['id' => 'form-fsn']); ?>
    <div class="row">
    <div class="col-4">
        <input type="file" id="my_file" style="display: none;" />
        <div class="d-flex justify-content-center">
        <a href="#" class="select-photo">
            <?=Html::img($model->ShowImg(),['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;'])?>
        </a>
        </div>
    </div>
    <div class="col-8">
        <div class="row">
            <div class="col-8">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อครุภัณฑ์'])->label("ชื่อรายการ") ?>
                <?= $form->field($model, 'ref')->hiddenInput(['value'=>$ref,'maxlength' => true,'placeholder'=>'ระบุชื่อครุภัณฑ์'])->label(false) ?>
            </div>
            <div class="col-4">
                <?= $form->field($model, 'code')->textInput(['maxlength' => true,'placeholder'=>'ระบุรหัส'])->label("รหัส") ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[service_life]')->textInput(['placeholder' => "ระบุจำนวน ปี"])->label("อายุการใช้งาน (ปี)") ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[depreciation]')->textInput(['placeholder' => "ตัวอยย่าง 00.00"])->label("อัตราค่าเสื่อม") ?>
                </div>
        </div>
        <div class="col-12">
                                <?php
                echo $form->field($model, 'category_id')->widget(Select2::classname(), [
                    'data' => [
                        2 => "สิ่งปลูกสร้าง",
                        3 => "ครุภัณฑ์",
                        4 => "วัสดุ"
                    ],
                    'options' => ['placeholder' => 'ระบุ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label("ประเภท")
                ?>
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