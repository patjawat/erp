<?php

use app\components\AppHelper;
use app\modules\complaint\models\Complaint;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $m */
/** @var int $fiscalYear */
/** @var int[] $years */

$this->title = 'ติดตาม SLA';
$sla = $m['slaOverdue'];
$sections = [
    'respond' => ['ยังไม่เริ่มดำเนินการ (เกินกำหนดตอบสนอง)', 'bi-play-circle', 'respond_due'],
    'review' => ['ยังไม่ทบทวน/RCA (เกินกำหนด)', 'bi-search', 'review_due'],
    'reply' => ['ยังไม่ตอบกลับ (เกินกำหนด)', 'bi-reply', 'reply_due'],
    'close' => ['ยังไม่ปิดเคส (เกินกำหนดปิด)', 'bi-flag', 'close_due'],
];
$daysLate = static fn (?string $due): int => $due ? (int) round((strtotime(date('Y-m-d')) - strtotime($due)) / 86400) : 0;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ปีงบ <?= $fiscalYear ?> · เกินกำหนดรวม <?= number_format($m['slaOverdueTotal']) ?> รายการ<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-semibold mb-0"><i class="bi bi-alarm me-1"></i> ติดตาม SLA</h1>
        <?= Html::beginForm(['monitor'], 'get', ['class' => 'd-flex align-items-center gap-2 mb-0']) ?>
            <label class="small text-body-secondary mb-0">ปีงบ</label>
            <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()']) ?>
        <?= Html::endForm() ?>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/complaint/menu', ['active' => 'monitor']) ?></div>

    <?php if ($m['slaOverdueTotal'] === 0): ?>
        <div class="alert alert-success py-2"><i class="bi bi-check2-circle me-1"></i> ไม่มีเรื่องที่เกินกำหนด SLA ในปีงบนี้</div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <?php foreach ($sections as $key => [$title, $icon]): ?>
            <div class="col-6 col-lg-3">
                <div class="card border shadow-sm h-100 <?= count($sla[$key]) ? 'border-danger-subtle' : '' ?>">
                    <div class="card-body d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-<?= count($sla[$key]) ? 'danger' : 'success' ?>-subtle text-<?= count($sla[$key]) ? 'danger' : 'success' ?>-emphasis" style="width:42px;height:42px;">
                            <i class="bi <?= $icon ?> fs-5"></i>
                        </span>
                        <div>
                            <div class="h4 fw-bold mb-0"><?= number_format(count($sla[$key])) ?></div>
                            <div class="text-body-secondary" style="font-size:.72rem"><?= Html::encode($title) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php foreach ($sections as $key => [$title, $icon, $dueField]): ?>
        <?php if (!count($sla[$key])) { continue; } ?>
        <div class="card border shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold"><i class="bi <?= $icon ?> me-1 text-danger"></i> <?= Html::encode($title) ?> (<?= count($sla[$key]) ?>)</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th style="width:130px">เลขที่</th><th>เรื่อง</th><th style="width:150px">หน่วยงาน</th><th style="width:90px" class="text-center">ระดับ</th><th style="width:110px">กำหนด</th><th style="width:90px" class="text-center">เกิน (วัน)</th></tr>
                    </thead>
                    <tbody>
                        <?php /** @var Complaint $c */ foreach ($sla[$key] as $c): ?>
                            <tr>
                                <td><?= Html::a(Html::encode($c->complaint_no), ['/complaint/complaint/view', 'id' => $c->id], ['class' => 'text-decoration-none fw-semibold']) ?></td>
                                <td class="small"><?= Html::encode($c->title) ?></td>
                                <td class="small"><?= $c->assignedUnit ? Html::encode($c->assignedUnit->name) : '—' ?></td>
                                <td class="text-center"><?= $c->severity_level ? '<span class="badge text-bg-' . ($c->severity_level >= 3 ? 'danger' : 'secondary') . '">ระดับ ' . (int) $c->severity_level . '</span>' : '—' ?></td>
                                <td class="small"><?= AppHelper::convertToThai($c->$dueField) ?></td>
                                <td class="text-center"><span class="badge text-bg-danger"><?= $daysLate($c->$dueField) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="card border shadow-sm">
        <div class="card-header bg-transparent fw-semibold"><i class="bi bi-table me-1"></i> เกณฑ์ SLA ตามระดับ (วัน)</div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 text-center">
                <thead class="table-light"><tr><th>ระดับ</th><th>ตอบสนอง</th><th>ตรวจสอบ/RCA</th><th>ตอบกลับ</th><th>เยียวยา</th><th>ปิดเคส</th></tr></thead>
                <tbody>
                    <?php foreach (Complaint::SLA as $lv => $row): ?>
                        <tr>
                            <td class="fw-semibold">ระดับ <?= $lv ?></td>
                            <td><?= $row['respond'] ?? '—' ?></td>
                            <td><?= $row['review'] ?? '—' ?></td>
                            <td><?= $row['reply'] ?? '—' ?></td>
                            <td><?= $row['remedy'] ?? '—' ?></td>
                            <td><?= $row['close'] ?? '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
