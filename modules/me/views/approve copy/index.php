<?php
/** @var yii\web\View $this */
?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-bell noti-animate"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/views/approve/menu') ?>
<?php $this->endBlock(); ?>


<div class="card">
    <img class="card-img-top" src="holder.js/100px180/" alt="Title" />
    <div class="card-body">
        <h4 class="card-title">Title</h4>
        <p class="card-text">Body</p>
    </div>
</div>
