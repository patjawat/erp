<?php
/**
 * Layout สำหรับหน้ารายงาน/ฟอร์มที่ใช้พิมพ์เท่านั้น - ไม่มี header, sidebar, footer
 * ใช้กับ action ที่ต้องการแสดงเฉพาะเนื้อหาเวลา print หรือเปิดในแท็บใหม่
 */
use app\assets\AppAsset;
use yii\bootstrap5\Html;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body style="margin: 0; padding: 1rem; background: #fff;">
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
