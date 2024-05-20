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

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?=Yii::$app->language?>" class="h-100"">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="blank-page">
    <?php $this->beginBody() ?>
    <?= $content?>
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