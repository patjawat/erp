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

<?=$this->render('@app/themes/v3/layouts/modal')?>
    <body class="bg-primary">
        <?php $this->beginBody() ?>
        <section class="bg-primary py-3 py-md-5 py-xl-8 h-screen-100">
        <div class="d-flex justify-content-center mb-4 mt-5 fs-1 gap-2" style="color:white">
            <span data-aos="fade-right" data-aos-delay="300">
                <i class="bi bi-box"></i>ERP</span> <span data-aos="fade-left" data-aos-delay="300">
                <?=SiteHelper::getInfo()['company_name'] !='' ?  (' | '.SiteHelper::getInfo()['company_name']) : ''?>
            </span>
        </div>
        <div class="container">
            <?= $content?>
        </div>
    </section>


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