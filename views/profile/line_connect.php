<?php
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap5\Html;
use yii\widgets\MaskedInput;
use app\components\SiteHelper;
use app\modules\usermanager\models\User;
use app\modules\employees\models\Employees;
$user = User::findOne(Yii::$app->user->id);
// $assets = AppAsset::register($this);
$siteInfo = SiteHelper::getInfo();
$this->title = "ระบบลงทะเบียน";

?>
<style>
  .card-img-top{
    /* max-width:200px; */
  }
</style>
<?php if($user->line_id):?>
  <div class="row d-flex justify-content-center align-items-center">
  <div class="col-2">
    <div class="card">
    <?php if(isset($siteInfo['line_qrcode'])):?>
                <img src="<?php  echo $siteInfo['line_qrcode']?>" alt="">
                <?php endif;?>
      <div class="card-body text-center">
        <h4 class="card-title">เชื่อม LineID สำเร็จ!</h4>
        <button class="btn btn-danger" id="lineConnect">เชื่อม LineID</button>
      </div>
    </div>

  </div>

</div>

  <?php else:?>
    

<div class="row d-flex justify-content-center align-items-center">
  <div class="col-2">
    <div class="card">
      <img id="pictureUrl" class="card-img-top">
      <div class="card-body text-center">
        <h4 class="card-title" id="displayName">xxx</h4>
        <button class="btn btn-danger" id="lineConnect">เชื่อม LineID</button>
      </div>
    </div>

  </div>

</div>
<?php endif;?>

<?php // Html::a('Lgin',['/profile/line-connect'],['class' => 'btn btn-primary','target' => '_blank'])?>
<?php

$lineConnectUrl = Url::to(['/profile/line-connect']);
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffLineConnect = SiteHelper::getInfo()['line_liff_user_connect'];
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffRegisterUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_register'];

$urlCheckProfile = Url::to(['/line/auth/check-profile']);

$js = <<< JS

let lineID = '';

$('#lineConnect').click(function (e) { 
  e.preventDefault();
  console.log(lineID);
  
  Swal.fire({
            title: "ยืนยัน?",
            text: "เชื่อม LineID เข้ากับระบบ!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "ยกเลิก!",
            confirmButtonText: "ใช่, ยืนยัน!"
            }).then((result) => {
            if (result.isConfirmed) {
                
              $.ajax({
                type: "post",
                url: "$lineConnectUrl",
                data:{line_id:lineID},
                dataType: "json",
                success: function (res) {
                  console.log(res);
                  if(res.status == 'success'){
                    window.location.reload(true);
                  }
                  
                }
              });

            }else{

            }
            });
            
});
\$('#form-register').on('beforeSubmit', function (e) {
                var form = \$(this);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        location.replace("https://liff.line.me/2005893839-1vEqqXoQ");
                        form.yiiActiveForm('updateMessages', response.validation, true);
                        if(response.status == true) {
                            $('#welcome').show()
                            console.log('register Success');
                        }
                    }
                });
                return false;
            });

function logout(){
    if (liff.isLoggedIn()) {
  liff.logout();
  $("#main-modal").modal("toggle");
}
}


// ตรวจสอบสิทธิในระบบและยืนยันตัวตน
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
                  console.log('CheckProfile');
                  console.log(res);
                  if(res.status == false){
                      location.replace("$liffRegisterUrl");
                  }
                  if(res.status == true){
                      $('#avatar').html(res.avatar)
                      $('#loading').hide()
                  }
              }
          });
      }

      

async function main() {
  await liff.init({ liffId:"$liffLineConnect", withLoginOnExternalBrowser: true });

  if (liff.isLoggedIn()) {
    try {
      var profile = await liff.getProfile();
      
      lineID = profile.userId;
      $('#loginform-line_id').val(profile.userId);
      $("#pictureUrl").attr("src", profile.pictureUrl);
      console.log(profile);
      $("pictureUrl").src = profile.pictureUrl;
        $("#displayName").html(profile.displayName)
        
      // await checkProfile()
    } catch (error) {
      console.error("Error fetching profile:", error);
    }
  } else {
    liff.login();
  }
}

main();


JS;
$this->registerJs($js,View::POS_END);
?>

