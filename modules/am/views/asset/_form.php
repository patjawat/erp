<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\components\AppHelper;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;
use app\components\CategoriseHelper;
use app\modules\hr\models\Organization;
$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var yii\widgets\ActiveForm $form */

?>

<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>
<style>
.modal-footer {
    display: none !important;
}
</style>


<div class="row">
    <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-body">
            <div class="dropdown edit-field-half-left ml-2">
                              <div class="btn-icon btn-icon-sm btn-icon-soft-primary dropdown-toggle me-0 edit-field-icon" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="fa-solid fa-ellipsis"></i>
                              </div>
                              <div class="dropdown-menu dropdown-menu-right" style="">
                                 <a href="#" class="dropdown-item select-photo">
                                    <i class="fa-solid fa-file-image me-2 fs-5"></i>
                                    <span>อัพโหลดภาพ</span>
                                 </a>
                                
                              </div>
                           </div>

                <input type="file" id="my_file" style="display: none;" />
                <a href="#" class="select-photo">
                    <?= Html::img($model->showImg(),['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;']) ?>
                </a>
            </div>
        </div>

    </div>
    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
        <?php $form = ActiveForm::begin([
                     'id' => 'form-asset',
                     'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
                     'validationUrl' =>['/am/asset/validator']
                ]); ?>
        <?= $this->render('_form_detail'.$model->asset_group .'.php',['model' => $model,'form' => $form])?>
        <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false)?>
        <?= $form->field($model, 'asset_group')->hiddenInput(['maxlength' => true])->label(false)?>

        <div class="form-group mt-4 d-flex justify-content-center">
            <?= AppHelper::BtnSave(); ?>
        </div>


        <?php ActiveForm::end(); ?>


    </div>

</div>

</div>

<?php
$js = <<< JS
$('#form-asset').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (res) {
            form.yiiActiveForm('updateMessages', res, true);
            if (form.find('.invalid-feedback').length) {
                // validation failed
            } else {
                // validation succeeded
            }
            if(res.status == 'success') {
                // alert(data.status)
                console.log(res.container);
                // $('#main-modal').modal('toggle');
                success()
                 $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});                                                        
            }
        }
    });
    return false;
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
        uploadImg()
    }

});

function uploadImg()
{
    formdata = new FormData();
    if($("input[id='my_file']").prop('files').length > 0)
    {
		file = $("input[id='my_file']").prop('files')[0];
        formdata.append("asset", file);
        formdata.append("id", 1);
        formdata.append("ref", '$model->ref');
        formdata.append("name", 'asset');

        console.log(file);
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
}

$("button[id='summit']").on('click', function() {
    formdata = new FormData();
    if($("input[id='my_file']").prop('files').length > 0)
    {
		file = $("input[id='my_file']").prop('files')[0];
        formdata.append("avatar", file);
        formdata.append("id", 1);
        formdata.append("ref", '$model->ref');
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
JS;
$this->registerJs($js);
?>