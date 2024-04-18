<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<style>
.avatar {
    position: relative;
    width: 2.375rem;
    height: 2.375rem;
    cursor: pointer;
}

.avatar img {
    width: 38px;
}
.dropup,
.dropend,
.dropdown,
.dropstart,
.dropup-center,
.dropdown-center {
    position: relative;
}

.dropdown-toggle {
    white-space: nowrap;
}

.dropdown-toggle::after {
    display: inline-block;
    margin-left: 0.5em;
    vertical-align: middle;
    content: "";
    margin-top: -0.28em;
    width: 0.42em;
    height: 0.42em;
    border: 1px solid;
    border-top: 0;
    border-left: 0;
    transform: rotate(45deg);
}

.dropdown-toggle:empty::after {
    margin-left: 0;
}

.dropdown-menu {
    --bs-dropdown-zindex: 1000;
    --bs-dropdown-min-width: 12rem;
    --bs-dropdown-padding-x: 0;
    --bs-dropdown-padding-y: 0.3125rem;
    --bs-dropdown-spacer: 0.125rem;
    --bs-dropdown-font-size: 0.9375rem;
    --bs-dropdown-color: var(--bs-body-color);
    --bs-dropdown-bg: #fff;
    --bs-dropdown-border-color: transparent;
    --bs-dropdown-border-radius: var(--bs-border-radius);
    --bs-dropdown-border-width: var(--bs-border-width);
    --bs-dropdown-inner-border-radius: 0;
    --bs-dropdown-divider-bg: #d9dee3;
    --bs-dropdown-divider-margin-y: 0.5rem;
    --bs-dropdown-box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
    --bs-dropdown-link-color: #697a8d;
    --bs-dropdown-link-hover-color: #697a8d;
    --bs-dropdown-link-hover-bg: rgba(67, 89, 113, 0.04);
    --bs-dropdown-link-active-color: #fff;
    --bs-dropdown-link-active-bg: rgba(105, 108, 255, 0.08);
    --bs-dropdown-link-disabled-color: #c7cdd4;
    --bs-dropdown-item-padding-x: 1.25rem;
    --bs-dropdown-item-padding-y: 0.532rem;
    --bs-dropdown-header-color: #a1acb8;
    --bs-dropdown-header-padding-x: 1.25rem;
    --bs-dropdown-header-padding-y: 0.3125rem;
    position: absolute;
    z-index: var(--bs-dropdown-zindex);
    display: none;
    min-width: var(--bs-dropdown-min-width);
    padding: var(--bs-dropdown-padding-y) var(--bs-dropdown-padding-x);
    margin: 0;
    font-size: var(--bs-dropdown-font-size);
    color: var(--bs-dropdown-color);
    text-align: left;
    list-style: none;
    background-color: var(--bs-dropdown-bg);
    background-clip: padding-box;
    border: var(--bs-dropdown-border-width) solid var(--bs-dropdown-border-color);
    border-radius: var(--bs-dropdown-border-radius);
}

.dropdown-menu[data-bs-popper] {
    top: 100%;
    left: 0;
    margin-top: var(--bs-dropdown-spacer);
}

.dropdown-menu-start {
    --bs-position: start;
}

.dropdown-menu-start[data-bs-popper] {
    right: auto;
    left: 0;
}

.dropdown-menu-end {
    --bs-position: end;
}

.dropdown-menu-end[data-bs-popper] {
    right: 0;
    left: auto;
}

@media (min-width: 576px) {
    .dropdown-menu-sm-start {
        --bs-position: start;
    }

    .dropdown-menu-sm-start[data-bs-popper] {
        right: auto;
        left: 0;
    }

    .dropdown-menu-sm-end {
        --bs-position: end;
    }

    .dropdown-menu-sm-end[data-bs-popper] {
        right: 0;
        left: auto;
    }
}

@media (min-width: 768px) {
    .dropdown-menu-md-start {
        --bs-position: start;
    }

    .dropdown-menu-md-start[data-bs-popper] {
        right: auto;
        left: 0;
    }

    .dropdown-menu-md-end {
        --bs-position: end;
    }

    .dropdown-menu-md-end[data-bs-popper] {
        right: 0;
        left: auto;
    }
}

@media (min-width: 992px) {
    .dropdown-menu-lg-start {
        --bs-position: start;
    }

    .dropdown-menu-lg-start[data-bs-popper] {
        right: auto;
        left: 0;
    }

    .dropdown-menu-lg-end {
        --bs-position: end;
    }

    .dropdown-menu-lg-end[data-bs-popper] {
        right: 0;
        left: auto;
    }
}

