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

<body class="horizontal-navbar">

    <style>
    body {
        background-color: rgba(var(--bs-primary-rgb)) !important;
    }
    </style>
    <?php $this->beginBody() ?>


    <?=$this->render('@app/themes/v3/layouts/modal')?>
    <div class="container-fluid mt-3">
        <?=$content?>
    </div>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>