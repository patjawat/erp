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
SweetAlert2Asset::register($this);

AppAsset::register($this);
BootstapIconAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?=Yii::$app->language?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anuphan:wght@100;200;300;400;500;600;700&family=K2D:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Krub:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,500;1,600;1,700&family=Mitr:wght@200;300;400;500;600;700&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;1,100;1,200;1,300;1,400&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,800;1,900&display=swap"
        rel="stylesheet">

    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>

    <style>
        .loader-line {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      position: relative;
      animation: rotate 1s linear infinite
    }
    .loader-line::before {
      content: "";
      box-sizing: border-box;
      position: absolute;
      inset: 0px;
      border-radius: 50%;
      border: 5px solid #FFF;
      animation: prixClipFix 2s linear infinite ;
    }

    @keyframes rotate {
      100%   {transform: rotate(360deg)}
    }

    @keyframes prixClipFix {
        0%   {clip-path:polygon(50% 50%,0 0,0 0,0 0,0 0,0 0)}
        25%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 0,100% 0,100% 0)}
        50%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,100% 100%,100% 100%)}
        75%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,0 100%,0 100%)}
        100% {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,0 100%,0 0)}
    }
    </style>
    <?php $this->head() ?>
    
</head>


<body>
    <?php $this->beginBody() ?>
    <?= $this->render('page_title') ?>
    <div class="container mt--45">
        <?=$content?>
    </div>
    </div>

</body>

<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>