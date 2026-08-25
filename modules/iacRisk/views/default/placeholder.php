<?php
use yii\helpers\Html;

$this->title = $title;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>IAC&Risk<?php $this->endBlock(); ?>

<?= $this->render('_context', ['context' => $context]) ?>
<div class="mb-3"><?= $this->render('@app/modules/iacRisk/menu', ['active' => $active, 'context' => $context]) ?></div>

<section class="card bg-body border shadow-sm">
    <div class="card-body text-center py-5">
        <h2 class="h5 fw-semibold"><?= Html::encode($title) ?></h2>
        <p class="text-body-secondary mb-0">เมนูและขอบเขตข้อมูลพร้อมสำหรับทดสอบใน Phase 1 เนื้อหาส่วนนี้จะพัฒนาในเฟสที่กำหนดไว้</p>
    </div>
</section>
