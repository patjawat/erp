<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use app\modules\appreciation\models\AppreciationChallenge;

$this->title = $challenge->name;
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'พลังแห่งคำขอบคุณ', 'url' => ['/appreciation/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'Challenge', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="badge <?= $challenge->isActive() ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                <?= AppreciationChallenge::statusLabels()[$challenge->status] ?? $challenge->status ?>
            </span>
            <span class="text-muted small"><?= ThaiDateHelper::formatThaiDate($challenge->start_at) ?> - <?= ThaiDateHelper::formatThaiDate($challenge->end_at) ?></span>
        </div>
        <h4 class="fw-bold"><?= Html::encode($challenge->name) ?></h4>
        <?php if ($challenge->description): ?>
            <p class="text-muted"><?= nl2br(Html::encode($challenge->description)) ?></p>
        <?php endif; ?>
        <div class="d-flex flex-wrap gap-3 mb-2">
            <span class="badge bg-primary rounded-pill">เป้า: <?= AppreciationChallenge::goalTypeLabels()[$challenge->goal_type] ?? $challenge->goal_type ?> <?= (int) $challenge->goal_value ?> ครั้ง</span>
            <?php if ($challenge->reward_name): ?>
                <span class="badge bg-warning text-dark rounded-pill">รางวัล: <?= Html::encode($challenge->reward_name) ?></span>
            <?php endif; ?>
        </div>
        <?php if ($myProgress): ?>
            <div class="alert alert-light border mb-0">
                <strong>ความคืบหน้าของคุณ:</strong> <?= (int) $myProgress->current_value ?> / <?= (int) $challenge->goal_value ?>
                <?php if ($myProgress->isCompleted()): ?>
                    <span class="badge bg-success ms-2">ทำครบแล้ว!</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white py-2 px-3">
                <h6 class="mb-0 small fw-normal">ผู้ทำครบเป้าแล้ว (เรียงตามลำดับ)</h6>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($leaderboard)): ?>
                    <div class="list-group-item text-muted small">ยังไม่มีผู้ทำครบเป้า</div>
                <?php else: ?>
                    <?php foreach ($leaderboard as $i => $p): ?>
                        <div class="list-group-item d-flex align-items-center">
                            <span class="badge bg-secondary rounded-pill me-2"><?= $i + 1 ?></span>
                            <?= $p->emp ? Html::encode($p->emp->fullname()) : '-' ?>
                            <span class="ms-2 small text-muted"><?= ThaiDateHelper::formatThaiDate($p->completed_at, 'medium') ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light py-2 px-3">
                <h6 class="mb-0 small fw-normal">ความคืบหน้าล่าสุด (Top 20)</h6>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($inProgress)): ?>
                    <div class="list-group-item text-muted small">ยังไม่มีข้อมูล</div>
                <?php else: ?>
                    <?php foreach ($inProgress as $p): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= $p->emp ? Html::encode($p->emp->fullname()) : '-' ?></span>
                            <span class="badge bg-primary rounded-pill"><?= (int) $p->current_value ?> / <?= (int) $challenge->goal_value ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= Html::a('กลับรายการ Challenge', ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
