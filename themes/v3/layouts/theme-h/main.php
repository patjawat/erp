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
$this->registerCssFile('@app/themes/v3/layouts/theme-h/css/custom.css', ['depends' => [\yii\web\YiiAsset::class]]);
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
      <?=$this->render('header')?>
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


<?php
$js = <<< JS

  AOS.init({});

			// });

			
			

JS;
$this->registerJS($js, View::POS_END);
?>

<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>