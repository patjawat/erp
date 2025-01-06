<?php

$this->title = 'การเงิน';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calculator fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  // echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <h6><i class="bi bi-ui-checks"></i> ระบบการเงิน</h6>
        <h5 class="text-center"><i class="fa-solid fa-code text-warning"></i> อยู่ระหว่างพัฒนา</h5>
    </div>
</div>
