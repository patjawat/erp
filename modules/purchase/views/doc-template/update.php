<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\DocTemplate $model */
/** @var array $catalog */

$this->title = 'แก้ไขแม่แบบ: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'แม่แบบเอกสาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-pencil-square"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับรายการแม่แบบ', ['index'], [
    'class' => 'btn btn-sm btn-outline-secondary',
]) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <?= $this->render('_form', ['model' => $model, 'catalog' => $catalog]) ?>
    </div>
</div>
