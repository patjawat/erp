<?php
use yii\web\View;
use yii\helpers\Url;
/** @var yii\web\View $this */
use yii\helpers\Html;
$this->registerJsFile('https://unpkg.com/vconsole@latest/dist/vconsole.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<style>
    /* HTML: <div class="loader"></div> */
.loader {
  width: 100px;
  height: 40px;
  --g: radial-gradient(farthest-side,#0000 calc(95% - 3px),#fff calc(100% - 3px) 98%,#0000 101%) no-repeat;
  background: var(--g), var(--g), var(--g);
  background-size: 30px 30px;
  animation: l9 1s infinite alternate;
}
@keyframes l9 {
  0% {
    background-position: 0 50%, 50% 50%, 100% 50%;
  }
  20% {
    background-position: 0 0, 50% 50%, 100% 50%;
  }
  40% {
    background-position: 0 100%, 50% 0, 100% 50%;
  }
  60% {
    background-position: 0 50%, 50% 100%, 100% 0;
  }
  80% {
    background-position: 0 50%, 50% 50%, 100% 100%;
  }
  100% {
    background-position: 0 50%, 50% 50%, 100% 50%;
  }
}
</style>

<div class="page-title-box">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-5 col-xl-4">
                <div class="page-title">
                    <h5 class="mb-1 text-white">Profile</h5>

                </div>
            </div>
            <div class="col-sm-7 col-xl-8">
				<div class="d-flex justify-content-sm-end">

				
			</div>
            </div>
        </div>
    </div>
</div>



<div id="avatar"></div>

<div  style="margin-top:40%" id="loading">
        <div class="d-flex justify-content-center">
            <div class="loader"></div>
        </div>
</div>


<?php
use app\components\SiteHelper;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffLofinUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_login'];

$js = <<< JS

let userId = "";

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
                  if(res.status == false){
                      location.replace("$liffLofinUrl");
                  }
                  if(res.status == true){
                      $('#avatar').html(res.avatar)
                      $('#loading').hide()
                  }
              }
          });
      }

      

async function main() {
  await liff.init({ liffId:"$liffProfile", withLoginOnExternalBrowser: true });

  if (liff.isLoggedIn()) {
    try {
      var profile = await liff.getProfile();
      
      $('#loginform-line_id').val(profile.userId);
      $("#pictureUrl").attr("src", profile.pictureUrl);
      console.log(profile);
      await checkProfile()
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