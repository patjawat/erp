<?php

$this->title = 'ระบบขอใช้ยานพาหนะ/ปฏิทินการใช้รถ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ปฏิทินการใช้รถยนต์
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('menu',['active' => 'calendar'])?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/me//menu',['active' => 'vehicle']) ?>
<?php $this->endBlock(); ?>


<?=$this->render('@app/modules/booking/views/vehicle/carlendar_item',['vehicle_type' => 'official']);?>