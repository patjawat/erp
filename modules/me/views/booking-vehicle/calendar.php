<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'ระบบขอใช้ยานพาหนะ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-1 white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ปฏิทินรวม
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('menu')?>
<?php $this->endBlock(); ?>

<?=$this->render('@app/components/ui/calendar',['url' => '/me/booking-vehicle/'])?>