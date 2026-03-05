<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\jd\models\JdTemplate $model */

$this->title = 'สร้าง Template JD';
$this->params['breadcrumbs'][] = ['label' => 'Template JD', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <i class="bi bi-file-earmark-plus fs-4 text-primary"></i>
    <h4 class="fw-medium mb-0"><?= Html::encode($this->title) ?></h4>
</div>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal text-white">
            <i class="bi bi-file-earmark-text me-1"></i> กรอกข้อมูล JD — ข้อมูลพื้นฐานและสถานะจำเป็นต้องกรอก
        </h6>
    </div>
    <div class="card-body p-4">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
