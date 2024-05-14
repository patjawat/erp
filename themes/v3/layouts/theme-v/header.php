<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;
?>
<style>
.color-switchers .btn.blue.active {
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #4070F4;
}

.color-box .color-switchers .btn {
    display: inline-block;
    height: 40px;
    width: 40px;
    border: none;
    outline: none;
    border-radius: 50%;
    margin: 0 5px;
    cursor: pointer;
    background: #4070F4;
}
</style>
<header id="page-topbar" class="topbar-header">
    <div class="navbar-header">
        <div class="left-bar">
            <div class="navbar-brand-box">
                <a href="index.html" class="logo logo-dark">
                    <span class="logo-sm">
                        <i class="bi bi-box fs-2 text-white"></i></span>
                    <span class="logo-lg"><img
                            src="https://bootstrapplanet.com/themes/marvel/app/assets/images/logo-white.png"
                            alt="Lettstart Admin"></span>
                </a>
                <a href="<?=Url::to(['/'])?>" class="logo logo-light">
                    <span class="logo-sm">
                        <i class="bi bi-box fs-2"></i>
                    </span>
                    <span class="logo-lg fs-1">
                        <i class="bi bi-box"></i>ERP
                    </span>
                </a>
            </div>
            <button type="button" id="vertical-menu-btn" class="btn hamburg-icon">
                <!-- <i class="mdi mdi-menu"></i> -->
                <i class="fa-solid fa-bars"></i>
            </button>
            <form class="app-search d-none d-lg-block">
                <div class="search-box position-relative">
                    <input type="text" placeholder="Search..." class="form-control">
                    <span class="bx bx-search"></span>
                </div>
            </form>

        </div>
        <div class="right-bar">
            <div class="d-inline-flex ms-0 ms-sm-2 d-lg-none dropdown">
                <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-search-dropdown"
                    aria-expanded="false" class="btn header-item notify-icon">
                    <i class="bx bx-search"></i>
                </button>
                <div aria-labelledby="page-header-search-dropdown"
                    class="dropdown-menu-lg dropdown-menu-right p-0 dropdown-menu">
                    <form class="p-3">
                        <div class="search-box">
                            <div class="position-relative">
                                <input type="text" placeholder="Search..." class="form-control">
                                <i class="bx bx-search icon"></i>
                            </div>
                        </div>
                    </form>
                </div>
            </div>







            <div class="d-none d-lg-inline-flex ms-2 dropdown" data-aos="zoom-in" data-aos-delay="100">
                <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-app-dropdown"
                    aria-expanded="false" class="btn header-item notify-icon">
                    <i class="bx bx-customize"></i>
                </button>
                <div aria-labelledby="page-header-app-dropdown"
                    class="dropdown-menu-lg dropdown-menu-right dropdown-menu" style="width: 600px;">
                    <div class="px-lg-2">

                        <h6 class="text-center mt-3"><i class="fa-solid fa-grip"></i> บริการ</h6>

                        <!-- App Service -->
                        <div class="row mt-3 p-3">
                            <div class="col-4">
                                <div class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?=html::img('@web/images/svg-icons/leave.svg',['width' => '50px'])?>
                                    <div>ระบบลา</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?=html::img('@web/images/svg-icons/booking.svg',['width' => '50px'])?>
                                    <div>ระบบจองรถ</div>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?=html::img('@web/images/svg-icons/meeting.svg',['width' => '50px'])?>
                                    <div>ระบบจองห้องประชุม</div>
                                </div>
                            </div>

                            <div class="col-4 mt-3">
                                <div class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?=html::img('@web/images/svg-icons/document.svg',['width' => '50px'])?>
                                    <div>ระบบสารบัญ</div>
                                </div>
                            </div>
                            <div class="col-4 mt-3">
                                <div class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?=html::img('@web/images/svg-icons/check-list.svg',['width' => '50px'])?>
                                    <div>ระบบความเสี่ยง</div>
                                </div>
                            </div>
                            <div class="col-4 mt-3">
                                <a href="<?=Url::to(['/helpdesk/default/repair-select','title' => '<i class="fa-regular fa-square-check"></i> เลือกประเภทการซ่อม']);?>" class="open-modal" data-title="xxx">
                                    <div class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                        <i class="fa-solid fa-triangle-exclamation fs-1"></i>
                                        <div>แจ้งซ่อม</div>
                                    </div>
                                </div>
                            </a>

                        </div>

                    </div>
                </div>
            </div>
            <div class="d-none d-lg-inline-flex ms-2" data-aos="zoom-in" data-aos-delay="200">
                <button type="button" data-bs-toggle="fullscreen" class="btn header-item notify-icon" id="full-screen">
                    <i class="bx bx-fullscreen"></i>
                </button>
            </div>
            <?=$this->render('notification');?>
            <div class="d-inline-flex ms-0 ms-sm-2 dropdown" data-aos="zoom-in" data-aos-delay="400">
                <?php if(!Yii::$app->user->isGuest):?>
                <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-profile-dropdown"
                    aria-expanded="false" class="btn header-item">
                    <?php if(UserHelper::GetEmployee()):?>
                    <?=Html::img(UserHelper::GetEmployee()->ShowAvatar(), ['class' => 'avatar avatar-xs me-0','data-aos' => 'zoom-in','data-aos-delay'=>"500"])?>
                    <span class="d-none d-xl-inline-block ms-1"><?=UserHelper::GetEmployee()->fullname?></span>
                    <?php endif;?>
                    <i class="bx bx-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <?php endif;?>
                <div aria-labelledby="page-header-profile-dropdown" class="dropdown-menu-right dropdown-menu">
                    <a href="<?=Url::to('/profile')?>" class="dropdown-item">
                        <i class="bx bx-user me-1"></i> Profile
                    </a>
                    <a href="<?=Url::to('/profile/setting')?>" class="dropdown-item">
                        <i class="bx bx-wrench me-1"></i> Settings
                    </a>
                    <div class="dropdown-divider"></div>
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


                    <!-- <a href="javascript: void(0);" class="text-danger dropdown-item">
                        <i class="bx bx-log-in me-1 text-danger"></i> Logout
                    </a> -->
                </div>
            </div>
            <div class="d-inline-flex" data-aos="zoom-in" data-aos-delay="600">
                <?=Html::a('  <i class="fa-solid fa-sliders fs-6"></i>',['/setting'],['class' => 'btn header-item notify-icon']);?>
            </div>
        </div>
    </div>
</header>