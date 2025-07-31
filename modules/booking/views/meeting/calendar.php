<?php
        
$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->title = 'ปฏิทินการใช้ห้องประชุม';
$this->params['breadcrumbs'][] = ['label' => 'ระบบห้องประชุม', 'url' => ['/booking/meeting/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-regular fa-calendar fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ปฏิทินการใช้ห้องประชุม
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('menu',['active' => 'calendar'])?>
<?php $this->endBlock(); ?>



<div class="row">
<div class="col-lg-9 col-md-9 col-sm-12">
<div class="card">
    <div class="card-body">
       <?=$this->render('calendar_item')?>
    </div>
</div>
</div>
<div class="col-lg-3 col-md-3 col-sm-12">
    <?=$this->render('panel_room');?>
</div>
</div>


        