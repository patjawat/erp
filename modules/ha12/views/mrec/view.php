<?php

use app\components\AppHelper;
use app\modules\ha12\models\Ha12MrecAudit;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12MrecAudit $audit */
/** @var bool $canManage */

$this->title = 'HA12-PCT · เวชระเบียน';
$overall = $audit->overallPercent();
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ความสมบูรณ์ของเวชระเบียน · <?= Html::encode($audit->ownerUnit->name ?? '—') ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'mrec']) ?></div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 fw-semibold mb-0"><i class="bi bi-journal-medical me-1"></i> ความสมบูรณ์ของเวชระเบียน</h1>
            <div class="text-body-secondary small">
                จำนวนตรวจ <?= $audit->total_charts !== null ? number_format((int) $audit->total_charts) : '—' ?> ฉบับ
                · ความสมบูรณ์รวม <b><?= $overall !== null ? number_format($overall, 1) . '%' : '—' ?></b>
                · วันที่ทบทวน <?= $audit->review_date ? AppHelper::convertToThai($audit->review_date) : '—' ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับ', ['index', 'fy' => $audit->fiscal_year], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
            <?php if ($canManage && !$audit->deleted): ?>
                <?= Html::a('<i class="bi bi-pencil-square"></i> กรอก/แก้ไข', ['edit', 'id' => $audit->id], ['class' => 'btn btn-primary btn-sm']) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($audit->problem || $audit->result): ?>
        <div class="card border shadow-sm mb-3"><div class="card-body">
            <?php if ($audit->problem): ?><div class="mb-2"><span class="small fw-semibold text-body-secondary">ปัญหาที่พบ:</span> <?= nl2br(Html::encode($audit->problem)) ?></div><?php endif; ?>
            <?php if ($audit->result): ?><div><span class="small fw-semibold text-body-secondary">ผล/การปรับปรุง:</span> <?= nl2br(Html::encode($audit->result)) ?></div><?php endif; ?>
        </div></div>
    <?php endif; ?>

    <div class="card border shadow-sm">
        <div class="card-body p-0">
            <table class="table table-sm align-middle mb-0">
                <thead class="bg-body-tertiary"><tr>
                    <th style="width:44px;" class="text-center">#</th><th>หัวข้อ</th>
                    <th style="width:120px;" class="text-end">ครบ</th><th style="width:90px;" class="text-end">ร้อยละ</th>
                </tr></thead>
                <tbody>
                <?php foreach ($audit->items as $it): $pct = $it->percent(); ?>
                    <tr>
                        <td class="text-center text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= $it->item_no ?></td>
                        <td><?= Html::encode($it->item_name) ?></td>
                        <td class="text-end" style="font-variant-numeric:tabular-nums;"><?= $it->complete_count !== null ? number_format((int) $it->complete_count) : '—' ?></td>
                        <td class="text-end fw-semibold" style="font-variant-numeric:tabular-nums;"><?= $pct !== null ? number_format($pct, 1) . '%' : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
