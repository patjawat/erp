

<?php

$this->title = 'ระบบจองรถ/ปฏิทินการใช้รถ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('menu',['active' => 'index'])?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?=$this->render('@app/modules/booking/views/vehicle/_search', ['model' => $searchModel])?>
    </div>
</div>
<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'index']) ?>
<?php $this->endBlock(); ?>



<?=$this->render('@app/modules/booking/views/vehicle/list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ])?>