<?php

/** @var yii\web\View $this */
/** @var string $content */
use app\assets\AppAsset;
use app\assets\BootstapIconAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\ArrayHelper;
use dominus77\sweetalert2\assets\SweetAlert2Asset;
use app\components\SiteHelper;
use app\models\Categorise;

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
<style>
@-webkit-keyframes animateWave {
    0% {
        transform: scale(1, 0)
    }

    100% {
        transform: scale(1, 1)
    }
}

@keyframes animateWave {
    0% {
        transform: scale(1, 0)
    }

    100% {
        transform: scale(1, 1)
    }
}

.wave-bg {
    display: block;
    height: 220px;
    width: 100%;
    min-width: 600px;
    transform-origin: top;
    -webkit-animation: animateWave 2000ms cubic-bezier(.23, 1, .32, 1) forwards;
    animation: animateWave 2000ms cubic-bezier(.23, 1, .32, 1) forwards;
    /* background-image: url(../images/wave-bg.svg); */
    background-position: center;
    background-repeat: no-repeat;
}

.blank-pagexq {
    background: #c9e8ef;
    /* fallback for old browsers */
    background: -webkit-linear-gradient(to bottom, #c9e8ef 0%, #078da9);
    /* Chrome 10-25, Safari 5.1-6 */
    background: linear-gradient(to bottom, #c9e8ef 0%, #078da9);
    /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
}

.blank-pagex {
    background: rgb(7, 141, 169);
    background: linear-gradient(0deg, rgba(7, 141, 169, 0.9178046218487395) 0%, rgba(7, 141, 169, 1) 41%, rgba(201, 232, 239, 1) 100%);
}
</style>
<?=$this->render('@app/themes/v3/layouts/modal')?>
<?php if($style == 1):?>
<body class="blank-page">
    <?php $this->beginBody() ?>
    <div class="d-flex justify-content-center mb-4 mt-5 fs-1 gap-2">
        <span data-aos="fade-right" data-aos-delay="300">
            <i class="bi bi-box"></i>ERP</span> <span data-aos="fade-left" data-aos-delay="300">
            <?=SiteHelper::getInfo()['company_name'] !='' ?  (' | '.SiteHelper::getInfo()['company_name']) : ''?>

        </span>
    </div>
    <?= $content?>

    <footer class="bg-body" style="margin-top:200px">
        <div class="wave-bg">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none" width="100%" height="220">
                <path fill="<?=$color?>" fill-opacity="10"
                    d="M0,256L21.8,250.7C43.6,245,87,235,131,192C174.5,149,218,75,262,80C305.5,85,349,171,393,186.7C436.4,203,480,149,524,149.3C567.3,149,611,203,655,197.3C698.2,192,742,128,785,90.7C829.1,53,873,43,916,69.3C960,96,1004,160,1047,176C1090.9,192,1135,160,1178,138.7C1221.8,117,1265,107,1309,112C1352.7,117,1396,139,1418,149.3L1440,160L1440,0L1418.2,0C1396.4,0,1353,0,1309,0C1265.5,0,1222,0,1178,0C1134.5,0,1091,0,1047,0C1003.6,0,960,0,916,0C872.7,0,829,0,785,0C741.8,0,698,0,655,0C610.9,0,567,0,524,0C480,0,436,0,393,0C349.1,0,305,0,262,0C218.2,0,175,0,131,0C87.3,0,44,0,22,0L0,0Z">
                </path>
            </svg>
        </div>

        <h4 class="text-center fw-light">ผู้ให้การสนับสนุน</h4>

        <div class="d-flex justify-content-center gap-5 mt-4">

            <?=Html::img('@web/banner/banner1.png',['style'=> 'width:150px'])?>

            <?=Html::img('@web/banner/banner2.png',['style'=> 'width:100px'])?>

            <?=Html::img('@web/banner/banner3.png',['style'=> 'width:100px'])?>

        </div>

    </footer>
<?php else:?>

    <body>
    <?php $this->beginBody() ?>
    <div class="d-flex justify-content-center mb-4 mt-5 fs-1 gap-2">
        <span data-aos="fade-right" data-aos-delay="300">
            <i class="bi bi-box"></i>ERP</span> <span data-aos="fade-left" data-aos-delay="300">
            <?=SiteHelper::getInfo()['company_name'] !='' ?  (' | '.SiteHelper::getInfo()['company_name']) : ''?>

        </span>
    </div>
    <div class="container">
        <?= $content?>
    </div>

    <footer style="margin-top:100px">

        <h4 class="text-center fw-light">ผู้ให้การสนับสนุน</h4>

        <div class="d-flex justify-content-center gap-5 mt-4">

            <?=Html::img('@web/banner/banner1.png',['style'=> 'width:150px'])?>

            <?=Html::img('@web/banner/banner2.png',['style'=> 'width:100px'])?>

            <?=Html::img('@web/banner/banner3.png',['style'=> 'width:100px'])?>

        </div>

    </footer>

    </body>
<?php endif;?>
    <?php
$js = <<< JS
 AOS.init({
    
 });

JS;
$this->registerJS($js);
?>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>