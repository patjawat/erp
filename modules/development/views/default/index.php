<?php
// ใช้ table development เดิม — หน้า index redirect ไป dashboard ภายในโมดูล
// ดู DefaultController::actionIndex()
use yii\helpers\Html;

$this->title = 'Development';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<?= Html::encode($this->title) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <p class="text-muted mb-0">กำลังไปหน้ารายการอบรม/ประชุม/ดูงาน...</p>
        </div>
    </div>
</div>