@media (min-width: 1200px) {
    .dropdown-menu-xl-start {
        --bs-position: start;
    }

    .dropdown-menu-xl-start[data-bs-popper] {
        right: auto;
        left: 0;
    }

    .dropdown-menu-xl-end {
        --bs-position: end;
    }

    .dropdown-menu-xl-end[data-bs-popper] {
        right: 0;
        left: auto;
    }
}

@media (min-width: 1400px) {
    .dropdown-menu-xxl-start {
        --bs-position: start;
    }

    .dropdown-menu-xxl-start[data-bs-popper] {
        right: auto;
        left: 0;
    }

    .dropdown-menu-xxl-end {
        --bs-position: end;
    }

    .dropdown-menu-xxl-end[data-bs-popper] {
        right: 0;
        left: auto;
    }
}

.dropup .dropdown-menu[data-bs-popper] {
    top: auto;
    bottom: 100%;
    margin-top: 0;
    margin-bottom: var(--bs-dropdown-spacer);
}

.dropup .dropdown-toggle::after {
    display: inline-block;
    margin-left: 0.5em;
    vertical-align: middle;
    content: "";
    margin-top: 0;
    width: 0.42em;
    height: 0.42em;
    border: 1px solid;
    border-bottom: 0;
    border-left: 0;
    transform: rotate(-45deg);
}

.dropup .dropdown-toggle:empty::after {
    margin-left: 0;
}

.dropend .dropdown-menu[data-bs-popper] {
    top: 0;
    right: auto;
    left: 100%;
    margin-top: 0;
    margin-left: var(--bs-dropdown-spacer);
}

.dropend .dropdown-toggle::after {
    display: inline-block;
    margin-left: 0.5em;
    vertical-align: middle;
    content: "";
    border-top: 0.42em solid transparent;
    border-right: 0;
    border-bottom: 0.42em solid transparent;
    border-left: 0.42em solid;
}

.dropend .dropdown-toggle:empty::after {
    margin-left: 0;
}

.dropend .dropdown-toggle::after {
    vertical-align: 0;
}

.dropstart .dropdown-menu[data-bs-popper] {
    top: 0;
    right: 100%;
    left: auto;
    margin-top: 0;
    margin-right: var(--bs-dropdown-spacer);
}

.dropstart .dropdown-toggle::after {
    display: inline-block;
    margin-left: 0.5em;
    vertical-align: middle;
    content: "";
}

.dropstart .dropdown-toggle::after {
    display: none;
}

.dropstart .dropdown-toggle::before {
    display: inline-block;
    margin-right: 0.5em;
    vertical-align: middle;
    content: "";
    border-top: 0.42em solid transparent;
    border-right: 0.42em solid;
    border-bottom: 0.42em solid transparent;
}

.dropstart .dropdown-toggle:empty::after {
    margin-left: 0;
}

.dropstart .dropdown-toggle::before {
    vertical-align: 0;
}

.dropdown-divider {
    height: 0;
    margin: var(--bs-dropdown-divider-margin-y) 0;
    overflow: hidden;
    border-top: 1px solid var(--bs-dropdown-divider-bg);
    opacity: 1;
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: var(--bs-dropdown-item-padding-y) var(--bs-dropdown-item-padding-x);
    clear: both;
    font-weight: 400;
    color: var(--bs-dropdown-link-color);
    text-align: inherit;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
    border-radius: var(--bs-dropdown-item-border-radius, 0);
}

.dropdown-item:hover,
.dropdown-item:focus {
    color: var(--bs-dropdown-link-hover-color);
    background-color: var(--bs-dropdown-link-hover-bg);
}

.dropdown-item.active,
.dropdown-item:active {
    color: var(--bs-dropdown-link-active-color);
    text-decoration: none;
    background-color: var(--bs-dropdown-link-active-bg);
}

