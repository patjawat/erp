<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var array{activities:int,published:int,categories:int} $stats */

$this->title = 'คลังกิจกรรม KM';

$cards = [
    ['label' => 'กิจกรรมปีนี้', 'value' => $stats['activities'], 'icon' => 'bi-collection', 'tone' => 'primary'],
    ['label' => 'เผยแพร่แล้ว', 'value' => $stats['published'], 'icon' => 'bi-check2-circle', 'tone' => 'success'],
    ['label' => 'หมวดหมู่', 'value' => $stats['categories'], 'icon' => 'bi-tags', 'tone' => 'info'],
];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>คลังกิจกรรมและหลักฐานการดำเนินงาน ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-semibold mb-0"><i class="bi bi-collection me-1"></i> คลังกิจกรรม KM</h1>
            <div class="text-body-secondary small">บันทึกกิจกรรม แนบภาพ และผูกหลักฐานงานคุณภาพ</div>
        </div>
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-2']) ?>
            <label class="small text-body-secondary mb-0">ปีงบ</label>
            <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()']) ?>
        <?= Html::endForm() ?>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/km/menu', ['active' => 'overview']) ?></div>

    <div class="row g-3 mb-3">
        <?php foreach ($cards as $c): ?>
            <div class="col-6 col-xl-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-<?= $c['tone'] ?>-subtle text-<?= $c['tone'] ?>-emphasis" style="width:48px;height:48px;">
                            <i class="bi <?= $c['icon'] ?> fs-4"></i>
                        </span>
                        <div>
                            <div class="text-body-secondary small"><?= $c['label'] ?></div>
                            <div class="h4 fw-bold mb-0"><?= number_format($c['value']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border shadow-sm">
        <div class="card-body">
            <h2 class="h6 fw-semibold"><i class="bi bi-signpost-2 me-1"></i> โครงระบบ (เฟส 0)</h2>
            <p class="text-body-secondary small mb-2">
                โมดูลนี้เป็น <b>evidence hub</b> ของงานคุณภาพ — บันทึกกิจกรรมของหน่วยงาน แนบรูปเป็นชุด
                แล้ว "ชี้" ไปหาหลักฐานที่มีอยู่แล้วในระบบ (งานมอบหมาย / KPI / ความเสี่ยง / เอกสาร)
                โดยไม่คัดลอกข้อมูลซ้ำ
            </p>
            <ul class="small text-body-secondary mb-0">
                <li>เฟส 1 — ทะเบียนกิจกรรม + หมวดหมู่ + ค้นหา/กรอง (ปีงบ/หมวด/หน่วยงาน)</li>
                <li>เฟส 2 — คลังภาพ (อัปโหลดหลายรูป + รูปปก)</li>
                <li>เฟส 3 — ผูกหลักฐานไป task / KPI / ความเสี่ยง (เอกสาร dms/medsop ประสานทีมก่อน)</li>
                <li>เฟส 4 — แม่แบบ + รายงานหลักฐาน HA</li>
            </ul>
        </div>
    </div>
</div>
