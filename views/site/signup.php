<?php
use yii\web\View;
// use app\themes\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\widgets\MaskedInput;
use yii\bootstrap5\ActiveForm;
use app\modules\employees\models\Employees;
// $assets = AppAsset::register($this);
$this->title = "ระบบลงทะเบียน";
?>

<style>
input,
input::placeholder {
    font-weight: 200;
}
.form-control {
    background-color:#eee;
}
.form-control:focus {
    background-color:#eee;
}

.was-validated .form-control:invalid:focus, .form-control.is-invalid:focus {
    border-color: var(--bs-form-invalid-border-color);
    box-shadow: 0 0 0 0.1rem rgba(var(--bs-primary-rgb), 0.25);
}
</style>

    <div id="success-container"></div>

    <div id="signup-container" class="row justify-content-center">
            <h3 class=""><?=$this->title?></h3>
            <?php $form = ActiveForm::begin(['id' => 'blank-form','enableAjaxValidation' => false,]); ?>
            <div class="row justify-content-center">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <?= $form->field($model, 'cid')->textInput(['placeholder' => 'ระบุเลขบัตรประชาชน','autofocus' => true,'class' => 'form-control form-control-lg rounded-pill border-0'])->label('เลขบัตรประชาชน') ?>
                    <?= $form->field($model, 'email')->textInput(['placeholder' => 'ระบุอีเมล','class' => 'form-control form-control-lg rounded-pill border-0'])->label('อีเมล') ?>
                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'กำหนดรหัสผ่าน','class' => 'form-control form-control-lg rounded-pill border-0'])->label('รหัสผ่าน') ?>
                    <div class="d-inline-block w-100">
                        <div class="d-flex justify-content-between">

                            <div class="custom-control custom-checkbox d-inline-block mt-2 pt-1">
                                <input type="checkbox" class="custom-control-input" id="customCheck1" disabled="true">
                                <label class="custom-control-label for=" customCheck1">ฉันยอมรับ |
                                    <?=Html::a('ข้อกำหนดและเงื่อนไข',['/site/conditions-register'],['class' => 'open-modal']);?>
                                </label>
                            </div>

                            <div class="sign-info mt-2">
                                <span class="dark-color d-inline-block line-height-2">มีบัญชีอยู่แล้ว |
                                    <?=Html::a('เข้าสู่ระบบ',['site/login'],['class' => ''])?></span>
                            </div>

                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-lg btn-primary account-btn rounded-pill" id="btn-regster"
                                type="submit">
                                <i class="fa-solid fa-circle-check"></i> ลงทะเบียน</button>

                            <button class="btn btn-lg btn-primary account-btn rounded-pill d-none" id="btn-loading"
                                type="button" disabled="">
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>
                                Loading...
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <?php ActiveForm::end(); ?>

    </div>




<?php
$js = <<< JS

// $("#customCheck1").change(function() {
//     var ischecked= $(this).is(':checked');
//     if(!ischecked)
//     $('#btn-regster,#customCheck1').prop('disabled', true)
// }); 


$('#blank-form').on('beforeSubmit', function () {

    var ischecked= $('#customCheck1').is(':checked');
    console.log(ischecked);
    if(!ischecked){
        alert('คุณไม่ได้ยอมรับข้อกำหนดและเงื่อนไข')
        return false;
    }else{

    var yiiform = $(this);
    $('#btn-regster').hide();
    $('#btn-loading').show();
    $.ajax({
            type: yiiform.attr('method'),
            url: yiiform.attr('action'),
            data: yiiform.serializeArray(),
        }
    )
        .done(function(data) {
            if(data.success) {
                // data is saved
                $('#success-container').html(data.content);
                $('#signup-container').hide();
                success()
            } else if (data.validation) {
                // server validation failed
                yiiform.yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
                $('#btn-regster').show();
                $('#btn-loading').hide();
            } else {
                // incorrect server response
            }
        })
        .fail(function () {
            // request failed
        })

    }
    return false; // prevent default form submission
})




// $('#form-signup').on('beforeSubmit', function (e) {
//    e.preventDefault();
    // var ischecked= $('#customCheck1').is(':checked');
    // console.log(ischecked);
    // if(!ischecked){
    //     alert('คุณไม่ได้ยอมรับข้อกำหนดและเงื่อนไข')
    //     return false;
    // }else{
        // }
//         $.ajax({
//             type: "post",
//             url: $(this).attr('href'),
//             data: $(this).serialize(),
//             dataType: "json",
//             success: function (response) {
//                 form.yiiActiveForm('updateMessages', res, true);
//             if (form.find('.has-error').length) {
//                 // validation failed
//             } else {
//                 // validation succeeded
//             }
//                 //if(!response.status)
//                 // console.log(response);
//                 //warning();
//             }
//         });
//     return false;
// });

JS;
$this->registerJs($js,View::POS_END);
?>