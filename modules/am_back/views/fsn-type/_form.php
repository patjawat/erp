<?php
use app\components\AppHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */
/** @var yii\widgets\ActiveForm $form */





?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>



<!-- <div class="card">
    <div class="card-body d-flex justify-content-between">
        <h4 class="card-title">Title</h4>
        <p class="card-text">Text</p>
    </div>
</div> -->

<div class="card">
    <div class="card-body">

    <?php $form = ActiveForm::begin(['id' => 'form-fsn']); ?>
    <div class="row">
    <div class="col-sm-6 col-md-6">
<?= $form->field($model, 'code')->textInput(['maxlength' => true,'placeholder' => "7440-001"])->label("ชนิชครุภัณฑ์") ?>
    </div>
    <div class="col-sm-6 col-md-6">
        <input type="file" id="my_file" style="display: none;" />
        <div class="d-flex justify-content-center">
            <a href="#" class="select-photo">
                <?php if($model->showLogoImg($model->ref) != false){ ?>
                    <?= Html::img($model->showLogoImg($model->reff),['class' => 'avatar-profile object-fit-cover rounded shadow','style' =>'max-width: 135px;max-height: 135px;width: 100%;height: 100%;']) ?>
                <?php }else{ ?>
                    <?=Html::img('https://m.media-amazon.com/images/I/81gK08T6tYL._AC_SL1500_.jpg',['class' => 'avatar-profile object-fit-cover rounded shadow','style' =>'max-width: 135px;max-height: 135px;width: 100%;height: 100%;'])?>
                <?php } ?>
            </a>
        </div>
    </div>
</div>

    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true,'value'=>'asset_type'])->label(false) ?>
    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'เครื่องกลสำนักงานและอุปกรณ์กรรมวิธีบันทึกและลงข้อมูล'])->label("ชื่อครุภัณฑ์") ?>

    <?= $form->field($model, 'data_json[service_life]')->textInput(['placeholder' => "3"])->label("อายุการใช้งาน (ปี)") ?>
    <?= $form->field($model, 'data_json[depreciation]')->textInput(['placeholder' => "33.33"])->label("อัตราค่าเสื่อมราคาจ่อปี (ร้อยละ)") ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> Save', ['class' => 'btn btn-primary','id' => "summit"]) ?>
    </div>
    <?php ActiveForm::end(); ?>
          
    </div>
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