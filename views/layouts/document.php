<?php

use app\widgets\Alert;
use yii\bootstrap5\Nav;
use app\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\bootstrap5\NavBar;
use yii\bootstrap5\Breadcrumbs;
use app\assets\BootstapIconAsset;

AppAsset::register($this);
BootstapIconAsset::register($this);


$style = 2;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?=Yii::$app->language?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
    <body>
    <?php $this->beginBody() ?>

    <?php
    NavBar::begin([
        'brandLabel' => '',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => '',
            'id' => 'header-nav'
        ],
    ]);
    NavBar::end();
    ?>
    <div class="container-fuild">
        <?= $content?>
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