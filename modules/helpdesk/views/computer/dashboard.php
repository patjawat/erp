<?php
use yii\helpers\Html;
$this->title = 'ศูนย์คอมพิวเตอร์';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<div class="card vh-100 d-flex justify-content-center align-items-center">
    <div class="card-body text-center">
        <h1>อยู่ระหว่างปรับปรุง</h1>
        <?php echo Html::img('@web/img/ma_service.jpg', ['width' => 500]) ?>
    </div>
</div>
