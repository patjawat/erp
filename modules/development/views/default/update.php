<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\development\models\Development $model */

$this->title = 'แก้ไข อบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวมอบรม/ประชุม/ดูงาน', 'url' => ['/development/default/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รายการกิจกรรม', 'url' => ['/development/default/list', 'thai_year' => $model->thai_year]];
$this->params['breadcrumbs'][] = $this->title;

$backUrl = ['/development/default/view', 'id' => $model->id];
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <?= Html::a('<i class="bi bi-chevron-left fs-4 text-body"></i>', $backUrl, ['class' => 'text-decoration-none', 'title' => 'ย้อนกลับ']) ?>
        <div>
            <h4 class="fw-medium text-body mb-0"><?= Html::encode($this->title) ?></h4>
            <p class="text-muted small mb-0">กลุ่มบริหารงานบุคคล</p>
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>

<div class="development-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
