<?php
use app\models\Categorise;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */
/** @var yii\widgets\ActiveForm $form */
$title = Yii::$app->request->get('title');
?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>

<?php $form = ActiveForm::begin([
    'id' => 'form-fsn',
    'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' => $model->isNewRecord
        ? ['/sm/asset-item/validator']
        : ['/sm/asset-item/validator', 'id' => $model->id]
    ]); ?>

<?= !$model->isNewRecord ? $form->field($model, 'id')->hiddenInput()->label(false) : '' ?>
<?php $form->field($model, 'data_json[title]')->textInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'group_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
 <?php
    echo $form->field($model, 'category_id')->widget(Select2::classname(), [
        'data' => $model->listAssetCategory(),
        'options' => [
            'placeholder' => 'เลือกประเภท...',
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'dropdownParent' => '#main-modal',
        ],
    ])->label('ประเภท');
    ?>
   <div class="row">
    
    <div class="col-12">
        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อครุภัณฑ์...'])->label("ชื่อรายการ") ?>
            </div>
            <div class="col-8">
                <?=$form->field($model, 'code')->textInput()->label("รหัสครุภัณฑ์(ปล่อยว่างระบบจะสร้างอัตโนมัติ)")?>
            </div>
            <div class="col-4">
                <?php
                echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
                    'data' => $model->listUnit(),
                    'options' => ['placeholder' => 'ระบุ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label("หน่วยนับ")
                ?>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'data_json[fsn]')->textInput(['maxlength' => true])->label('FSN') ?>
            <?= $form->field($model, 'data_json[price]')->textInput(['maxlength' => true])->label('ราคากลาง') ?>

        </div>
        <div class="col-6">
            <?= $form->field($model, 'data_json[depreciation]')->textInput(['maxlength' => true])->label('ค่าเสื่อมราคา') ?>
            <?= $form->field($model, 'data_json[service_life]')->textInput()->label('อายุการใช้งาน') ?>
        </div>
    </div>

    <div class="col-12">
        <input type="file" id="my_file" style="display: none;" />

        <a href="#" class="select-photo">
            <?php if($model->showImg() != false):?>
            <?= Html::img($model->showImg(),['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;']) ?>
            <?php else:?>
            <?=Html::img('@web/img/placeholder-img.jpg',['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;'])?>
            <?php endif;?>
        </a>
    </div>
</div>


        <div class="form-group mt-3 d-flex justify-content-center align-items-center gap-2 flex-wrap">
            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
            <?= Html::button('<i class="fa-solid fa-circle-xmark"></i> ปิด', ['class' => 'btn btn-secondary', 'data-bs-dismiss' => 'modal']) ?>
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

function uploadSelectedImage() {
    var deferred = $.Deferred();
    var fileInput = $("input[id='my_file']");

    if (fileInput.prop('files').length > 0) {
		var file = fileInput.prop('files')[0];
        var formdata = new FormData();
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
                deferred.resolve(res);
			},
            error: function () {
                deferred.resolve();
            }
		});
        return deferred.promise();
    }

    deferred.resolve();
    return deferred.promise();
}

function submitAssetItem(form) {
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
}

$('#form-fsn').on('beforeSubmit', function (e) {
    e.preventDefault();
    var form = $(this);

    Swal.fire({
        title: 'ยืนยันการบันทึก?',
        text: 'คุณต้องการบันทึกข้อมูลรายการนี้ใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, บันทึกเลย',
        cancelButtonText: 'ยกเลิก'
    }).then(function (result) {
        if (result.isConfirmed) {
            uploadSelectedImage().always(function () {
                submitAssetItem(form);
            });
        }
    });

    return false;
});

JS;
$this->registerJS($js)
?>
