<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Contract $model */
/** @var app\modules\purchase\models\ContractMilestone[] $milestones */

$this->title = 'แก้ไขสัญญา';
$this->params['breadcrumbs'][] = ['label' => 'บริหารสัญญา', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-pencil-square"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?= Html::encode(($model->contract_no ?: $model->doc_no) . ' — ' . $model->title) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับหน้ารายละเอียด', ['view', 'id' => $model->id], [
    'class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3',
]) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <?= $this->render('_form', ['model' => $model, 'milestones' => $milestones]) ?>
    </div>
</div>
