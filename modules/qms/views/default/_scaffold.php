<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $active คีย์เมนู */
/** @var string $heading หัวข้อหน้า */
/** @var string $icon คลาสไอคอน bootstrap */
$this->title = $heading . ' — QMS';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($heading) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ระบบติดตามมาตรฐานโรงพยาบาล<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-semibold mb-0"><i class="bi <?= Html::encode($icon) ?> me-1"></i> <?= Html::encode($heading) ?></h1>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/qms/menu', ['active' => $active]) ?></div>

    <div class="card border shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-cone-striped fs-1 text-warning" aria-hidden="true"></i>
            <h2 class="h5 fw-semibold mt-2">อยู่ระหว่างพัฒนา</h2>
            <p class="text-body-secondary mb-0">หน้า “<?= Html::encode($heading) ?>” ยังเป็นโครงเปล่า จะเติมเนื้อหาในเฟสถัดไป</p>
        </div>
    </div>
</div>
