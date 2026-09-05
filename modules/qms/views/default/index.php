<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
$this->title = 'ระบบติดตามมาตรฐานโรงพยาบาล';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ภาพรวมความพร้อมตามมาตรฐาน (Quality Management System)<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-semibold mb-0"><i class="bi bi-shield-check me-1"></i> <?= Html::encode($this->title) ?></h1>
            <div class="text-body-secondary small">ภาพรวมผู้บริหาร</div>
        </div>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/qms/menu', ['active' => 'overview']) ?></div>

    <!-- KPI แถวบน (ยังเป็นข้อมูลตัวอย่าง — โครงเปล่า) -->
    <div class="row g-3 mb-3">
        <?php
        $kpis = [
            ['label' => 'ความพร้อมรวม', 'value' => '—', 'icon' => 'bi-shield-check', 'tone' => 'success'],
            ['label' => 'ตัวชี้วัด',      'value' => '—', 'icon' => 'bi-clipboard-data', 'tone' => 'primary'],
            ['label' => 'งานค้าง',       'value' => '—', 'icon' => 'bi-hourglass-split', 'tone' => 'warning'],
            ['label' => 'ความเสี่ยงสูง',  'value' => '—', 'icon' => 'bi-exclamation-triangle', 'tone' => 'danger'],
        ];
        foreach ($kpis as $kpi): ?>
            <div class="col-6 col-xl-3">
                <div class="card border shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-<?= $kpi['tone'] ?>-subtle text-<?= $kpi['tone'] ?>-emphasis" style="width:48px;height:48px;">
                            <i class="bi <?= $kpi['icon'] ?> fs-4" aria-hidden="true"></i>
                        </span>
                        <div>
                            <div class="text-body-secondary small"><?= $kpi['label'] ?></div>
                            <div class="h4 fw-bold mb-0"><?= $kpi['value'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-cone-striped fs-1 text-warning" aria-hidden="true"></i>
            <h2 class="h5 fw-semibold mt-2">โครงโมดูลพร้อมแล้ว</h2>
            <p class="text-body-secondary mb-0">
                เมนูและหน้าเปล่าถูกวางเรียบร้อย ขั้นถัดไปคือออกแบบตารางฐานข้อมูล (เฟส 1):<br>
                ทะเบียนมาตรฐาน → ข้อกำหนด → รอบปีงบ → checklist → หลักฐาน (ดึง DMS/medsop หรือแนบไฟล์)
            </p>
        </div>
    </div>
</div>
