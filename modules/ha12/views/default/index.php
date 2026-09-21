<?php

use app\modules\ha12\models\Ha12Activity;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var Ha12Activity[] $activities */

$this->title = 'HA12-PCT';

// สีป้ายชนิดฟอร์ม (semantic, theme-aware)
$formTone = [
    Ha12Activity::FORM_GENERAL => 'secondary',
    Ha12Activity::FORM_MED => 'danger',
    Ha12Activity::FORM_MREC => 'info',
    Ha12Activity::FORM_KPI => 'primary',
];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ทบทวน 12 กิจกรรม และสรุปประเมินโดย PCT · ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-semibold mb-0"><i class="bi bi-clipboard2-pulse me-1"></i> HA12-PCT</h1>
            <div class="text-body-secondary small">บันทึกการทบทวนของหน่วยงาน แล้ว PCT สรุปและประเมินระดับการพัฒนาตามรอบ</div>
        </div>
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-2']) ?>
            <label class="small text-body-secondary mb-0">ปีงบ</label>
            <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()']) ?>
        <?= Html::endForm() ?>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'overview']) ?></div>

    <div class="card border shadow-sm mb-3">
        <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
            <span class="fw-semibold"><i class="bi bi-list-ol me-1"></i> ทะเบียน 12 กิจกรรมทบทวน</span>
            <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= count($activities) ?> กิจกรรม</span>
        </div>

        <?php if (!$activities): ?>
            <div class="card-body text-center text-body-secondary py-5">
                ยังไม่มีทะเบียนกิจกรรม — โปรดรัน migration เฟส 0
            </div>
        <?php else: ?>
            <!-- Desktop -->
            <div class="table-responsive d-none d-lg-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th class="text-center" style="width:56px;">ลำดับ</th>
                            <th>ชื่อกิจกรรม</th>
                            <th style="width:190px;">ชนิดฟอร์ม</th>
                            <th>ข้อมูลหลัก</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $a): ?>
                            <tr>
                                <td class="text-center fw-semibold text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= $a->no ?></td>
                                <td class="fw-semibold"><?= Html::encode($a->name) ?></td>
                                <td>
                                    <span class="badge bg-<?= $formTone[$a->form_type] ?? 'secondary' ?>-subtle text-<?= $formTone[$a->form_type] ?? 'secondary' ?>-emphasis">
                                        <?= Html::encode($a->formTypeLabel()) ?>
                                    </span>
                                </td>
                                <td class="text-body-secondary small"><?= Html::encode((string) $a->data_hint) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile -->
            <ul class="list-group list-group-flush d-lg-none">
                <?php foreach ($activities as $a): ?>
                    <li class="list-group-item">
                        <div class="d-flex align-items-start gap-2">
                            <span class="badge bg-body-secondary text-body-emphasis rounded-pill" style="font-variant-numeric:tabular-nums;"><?= $a->no ?></span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold"><?= Html::encode($a->name) ?></div>
                                <div class="text-body-secondary small mb-1"><?= Html::encode((string) $a->data_hint) ?></div>
                                <span class="badge bg-<?= $formTone[$a->form_type] ?? 'secondary' ?>-subtle text-<?= $formTone[$a->form_type] ?? 'secondary' ?>-emphasis">
                                    <?= Html::encode($a->formTypeLabel()) ?>
                                </span>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card border shadow-sm">
        <div class="card-body">
            <h2 class="h6 fw-semibold"><i class="bi bi-signpost-2 me-1"></i> แผนพัฒนา (เฟส)</h2>
            <p class="text-body-secondary small mb-2">
                หลักการ: แยก <b>การทบทวนจริง</b> (ข้อมูลหน้างาน) ออกจาก <b>ผลสรุป/ประเมินของ PCT</b>
                เชื่อมกันด้วย id + revision และเก็บหลักฐานเป็น snapshot เฉพาะรุ่น
            </p>
            <ul class="small text-body-secondary mb-0">
                <li><b>เฟส 0</b> — โครงโมดูล + ทะเบียน 12 กิจกรรม + เกณฑ์ 5 ระดับ <span class="badge bg-success-subtle text-success-emphasis">กำลังทำ</span></li>
                <li><b>เฟส 1</b> — การทบทวน (กิจกรรมทั่วไป) + ประวัติรุ่น + ผลติดตาม</li>
                <li><b>เฟส 2</b> — แบบฟอร์มเฉพาะ กิจกรรม 7 (ยา) · 9 (เวชระเบียน) · 12 (ตัวชี้วัด/เชื่อม KPI)</li>
                <li><b>เฟส 3</b> — รอบสรุป/ประเมิน PCT (ร่าง → เผยแพร่ → ปิดรอบ) + หลักฐาน snapshot</li>
                <li><b>เฟส 4</b> — รายงาน/พิมพ์/เทียบรอบ + Export</li>
                <li><b>เฟส 5</b> — audit + นำเข้าข้อมูลเดิมจาก Google Sheets</li>
            </ul>
        </div>
    </div>
</div>
