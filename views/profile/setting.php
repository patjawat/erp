<?php

use app\components\AppHelper;
use yii\bootstrap5\ActiveForm;
use yii\web\View;
$this->title = "ตั้งค่าโปรไฟล์";
?>

<?php $this->beginBlock('page-title');?>
<i class="fa-solid fa-address-card"></i> <?=$this->title;?>
<?php $this->endBlock();?>

<div class="row justify-content-center">
    <div class="col-xl-4 col-lg-4 col-md-8 col-sm-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title"><i class="fa-solid fa-user-gear"></i> ตั้งค่าโปรไฟล์</h4>

                <?php $form = ActiveForm::begin(['id' => 'setting-form','enableAjaxValidation' => false,]); ?>


                <?=$form->field($model, 'email')->label('อีเมล')?>
                <?=$form->field($model, 'phone')->textInput()->label('หมายเลขโทรศัพท์')?>

                <?=$form->field($model, 'password')->passwordInput()->label('รหัสผ่าน')?>

                <?=$form->field($model, 'confirm_password')->passwordInput()->label('ยืนยันรหัสผ่าน')?>

                <div class="form-group d-flex justify-content-center">
                    <?=AppHelper::BtnSave()?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end();?>





    </div>
</div>


<?php
$js = <<< JS

$('#setting-form').on('beforeSubmit', function () {
    var yiiform = $(this);
    $.ajax({
            type: yiiform.attr('method'),
            url: yiiform.attr('action'),
            data: yiiform.serializeArray(),
        }
    )
        .done(function(data) {
            if(data.success) {
                // data is saved
                success()
                window.setTimeout(function(){
                    window.location.href = data.url
                }, 2000 )
            } else if (data.validation) {
                // server validation failed
                yiiform.yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
            } else {
                // incorrect server response
            }
        })
        .fail(function () {
            // request failed
        })

    return false; // prevent default form submission
})

// $('#form-setting').on('beforeSubmit', function (e) {

//     var yiiform = $(this);
    // $.ajax({
    //         type: yiiform.attr('method'),
    //         url: yiiform.attr('action'),
    //         data: yiiform.serializeArray(),
    //     }
    // )
    //     .done(function(data) {
    //         if(data.success) {

    //             success()
    //         } else if (data.validation) {
    //             // server validation failed
    //             $('#form-setting').yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
    //             warning()
    //         } else {
    //             // incorrect server response
    //         }
    //     })
    //     .fail(function () {
    //         // request failed
    //     })
    // $.ajax({
    //     url: form.attr('action'),
    //     type: 'post',
    //     data: form.serialize(),
    //     dataType: 'json',
    //     success: async function (response) {
    //         form.yiiActiveForm('updateMessages', response, true);
    //         if(response.status == 'success') {
    //             // closeModal()
    //             success()
    //             window.setTimeout(function(){
    //                 window.location.href = response.url
    //             }, 2000 )
    //             // await  $.pjax.reload({ container:"#employee-container", history:false,replace: false,timeout: false});
    //         } else if (response.validation) {
    //             // server validation failed
    //             yiiform.yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
    //         }
    //     }
    // });
//     return false;
// });



JS;
$this->registerJS($js, View::POS_END);
?>