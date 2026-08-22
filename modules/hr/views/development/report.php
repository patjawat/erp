<?php

use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'รายงานการเดินทางไปราชการ';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-bar-chart-line text-primary" aria-hidden="true"></i>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('menu', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="card border shadow-sm">
    <div class="card-body py-5 text-center">
        <h5 class="fw-semibold mb-2">พื้นที่สำหรับรายงาน</h5>
        <p class="text-body-secondary mx-auto mb-4">
            โครงเมนูพร้อมแล้ว ขั้นต่อไปสามารถเพิ่มรายงานค่าใช้จ่าย รายงานแยกบุคลากร หน่วยงาน และปีงบประมาณได้จากหน้านี้
        </p>
        <?= Html::a(
            '<i class="bi bi-journal-check me-1" aria-hidden="true"></i>ดูทะเบียนประวัติ',
            ['/hr/development/index', 'status' => 'Checking'],
            ['class' => 'btn btn-outline-primary']
        ) ?>
    </div>
</div>
