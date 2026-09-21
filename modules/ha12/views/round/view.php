<?php

use app\modules\ha12\models\Ha12Assessment;
use app\modules\ha12\models\Ha12Round;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12Round $round */
/** @var app\modules\ha12\models\Ha12Activity[] $activities */
/** @var array<int,Ha12Assessment> $assessments  index by activity_id */
/** @var array{total:int,published:int} $progress */
/** @var bool $canClose */
/** @var bool $isManager */

$this->title = 'HA12-PCT · ' . $round->displayTitle();
$pctDone = $progress['total'] ? round($progress['published'] / $progress['total'] * 100) : 0;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>รอบประเมิน · <?= Html::encode($round->periodLabel()) ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'round']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $tone): ?>
        <?php if ($msg = Yii::$app->session->getFlash($flash)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 fw-semibold mb-0"><i class="bi bi-clipboard2-data me-1"></i> <?= Html::encode($round->displayTitle()) ?></h1>
            <div class="text-body-secondary small">
                <?= Html::encode($round->scopeUnit->name ?? 'ทั้งโรงพยาบาล') ?>
                · <?= Html::encode($round->periodLabel()) ?>
                · <?php if ($round->isClosed()): ?><span class="badge bg-secondary-subtle text-secondary-emphasis">ปิดรอบ</span><?php else: ?><span class="badge bg-success-subtle text-success-emphasis">เปิดอยู่</span><?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับ', ['index', 'fy' => $round->fiscal_year], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
            <?php if ($isManager && !$round->isClosed()): ?>
                <?= Html::a('<i class="bi bi-lock"></i> ปิดรอบ', ['close', 'id' => $round->id], [
                    'class' => 'btn btn-sm ' . ($canClose ? 'btn-primary' : 'btn-outline-secondary disabled'),
                    'title' => $canClose ? 'ปิดรอบ' : 'ต้องเผยแพร่ครบทุกกิจกรรมก่อน',
                    'data' => ['method' => 'post', 'confirm' => 'ปิดรอบนี้?'],
                ]) ?>
            <?php elseif ($isManager && $round->isClosed()): ?>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reopenModal"><i class="bi bi-unlock"></i> เปิดรอบใหม่</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border shadow-sm mb-3"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-semibold">ความคืบหน้าการเผยแพร่</span>
            <span class="small text-body-secondary"><?= $progress['published'] ?> / <?= $progress['total'] ?> กิจกรรม</span>
        </div>
        <div class="progress" style="height:10px;"><div class="progress-bar bg-success" style="width: <?= $pctDone ?>%"></div></div>
        <?php if ($round->reopen_reason): ?>
            <div class="small text-body-secondary mt-2"><i class="bi bi-info-circle"></i> เหตุผลเปิดรอบใหม่: <?= Html::encode($round->reopen_reason) ?></div>
        <?php endif; ?>
    </div></div>

    <div class="card border shadow-sm">
        <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-list-ol me-1"></i> 12 กิจกรรม</div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary"><tr>
                    <th style="width:44px;" class="text-center">#</th><th>กิจกรรม</th>
                    <th style="width:140px;">ระดับที่ประเมิน</th><th style="width:120px;" class="text-center">สถานะ</th>
                    <th style="width:120px;" class="text-center">จัดการ</th>
                </tr></thead>
                <tbody>
                <?php foreach ($activities as $act): $a = $assessments[$act->id] ?? null; ?>
                    <tr>
                        <td class="text-center text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= $act->no ?></td>
                        <td class="fw-semibold"><?= Html::encode($act->name) ?></td>
                        <td>
                            <?php if ($a && $a->levelArray()): ?>
                                <?php foreach ($a->levelArray() as $lv): ?><span class="badge bg-primary-subtle text-primary-emphasis me-1">ระดับ <?= $lv ?></span><?php endforeach; ?>
                            <?php else: ?><span class="text-body-tertiary">—</span><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($a && $a->isPublished()): ?><span class="badge bg-success-subtle text-success-emphasis">เผยแพร่</span>
                            <?php elseif ($a): ?><span class="badge bg-warning-subtle text-warning-emphasis">ร่าง</span>
                            <?php else: ?><span class="badge bg-body-secondary text-body-secondary">ยังไม่ประเมิน</span><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($isManager && !$round->isClosed()): ?>
                                <?= Html::a('<i class="bi bi-pencil-square"></i> ประเมิน', ['/ha12/assessment/edit', 'round_id' => $round->id, 'activity_id' => $act->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            <?php elseif ($a && $a->isPublished()): ?>
                                <?= Html::a('<i class="bi bi-eye"></i> ดู', ['/ha12/assessment/view', 'id' => $a->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            <?php else: ?><span class="text-body-tertiary small">—</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($isManager && $round->isClosed()): ?>
<div class="modal fade" id="reopenModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <?= Html::beginForm(['reopen', 'id' => $round->id], 'post') ?>
    <div class="modal-header"><h5 class="modal-title">เปิดรอบใหม่</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label small fw-semibold">เหตุผลการเปิดรอบใหม่ <span class="text-danger">*</span></label>
        <?= Html::textarea('reopen_reason', '', ['class' => 'form-control', 'rows' => 3, 'required' => true]) ?>
    </div>
    <div class="modal-footer">
        <?= Html::submitButton('เปิดรอบใหม่', ['class' => 'btn btn-primary']) ?>
        <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data' => ['bs-dismiss' => 'modal']]) ?>
    </div>
    <?= Html::endForm() ?>
</div></div></div>
<?php endif; ?>
