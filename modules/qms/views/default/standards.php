<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var app\modules\qms\models\Standard[] $standards */
/** @var array $stats  keyed by standard id */

$this->title = 'ทะเบียนมาตรฐานและข้อกำหนด';

// ปีงบให้เลือก (ย้อนหลัง 2 ปี ถึงหน้า 1 ปี)
$years = range($fiscalYear + 1, $fiscalYear - 2);

/** แปลง % เป็นป้ายสถานะ + สี */
$statusOf = static function (array $st): array {
    if (!$st['cycle']) {
        return ['label' => 'ยังไม่เปิดรอบ', 'tone' => 'secondary'];
    }
    $p = $st['percent'];
    if ($p >= 90) return ['label' => 'พร้อมประเมิน', 'tone' => 'success'];
    if ($p >= 50) return ['label' => 'กำลังดำเนินการ', 'tone' => 'primary'];
    return ['label' => 'ต้องปรับปรุง', 'tone' => 'warning'];
};
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ระบบติดตามมาตรฐานโรงพยาบาล<?php $this->endBlock(); ?>

<style>
.qms-ring { --p: 0; width: 62px; height: 62px; border-radius: 50%;
    background: conic-gradient(var(--bs-primary) calc(var(--p) * 1%), var(--bs-secondary-bg) 0);
    display: grid; place-items: center; }
.qms-ring::before { content: ''; position: absolute; width: 48px; height: 48px;
    border-radius: 50%; background: var(--bs-body-bg); }
.qms-ring span { position: relative; font-weight: 700; font-size: .85rem; }
.qms-std-card .qms-logo { width: 44px; height: 44px; border-radius: 12px; display: grid;
    place-items: center; font-weight: 800; }
</style>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-semibold mb-0"><i class="bi bi-shield-check me-1"></i> <?= Html::encode($this->title) ?></h1>
            <div class="text-body-secondary small">เลือกปีงบเพื่อดูความพร้อมของแต่ละมาตรฐาน</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::beginForm(['standards'], 'get', ['class' => 'd-flex align-items-center gap-2 me-1']) ?>
                <label class="small text-body-secondary mb-0">ปีงบ</label>
                <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), [
                    'class' => 'form-select form-select-sm', 'style' => 'width:auto',
                    'onchange' => 'this.form.submit()',
                ]) ?>
            <?= Html::endForm() ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i>เพิ่มมาตรฐาน', ['standard-form'], ['class' => 'btn btn-primary']) ?>
            <button type="button" class="btn btn-outline-secondary" disabled title="เฟสถัดไป"><i class="bi bi-upload me-1"></i>นำเข้า Checklist</button>
            <?= Html::a('<i class="bi bi-diagram-3 me-1"></i>Mapping ข้อกำหนด', ['mapping'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/qms/menu', ['active' => 'standards']) ?></div>

    <?php if (empty($standards)): ?>
        <div class="card border shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox fs-1 text-body-secondary" aria-hidden="true"></i>
                <h2 class="h5 fw-semibold mt-2">ยังไม่มีมาตรฐาน</h2>
                <p class="text-body-secondary">เริ่มด้วยการเพิ่มมาตรฐานแรก เช่น HA, ISO 9001, PDPA</p>
                <?= Html::a('<i class="bi bi-plus-lg me-1"></i>เพิ่มมาตรฐาน', ['standard-form'], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($standards as $standard): ?>
                <?php
                $st = $stats[$standard->id];
                $status = $statusOf($st);
                $color = $standard->color ?: '#1a508e';
                ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card border shadow-sm h-100 qms-std-card <?= $standard->is_active ? '' : 'opacity-75' ?>">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <div class="qms-logo" style="background: <?= Html::encode($color) ?>1a; color: <?= Html::encode($color) ?>;">
                                    <?= Html::encode($standard->short_name ?: mb_substr($standard->code, 0, 3)) ?>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate"><?= Html::encode($standard->name) ?></div>
                                    <div class="small text-body-secondary text-truncate">
                                        <i class="bi bi-person-badge me-1"></i><?= Html::encode($standard->getOwnerName() ?: '—') ?>
                                    </div>
                                </div>
                                <div class="qms-ring position-relative" style="--p: <?= (int) $st['percent'] ?>;">
                                    <span><?= (int) $st['percent'] ?>%</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div class="small">
                                    <div class="text-body-secondary">ข้อกำหนด</div>
                                    <div class="fw-semibold"><?= (int) $st['requirements'] ?> ข้อ</div>
                                </div>
                                <div class="small text-center">
                                    <div class="text-body-secondary">ทบทวนถัดไป</div>
                                    <div class="fw-semibold">
                                        <?= $st['cycle'] && $st['cycle']->next_review_date
                                            ? Yii::$app->formatter->asDate($st['cycle']->next_review_date)
                                            : '—' ?>
                                    </div>
                                </div>
                                <span class="badge rounded-pill text-bg-<?= $status['tone'] ?>"><?= $status['label'] ?></span>
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <?= Html::a('<i class="bi bi-list-check me-1"></i>ข้อกำหนด <span class="badge text-bg-light ms-1">' . (int) $st['requirements'] . '</span>', ['requirements', 'standard_id' => $standard->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?= Html::a('<i class="bi bi-ui-checks-grid me-1"></i>Checklist', ['checklist', 'standard_id' => $standard->id, 'fy' => $fiscalYear], ['class' => 'btn btn-sm btn-primary flex-grow-1']) ?>
                                <?= Html::a('<i class="bi bi-pencil"></i>', ['standard-form', 'id' => $standard->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'แก้ไข']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
