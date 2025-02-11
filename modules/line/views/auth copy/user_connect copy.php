<?php
use yii\web\View;
// use app\themes\assets\AppAsset;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\components\SiteHelper;
use kartik\widgets\ActiveForm;
$site = Categorise::findOne(['name' => 'site']);
$color = isset($site->data_json['theme_color']) ? $site->data_json['theme_color'] : '';
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : '';

$this->title = 'กรุณายืนยันตัวตน';
$this->params['breadcrumbs'][] = $this->title;
$this->title = "ระบบยืนยันตัวตน";
$this->registerJsFile('https://unpkg.com/vconsole@latest/dist/vconsole.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
<?php Pjax::begin(['id' => 'sm-container', 'enablePushState' => true, 'timeout' => 5000]); ?>

<style>
input,
input::placeholder {
    font-weight: 200;
}
</style>

<div class="d-flex justify-content-center mb-1" style="margin-top:15%">
    <img id="pictureUrl" width="25%" class="avatar avatar-xxl border border-primary-subtl card-img-top  shadow">
</div>
<div id="signup-container" class="row justify-content-center">

        <?php $form = ActiveForm::begin(['id' => 'blank-form','enableAjaxValidation' => false,]); ?>
        <div class="container row justify-content-center" data-aos="fade-up" data-aos-duration="4000">
            <div class="col-lg-4 col-md-12 col-sm-12">
                <?= $form->field($model, 'line_id')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'username')->textInput(['autofocus' => true,'placeholder' => 'ระบุอีเมล','class' => 'form-control form-control-lg rounded-pill border-0'])->label('อีเมล') ?>
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'กำหนดรหัสผ่าน','class' => 'form-control form-control-lg rounded-pill border-0'])->label('รหัสผ่าน') ?>
                <div class="d-inline-block w-100">

                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-lg btn-primary account-btn rounded-pill" id="btn-login" type="submit">
                            <i class="fa-solid fa-fingerprint"></i> เข้าสู่ระบบ
                        </button>

                    </div>
                </div>

            </div>
        </div>

        <?php ActiveForm::end(); ?>


</div>
<p id="userId"></p>
<p id="displayName"></p>
<p id="statusMessage"></p>
<p id="getDecodedIDToken"></p>
<button class="btn btn-success" onclick="return logout()">Lofout</button>


<?php
use app\modules\employees\models\Employees;

$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffUserConnect = SiteHelper::getInfo()['line_liff_user_connect'];
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffProfileUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_profile'];
$liffRegisterUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_register'];

$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$js = <<< JS

 var vConsole = new window.VConsole();
 
  
// $('#blank-form').on('beforeSubmit', function (e) {
// e.preventDefault
//     var yiiform = $(this);
//     $('#btnAwait').show();
//     $('#btn-login').hide();

//     $.ajax({
//         type: yiiform.attr('method'),
//             url: yiiform.attr('action'),
//             data: yiiform.serializeArray(),
//         dataType: "json",
//         success: function (data) {
//             if(data.success) {
//                 // data is saved
//                 $('#success-container').html(data.content);
//                 $('#signup-container').hide();
//                 success()
//                 location.replace("$liffProfileUrl");
                
//             } else if (data.validation) {
//                 // server validation failed
//                 yiiform.yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
//                 $('#btnAwait').hide();
//                 $('#btn-login').show();
            
//             } else {
//                 // incorrect server response
//             }
//         }
//     });
//         return false;
// })



function logout(){
    if (liff.isLoggedIn()) {
  liff.logout();
//   $("#main-modal").modal("toggle");
}
}

async function main(){
  await liff.init({ liffId: "$liffUserConnect",withLoginOnExternalBrowser:true});
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
$this->registerJs($js,View::POS_END);
?>
<?php Pjax::end() ?>