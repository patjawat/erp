<?php
$title = '';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-clipboard-user fs-1"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>