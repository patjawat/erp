<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
?>
<header class="topbar-header">
<!-- navbar-header -->
    <div class="d-flex align-items-center justify-content-between" style="height: 70px;">
        <div class="d-flex align-items-center h-100">
            <div class="navbar-brand-box">

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
                <i class="fa-solid fa-bars text-white fs-4"></i>
            </button>
            <form class="app-search d-none d-lg-block">
                <div class="search-box position-relative">
                    <input type="text" placeholder="Search..." class="form-control">
                    <span class="bx bx-search"></span>
                </div>
            </form>

        </div>
        <div class="">
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

            <?=$this->render('app_service')?>
            <?=$this->render('app_manage')?>



            <div class="d-none d-lg-inline-flex">
                <button type="button" data-bs-toggle="fullscreen" class="btn header-item notify-icon" id="full-screen">
                    <i class="fa-solid fa-expand"></i>
                </button>
            </div>
            <?=$this->render('notification');?>
            <?php // echo $this->render('app_cart')?>
            <div class="d-inline-flex ms-0 ms-sm-2 dropdown">
                <?php if(!Yii::$app->user->isGuest):?>
                <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-profile-dropdown"
                    aria-expanded="false" class="btn header-item">
                    <?php if(UserHelper::GetEmployee()):?>
                    <?=Html::img(UserHelper::GetEmployee()->ShowAvatar(), ['class' => 'avatar avatar-xs me-0'])?>
                    <span class="d-none d-xl-inline-block ms-1"><?=UserHelper::GetEmployee()->fullname?></span>
                    <?php endif;?>
                    <i class="bx bx-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <?php endif;?>
                <div aria-labelledby="page-header-profile-dropdown" class="dropdown-menu-right dropdown-menu">
                    <a href="<?=Url::to('/profile')?>" class="dropdown-item">
                    <i class="fa-solid fa-clipboard-user fs-4 me-3"></i> โปรไฟล์
                    </a>
                    <a href="<?=Url::to('/profile/setting')?>" class="dropdown-item">
                        <i class="fa-solid fa-user-gear fs-4 me-3"></i> ตั้งค่า
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
                </div>
            </div>
            <div class="d-inline-flex">
                <?=Html::a('  <i class="fa-solid fa-sliders fs-6"></i>',['/setting'],['class' => 'btn header-item notify-icon']);?>
            </div>
        </div>
    </div>
</header>