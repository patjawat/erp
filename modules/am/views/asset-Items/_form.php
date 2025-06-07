<?php
use yii\helpers\Html;
use app\models\Categorise;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\components\AppHelper;
use unclead\multipleinput\MultipleInput;
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-fsn',
    // 'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    // 'validationUrl' =>['/sm/asset-item/validator']
    ]); ?>




<?php $form->field($model, 'data_json[title]')->textInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput(['value'=>'asset_item','maxlength' => true])->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<div class="row">
    <div class="col-7">
        <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
                    'data' => $model->listAssetType(),
                    'options' => ['placeholder' => 'ระบุประเภทรัพย์สินย์...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                   
                ])->label("ประเภททรัพย์สิน");
                ?>

        <?=$form->field($model, 'code')->textInput()->label("รหัส FSN")?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อทรัพย์สิน...'])->label("ชื่อทรัพย์สิน") ?>

        <?php
                echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
                    'data' => $model->listUnit(),
                    'options' => ['placeholder' => 'ระบุ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                         'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label("หน่วยนับ")
                ?>
    </div>
    <div class="col-5">

        <label class="form-label mb-0">รูปภาพทรัพย์สิน</label>

        <div class="mb-3">
            <div class="file-preview" id="editImagePreview" data-isfile="<?=$model->showImg()['isFile']?>">
                <?= Html::img($model->showImg()['image'],['id' => 'editPreviewImg']) ?>
                <div class="file-remove" id="editRemoveImage">
                    <i class="bi bi-x"></i>
                </div>
            </div>
            
            <div class="file-upload">
                <div class="file-upload-btn" id="editUploadBtn">
                    <i class="bi bi-cloud-arrow-up fs-3 mb-2"></i>
                    <span>คลิกหรือลากไฟล์มาวางที่นี่</span>
                    <small class="d-block text-muted mt-2">รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB</small>
                </div>
                <input type="file" class="file-upload-input" id="my_file" accept="image/*">
            </div>
        </div>
    </div>

</div>


<div class="form-group mt-3 d-flex justify-content-center gap-2">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summitxx"]) ?>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-circle-xmark"></i> ปิด</button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

// เรียกใช้ฟังก์ชัน isFile() เพื่อกำหนดสถานะการแสดงผลของรูปภาพ
isFile()
 
function isFile(){
    var isFile = $('#editImagePreview').data('isfile');
    console.log(isFile);
    
    if(isFile == true){
        $("#editImagePreview").show();
        $("#editUploadBtn").hide();
        $("#editRemoveImage").show();
    }else{
        $("#editImagePreview").hide();
        $("#editUploadBtn").show();
        $("#editRemoveImage").hide();
    }
}

$(".file-upload-input").on("change", function() {

    var fileInput = $(this)[0];
    if (fileInput.files && fileInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
        $("#editPreviewImg").attr("src", e.target.result);
        console.log(e.target.result)
        $("#editImagePreview").show();
        $("#editUploadBtn").hide();
        $("#editRemoveImage").show();
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
});

$('#editRemoveImage').click(function (e) { 
    e.preventDefault();
    $("#editPreviewImg").attr("src", '');
    $("#editImagePreview").hide();
    $("#editUploadBtn").show();
    $("#editRemoveImage").hide();

});

async function uploadImage() {
    formdata = new FormData();
    if($("input[id='my_file']").prop('files').length > 0)
    {
		file = $("input[id='my_file']").prop('files')[0];
        formdata.append("asset", file);
        formdata.append("id", 1);
        formdata.append("ref", '$model->ref');
        formdata.append("name", 'asset');
		await $.ajax({
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
    }
// #### จบการอัพโหลดรูปภาพ ####


// ตั้งค่า localStorage สำหรับ fsn_auto
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


$("#summit").on('click', async function() {

});


         // เรียกใช้ function handleFormSubmit
        handleFormSubmit('#form-fsn', null, async function(response) {
            await uploadImage();
            await location.reload();
        });

    
JS;
$this->registerJs($js);
?>