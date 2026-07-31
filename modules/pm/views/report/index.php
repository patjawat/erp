<?php

/** @var yii\web\View $this */

$this->title = 'รายงาน';
$this->beginBlock('page-title'); ?>แผนงาน/โครงการ<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'report']) ?><?php $this->endBlock();
?>
<div class="pm-report container-fluid">
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i data-lucide="bar-chart-3" style="width:40px;height:40px"></i>
            <h5 class="mt-3">รายงานผลการดำเนินงานโครงการ</h5>
            <p class="mb-0">อยู่ระหว่างพัฒนา (เฟส 4 — แบบรายงานผลการดำเนินงาน)</p>
        </div>
    </div>
</div>
