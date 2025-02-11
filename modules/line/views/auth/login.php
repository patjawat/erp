<?php
use yii\web\View;
// use app\themes\assets\AppAsset;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\components\SiteHelper;
use kartik\widgets\ActiveForm;
use app\modules\employees\models\Employees;
$site = Categorise::findOne(['name' => 'site']);
$color = isset($site->data_json['theme_color']) ? $site->data_json['theme_color'] : '';
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : '';

$this->title = 'กรุณายืนยันตัวตน';
$this->params['breadcrumbs'][] = $this->title;
$this->title = "ระบบยืนยันตัวตน";
$this->registerJsFile('https://unpkg.com/vconsole@latest/dist/vconsole.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<style>
input,
input::placeholder {
    font-weight: 200;
}
</style>


<div class="page-title-box">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-5 col-xl-4">
                <div class="page-title">
                    <h5 class="mb-1 text-white">ยืนยันตัวตน</h5>
                </div>
            </div>
            <div class="col-sm-7 col-xl-8">
                <div class="d-flex justify-content-sm-end">

                </div>
            </div>
        </div>
    </div>
</div>



<div class="card">
    <div class="card-body">
้<h1 class="text-center">เข้าสู่ระบบ</h1>
        <div class="d-flex justify-content-center mb-1" style="margin-top:15%">
            <img id="pictureUrl" width="25%" class="avatar avatar-xxl border border-primary-subtl card-img-top  shadow">
        </div>

        <div id="signup-container" class="row justify-content-center">

            <?php $form = ActiveForm::begin(['id' => 'blank-form']); ?>
            <div class="ยข/">
                    <?= $form->field($model, 'line_id')->hiddenInput()->label(false) ?>
                    <?= $form->field($model, 'username')->textInput(['autofocus' => true,'placeholder' => 'ระบุอีเมล','class' => 'form-control form-control-lg rounded-pill border-0'])->label('อีเมล') ?>
                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'กำหนดรหัสผ่าน','class' => 'form-control form-control-lg rounded-pill border-0'])->label('รหัสผ่าน') ?>
                    <div class="d-inline-block w-100">
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-lg btn-primary account-btn rounded-pill" id="btn-login"
                                type="submit">
                                <i class="fa-solid fa-fingerprint"></i> เข้าสู่ระบบ
                            </button>
                        </div>
                    </div>

            </div>

            <?php ActiveForm::end(); ?>


        </div>

    </div>
</div>



<?php


$liffLogin = SiteHelper::getInfo()['line_liff_login'];
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffProfileUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_profile'];
$liffRegisterUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_register'];

$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$js = <<< JS

$('#blank-form').on('beforeSubmit', function (e) {
e.preventDefault
    var yiiform = $(this);
    $('#btnAwait').show();
    $('#btn-login').hide();

    $.ajax({
        type: yiiform.attr('method'),
            url: yiiform.attr('action'),
            data: yiiform.serializeArray(),
        dataType: "json",
        success: function (data) {
            if(data.success) {
                // data is saved
                $('#success-container').html(data.content);
                $('#signup-container').hide();
                success()
                location.replace("$liffProfileUrl");
                
            } else if (data.validation) {
                // server validation failed
                yiiform.yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
                $('#btnAwait').hide();
                $('#btn-login').show();
            
            } else {
                // incorrect server response
            }
        }
    });
        return false;
})




function logout(){
    if (liff.isLoggedIn()) {
  liff.logout();
//   $("#main-modal").modal("toggle");
}
}

async function main(){
  await liff.init({ liffId: "$liffLogin",withLoginOnExternalBrowser:true});
  if (liff.isLoggedIn()) {
            const profile = await liff.getProfile();
            $('#loginform-line_id').val(profile.userId)
            document.getElementById("pictureUrl").src = profile.pictureUrl;
        
        //   await checkProfile()
        } else {
          liff.login();
        }
  }
  main();
  
JS;
$this->registerJs($js);
?>