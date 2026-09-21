<?php

use app\components\AppHelper;
use app\modules\ha12\models\Ha12MedReport;
use app\modules\ha12\models\Ha12MedTemplate;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12MedReport $report */
/** @var bool $canManage */

$this->title = 'HA12-PCT · รายงานยา';
$sev = Ha12MedTemplate::SEVERITY;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>รายงานความคลาดเคลื่อนทางยา · <?= AppHelper::convertToThai($report->period_start) ?> – <?= AppHelper::convertToThai($report->period_end) ?><?php $this->endBlock(); ?>

<style>.med-grid th,.med-grid td{white-space:nowrap;vertical-align:middle}.med-num{font-variant-numeric:tabular-nums;text-align:right}</style>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'med']) ?></div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 fw-semibold mb-0"><i class="bi bi-capsule me-1"></i> รายงานความคลาดเคลื่อนทางยา</h1>
            <div class="text-body-secondary small">
                <?= Html::encode($report->ownerUnit->name ?? '—') ?>
                · วันที่ทบทวน <?= $report->review_date ? AppHelper::convertToThai($report->review_date) : '—' ?>
                · รวมทุกหัวข้อ <b><?= number_format($report->grandTotal()) ?></b> ครั้ง
            </div>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับ', ['index', 'fy' => $report->fiscal_year], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
            <?php if ($canManage && !$report->deleted): ?>
                <?= Html::a('<i class="bi bi-pencil-square"></i> กรอก/แก้ไข', ['edit', 'id' => $report->id], ['class' => 'btn btn-primary btn-sm']) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($report->note): ?>
        <div class="card border shadow-sm mb-3"><div class="card-body"><div class="small text-body-secondary fw-semibold mb-1">บันทึกรวม</div><?= nl2br(Html::encode($report->note)) ?></div></div>
    <?php endif; ?>

    <?php foreach ($report->groups as $g): ?>
        <?php $rate = $g->rate(); ?>
        <div class="card border shadow-sm mb-3">
            <div class="card-header bg-body-tertiary d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="fw-semibold"><?= $g->group_no ?>. <?= Html::encode($g->groupName()) ?></span>
                <span class="small text-body-secondary">
                    ตัวหาร <?= $g->divisor ? number_format((int) $g->divisor) . ' ' . Ha12MedTemplate::unitLabel($g->divisor_unit) : '—' ?>
                    · รวม <b><?= number_format($g->groupTotal()) ?></b>
                    <?php if ($rate !== null): ?> · อัตรา <b><?= number_format($rate, 2) ?></b> ต่อ <?= number_format((int) $g->rate_base) ?><?php endif; ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle med-grid mb-0">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th style="min-width:12rem;">ความเสี่ยงย่อย</th>
                                <th>ทีม</th>
                                <?php foreach ($sev as $label): ?><th class="text-center"><?= Html::encode($label) ?></th><?php endforeach; ?>
                                <th class="text-center">รวม</th>
                                <th style="min-width:14rem;">ผล / การแก้ไข</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($g->counts as $c): ?>
                                <?php if ($c->total_count === null && !$c->review_result && !$c->fix): ?>
                                    <?php continue; // ข้ามแถวที่ยังไม่รายงานและไม่มีบันทึก ?>
                                <?php endif; ?>
                                <tr>
                                    <td><?= Html::encode($c->risk_name) ?><?= $c->is_other ? ' <span class="badge bg-secondary-subtle text-secondary-emphasis">อื่น ๆ</span>' : '' ?></td>
                                    <td class="text-body-secondary"><?= Html::encode($c->teamLabel()) ?></td>
                                    <?php foreach (array_keys($sev) as $col): ?>
                                        <td class="med-num text-center"><?= $c->$col === null ? '' : number_format((int) $c->$col) ?></td>
                                    <?php endforeach; ?>
                                    <td class="med-num text-center fw-semibold"><?= $c->total_count === null ? '—' : number_format((int) $c->total_count) ?></td>
                                    <td class="small">
                                        <?php if ($c->review_result): ?><div><?= nl2br(Html::encode($c->review_result)) ?></div><?php endif; ?>
                                        <?php if ($c->fix): ?><div class="text-body-secondary"><i class="bi bi-arrow-return-right"></i> <?= nl2br(Html::encode($c->fix)) ?></div><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
