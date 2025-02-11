<?php
use yii\web\View;
use yii\helpers\Url;
/** @var yii\web\View $this */
use yii\helpers\Html;
use app\components\SiteHelper;
use app\components\UserHelper;
use yii\web\HtmlResponseFormatter;
use app\components\NotificationHelper;
$totalNotification = NotificationHelper::Info()['total'];
$site = SiteHelper::getInfo();
$me = UserHelper::GetEmployee();
$this->registerJsFile('https://unpkg.com/vconsole@latest/dist/vconsole.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<!-- <div id="avatar"></div> -->
<div class="card" style="margin-top:40%" id="loading">
    <div class="card-body">
        <div class="d-flex justify-content-center">
            <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div>
        </div>
        <h6 class="text-center mt-3">Loading...</h6>
    </div>
</div>

<div id="wraperContainer" style="display:none">

    <div class="page-title-box-line mb-5">
        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="page-title-line">

                <?php if($me):?>

                <div class="d-flex gap-2">
                    <?=Html::img('@web/banner/banner2.png', ['class' => 'avatar avatar-md me-0 mt-2'])?>

                    <div class="avatar-detail">
                        <h5 class="mb-0 text-white text-truncate mt-3"><?php echo $site['company_name']?></h5>
                        <p class="text-white mb-0 fs-13">ERP Hospital</p>
                    </div>
                </div>
                <?php endif;?>
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop"
                aria-controls="staticBackdrop">
                <i class="fa-solid fa-bars fs-3"></i>
            </button>

        </div>
    </div>


    <div class="card employee-welcome-card flex-fill shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div class="welcome-content">
                    <h6>สวัสดี, <?=$me->fullname?></h6>
                    <?=Html::a('<i class="fa-solid fa-clipboard-user"></i> โปรไฟล์',['#'],['class' => 'btn btn-primary shadow rounded-pill'])?>
                </div>
                <div class="welcome-img">
                    <?=Html::img($me->ShowAvatar(), ['class' => 'avatar avatar-lg border border-white'])?>
                </div>
            </div>

        </div>
    </div>





    <div class="p-2 mb-3">
        <h6 class="text-white">App Menu</h6>
        <div class="overflow-scroll d-flex flex-row borde-0 gap-4 mt-4"
            style="white-space: nowrap; max-width: 100%; height: 100px;">
            <div class="d-flex flex-column gap-2 border-0 text-white">
                <div class=" bg-secondary rounded-pill p-3 shadow border border-white">
                    <i class="fa-solid fa-screwdriver-wrench fs-1"></i>
                </div>
                <p class="text-center">แจ้งซ่อม</p>
            </div>
            <div class="d-flex flex-column gap-2 border-0 text-white">
                <div class=" bg-secondary rounded-pill p-3 shadow border border-white">
                    <i class="fa-solid fa-calendar-day fs-1"></i>
                </div>
                <p class="text-center">ขอลา</p>
            </div>
            <div class="d-flex flex-column gap-2 border-0 text-white">
                <div class=" bg-secondary rounded-pill p-3 shadow border border-white">
                    <i class="fa-solid fa-car-side fs-1"></i>
                </div>
                <p class="text-center">จองรถ</p>
            </div>
            <div class="d-flex flex-column gap-2 border-0 text-white">
                <div class=" bg-secondary rounded-pill p-3 shadow border border-white">
                    <i class="fa-solid fa-person-chalkboard fs-1"></i>
                </div>
                <p class="text-center">ห้องประชุม</p>
            </div>

            <div class="d-flex flex-column gap-2 border-0 text-white">
                <div class=" bg-secondary rounded-pill p-3 shadow border border-white">
                    <i class="fa-solid fa-triangle-exclamation fs-1 ms-1"></i>
                </div>
                <p class="text-center">ความเสี่ยง</p>
            </div>

        </div>
    </div>

    <h6 class="text-white">หนังสือ/ประกาศ/ประชาสัมพันธ์</h6>
    <div class="card rounded-4">
        <div class="card-body rounded-4" style="background:rgba(241, 238, 240, 0.98); min-height:200px">




        </div>
    </div>



</div>



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
                      $('#wraperContainer').show()
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