.dropdown-item.disabled,
.dropdown-item:disabled {
    color: var(--bs-dropdown-link-disabled-color);
    pointer-events: none;
    background-color: transparent;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-header {
    display: block;
    padding: var(--bs-dropdown-header-padding-y) var(--bs-dropdown-header-padding-x);
    margin-bottom: 0;
    font-size: 0.75rem;
    color: var(--bs-dropdown-header-color);
    white-space: nowrap;
}

.dropdown-item-text {
    display: block;
    padding: var(--bs-dropdown-item-padding-y) var(--bs-dropdown-item-padding-x);
    color: var(--bs-dropdown-link-color);
}

.dropdown-menu-dark {
    --bs-dropdown-color: rgba(67, 89, 113, 0.3);
    --bs-dropdown-bg: rgba(67, 89, 113, 0.8);
    --bs-dropdown-border-color: transparent;
    --bs-dropdown-box-shadow: ;
    --bs-dropdown-link-color: rgba(67, 89, 113, 0.3);
    --bs-dropdown-link-hover-color: #fff;
    --bs-dropdown-divider-bg: #d9dee3;
    --bs-dropdown-link-hover-bg: rgba(255, 255, 255, 0.15);
    --bs-dropdown-link-active-color: #fff;
    --bs-dropdown-link-active-bg: rgba(105, 108, 255, 0.08);
    --bs-dropdown-link-disabled-color: rgba(67, 89, 113, 0.5);
    --bs-dropdown-header-color: rgba(67, 89, 113, 0.5);
}



.dropdown-menu {
    box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
}

.mega-dropdown>.dropdown-menu {
    left: 0 !important;
    right: 0 !important;
}

.dropdown-menu .badge[class^=float-],
.dropdown-menu .badge[class*=" float-"] {
    position: relative;
    top: 0.071em;
}

.dropdown-item {
    line-height: 1.54;
}

.dropdown-toggle.hide-arrow::before,
.dropdown-toggle.hide-arrow::after,
.dropdown-toggle-hide-arrow>.dropdown-toggle::before,
.dropdown-toggle-hide-arrow>.dropdown-toggle::after {
    display: none;
}

.dropdown-toggle::after {
    margin-top: -0.28em;
    width: 0.42em;
    height: 0.42em;
    border: 1px solid;
    border-top: 0;
    border-left: 0;
    transform: rotate(45deg);
}

.dropend .dropdown-toggle::after {
    margin-top: -0.168em;
    width: 0.42em;
    height: 0.42em;
    border: 1px solid;
    border-top: 0;
    border-left: 0;
    transform: rotate(-45deg);
}

.dropstart .dropdown-toggle::before {
    margin-top: -0.168em;
    width: 0.42em;
    height: 0.42em;
    border: 1px solid;
    border-top: 0;
    border-right: 0;
    transform: rotate(45deg);
}

.dropup .dropdown-toggle::after {
    margin-top: 0;
    width: 0.42em;
    height: 0.42em;
    border: 1px solid;
    border-bottom: 0;
    border-left: 0;
    transform: rotate(-45deg);
}

.dropstart .dropdown-toggle::before,
.dropend .dropdown-toggle::after {
    vertical-align: middle;
}

.nav .nav-item,
.nav .nav-link,
.tab-pane,
.tab-pane .card-body {
    outline: none !important;
}

@supports (-moz-appearance: none) {
    .table .dropdown-menu.show {
        display: inline-table;
    }
}

.layout-navbar .navbar-dropdown .dropdown-menu {
    min-width: 22rem;
    overflow: hidden;
}


.layout-navbar .navbar-dropdown.dropdown-shortcuts .dropdown-shortcuts-item {
    text-align: center;
    padding: 1.5rem;
}

.row-bordered>.col,
.row-bordered>[class^=col-],
.row-bordered>[class*=" col-"],
.row-bordered>[class^="col "],
.row-bordered>[class*=" col "],
.row-bordered>[class$=" col"],
.row-bordered>[class=col] {
    position: relative;
    padding-top: 1px;
}


.layout-navbar .navbar-dropdown .badge-notifications {
    top: 0.5rem;
    padding: 0.2rem 0.4rem;
}

.badge.badge-notifications:not(.badge-dot) {
    padding: 0.05rem 0.2rem;
    font-size: .582rem;
    line-height: .75rem;
}

.badge.badge-notifications {
    position: absolute;
    top: auto;
    display: inline-block;
    margin: 0;
    transform: translate(-50%, -30%);
}

.rounded-pill {
    border-radius: 50rem !important;
}

.bg-danger {
    --bs-bg-opacity: 1;
    background-color: rgba(var(--bs-danger-rgb), var(--bs-bg-opacity)) !important;
}

.badge {
    text-transform: uppercase;
    line-height: .75;
}


</style>
<div class="">




    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="container-fluid px-5 d-flex justify-content-between">



            <div class="" id="submenu">
            <?php if(Yii::$app->controller->module->id == 'hr'):?>
								<?=$this->render('@app/modules/hr/subMenu')?>

            <?php endif;?>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

                <ul class="navbar-nav flex-row align-items-center ms-auto">



                    <!-- Search -->
                    <li class="nav-item navbar-search-wrapper me-2 me-xl-0">
                        <a class="nav-link search-toggler" href="javascript:void(0);">
                            <i class="bx bx-search bx-sm"></i>
                        </a>
                    </li>
                    <!-- /Search -->


                    <!-- Language -->
                    <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <i class="bx bx-globe bx-sm"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item active" href="javascript:void(0);" data-language="en"
                                    data-text-direction="ltr">
                                    <span class="align-middle">English</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-language="fr"
                                    data-text-direction="ltr">
                                    <span class="align-middle">French</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-language="ar"
                                    data-text-direction="rtl">
                                    <span class="align-middle">Arabic</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-language="de"
                                    data-text-direction="ltr">
                                    <span class="align-middle">German</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- /Language -->

                    <!-- Quick links  -->
                    <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-0">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <i class="bx bx-grid-alt bx-sm"></i>
                        </a>
                        <?=$this->render('app_ shortcuts')?>
                    </li>
                    <!-- Quick links -->


                    <!-- Style Switcher -->
                    <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <i class="bx bx-sm bx-sun"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                                    <span class="align-middle"><i class="bx bx-sun me-2"></i>Light</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                                    <span class="align-middle"><i class="bx bx-moon me-2"></i>Dark</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                                    <span class="align-middle"><i class="bx bx-desktop me-2"></i>System</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- / Style Switcher-->


                    <!-- Notification -->
                    <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <i class="bx bx-bell bx-sm"></i>
                            <span class="badge bg-danger rounded-pill badge-notifications">5</span>
                        </a>
                        <?=$this->render('notify')?>
                    </li>
                    <!--/ Notification -->
                    <!-- User -->
                    <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar avatar-online">
                                <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/avatars/1.png"
                                    alt="" class="w-px-40 h-auto rounded-circle">
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="pages-account-settings-account.html">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar avatar-online">
                                                <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/avatars/1.png"
                                                    alt="" class="w-px-40 h-auto rounded-circle">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-medium d-block">John Doe</span>
                                            <small class="text-muted">Admin</small>
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
                            <!-- <li>
                                <a class="dropdown-item" href="pages-account-settings-billing.html">
                                    <span class="d-flex align-items-center align-middle">
                                        <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                                        <span class="flex-grow-1 align-middle">Billing</span>
                                        <span
                                            class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <a class="dropdown-item" href="pages-faq.html">
                                    <i class="bx bx-help-circle me-2"></i>
                                    <span class="align-middle">FAQ</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="pages-pricing.html">
                                    <i class="bx bx-dollar me-2"></i>
                                    <span class="align-middle">Pricing</span>
                                </a>
                            </li> -->
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


            <!-- Search Small Screens -->
            <div class="navbar-search-wrapper search-input-wrapper container-xxl d-none">
                <span class="twitter-typeahead" style="position: relative; display: inline-block;"><input type="text"
                        class="form-control search-input border-0 container-xxl tt-input" placeholder="Search..."
                        aria-label="Search..." autocomplete="off" spellcheck="false" dir="auto"
                        style="position: relative; vertical-align: top;">
                    <pre aria-hidden="true"
                        style="position: absolute; visibility: hidden; white-space: pre; font-family: &quot;Public Sans&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Oxygen, Ubuntu, Cantarell, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif; font-size: 15px; font-style: normal; font-variant: normal; font-weight: 400; word-spacing: 0px; letter-spacing: 0px; text-indent: 0px; text-rendering: auto; text-transform: none;"></pre>
                    <div class="tt-menu navbar-search-suggestion ps"
                        style="position: absolute; top: 100%; left: 0px; z-index: 100; display: none;">
                        <div class="tt-dataset tt-dataset-pages"></div>
                        <div class="tt-dataset tt-dataset-files"></div>
                        <div class="tt-dataset tt-dataset-members"></div>
                        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                        </div>
                        <div class="ps__rail-y" style="top: 0px; right: 0px;">
                            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                        </div>
                    </div>
                </span>
                <i class="bx bx-x bx-sm search-toggler cursor-pointer"></i>
            </div>




        </div>
    </nav>


</div>