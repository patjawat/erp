<?php
$this->title = "Dashboard"
?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-chart-pie fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <h6>Dashboard</h6>
    </div>
</div>
