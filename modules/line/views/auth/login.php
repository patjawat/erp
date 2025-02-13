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

<!-- Login 9 - Bootstrap Brain Component -->
<section class="bg-primary py-3 py-md-5 py-xl-8 h-screen-100  mt-5">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-12 col-md-6 col-xl-7">
                <div class="d-flex justify-content-center text-bg-primary">
                    <div class="col-12 col-xl-9">
                        <h1 class="text-white text-center">
                            <i class="bi bi-box"></i> ERP Hospital
                        </h1>
                        <hr class="border-primary-subtle">
                        <div class="d-flex justify-content-center mb-4 mt-0 fs-3 gap-2">
                            <?=SiteHelper::getInfo()['company_name'] !='' ?  (SiteHelper::getInfo()['company_name']) : ''?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-5">
                <div class="card border-0 rounded-4">
                    <div class="card-body p-3 p-md-4 p-xl-5">
                        <h3 class="text-center">กรุณายืนยันตัวตน</h3>
                        <div class="d-flex justify-content-center mt-1" >
                            <img id="pictureUrl" class="avatar avatar-xl border border-3 border-primary shadow">
                        </div>
                        <div id="signup-container" class="row justify-content-center">
                            <?php $form = ActiveForm::begin(['id' => 'blank-form']); ?>
                            <div class="ยข/">
                                <?= $form->field($model, 'line_id')->hiddenInput()->label(false) ?>
                                <?= $form->field($model, 'username')->textInput(['autofocus' => true,'placeholder' => 'ระบุอีเมล','class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10'])->label('อีเมล') ?>
                                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'กำหนดรหัสผ่าน','class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10'])->label('รหัสผ่าน') ?>
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
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="mb-4">
                                        <p>ยังไม่มีบัญชี ?
                                            <?=Html::a('<i class="fa-solid fa-pen-to-square"></i> ลงทะเบียน',['/line/auth/register'],['class' => 'text-primary'])?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            <div class="text-end mt-0">
                <p class="text-center text-white">ผู้ให้การสนับสนุน</p>
                <div class="d-flex justify-content-center gap-5">
                    <?=Html::img('@web/banner/banner2.png',['style'=> 'width:60px;height:60px'])?>
                    <?=Html::img('@web/banner/banner1.png',['style'=> 'width:130px;height:70px'])?>
                    <?=Html::img('@web/banner/banner3.png',['style'=> 'width:80px;height:70px'])?>

                </div>
            </div>
        </div>

    </div>
</section>

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
            $('#content').show()
            $('#loading').hide()
        
        //   await checkProfile()
        } else {
          liff.login();
        }
  }
  main();
  
JS;
$this->registerJs($js);
?>