<?php

use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'การตั้งค่าระบบ';
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวมอบรม/ประชุม/ดูงาน', 'url' => ['/development/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-gear text-primary"></i>
    <h4 class="fw-medium text-body mb-0"><?= Html::encode($this->title) ?></h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/development/views/menu_admin', ['active' => 'setting-system']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <p class="text-muted mb-0">ตั้งค่าระบบสำหรับโมดูลอบรม/ประชุม/ดูงาน — รายการตั้งค่าจะแสดงในส่วนนี้</p>
    </div>
</div>
