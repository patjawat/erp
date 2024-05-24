<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\AppHelper;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="row">
    <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12">
        <div class="card">
            <h5 class="text-center mt-1">ภาพประกอบ</h5>
            <input type="file" id="my_file" style="display: none;" />
            <div class="card-body">
                <a href="#" class="select-photo">
                    <?php if($model->showLogoImg($ref) != false){ ?>
                        <?= Html::img($model->showLogoImg($ref),['class' => 'avatar-profile object-fit-cover rounded shadow img-fluid rounded-start p-1' ]) ?>
                    <?php }else{ ?>
                        <?=Html::img('https://m.media-amazon.com/images/I/81gK08T6tYL._AC_SL1500_.jpg',['class' => 'avatar-profile object-fit-cover rounded shadow img-fluid rounded-start p-1'])?>
                    <?php } ?>
                </a>
            </div>
        </div>

    </div>
    <div class="col-xl-9 col-lg-9 col-md-12 col-sm-12">
    <?php $form = ActiveForm::begin([]); ?>
<div class="card">   
<div class="card-body">
        <div class="asset-form">
            <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
            <?= $form->field($model, 'data_json[name]')->textInput(['maxlength' => true])->label('ชื่อวัสดุ') ?>
            <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true,'value' => $ref])->label(false)?>
            <div class="row">
                <?= $form->field($model, 'fsn')->textInput(['maxlength' => true])->label('รหัส') ?>
            </div>
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <div class="row">
                        <div class="col-12">
                             <?= $form->field($model, 'qty')->textInput()->label("จำนวน") ?>
                        </div>
                    </div>
                    <!-- End Row Sub  -->
                </div>
                <!-- En col-6 main -->
                <div class="col-6">
                    <div class="row">
                        <div class="col-12">
                        <?= $form->field($model, 'price')->textInput() ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm">
                    <?php
                    echo $form->field($model, 'data_json[detail]')->textarea()->label('รายละเอียดวัสดุ');
                                // echo $form->field($model, 'dep_id')->widget(Select2::classname(), [
                                //     'data' => $model->ListDepartment(),
                                //     'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                                //     'pluginOptions' => [
                                //     'allowClear' => true,
                                //     ],
                                // ])->label(true);
                        ?>
                   

                </div>
              
            </div>
            <div class="form-group mt-4 d-flex justify-content-center">
            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> Save', ['class' => 'btn btn-primary','id' => "summit"]) ?>
            </div>

        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

        
    </div>

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


JS;
$this->registerJS($js)
?>