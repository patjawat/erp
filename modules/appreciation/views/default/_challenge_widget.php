<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\ThaiDateHelper;
use app\modules\appreciation\models\AppreciationChallenge;

/**
 * @var AppreciationChallenge[] $activeChallenges
 * @var array $myChallengeProgress [ challenge_id => AppreciationChallengeProgress|null ]
 */
?>
<div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
    <div class="card-header bg-warning text-dark py-2 px-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 small fw-bold"><i class="bi bi-trophy me-1"></i> Challenge</h6>
        <?= Html::a('ดูทั้งหมด', ['challenge/index'], ['class' => 'btn btn-sm btn-outline-dark rounded-pill']) ?>
    </div>
    <div class="card-body p-3">
        <?php if (empty($activeChallenges)): ?>
            <p class="small text-muted mb-2">ยังไม่มี Challenge กำลังจัด</p>
            <?= Html::a('ดู Challenge', ['challenge/index'], ['class' => 'btn btn-warning w-100 rounded-3 btn-sm']) ?>
        <?php else: ?>
            <?php foreach ($activeChallenges as $idx => $ch): ?>
                <?php
                $prog = $myChallengeProgress[$ch->id] ?? null;
                $current = $prog ? (int) $prog->current_value : 0;
                $goal = (int) $ch->goal_value;
                $pct = $goal > 0 ? min(100, (int) round($current / $goal * 100)) : 0;
                $done = $prog && $prog->completed_at;
                $isLast = $idx === count($activeChallenges) - 1;
                ?>
                <div class="mb-3 pb-3 <?= !$isLast ? 'border-bottom border-light' : '' ?>">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="mb-0 small fw-bold text-dark"><?= Html::encode($ch->name) ?></h6>
                        <?php if ($done): ?>
                            <span class="badge bg-success rounded-pill">ครบแล้ว</span>
                        <?php endif; ?>
                    </div>
                    <p class="small text-muted mb-2"><?= Html::encode(AppreciationChallenge::goalTypeLabels()[$ch->goal_type] ?? $ch->goal_type) ?> <?= $goal ?> ครั้ง · ถึง <?= ThaiDateHelper::formatThaiDate($ch->end_at) ?></p>
                    <div class="progress rounded-pill" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $current ?>" aria-valuemin="0" aria-valuemax="<?= $goal ?>"></div>
                    </div>
                    <p class="small mb-0 mt-1 text-muted"><?= $current ?> / <?= $goal ?></p>
                </div>
            <?php endforeach; ?>
            <?= Html::a('ดูทั้งหมด', ['challenge/index'], ['class' => 'btn btn-outline-warning w-100 rounded-3 btn-sm']) ?>
        <?php endif; ?>
    </div>
</div>
