
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-person-chalkboard fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('menu')?>
<?php $this->endBlock(); ?>
