<?php

/** @var yii\web\View $this */

/**
 * @var string $content
 */

use yii\web\View;
use app\assets\AppAsset;
// use app\widgets\Alert;
// use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
// use yii\bootstrap5\Nav;
// use yii\bootstrap5\NavBar;
use app\assets\BootstapIconAsset;

AppAsset::register($this);
BootstapIconAsset::register($this);
// $this->registerCssFile('@app/themes/v3/layouts/theme-h/css/custom.css', ['depends' => [\yii\web\YiiAsset::class]]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>
    <link rel="stylesheet" href="https://lettstartdesign.com/marvel/app/assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php $this->head() ?>
</head>

<body class="horizontal-navbar">
   <!-- Begin Page -->
   <div class="page-wrapper">
      <!-- Begin Header -->
      <!-- Begin Header -->
      <header id="page-topbar" class="topbar-header">
         <div class="navbar-header">
            <div class="left-bar">
               <div class="navbar-brand-box">
                  <a href="index.html" class="logo logo-dark">
                     <span class="logo-sm"><img src="assets/images/logo.png" alt="Lettstart Admin"></span>
                     <span class="logo-lg"><img src="assets/images/logo.png" alt="Lettstart Admin"></span>
                  </a>
                  <a href="index.html" class="logo logo-light">
                     <span class="logo-sm"><img src="assets/images/logo-white-sm.png" alt="Lettstart Admin"></span>
                     <span class="logo-lg"><img src="assets/images/logo-white.png" alt="Lettstart Admin"></span>
                  </a>
               </div>
               <a class="navbar-toggle collapsed" href="javascript:void(0)" data-bs-toggle="collapse"
                  data-bs-target="#topnav-menu-content" aria-expanded="false">
                  <span></span>
                  <span></span>
                  <span></span>
               </a>
               <div class="dropdown-mega dropdown d-none d-lg-block ms-2">
                  <a href="javascript:void(0)" data-bs-toggle="dropdown" id="mega-dropdown" aria-haspopup="true"
                     aria-expanded="false" class="btn header-item">
                     Mega Menu <i class="bx bx-chevron-down"></i>
                  </a>
                  <div class="dropdown-megamenu dropdown-menu" aria-labelledby="mega-dropdown">
                     <div class="row">
                        <div class="col-sm-8">
                           <div class="row">
                              <div class="col-md-4">
                                 <h5 class="fs-14 fw-semibold">UI Components</h5>
                                 <ul class="list-unstyled megamenu-list">
                                    <li><a href="javascript:void(0);">Lightbox</a></li>
                                    <li><a href="javascript:void(0);">Range Slider</a></li>
                                    <li><a href="javascript:void(0);">Sweet Alert</a></li>
                                    <li><a href="javascript:void(0);">Rating</a></li>
                                    <li><a href="javascript:void(0);">Forms</a></li>
                                    <li><a href="javascript:void(0);">Tables</a></li>
                                    <li><a href="javascript:void(0);">Charts</a></li>
                                 </ul>
                              </div>
                              <div class="col-md-4">
                                 <h5 class="fs-14 fw-semibold">Applications</h5>
                                 <ul class="list-unstyled megamenu-list">
                                    <li><a href="javascript:void(0);">Ecommerce</a></li>
                                    <li><a href="javascript:void(0);">Calendar</a></li>
                                    <li><a href="javascript:void(0);">Email</a></li>
                                    <li><a href="javascript:void(0);">Projects</a></li>
                                    <li><a href="javascript:void(0);">Tasks</a></li>
                                    <li><a href="javascript:void(0);">Contacts</a></li>
                                 </ul>
                              </div>
                              <div class="col-md-4">
                                 <h5 class="fs-14 fw-semibold">Extra Pages</h5>
                                 <ul class="list-unstyled megamenu-list">
                                    <li><a href="javascript:void(0);">Light Sidebar</a></li>
                                    <li><a href="javascript:void(0);">Compact Sidebar</a></li>
                                    <li><a href="javascript:void(0);">Horizontal layout</a></li>
                                    <li><a href="javascript:void(0);">Maintenance</a></li>
                                    <li><a href="javascript:void(0);">Coming Soon</a></li>
                                    <li><a href="javascript:void(0);">Timeline</a></li>
                                    <li><a href="javascript:void(0);">FAQs</a></li>
                                 </ul>
                              </div>
                           </div>
                        </div>
                        <div class="col-sm-4">
                           <div class="row align-items-center">
                              <div class="col-sm-6">
                                 <h5 class="fs-14 fw-semibold">UI Components</h5>
                                 <ul class="list-unstyled megamenu-list">
                                    <li><a href="javascript:void(0);">Lightbox</a></li>
                                    <li><a href="javascript:void(0);">Range Slider</a></li>
                                    <li><a href="javascript:void(0);">Sweet Alert</a></li>
                                    <li><a href="javascript:void(0);">Rating</a></li>
                                    <li><a href="javascript:void(0);">Forms</a></li>
                                    <li><a href="javascript:void(0);">Tables</a></li>
                                    <li><a href="javascript:void(0);">Charts</a></li>
                                 </ul>
                              </div>
                              <div class="col-sm-6">
                                 <div class="mega-dd-slider">
                                    <div class="owl-carousel">
                                       <div class="item">
                                          <img src="assets/images/megamenu-img.svg" alt="Lettstart Admin" class="img-fluid mx-auto d-block">
                                       </div>
                                       <div class="item">
                                          <img src="assets/images/megamenu-img2.svg" alt="Lettstart Admin" class="img-fluid mx-auto d-block">
                                       </div>
                                       <div class="item">
                                          <img src="assets/images/megamenu-img3.svg" alt="Lettstart Admin" class="img-fluid mx-auto d-block">
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="right-bar">
               <form class="app-search me-2 d-none d-lg-block">
                  <div class="search-box position-relative">
                     <input type="text" placeholder="Search..." class="form-control">
                     <span class="bx bx-search"></span>
                  </div>
               </form>
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
               <div class="d-inline-flex ms-0 ms-sm-2 dropdown">
                  <button aria-haspopup="true" data-bs-toggle="dropdown" type="button" id="page-header-country-dropdown"
                     aria-expanded="false" class="btn header-item">
                     <img src="assets/images/flags/us.svg" class="mh-16" alt="USA">
                     <span class="ms-2 d-none d-sm-inline-block">EN</span>
                  </button>
                  <div aria-labelledby="page-header-country-dropdown" id="countries"
                     class="dropdown-menu-right dropdown-menu">
                     <a href="javascript:void(0);" class="dropdown-item">
                        <img class="me-1 mh-12" src="assets/images/flags/us.svg" alt="USA">
                        <span class="align-middle" data-lang="en">USA</span>
                     </a>
                     <a href="javascript:void(0);" class="dropdown-item">
                        <img class="me-1 mh-12" src="assets/images/flags/ge.svg" alt="German">
                        <span class="align-middle" data-lang="ge">German</span>
                     </a>
                     <a href="javascript:void(0);" class="dropdown-item">
                        <img class="me-1 mh-12" src="assets/images/flags/ru.svg" alt="Russia">
                        <span class="align-middle" data-lang="ru">Russia</span>
                     </a>
                     <a href="javascript:void(0);" class="dropdown-item">
                        <img class="me-1 mh-12" src="assets/images/flags/in.svg" alt="India">
                        <span class="align-middle" data-lang="in">India</span>
                     </a>
                  </div>
               </div>
               <div class="d-none d-lg-inline-flex ms-2 dropdown">
                  <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-app-dropdown"
                     aria-expanded="false" class="btn header-item notify-icon">
                     <i class="bx bx-customize"></i>
                  </button>
                  <div aria-labelledby="page-header-app-dropdown" class="dropdown-menu-lg dropdown-menu-right dropdown-menu">
                     <div class="px-lg-2">
                        <div class="row g-0">
                           <div class="col">
                              <a href="javascript: void(0);" class="dropdown-icon-item">
                                 <img src="assets/images/brands/github.png" alt="Github">
                                 <span>GitHub</span>
                              </a>
                           </div>
                           <div class="col">
                              <a href="javascript: void(0);" class="dropdown-icon-item">
                                 <img src="assets/images/brands/bitbucket.png" alt="bitbucket">
                                 <span>Bitbucket</span>
                              </a>
                           </div>
                           <div class="col">
                              <a href="javascript: void(0);" class="dropdown-icon-item">
                                 <img src="assets/images/brands/dribbble.png" alt="dribbble">
                                 <span>Dribbble</span>
                              </a>
                           </div>
                        </div>
                        <div class="row g-0">
                           <div class="col">
                              <a href="javascript: void(0);" class="dropdown-icon-item">
                                 <img src="assets/images/brands/dropbox.png" alt="dropbox">
                                 <span>Dropbox</span>
                              </a>
                           </div>
                           <div class="col">
                              <a href="javascript: void(0);" class="dropdown-icon-item">
                                 <img src="assets/images/brands/mail_chimp.png" alt="mail_chimp">
                                 <span>Mail Chimp</span>
                              </a>
                           </div>
                           <div class="col">
                              <a href="javascript: void(0);" class="dropdown-icon-item">
                                 <img src="assets/images/brands/slack.png" alt="slack">
                                 <span>Slack</span>
                              </a>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="d-none d-lg-inline-flex ms-2">
                  <button type="button" data-bs-toggle="fullscreen" class="btn header-item notify-icon" id="full-screen">
                     <i class="bx bx-fullscreen"></i>
                  </button>
               </div>
               <div class="d-inline-flex ms-0 ms-sm-2 dropdown">
                  <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-notification-dropdown"
                     aria-expanded="false" class="btn header-item notify-icon position-relative">
                     <i class="bx bx-bell bx-tada"></i>
                     <span class="badge bg-danger badge-pill notify-icon-badge">3</span>
                  </button>
                  <div aria-labelledby="page-header-notification-dropdown"
                     class="dropdown-menu-lg dropdown-menu-right p-0 dropdown-menu">
                     <div class="notify-title p-3">
                        <h5 class="fs-14 fw-semibold mb-0">
                           <span>Notification</span>
                           <a class="text-primary" href="javascript: void(0);">
                              <small>Clear All</small>
                           </a>
                        </h5>
                     </div>
                     <div class="notify-scroll">
                        <div class="scroll-content" id="notify-scrollbar">
                           <div class="scroll-content">
                              <a href="javascript:void(0);" class="dropdown-item notification-item">
                                 <div class="media">
                                    <div class="avatar avatar-xs bg-primary">
                                       <i class="bx bx-user-plus"></i>
                                    </div>
                                    <p class="media-body">
                                       New user registered.
                                       <small class="text-muted">5 hours ago</small></p>
                                 </div>
                              </a>
                              <a href="javascript:void(0);" class="dropdown-item notification-item">
                                 <div class="media">
                                    <div class="avatar avatar-xs">
                                       <img alt="Lettstart Admin" class="img-fluid rounded-circle" src="assets/images/users/avatar-1.jpg">
                                    </div>
                                    <p class="media-body">
                                       John likes your photo
                                       <small class="text-muted">5 hours ago</small>
                                    </p>
                                 </div>
                              </a><a href="javascript:void(0);" class="dropdown-item notification-item">
                                 <div class="media">
                                    <div class="avatar avatar-xs">
                                       <img alt="Lettstart Admin" class="img-fluid rounded-circle" src="assets/images/users/avatar-2.jpg">
                                    </div>
                                    <p class="media-body">
                                       Johnson
                                       <small class="text-muted">Wow! admin looks good</small>
                                    </p>
                                 </div>
                              </a><a href="javascript:void(0);" class="dropdown-item notification-item">
                                 <div class="media">
                                    <div class="avatar avatar-xs bg-danger">
                                       <i class="bx bx-server"></i>
                                    </div>
                                    <p class="media-body">
                                       Server getting down
                                       <small class="text-muted">1 min ago</small>
                                    </p>
                                 </div>
                              </a><a href="javascript:void(0);" class="dropdown-item notification-item">
                                 <div class="media">
                                    <div class="avatar avatar-xs bg-info">
                                       <i class="bx bx-tag"></i>
                                    </div>
                                    <p class="media-body">
                                       Someone tag you
                                       <small class="text-muted">2 hours ago</small></p>
                                 </div>
                              </a>
                           </div>
                        </div>
                        <div class="notify-all">
                           <a href="javascript: void(0);" class="text-primary text-center p-3">
                              <small>View All</small>
                           </a>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="d-inline-flex ms-0 ms-sm-2 dropdown">
                  <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-profile-dropdown"
                     aria-expanded="false" class="btn header-item">
                     <img src="assets/images/users/avatar-1.jpg" alt="Header Avatar" class="avatar avatar-xs me-0">
                     <span class="d-none d-xl-inline-block ms-1">Henry</span>
                     <i class="bx bx-chevron-down d-none d-xl-inline-block"></i>
                  </button>
                  <div aria-labelledby="page-header-profile-dropdown" class="dropdown-menu-right dropdown-menu">
                     <a href="javascript: void(0);" class="dropdown-item">
                        <i class="bx bx-user me-1"></i> Profile
                     </a>
                     <a href="javascript: void(0);" class="dropdown-item">
                        <i class="bx bx-wrench me-1"></i> Settings
                     </a>
                     <a href="javascript: void(0);" class="dropdown-item">
                        <i class="bx bx-wallet me-1"></i> My Wallet
                     </a>
                     <a href="javascript: void(0);" class="dropdown-item">
                        <i class="bx bx-lock me-1"></i> Lock screen
                     </a>
                     <div class="dropdown-divider"></div>
                     <a href="javascript: void(0);" class="text-danger dropdown-item">
                        <i class="bx bx-log-in me-1 text-danger"></i> Logout
                     </a>
                  </div>
               </div>
               <div class="d-inline-flex">
                  <button type="button" id="layout" class="btn header-item notify-icon">
                     <i class="bx bx-cog bx-spin"></i>
                  </button>
               </div>
            </div>
         </div>
      </header>
      <!-- Header End -->
      <!-- Header End -->
      <!-- Begin Left Navigation -->
      <div class="horizontal-topnav shadow-sm">
         <div class="container-fluid">
            <nav class="navbar navbar-expand-lg topnav-menu">
               <div id="topnav-menu-content" class="collapse navbar-collapse">
                  <ul id="side-menu" class="navbar-nav">
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="topnav-dashboard" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <i class="bx bxs-dashboard me-1"></i> Dashboards
                           <i class="bx bx-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
                           <a class="dropdown-item" href="index.html"> Multi Purpose </a>
                           <a class="dropdown-item" href="dashboard2.html"> E-commerce </a>
                           <a class="dropdown-item" href="dashboard3.html"> Server Statistics </a>
                        </div>
                     </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="topnav-dashboard" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <i class="bx bxs-customize me-1"></i> Apps
                           <i class="bx bx-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-apps">
                           <a class="dropdown-item" href="calender.html">
                              <i class="bx bx-calendar me-1"></i>
                              <span> Calender</span>
                           </a>
                           <a class="dropdown-item" href="chat.html">
                              <i class="bx bx-chat me-1"></i>
                              <span> Chat</span>
                           </a>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-haspopup="true"  data-bs-toggle="dropdown"  aria-expanded="false">
                                 <i class="bx bxs-user-detail me-1"></i>
                                 <span> Contacts</span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-contact">
                                 <a class="dropdown-item" href="member-create.html"> Add Member </a>
                                 <a class="dropdown-item" href="member-list.html"> Member List </a>
                                 <a class="dropdown-item" href="member-grid.html"> Member Grid </a>
                                 <a class="dropdown-item" href="member-profile.html"> Member Profile </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bx-store me-1"></i>
                                 <span> Ecommerce</span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-contact">
                                 <a class="dropdown-item" href="ecommerce-add-product.html"> Add Product </a>
                                 <a class="dropdown-item" href="ecommerce-product.html"> Products </a>
                                 <a class="dropdown-item" href="ecommerce-product-details.html"> Product Detail </a>
                                 <a class="dropdown-item" href="ecommerce-orders.html"> Orders </a>
                                 <a class="dropdown-item" href="ecommerce-customers.html"> Customers </a>
                                 <a class="dropdown-item" href="ecommerce-cart.html"> Cart </a>
                                 <a class="dropdown-item" href="ecommerce-checkout.html"> Checkout </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bx-envelope me-1"></i>
                                 <span> Email</span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-contact">
                                 <a class="dropdown-item" href="email-inbox.html"> Inbox </a>
                                 <a class="dropdown-item" href="email-read.html"> Read Mail </a>
                                 <a class="dropdown-item" href="email-compose.html"> Compose Mail </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bx-receipt me-1"></i>
                                 <span> Invoices</span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-contact">
                                 <a class="dropdown-item" href="invoice-list.html"> Invoice List </a>
                                 <a class="dropdown-item" href="invoice-details.html"> Invoice Detail </a>
                                 <a class="dropdown-item" href="invoice-grid.html"> Invoice Grid </a>
                                 <a class="dropdown-item" href="invoice-create.html"> Generate Invoice </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bx-briefcase-alt-2 me-1"></i>
                                 <span> Projects</span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-contact">
                                 <a class="dropdown-item" href="project-list.html"> Project List </a>
                                 <a class="dropdown-item" href="project-grid.html"> Project Grid </a>
                                 <a class="dropdown-item" href="project-overview.html"> Project Overview </a>
                                 <a class="dropdown-item" href="project-create.html"> Create New </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bx-task me-1"></i>
                                 <span> Tasks</span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-contact">
                                 <a class="dropdown-item" href="task-list.html"> Task List </a>
                                 <a class="dropdown-item" href="kanban-board.html"> Kanban Board </a>
                                 <a class="dropdown-item" href="task-overview.html"> Task Overview </a>
                                 <a class="dropdown-item" href="task-create.html"> Create Task </a>
                              </div>
                           </div>
                        </div>
                     </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="topnav-ui" role="button" data-bs-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                           <i class="bx bx-tone me-1"></i>
                           UI Elements
                           <i class="bx bx-chevron-down"></i>
                        </a>

                        <div class="dropdown-menu mega-dropdown-menu dropdown-mega-menu-xl" aria-labelledby="topnav-ui">
                           <div class="row">
                              <div class="col-lg-4">
                                 <a class="dropdown-item" href="ui-buttons.html">Buttons</a>
                                 <a class="dropdown-item" href="ui-cards.html">Cards</a>
                                 <a class="dropdown-item" href="ui-avatars.html">Avatars</a>
                                 <a class="dropdown-item" href="ui-portlets.html">Portlets</a>
                                 <a class="dropdown-item" href="ui-tabs-accordions.html">Tabs &amp; Accordions</a>
                                 <a class="dropdown-item" href="ui-modal.html">Modals</a>
                                 <a class="dropdown-item" href="ui-progress.html">Progress</a>
                              </div>
                              <div class="col-lg-4">
                                 <a class="dropdown-item" href="ui-notifications.html">Notifications</a>
                                 <a class="dropdown-item" href="ui-spinners.html">Spinners</a>
                                 <a class="dropdown-item" href="ui-images.html">Images</a>
                                 <a class="dropdown-item" href="ui-carousel.html">Carousel</a>
                                 <a class="dropdown-item" href="ui-list-group.html">List Group</a>
                                 <a class="dropdown-item" href="ui-video.html">Embed Video</a>
                              </div>
                              <div class="col-lg-4">
                                 <a class="dropdown-item" href="ui-dropdowns.html">Dropdowns</a>
                                 <a class="dropdown-item" href="ui-ribbons.html">Ribbons</a>
                                 <a class="dropdown-item" href="ui-tooltips-popovers.html">Tooltips &amp; Popovers</a>
                                 <a class="dropdown-item" href="ui-general.html">General UI</a>
                                 <a class="dropdown-item" href="ui-typography.html">Typography</a>
                                 <a class="dropdown-item" href="ui-grid.html">Grid</a>
                              </div>
                           </div>
                        </div>
                     </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="topnav-component" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <i class="bx bx-layer me-1"></i> Component
                           <i class="bx bx-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-components">
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bxs-layer-plus me-1"></i>
                                 <span> Advanced UI</span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-advanced UI">
                                 <a class="dropdown-item" href="advanced-confirmation-box.html"> Confirmation Box </a>
                                 <a class="dropdown-item" href="advanced-bootstrap-tour.html"> Bootstrap Tour </a>
                                 <a class="dropdown-item" href="advanced-dragula.html"> Dragula </a>
                                 <a class="dropdown-item" href="advanced-loading-buttons.html"> Loading Buttons </a>
                                 <a class="dropdown-item" href="advanced-nestable.html"> nestable </a>
                                 <a class="dropdown-item" href="advanced-range-slider.html"> Range Slider </a>
                                 <a class="dropdown-item" href="advanced-scrollspy.html"> Scroll Spy </a>
                                 <a class="dropdown-item" href="advanced-sweet-alert.html"> Sweet Alert </a>
                                 <a class="dropdown-item" href="advanced-tour.html"> Hopscotch Tour </a>
                                 <a class="dropdown-item" href="advanced-rating.html"> Rating </a>
                                 <a class="dropdown-item" href="advanced-alertify.html"> Alertify </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bxs-eraser me-1"></i>
                                 <span> Forms </span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-forms">
                                 <a class="dropdown-item" href="forms-elements.html"> General Elements </a>
                                 <a class="dropdown-item" href="forms-advanced.html"> Advanced </a>
                                 <a class="dropdown-item" href="forms-validation.html">Validation </a>
                                 <a class="dropdown-item" href="forms-pickers.html">Pickers </a>
                                 <a class="dropdown-item" href="forms-ckeditors.html"> CK Editors </a>
                                 <a class="dropdown-item" href="forms-quilljs.html">Quill Editor </a>
                                 <a class="dropdown-item" href="forms-summernote.html">Summernote </a>
                                 <a class="dropdown-item" href="forms-file-uploads.html"> File Uploads </a>
                                 <a class="dropdown-item" href="forms-masks.html"> Form Masks </a>
                                 <a class="dropdown-item" href="forms-wizards.html">Wizard</a>
                                 <a class="dropdown-item" href="forms-xeditable.html">X-Editable </a>
                                 <a class="dropdown-item" href="forms-image-crop.html"> Image Cropper </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bx-table me-1"></i>
                                 <span> Tables </span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-tables">
                                 <a class="dropdown-item" href="table-basic.html"> Basic Table </a>
                                 <a class="dropdown-item" href="table-bootstrap.html"> Bootstrap Table </a>
                                 <a class="dropdown-item" href="table-datatables.html"> Datatables Table </a>
                                 <a class="dropdown-item" href="table-editable.html"> Editable Table </a>
                                 <a class="dropdown-item" href="table-footables.html"> Footable Table </a>
                                 <a class="dropdown-item" href="table-responsive.html"> Responsive Table </a>
                                 <a class="dropdown-item" href="table-tablesaw.html"> Tablesaw Table </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bxs-bar-chart-alt-2 me-1"></i>
                                 <span> Charts</span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-Charts">
                                 <a class="dropdown-item" href="charts-apex.html"> Apex </a>
                                 <a class="dropdown-item" href="charts-c3.html"> C3 </a>
                                 <a class="dropdown-item" href="charts-chartist.html">Chartist </a>
                                 <a class="dropdown-item" href="charts-chartjs.html"> Chart JS </a>
                                 <a class="dropdown-item" href="charts-flot.html"> Flot </a>
                                 <a class="dropdown-item" href="charts-knob.html"> Knob </a>
                                 <a class="dropdown-item" href="charts-morris.html"> Morris </a>
                                 <a class="dropdown-item" href="charts-peity.html"> Peity </a>
                                 <a class="dropdown-item" href="charts-sparklines.html"> Sparklines </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bx-aperture me-1"></i>
                                 <span> Icons</span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-icons">
                                 <a class="dropdown-item" href="icons-boxicons.html"> Box Icon </a>
                                 <a class="dropdown-item" href="icons-feather.html"> Feather Icon </a>
                                 <a class="dropdown-item" href="icons-mdi.html"> Material Design Icons </a>
                                 <a class="dropdown-item" href="icons-simple-line.html"> Simple Line Icons </a>
                                 <a class="dropdown-item" href="icons-themify.html"> Themify Icons </a>
                                 <a class="dropdown-item" href="icons-two-tone.html"> Two Tone Icons </a>
                                 <a class="dropdown-item" href="icons-font-awesome.html"> Font Awesome 5 </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <i class="bx bx-map me-1"></i>
                                 <span> Map </span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-contact">
                                 <a class="dropdown-item" href="maps-vector.html"> Vector Map </a>
                                 <a class="dropdown-item" href="maps-google.html"> Google Map </a>
                              </div>
                           </div>
                        </div>
                     </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="topnav-component" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <i class="bx bx-cube-alt me-1"></i> Pages
                           <i class="bx bx-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-components">
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <span> Auth Style 1</span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-Auth">
                                 <a class="dropdown-item" href="auth-login.html">Login </a>
                                 <a class="dropdown-item" href="auth-signup.html"> Register </a>
                                 <a class="dropdown-item" href="auth-recover.html"> Recover Password </a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <span> Auth Style 2 </span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-forms">
                                 <a class="dropdown-item" href="auth-login-basic.html"> Login </a>
                                 <a class="dropdown-item" href="auth-signup-basic.html"> Register </a>
                                 <a class="dropdown-item" href="auth-recover-basic.html"> Recover Password</a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <span> Auth Style 3 </span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-forms">
                                 <a class="dropdown-item" href="auth-login-full.html"> Login </a>
                                 <a class="dropdown-item" href="auth-signup-full.html"> Register </a>
                                 <a class="dropdown-item" href="auth-recover-full.html"> Recover Password</a>
                              </div>
                           </div>
                           <div class="dropdown">
                              <a href="javascript:void(0);" class="dropdown-item dropdown-toggle" aria-expanded="false">
                                 <span> Extra Auth </span>
                                 <i class="bx bx-chevron-right"></i>
                              </a>
                              <div class="dropdown-menu" aria-labelledby="topnav-forms">
                                 <a class="dropdown-item" href="auth-lockscreen.html"> Lock Screen </a>
                                 <a class="dropdown-item" href="auth-confirmation.html"> Confirmation Screen </a>
                                 <a class="dropdown-item" href="auth-400.html"> 400 </a>
                                 <a class="dropdown-item" href="auth-404.html"> 404 </a>
                                 <a class="dropdown-item" href="auth-500.html"> 500 </a>
                              </div>
                           </div>
                        </div>
                     </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="topnav-ui" role="button" data-bs-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                           <i class="bx bx-file me-1"></i>
                           Utility
                           <i class="bx bx-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu mega-dropdown-menu dropdown-mega-menu-xl dropdown-menu-right" aria-labelledby="topnav-utility">
                           <div class="row">
                              <div class="col-lg-4">
                                 <a class="dropdown-item" href="utility-animation.html"> Animation </a>
                                 <a class="dropdown-item" href="utility-activity.html"> Activity </a>
                                 <a class="dropdown-item" href="utility-coming-soon.html"> Coming Soon </a>
                                 <a class="dropdown-item" href="utility-faq.html"> FAQs </a>
                                 <a class="dropdown-item" href="utility-fix-left.html"> Fix Left Sidebar </a>
                                 <a class="dropdown-item" href="utility-fix-right.html"> Fix Right Sidebar </a>
                                 <a class="dropdown-item" href="utility-gallery.html"> Gallery </a>
                              </div>
                              <div class="col-lg-4">
                                 <a class="dropdown-item" href="utility-helperclasses.html"> Helper Classes </a>
                                 <a class="dropdown-item" href="utility-lightbox.html"> Lightbox </a>
                                 <a class="dropdown-item" href="utility-maintenance.html"> Maintenance </a>
                                 <a class="dropdown-item" href="utility-pricing.html"> Pricing </a>
                                 <a class="dropdown-item" href="utility-scrollbar.html"> Scrollbar </a>
                              </div>
                              <div class="col-lg-4">
                                 <a class="dropdown-item" href="utility-search-result.html"> Search Result </a>
                                 <a class="dropdown-item" href="utility-starterpage.html"> Starter Page </a>
                                 <a class="dropdown-item" href="utility-timeline.html"> Timeline </a>
                                 <a class="dropdown-item" href="utility-timeline-horizontal.html"> Timeline Horizontal </a>
                                 <a class="dropdown-item" href="utility-treeview.html"> Tree View </a>
                              </div>
                           </div>
                        </div>
                     </li>
                  </ul>
               </div>
            </nav>
         </div>
      </div>
      <!-- Left Navigation End -->
      <!-- Begin main content -->
      <div class="main-content">
         <!-- content -->
         <div class="page-content">
        
              <!-- page header -->
            <div class="page-title-box">
               <div class="container-fluid">
                  <div class="row align-items-center">
                     <div class="col-sm-5 col-xl-6">
                        <div class="page-title">
                           <h3 class="mb-1 fw-bold text-dark">MultiPurpose</h3>
                           <ol class="breadcrumb mb-3 mb-md-0">
                              <li class="breadcrumb-item active">Welcome to Admin Dashboard</li>
                           </ol>
                        </div>
                     </div>
                     <div class="col-sm-7 col-xl-6">
                        <form class="form-inline justify-content-sm-end">
                           <div class="d-inline-flex me-2 input-date input-date-sm">
                              <input class="form-control" type="text" id="dashdaterange"
                                 placeholder="03-10-19 To 04-06-20">
                              <div class="date-icon">
                                 <i class="bx bx-calendar fs-sm"></i>
                              </div>
                           </div>
                           <div class="btn-group dropdown">
                              <button type="button" data-bs-toggle="dropdown" class="btn btn-primary dropdown-toggle">
                                 <i class="bx bx-download me-1"></i> Download <i class="bx bx-chevron-down"></i>
                              </button>
                              <div class="dropdown-menu-right dropdown-menu">
                                 <a href="javascript: void(0);" class="dropdown-item">
                                    <i class="bx bx-mail-send me-1"></i> Email
                                 </a>
                                 <a href="javascript: void(0);" class="dropdown-item">
                                    <i class="bx bx-printer me-1"></i> Print
                                 </a>
                                 <a href="javascript: void(0);" class="dropdown-item">
                                    <i class="bx bx-file me-1"></i> Re-Generate
                                 </a>
                              </div>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
<div class="page-content-wrapper mt--45">
    <div class="container-fluid">
        <?= $content ?>
    </div>

</div>
            
      </div>
      </div>
      <!-- main content End -->
      <!-- footer -->
      <!-- footer -->
      <div class="preloader">
      	<div class="status">
      		<div class="spinner-border avatar-sm text-primary m-2" role="status"></div>
      	</div>
      </div>
      <footer class="footer">
      	<div class="container-fluid">
      		<div class="row align-items-center">
      			<div class="col-md-5 mb-1 mb-md-0">
      				<span><span id="date">2022</span> &copy; Marvel.</span>
      			</div>
      			
      			<div class="col-md-5 text-md-end">
      				<span>Design and Develop By <span class="text-primary font-weight-500">Lettstart Design</span></span>
      			</div>
      		</div>
      	</div>
      </footer>



   </div>
<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>