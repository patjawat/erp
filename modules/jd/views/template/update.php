<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\jd\models\JdTemplate $model */

$this->title = 'แก้ไข Template: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Template JD', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <i class="bi bi-pencil-square fs-4 text-primary"></i>
    <h4 class="fw-medium mb-0"><?= Html::encode($this->title) ?></h4>
</div>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 small fw-normal text-white">
            <i class="bi bi-file-earmark-text me-1"></i> แก้ไขข้อมูล JD
        </h6>
        <?= Html::a('<i class="bi bi-eye me-1"></i> ดู Template', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-light']) ?>
    </div>
    <div class="card-body p-4">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
