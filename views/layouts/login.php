<?php

/** @var yii\web\View $this */
/** @var string $content */
use app\widgets\Alert;
use yii\bootstrap5\Nav;
use app\assets\AppAsset;
use yii\bootstrap5\Html;
use app\models\Categorise;
use yii\bootstrap5\NavBar;
use yii\helpers\ArrayHelper;
use app\components\SiteHelper;
use yii\bootstrap5\Breadcrumbs;
use app\assets\BootstapIconAsset;
use dominus77\sweetalert2\assets\SweetAlert2Asset;

AppAsset::register($this);
BootstapIconAsset::register($this);



SweetAlert2Asset::register($this);
$site = Categorise::findOne(['name' => 'site']);
$color = isset($site->data_json['theme_color']) ? $site->data_json['theme_color'] : '';
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : '';
$style = 2;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?=Yii::$app->language?>" class="h-100" data-bs-theme="<?=$colorName?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
<style>
    body {
        background-color: rgba(var(--bs-primary-rgb)) !important;
    }
</style>
    <?php $this->beginBody() ?>
    <?=$this->render('@app/themes/v3/layouts/modal')?>

<!-- Login 9 - Bootstrap Brain Component -->
<section class="bg-primary py-3 py-md-5 py-xl-8 h-screen-100">
  <div class="container">
    <div class="row gy-4 align-items-center">
      <div class="col-12 col-md-6 col-xl-7">
        <div class="d-flex justify-content-center text-bg-primary">
          <div class="col-12 col-xl-9">
            <h1 class="text-white text-center">

                <i class="bi bi-box"></i> ERP Hospital
            </h1>
                <!-- <img class="img-fluid rounded mb-4" loading="lazy" src="./assets/img/bsb-logo-light.svg" width="245" height="80" alt="BootstrapBrain Logo"> -->
            <hr class="border-primary-subtle mb-2">
            <div class="d-flex justify-content-center mb-4 mt-0 fs-3 gap-2">
            
            <?=SiteHelper::getInfo()['company_name'] !='' ?  (SiteHelper::getInfo()['company_name']) : ''?>

    </div>
    
    <div class="text-endx">
                <p class="text-center fw-light">ผู้ให้การสนับสนุน</p>
            <div class="d-flex justify-content-center gap-5 mt-4">

<?=Html::img('@web/banner/banner1.png',['style'=> 'width:100px;height:70px'])?>

<?=Html::img('@web/banner/banner2.png',['style'=> 'width:80px;height:70px'])?>

<?=Html::img('@web/banner/banner3.png',['style'=> 'width:80px;height:70px'])?>

</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-5">
        <div class="card border-0 rounded-4">
          <div class="card-body p-3 p-md-4 p-xl-5">

          <?= $content?>
          </div>
        </div>
      </div>
    </div>
    
  </div>
</section>


    
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>