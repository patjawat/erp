<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\health\models\HealthScreen $model */

$this->title = 'บันทึกข้อมูลสุขภาพพนักงาน';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลสุขภาพ', 'url' => ['/health']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนตรวจสุขภาพ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="scan-heart"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/health/menu', ['active' => 'list']) ?>
<?php $this->endBlock(); ?>

<?= $this->render('_form', ['model' => $model]) ?>
