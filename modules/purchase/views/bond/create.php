<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Bond $model */

$this->title = 'บันทึกหลักประกัน';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนหลักประกัน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-shield-plus"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับทะเบียนหลักประกัน', ['index'], [
    'class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3',
]) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
