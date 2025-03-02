<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'งานซ่อมบำรุง';
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench fs-2"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ระบบงานซ่อมบำรุง
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu') ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'helpdesk-container', 'timeout' => 5000, 'enablePushState' => true]); ?>
<?=$this->render('../repair/list_order', ['searchModel' => $searchModel,'dataProvider' => $dataProvider])?>

<?php Pjax::end() ?>
