<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

<style>
.avatar-online>img {
    width: 45px;
}

.dropdown-toggle::after {
    display: none;
}


.dropdown-menu {
  animation: 0.8s slidedown;
}
@keyframes slidedown {
  from {
    transform: translateY(10%);
  }
  to {
    transform: translateY(50);
    
  }
}

.header-top{
    /* background-color:hsl(231.76deg 42.86% 23.33%); */
    /* background-color:#212A51; */
    /* background-color: #3F51B5; */
    background-color:#574ec1;
    color: rgb(255, 255, 255);
}

</style>
<header>
    <!-- <div class="pt-1 text-bg-light border-bottom"> -->
    <div class="pt-1 border-bottom header-top">
        <div class="container-xxl">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <a href="/" class="d-flex align-items-center my-lg-0 me-lg-auto text-decoration-none">

                <i class="bi bi-box text-light fs-1"></i> <span class="fs-2 ml-2 text-light">ERP</span>
                </a>

                <ul class="nav col-12 col-lg-auto my-2 justify-content-center text-small nav-menu">
                    <li>
                        <a href="<?=Url::to(['/'])?>"
                            class="nav-link text-secondary d-flex flex-column text-center justify-content-center text-white">
                            <i class="bi bi-house-dash-fill fs-4"></i>
                            หน้าหลัก
                        </a>
                    </li>
                    <li>
                        <a href="<?=Url::to(['/hr'])?>"
                            class="nav-link text-secondary d-flex flex-column text-center justify-content-center text-white">
                            <i class="bi bi-people-fill fs-4"></i>
                            บุคลากร
                        </a>
                    </li>
                    <li>
                        <a href="<?=Url::to('/am')?>"
                            class="nav-link text-secondary d-flex flex-column text-center justify-content-center text-white">
                            <i class="bi bi-folder-check fs-4"></i>
                            ทรัพย์สิน
                        </a>
                    </li>
                    <li>
                    <a href="<?=Url::to('/sm')?>"
                            class="nav-link text-secondary d-flex flex-column text-center justify-content-center text-white">
                            <i class="bi bi-box fs-4"></i>
                            พัสดุ
                        </a>
                    </li>
                    <!-- <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a href="#"
                            class="nav-link text-secondary d-flex flex-column text-center justify-content-center">
                            <i class="bi bi-app-indicator fs-4"></i>
                            App
                        </a>
                    </li> -->
                     <!-- Quick links  -->
                     <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <!-- <a class="nav-link text-secondary d-flex flex-column text-center justify-content-center dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"> -->
                        <a class="nav-link text-secondary d-flex flex-column text-center justify-content-center dropdown-toggle hide-arrow text-white" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="true">
                        <i class="bi bi-grid-fill fs-4"></i>
                            บริการ
                        </a>
                        <?=$this->render('app_ shortcuts')?>
                    </li>
                    <!-- Quick links -->
                    <li>
                        <a href="<?=Url::to('/setting')?>"
                            class="nav-link text-secondary d-flex flex-column text-center justify-content-center text-white">
                            <i class="bi bi-gear fs-4"></i>
                            ตั้งค่า
                        </a>
                    </li>
                    <!-- User -->
                    <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar avatar-online d-flex justify-content-center">
                                <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/avatars/1.png"
                                    alt="" class="w-px-40 h-auto rounded-circle">
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 mt-4">
                            <li>
                                <a class="dropdown-item" href="pages-account-settings-account.html">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar avatar-online">
                                                <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/avatars/1.png"
                                                    alt="" class="w-px-10 h-auto rounded-circle">
                                            </div>

                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-light d-block">John Doe</span>
                                            <small class="">Admin</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?=Url::to(['/profile'])?>">
                                    <i class="bx bx-user me-2"></i>
                                    <span class="align-middle">โปรไฟล์ของฉัน</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?=Url::to(['/profile/setting'])?>">
                                    <i class="bx bx-cog me-2"></i>
                                    <span class="align-middle">ตั้งค่า</span>
                                </a>
                            </li>

                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <?php if(!Yii::$app->user->isGuest):?>
                                <?php
                     echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                     . Html::submitButton(
                         '<i class="bx bx-power-off me-2"></i> ออกจากระบบ (' . Yii::$app->user->identity->username . ')',
                         ['class' => 'dropdown-item']
                     )
                     . Html::endForm();
                    ?>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </li>
                    <!--/ User -->

                </ul>
            </div>
        </div>
    </div>
    <div class="px-3 p-1 border-bottom mb-3 text-bg-light">
        <div class="container-xxl d-flex flex-wrap justify-content-between">
            <?php if(Yii::$app->controller->module->id == 'hr'):?>

            <?=$this->render('@app/modules/hr/menu')?>

            <?php elseif(Yii::$app->controller->module->id == 'sm'):?>

            <?=$this->render('@app/modules/sm/menu')?>

            <?php elseif(Yii::$app->controller->module->id == 'pm'):?>

<?=$this->render('@app/modules/pm/menu')?>

<?php elseif(Yii::$app->controller->module->id == 'am'):?>

<?=$this->render('@app/modules/am/menu')?>
            <?php else:?>

            <nav class="nav" aria-label="Secondary navigation">
                <a class="nav-link active" aria-current="page" href="<?=Url::to(['/hr'])?>"><i
                        class="bi bi-bar-chart fs-5"></i> <?=$this->title?></a>
            </nav>
            <?php endif;?>




            <div class="text-end">
                <form class="col-12 col-lg-auto mb-2 mb-lg-0 me-lg-auto" role="search">
                    <input type="search" class="form-control rounded-pill" id="search-bar" placeholder="ค้นหา..."
                        aria-label="Search">
                </form>
            </div>
        </div>
    </div>
</header>
<?php
$js = <<< JS

$('#search-bar').focus(function (e) { 
    e.preventDefault();
    beforLoadModal();
    
});
JS;
$this->registerJS($js);
?>