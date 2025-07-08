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

AppAsset::register($this);
BootstapIconAsset::register($this);

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
        background: #265fac;
    }

    .text-gradient {
        background: linear-gradient(90deg, #d0f0ff, #c1ab8a);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    img.fix-orientation {
  image-orientation: from-image;
}
img {
  image-orientation: from-image;
}
    </style>
    <?php $this->beginBody() ?>
    <?=$this->render('@app/themes/v3/layouts/modal')?>

    <!-- Login 9 - Bootstrap Brain Component -->
    <section class="py-3 py-md-5 py-xl-8 h-screen-100">
      <div class="container">
        <div class="row gy-4 align-items-center">
          <div class="col-12 col-md-6 col-xl-7 mb-4 mb-md-0">
            <div class="d-flex justify-content-center">
              <div class="col-12 col-xl-9 d-none d-lg-block">
                <div class="text-center">
                  <?=Html::img('@web/images/logo_new.png',['class' => 'img-fluid', 'style' => 'max-width:400px; height:auto;'])?>
                </div>
                <hr class="border-primary-subtle mb-2">
                <div class="d-flex justify-content-center mb-4 mt-0 fs-3 gap-2">
                  <h2 class="text-gradient">
                    <?=SiteHelper::getInfo()['company_name'] !='' ?  (SiteHelper::getInfo()['company_name']) : ''?>
                  </h2>
                </div>
                <div class="text-endx">
                  <p class="text-center fw-light text-gradient">ผู้ให้การสนับสนุน</p>
                  <div class="d-flex justify-content-center align-items-center gap-3 gap-md-5 mt-4 flex-wrap">
                    <?=Html::img('@web/banner/banner2.png',['style'=> 'width:70px;height:70px'])?>
                    <?=Html::img('@web/banner/banner1.png',['style'=> 'width:170px;height:100px'])?>
                    <?=Html::img('@web/banner/banner3.png',['style'=> 'width:100px;height:100px'])?>
                  </div>
                </div>
              </div>
            </div>
          </div>
            <div class="col-12 col-md-12 col-xl-5">

            
            <div class="card border-0 rounded-4 mt-lg-5 mt-md-0 p-4 p-md-5">
              <?= $content?>
            </div>
            </div>
        </div>
      </div>
    </section>



    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>