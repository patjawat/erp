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

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anuphan:wght@100;200;300;400;500;600;700&family=K2D:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Krub:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,500;1,600;1,700&family=Mitr:wght@200;300;400;500;600;700&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;1,100;1,200;1,300;1,400&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,800;1,900&display=swap"
        rel="stylesheet">

    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    
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

<body>
    <?php $this->beginBody() ?>

    <?= $this->render('./modal') ?>
                <?= $this->render('page_title') ?>

        <div class="container mt--45">
            <?=$content?>

        </div>
    </div>
    
    <div class="container-fluid">

</div>



</body>
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