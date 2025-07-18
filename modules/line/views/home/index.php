<?php
use yii\web\View;
use yii\helpers\Url;
/** @var yii\web\View $this */
use yii\helpers\Html;
use app\components\SiteHelper;
use app\components\UserHelper;
use yii\web\HtmlResponseFormatter;
use app\components\ApproveHelper;
$totalNotification = ApproveHelper::Info()['total'];
$site = SiteHelper::getInfo();
$me = UserHelper::GetEmployee();
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

<div id="loading">
        <div class="d-flex justify-content-center">
            <div class="loader"></div>
        </div>
</div>

<?php if($me):?>
<div id="wraperContainer" style="display:none">
    <div class="page-title-box-line mb-5">
        <div class="d-flex justify-content-between align-items-center">
            <div class="page-title-line">
            </div>
        </div>
    </div>


    <div class="card employee-welcome-card flex-fill shadow mb-2">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div class="welcome-content">
                    <h6>สวัสดี, <?=$me->fullname?></h6>
                    <?=Html::a('<i class="fa-solid fa-clipboard-user"></i> โปรไฟล์',['/line/profile'],['class' => 'btn btn-primary shadow rounded-pill'])?>
                </div>
                <div class="welcome-img">
                    <?=Html::img($me->ShowAvatar(), ['class' => 'avatar avatar-lg border border-white'])?>
                </div>
            </div>

        </div>
    </div>

    <?php echo $this->render('app_menu')?>
    
    <?php echo $this->render('document')?>


<?php endif;?>

<div class="offcanvas offcanvas-start" data-bs-backdrop="static" tabindex="-1" id="staticBackdrop"
    aria-labelledby="staticBackdropLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="staticBackdropLabel">เมนูหลัก</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        soon
    </div>
</div>



<?php
$urlCheckProfile = Url::to(['/line/auth/check-profile']);
$liffProfile = SiteHelper::getInfo()['line_liff_home'];
$liffLoginUrl= 'https://liff.line.me/'.SiteHelper::getInfo()['line_liff_login'];

$js = <<< JS

let userId = "";

// ตรวจสอบสิทธิในระบบและยืนยันตัวตน
// async function checkProfile(){
//           const {userId} = await liff.getProfile()
//           await $.ajax({
//               type: "post",
//               url: "$urlCheckProfile",
//               data:{
//                   line_id:userId
//               },
//               dataType: "json",
//               success: function (res) {
//                 alert(res)
//                   if(res.status == false){
//                     //   location.replace("$liffLoginUrl");
//                   }
//                   if(res.status == true){
//                       $('#avatar').html(res.avatar)
//                       $('#loading').hide()
//                       $('#wraperContainer').show()
//                   }
//               }
//           });
//       }

async function checkProfile() {
    try {
        const { userId } = await liff.getProfile();
        console.log("UserID:", userId);

        await $.ajax({
            type: "POST",
            url: "$urlCheckProfile",
            data: { line_id: userId },
            dataType: "json",
            success: function (res) {
                if (res.status === false) {
                    location.replace("$liffLoginUrl");
                } else if (res.status === true) {
                    $('#loading').hide();
                    $('#wraperContainer').show();
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                alert('Error:'+status+' - '+ error);
            }
        });
    } catch (error) {
        console.error("LIFF Error:", error);
        alert('LIFF Error:' +error.message);
    }
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