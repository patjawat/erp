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
$siteInfo = SiteHelper::getInfo();
use app\components\ApproveHelper;
use dominus77\sweetalert2\assets\SweetAlert2Asset;
AppAsset::register($this);
BootstapIconAsset::register($this);



// SweetAlert2Asset::register($this);
$site = Categorise::findOne(['name' => 'site']);
$color = isset($site->data_json['theme_color']) ? $site->data_json['theme_color'] : '';
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : '';
$style = 2;
$this->registerJsFile('https://static.line-scdn.net/liff/edge/2/sdk.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
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
    <div class="container mt-3">
        <div class="d-flex justify-content-between">
            <div class="d-flex gap-1">
                <?=Html::img($siteInfo['logo'], ['class' => 'avatar avatar-md me-0'])?>

                <div class="avatar-detail">
                    <h5 class="mb-0 text-white text-truncate"><?php echo $siteInfo  ['company_name']?></h5>
                    <p class="text-white mb-0 fs-13">ERP Hospital</p>
                </div>
            </div>
            <!-- <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop"
                    aria-controls="staticBackdrop">
                    <i class="fa-solid fa-bars fs-3"></i>
                </button> -->
        </div>

        <div class="page-content mt-3">
            <div class="page-content-wrapper mt--45">
                <div id="page-content">
                    <?php  echo  $content; ?>
                </div>
                <div id="loader">
                    <?php  echo $this->render('loader'); ?>

                </div>
            </div>

        </div>
    </div>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>