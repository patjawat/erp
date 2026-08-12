<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\BondPolicy $model */

$this->title = 'แก้ไขเกณฑ์หลักประกัน';
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าเกณฑ์หลักประกัน', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-shield-exclamation"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?= Html::encode($model->title) ?> · <?= Html::encode($model->rangeText()) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับหน้าตั้งค่า', ['index'], [
    'class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3',
]) ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body">
                <?= $this->render('_form', ['model' => $model]) ?>
            </div>
        </div>
    </div>
</div>
