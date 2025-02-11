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



<style>
/* .employee-welcome-card {
    margin-bottom: 24px;
    position: relative;
    background: linear-gradient(90.31deg, #d2ebff -1.02%, #0866ad 99.59%);
    transition: border-color 0.3s ease, transform 0.3s ease;
}

.employee-welcome-card::before {
    z-index: 1;
    content: "";
    position: absolute;
    top: 0;
    right: 20px;
    border-radius: 0px 0px 10px 0px;
    width: 100px;
    height: 100%;
    transform: skew(10deg);
    background: linear-gradient(90.31deg, #5ca1d4 -1.02%, #0866ad 132.59%);
    animation: fadeIn 1s ease-in-out;
}

.welcome-img {
    z-index: 20;
}

@keyframes fadeInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }

    to {
        opacity: 1;
    }
}

@keyframes fadeInRight {
    from {
        opacity: 50;
        transform: translateX(100px);
    }

    to {
        opacity: 1;
    }
}

@keyframes fadeIn {
    0% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
} */

</style>

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

<!-- 
<div class="p-2">

            <div class="row row-cols-2 row-cols-sm-2 row-cols-md-2 g-4">
                <div class="col">
                    <a href="<?php echo Url::to(['/settings/company'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                                <i class="fa-solid fa-house-medical-flag fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">ข้อมูลองค์กร</h6>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="<?php echo Url::to(['/setting'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                                <i class="fas fa-palette fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">ตั้งค่าสี</h6>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="<?php echo Url::to(['/usermanager/user'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="fa-solid fa-user-gear fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">ผู้ใช้งาน</h6>
                            </div>
                        </div>

                    </a>
                </div>
                <div class="col">
                    <a href="<?php echo Url::to(['/settings/line-group'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="fa-brands fa-line fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">LineNotify</h6>
                            </div>
                        </div>
                    </a>
                </div>

                

            </div>


        </div>
         -->


<div class="offcanvas offcanvas-start" data-bs-backdrop="static" tabindex="-1" id="staticBackdrop"
    aria-labelledby="staticBackdropLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="staticBackdropLabel">เมนูหลัก</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="p-2">

            <div class="row row-cols-2 row-cols-sm-2 row-cols-md-2 g-4">
                <div class="col">
                    <a href="<?php echo Url::to(['/settings/company'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                                <i class="fa-solid fa-house-medical-flag fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">ข้อมูลองค์กร</h6>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="<?php echo Url::to(['/setting'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                                <i class="fas fa-palette fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">ตั้งค่าสี</h6>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="<?php echo Url::to(['/usermanager/user'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="fa-solid fa-user-gear fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">ผู้ใช้งาน</h6>
                            </div>
                        </div>

                    </a>
                </div>
                <div class="col">
                    <a href="<?php echo Url::to(['/settings/line-group'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="fa-brands fa-line fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">LineNotify</h6>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="<?php echo Url::to(['/settings/line-official'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="fa-brands fa-line fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">LineOfficial</h6>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="<?php echo Url::to(['/settings/line-messaging'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="fa-brands fa-line fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">Messaging API</h6>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="<?php echo Url::to(['/hr/categorise','title'=>'การตั้งค่าบุคลากร'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="fa-solid fa-clipboard-user fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center"> บุคลากร</h6>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="<?php echo Url::to(['/am/setting'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="bi bi-folder-check fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center"> ทรัพย์สิน</h6>
                            </div>
                        </div>
                    </a>
                </div>

            </div>


        </div>
    </div>
</div>



<?php
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