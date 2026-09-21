<?php

use app\modules\ha12\models\Ha12Assessment;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12Assessment $assessment */
/** @var app\modules\ha12\models\Ha12Round $round */
/** @var app\modules\ha12\models\Ha12Activity $activity */
/** @var app\modules\ha12\models\Ha12Criteria[] $criteria */

$this->title = 'HA12-PCT · ผลประเมิน';
$levels = $assessment->levelArray();
$critByLevel = [];
foreach ($criteria as $c) {
    $critByLevel[(int) $c->level] = $c;
}
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ผลประเมิน · <?= Html::encode($activity->no . '. ' . $activity->name) ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'round']) ?></div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 fw-semibold mb-0"><i class="bi bi-clipboard2-check me-1"></i> <?= $activity->no ?>. <?= Html::encode($activity->name) ?></h1>
            <div class="text-body-secondary small"><?= Html::encode($round->displayTitle()) ?> · <?= Html::encode($round->scopeUnit->name ?? 'ทั้งโรงพยาบาล') ?></div>
        </div>
        <?= Html::a('<i class="bi bi-arrow-left"></i> กลับรอบ', ['/ha12/round/view', 'id' => $round->id], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
    </div>

    <div class="card border shadow-sm mb-3"><div class="card-body">
        <div class="mb-2">
            <span class="small fw-semibold text-body-secondary">ระดับการพัฒนาที่ประเมิน:</span>
            <?php if ($levels): ?>
                <?php foreach ($levels as $lv): ?>
                    <span class="badge bg-primary-subtle text-primary-emphasis me-1">ระดับ <?= $lv ?><?= isset($critByLevel[$lv]) && $critByLevel[$lv]->title ? ' · ' . Html::encode($critByLevel[$lv]->title) : '' ?></span>
                <?php endforeach; ?>
            <?php else: ?><span class="text-body-tertiary">—</span><?php endif; ?>
        </div>
        <?php if ($assessment->reason): ?><div class="mb-2"><span class="small fw-semibold text-body-secondary">เหตุผล:</span> <?= nl2br(Html::encode($assessment->reason)) ?></div><?php endif; ?>
        <?php if ($assessment->summary_text): ?><div><span class="small fw-semibold text-body-secondary">สรุป/ข้อเสนอแนะ:</span> <?= nl2br(Html::encode($assessment->summary_text)) ?></div><?php endif; ?>
    </div></div>

    <?php if ($assessment->rows): ?>
        <div class="card border shadow-sm">
            <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-table me-1"></i> ตารางสรุป</div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="bg-body-tertiary"><tr>
                        <th style="width:16rem;">หน่วยงาน</th><th>เรื่อง/โรค</th><th>ผลการปรับปรุง</th><th style="width:18rem;">หลักฐาน</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($assessment->rows as $row): ?>
                        <tr>
                            <td><?= Html::encode($row->unit_name ?: '—') ?></td>
                            <td><?= Html::encode($row->topic ?: '—') ?></td>
                            <td class="small"><?= $row->improvement ? nl2br(Html::encode($row->improvement)) : '—' ?></td>
                            <td class="small">
                                <?php if ($row->sources): ?>
                                    <?php foreach ($row->sources as $src): ?>
                                        <div><i class="bi bi-paperclip"></i> <?= Html::encode($src->label) ?></div>
                                    <?php endforeach; ?>
                                <?php else: ?><span class="text-body-tertiary">—</span><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
