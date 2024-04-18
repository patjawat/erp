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

AppAsset::register($this);
BootstapIconAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>
    <header>

        <?php
    NavBar::begin([
        // 'brandLabel' => Html::img('@web/img/THx.png',['class' => 'img-responsive','width' =>80]).'<i class="bi bi-columns-gap"></i> KKU',
        'brandLabel' => '<i class="fa-solid fa-scale-balanced"></i> KKU-lawdivision',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-lg navbar-light bg-light',
            'id' => 'header-nav'
        ],
    ]);

        $menuItems = [
            ['label' => '<i class="fa-solid fa-house"></i> หน้าหลัก', 'url' => ['/site/index']],
            ['label' => '<i class="bi bi-ui-checks"></i> ทะเบียน', 'url' => ['/complain']],
            ['label' => '<i class="bi bi-speedometer"></i> Dashbroad', 'url' => ['/dashbroad']],
            ['label' => '<i class="fa-solid fa-book"></i> คู่มือการใช้งาน', 'url' => ['/manul']],
            ['label' => '<i class="fa-solid fa-gear"></i> ตั้งค่า',  
        'url' => ['#'],
        'template' => '<a href="{url}" >{label}<i class="fa fa-angle-left pull-right"></i></a>',
        'items' => [
            ['label' => '<i class="fa-solid fa-user-gear"></i> ผู้ใช้งาน', 'url' => ['/usermanager']],
            ['label' => '<i class="fa-solid fa-sliders"></i> site', 'url' => ['/setting']],
        ],
    ],

        ];


    echo Nav::widget([
        'encodeLabels' => false,
        'options' => ['class' => 'navbar-nav ms-auto mb-2 mb-md-0 navbar-right','id' =>'main_nav'],
        'items' => $menuItems,
    ]);
    if (Yii::$app->user->isGuest) {
        echo Html::tag('div',Html::a('<i class="fa-solid fa-user-lock"></i> เข้าสู่ระบบ',['/site/login'],['class' => ['shadow  btn btn-kku-gradient rounded-pill login text-decoration-none']]),['class' => ['d-flex']]);
    } else {
        echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
            . Html::submitButton(
                '<i class="bi bi-fingerprint"></i> Logout (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-kku-gradient border-0 shadow rounded-pill logout text-decoration-none']
            )
            . Html::endForm();
    }
    NavBar::end();
    ?>
    </header>
    <?php \dominus77\sweetalert2\Alert::widget(['useSessionFlash' => true]); ?>
    
    <main role="main" class="flex-shrink-0">
        <?= $content ?>
        
    </main>




    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>