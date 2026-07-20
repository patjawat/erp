<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;
use app\modules\appreciation\models\AppreciationChallenge;

/**
 * @var AppreciationChallenge[] $activeChallenges
 * @var array $myChallengeProgress
 */
$featuredChallenge = $activeChallenges[0] ?? null;
?>
<section class="appreciation-promo appreciation-challenge" aria-labelledby="challenge-title">
    <div class="appreciation-section-head">
        <div>
            <h2 id="challenge-title">กิจกรรมท้าทาย</h2>
            <p>ทำเป้าหมายให้ครบเพื่อรับรางวัล</p>
        </div>
        <?= Html::a('ดูทั้งหมด', ['challenge/index'], ['class' => 'appreciation-text-link']) ?>
    </div>

    <?php if (!$featuredChallenge): ?>
        <div class="appreciation-quiet-state">
            <i class="bi bi-trophy" aria-hidden="true"></i>
            <span>ยังไม่มีกิจกรรมที่กำลังจัด</span>
        </div>
    <?php else: ?>
        <?php
        $progress = $myChallengeProgress[$featuredChallenge->id] ?? null;
        $current = $progress ? (int) $progress->current_value : 0;
        $goal = (int) $featuredChallenge->goal_value;
        $percent = $goal > 0 ? min(100, (int) round($current / $goal * 100)) : 0;
        $completed = $progress && $progress->completed_at;
        ?>
        <div class="appreciation-challenge__hero">
            <span class="appreciation-challenge__icon"><i class="bi bi-trophy" aria-hidden="true"></i></span>
            <div>
                <h3><?= Html::encode($featuredChallenge->name) ?></h3>
                <p><?= Html::encode(AppreciationChallenge::goalTypeLabels()[$featuredChallenge->goal_type] ?? $featuredChallenge->goal_type) ?> <?= number_format($goal) ?> ครั้ง</p>
            </div>
            <?php if ($completed): ?><span class="appreciation-status is-complete">สำเร็จแล้ว</span><?php endif; ?>
        </div>
        <div class="appreciation-progress" role="progressbar" aria-label="ความคืบหน้ากิจกรรม" aria-valuenow="<?= $current ?>" aria-valuemin="0" aria-valuemax="<?= $goal ?>">
            <span style="width: <?= $percent ?>%"></span>
        </div>
        <div class="appreciation-challenge__meta">
            <strong><?= number_format($current) ?> จาก <?= number_format($goal) ?> ครั้ง</strong>
            <span>ถึง <?= ThaiDateHelper::formatThaiDate($featuredChallenge->end_at) ?></span>
        </div>
        <?php if (count($activeChallenges) > 1): ?>
            <p class="appreciation-challenge__more">ยังมีอีก <?= count($activeChallenges) - 1 ?> กิจกรรมที่เข้าร่วมได้</p>
        <?php endif; ?>
    <?php endif; ?>
</section>
