<?php

use app\components\AppHelper;
use app\modules\ha12\models\Ha12Assessment;
use app\modules\ha12\models\Ha12Round;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12Round $round */
/** @var app\modules\ha12\models\Ha12Activity[] $activities */
/** @var array<int,Ha12Assessment> $assessments */

$this->title = 'HA12-PCT · รายงานรอบ';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>รายงานสรุปและประเมินโดย PCT<?php $this->endBlock(); ?>

<style>
@media print {
    .ha12-noprint, .navbar, .sidebar, .app-sidebar, .page-action, header, footer { display: none !important; }
    .ha12-report { box-shadow: none !important; border: 0 !important; }
    a[href]:after { content: none !important; }
}
.ha12-report table { width: 100%; }
</style>

<div class="container-fluid px-0">
    <div class="mb-3 ha12-noprint"><?= $this->render('@app/modules/ha12/menu', ['active' => 'report']) ?></div>

    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 ha12-noprint">
        <?= Html::a('<i class="bi bi-arrow-left"></i> กลับ', ['index', 'fy' => $round->fiscal_year], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-file-earmark-excel"></i> Excel', ['export', 'id' => $round->id], ['class' => 'btn btn-success btn-sm']) ?>
            <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> พิมพ์</button>
        </div>
    </div>

    <div class="card border shadow-sm ha12-report">
        <div class="card-body">
            <div class="text-center mb-4">
                <h1 class="h5 fw-bold mb-1">รายงานสรุปและประเมินโดย PCT</h1>
                <div class="fw-semibold"><?= Html::encode($round->displayTitle()) ?></div>
                <div class="text-body-secondary small">
                    ขอบเขต: <?= Html::encode($round->scopeUnit->name ?? 'ทั้งโรงพยาบาล') ?>
                    · ช่วง: <?= AppHelper::convertToThai($round->period_start) ?> – <?= AppHelper::convertToThai($round->period_end) ?>
                    · สถานะ: <?= $round->isClosed() ? 'ปิดรอบ' : 'เปิดอยู่' ?>
                </div>
            </div>

            <?php foreach ($activities as $act): $a = $assessments[$act->id] ?? null; ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-1 mb-2">
                        <h2 class="h6 fw-bold mb-0"><?= $act->no ?>. <?= Html::encode($act->name) ?></h2>
                        <div>
                            <?php if ($a && $a->levelArray()): ?>
                                <?php foreach ($a->levelArray() as $lv): ?><span class="badge bg-primary-subtle text-primary-emphasis">ระดับ <?= $lv ?></span> <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ($a && !$a->isPublished()): ?><span class="badge bg-warning-subtle text-warning-emphasis">ร่าง</span><?php endif; ?>
                            <?php if (!$a): ?><span class="badge bg-body-secondary text-body-secondary">ยังไม่ประเมิน</span><?php endif; ?>
                        </div>
                    </div>
                    <?php if ($a && $a->reason): ?><div class="small mb-1"><span class="text-body-secondary fw-semibold">เหตุผล:</span> <?= nl2br(Html::encode($a->reason)) ?></div><?php endif; ?>
                    <?php if ($a && $a->summary_text): ?><div class="small mb-2"><span class="text-body-secondary fw-semibold">สรุป:</span> <?= nl2br(Html::encode($a->summary_text)) ?></div><?php endif; ?>

                    <?php if ($a && $a->rows): ?>
                        <table class="table table-sm table-bordered align-middle small mb-0">
                            <thead class="bg-body-tertiary"><tr>
                                <th style="width:20%;">หน่วยงาน</th><th style="width:28%;">เรื่อง/โรค</th><th>ผลการปรับปรุง</th><th style="width:22%;">หลักฐาน</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($a->rows as $r): ?>
                                <tr>
                                    <td><?= Html::encode($r->unit_name ?: '—') ?></td>
                                    <td><?= Html::encode($r->topic ?: '—') ?></td>
                                    <td><?= $r->improvement ? nl2br(Html::encode($r->improvement)) : '—' ?></td>
                                    <td>
                                        <?php foreach ($r->sources as $src): ?><div><?= Html::encode($src->label) ?></div><?php endforeach; ?>
                                        <?= $r->sources ? '' : '—' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
