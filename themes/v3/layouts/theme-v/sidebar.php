<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
$moduleId = Yii::$app->controller->module->id; 
?>


<style>
.employee-welcome-card {
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
}



</style>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="scroll-content" id="metismenu" data-scrollbar="true" tabindex="-1" style="overflow: hidden; outline: none;">
        <div class="p-2 employee-welcome">
            <div class="card employee-welcome-card shadow">
                <div class="card-body" style="z-index: 2;">
                    <div class="d-flex flex-column justify-content-center">

                        <?=Html::img(UserHelper::GetEmployee()->ShowAvatar(), ['class' => 'rounded-pill border border-white w-50 mb-3','style' => 'margin-left:3rem;max-height:100px;'])?>
                        <h6 class="text-center text-white"><?=UserHelper::GetEmployee()->fullname?></h6>
                        <h6 class="text-center"><?=UserHelper::GetEmployee()->positionName()?></h6>

                        <div class="d-flex justify-content-between gap-2">
                            <?=Html::a('<i class="fa-solid fa-clipboard-user"></i> MyDashboard',['/me'],['class' => 'btn btn-primary shadow rounded-pill'])?>
                            <?=Html::a('<i class="fa-solid fa-power-off"></i>',['/site/logout'],['class' => 'btn btn-danger shadow rounded-pill logout'])?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="scroll-content">
            <ul id="side-menu" class="metismenu list-unstyled">
                <li class="side-nav-title side-nav-item menu-title fs-6">
                    <i class="bi bi-ui-checks fs-5"></i>
                    Menu
                </li>
                
                <li class="">
                    <a href="<?= Url::to(['/']) ?>" class="side-nav-link" aria-expanded="false">
                        <i class="fa-solid fa-gauge-high"></i> <span>Main Dashboard</span>
                        <!-- <span class="menu-arrow"></span> -->
                    </a>
                    
                    
                    <!-- <li class="side-nav-title side-nav-item menu-title fs-6">
                        <i class="bi bi-ui-checks fs-5"></i> บริการ
                    </li> -->

                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me/leave']) ?>">
                        <i class="fa-solid fa-calendar"></i>
                        <span> ทะเบียนการลา</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me/time']) ?>">
                    <i class="fa-solid fa-business-time"></i>
                        <span> เวลาเข้า-ออก</span>
                    </a>
                </li>
                
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me/salary']) ?>">
                    <i class="fa-solid fa-wallet"></i>
                        <span> เงินเดือน</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/profile/setting']) ?>">
                    <i class="fa-solid fa-user-gear"></i>
                        <span> ตั้งค่าโปรไฟล์</span>
                    </a>
                </li>
                <!-- <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me/purchase']) ?>">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span> ขอซื้อขอจ้าง</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me/repair']) ?>">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span> แจ้งซ่อม</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/inventory']) ?>">
                    <i class="fa-solid fa-cart-shopping"></i>
                        <span> เบิกวัสดุ</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me']) ?>">
                    <i class="fa-solid fa-route"></i>
                        <span> จองรถ</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me']) ?>">
                    <i class="fa-solid fa-person-chalkboard"></i>
                        <span> จองห้องประชุม</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me']) ?>">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>ความเสี่ยง</span>
                    </a>
                </li> -->
               
                

                <?php if(Yii::$app->user->can('admin')):?>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/settings']) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-settings fs-4 me-2">
                            <path
                                d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span> การตั้งค่าระบบ</span>
                    </a>
                </li>
                <?php endif;?>
                <!-- <li>
                    <a class="side-nav-link" href="<?= Url::to(['/pm']) ?>">
                        <i class="bi bi-folder-check fs-4"></i>
                        <span> แผนงานและโครงการ</span>
                    </a>
                </li> -->
                <li>


        </div>
        <div class="scrollbar-track scrollbar-track-x" style="display: none;">
            <div class="scrollbar-thumb scrollbar-thumb-x" style="width: 250px; transform: translate3d(0px, 0px, 0px);">
            </div>
        </div>
        <div class="scrollbar-track scrollbar-track-y" style="display: block;">
            <div class="scrollbar-thumb scrollbar-thumb-y"
                style="height: 98.1478px; transform: translate3d(0px, 0px, 0px);"></div>
        </div>
    </div>

</aside>
<?php
$js = <<< JS

$("body").on("click", ".logout", async function (e) {
  e.preventDefault();
  var url = $(this).attr("href");

  await Swal.fire({
    title: "ยืนยัน?",
    text: "ต้องการออกจากระบบ!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ลบเลย!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    console.log("result", result.value);
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: url,
        dataType: "json",
        success:  function (response) {
          
        },
      });
    }
  });
});



JS;
$this->registerJS($js, View::POS_END);

?>