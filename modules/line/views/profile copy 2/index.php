<?php
use yii\web\View;
use yii\helpers\Url;
/** @var yii\web\View $this */
use yii\helpers\Html;
$this->registerJsFile('https://unpkg.com/vconsole@latest/dist/vconsole.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<div class="page-title-box">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-5 col-xl-4">
                <div class="page-title">
                    <h5 class="mb-1 text-white">โปรไฟ์</h5>

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
<div class="card" id="loading">
    <div class="card-body">
        <div class="d-flex justify-content-center"><div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div></div><h6 class="text-center mt-3">Loading...</h6>
    </div>
</div>


<?php
use app\components\SiteHelper;
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfile = SiteHelper::getInfo()['line_liff_profile'];
$liffRegisterUrl = 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_register'];

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

      
async function main(){
  await liff.init({ liffId: "$liffProfile",withLoginOnExternalBrowser:true});
  if (liff.isLoggedIn()) {
          const profile = await liff.getProfile();
          await checkProfile()
        } else {
          liff.login();
        }
  }
  main();

      
    

JS;
$this->registerJs($js,View::POS_END);
?>