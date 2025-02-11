<?php
use yii\web\View;
// use app\themes\assets\AppAsset;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\widgets\MaskedInput;
use app\components\SiteHelper;
use yii\bootstrap5\ActiveForm;
use app\modules\employees\models\Employees;
// $assets = AppAsset::register($this);
$this->title = "Authentication";
?>

<style>
    #displayName{
        font-family: 'Prompt';
    }
</style>
<div class="card border">
    <div class="card-body">

<div id="signup-container" class="row justify-content-center mt-5">
    <div class="sign-in-from">
        <h4 class="text-center mb-3 text-primary" id="title"><?=$this->title?></h4>
        <div class="line-profile">
    <div class="d-flex justify-content-center align-items-center">
        <div class="round-image">
            <img id="pictureUrl" class="rounded-circle" width="200">
        </div>
    </div>
    <div class="text-center">
        <h4 class="mt-3" id="displayName"></h4>
    </div>
</div>
    <p id="debug"></p>

        <?php $form = ActiveForm::begin(['id' => 'blank-form','enableAjaxValidation' => false,]); ?>
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-12 col-sm-12">
                <?= $form->field($model, 'line_id')->hiddenInput()->label(false) ?>
                <div class="d-inline-block w-100">
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-lg btn-primary account-btn rounded-pill" id="btn-regster" type="submit">
                            <i class="fa-solid fa-circle-check"></i> ลงทะเบียน</button>

                        <button class="btn btn-lg btn-primary account-btn rounded-pill d-none" id="btn-loading"
                            type="button" disabled="">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Loading...
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>



</div>
</div>


<img id="pictureUrl" width="25%">
  <p id="userId"></p>
  <p id="displayName"></p>
  <p id="statusMessage"></p>
  <p id="getDecodedIDToken"></p>



<?php

$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfileUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_profile'];
$liffRegister = SiteHelper::getInfo()['line_liff_register'];
$liffRegisterUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_register'];
$js = <<< JS


async function checkProfile(){
    const {userId} = await liff.getProfile()
    await $.ajax({
        type: "post",
        url: "$urlCheckProfile",
        data:{
            line_id:userId
        },
        dataType: "json",
        success: function (res) {
            console.log(res);
            if(res.status == false){
                // location.replace("https://liff.line.me/2005893839-9qRwwMWG");
            }
            if(res.status == true){
                location.replace("$liffProfileUrl");
            }
        }
    });
    console.log('check profile');
}


async function main(){
  await liff.init({ liffId: "$liffRegister",withLoginOnExternalBrowser:true});
  if (liff.isLoggedIn()) {
            const profile = await liff.getProfile();
            document.getElementById("pictureUrl").src = profile.pictureUrl;
            document.getElementById("userId").innerHTML = '<b>UserId:</b> ' + profile.userId;
            document.getElementById("displayName").innerHTML = '<b>DisplayName:</b> ' + profile.displayName;
            document.getElementById("statusMessage").innerHTML = '<b>StatusMessage:</b> ' + profile.statusMessage;
            document.getElementById("getDecodedIDToken").innerHTML = '<b>Email:</b> ' + liff.getDecodedIDToken().email;
        
        //   await checkProfile()
        } else {
          liff.login();
        }
  }
  main();

      
  
// function runApp() {
//       liff.getProfile().then(profile => {
//         document.getElementById("pictureUrl").src = profile.pictureUrl;
//         document.getElementById("userId").innerHTML = '<b>UserId:</b> ' + profile.userId;
//         document.getElementById("displayName").innerHTML = '<b>DisplayName:</b> ' + profile.displayName;
//         document.getElementById("statusMessage").innerHTML = '<b>StatusMessage:</b> ' + profile.statusMessage;
//         document.getElementById("getDecodedIDToken").innerHTML = '<b>Email:</b> ' + liff.getDecodedIDToken().email;

//         checkProfile()
//       }).catch(err => console.error(err));
//     }
//     liff.init({ liffId: "2005893839-1vEqqXoQ" }, () => {
//       if (liff.isLoggedIn()) {
//         runApp()
//       } else {
//         liff.login();
//       }
//     }, err => console.error(err.code, error.message));

    

JS;
$this->registerJs($js,View::POS_END);
?>

