<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\ThaiDateHelper;
use app\modules\appreciation\models\Appreciation;

/** @var Appreciation $model */
/** @var \app\modules\hr\models\Employees|null $me */
$from = $model->fromEmp;
$to = $model->toEmp;
$badgeLabels = Appreciation::badgeLabels();
$badgeLabel = isset($badgeLabels[$model->badge_type]) ? $badgeLabels[$model->badge_type] : $model->badge_type;
$likeCount = $model->getLikeCount();
$isLiked = $me ? $model->isLikedBy($me->id) : false;
$timeAgo = ThaiDateHelper::formatThaiDate($model->created_at, 'short') ?? $model->created_at;
?>

<div class="card border-0 shadow-sm rounded-3 mb-3 appreciation-post">
    <div class="card-body p-4">
        <!-- Header: ผู้ส่ง → หัวใจ → ผู้รับ + เวลา -->
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <?= Html::img($from ? $from->showAvatar() : '', [
                    'class' => 'rounded-circle border border-2 border-white shadow-sm',
                    'width' => '48',
                    'height' => '48',
                    'alt' => '',
                ]) ?>
                <span class="text-muted small d-none d-md-inline">ส่งถึง</span>
                <span class="text-danger d-none d-md-inline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                </span>
                <?= Html::img($to ? $to->showAvatar() : '', [
                    'class' => 'rounded-circle border border-2 border-white shadow-sm',
                    'width' => '48',
                    'height' => '48',
                    'alt' => '',
                ]) ?>
            </div>
            <div class="flex-grow-1 min-w-0">
                <p class="mb-0 fw-bold">
                    <span class="text-dark"><?= $from ? Html::encode($from->fullname()) : '-' ?></span>
                    <span class="text-muted fw-normal small"> ชื่นชม </span>
                    <span class="text-primary"><?= $to ? Html::encode($to->fullname()) : '-' ?></span>
                </p>
                <p class="mb-0 text-muted small"><?= $timeAgo ?></p>
                <?php if ($badgeLabel): ?>
                    <?php $emoji = \app\modules\appreciation\models\Appreciation::badgeEmojis()[$model->badge_type] ?? '❤️'; ?>
                    <span class="badge rounded-pill bg-primary bg-opacity-25 text-primary mt-1"><?= $emoji ?> <?= Html::encode($badgeLabel) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- ข้อความ (เหมือนโพสต์) -->
        <div class="p-3 rounded-3 bg-light border-0 mb-3">
            <p class="mb-0 fst-italic">"<?= nl2br(Html::encode($model->message)) ?>"</p>
        </div>

        <!-- Actions: Like + คะแนน -->
        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
            <button type="button" class="btn btn-link text-decoration-none p-0 btn-appreciation-like <?= $isLiked ? 'active text-danger' : 'text-muted' ?>" data-id="<?= (int) $model->id ?>">
                <span class="me-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="<?= $isLiked ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
                </span>
                <span class="like-count fw-medium"><?= (int) $likeCount ?></span>
                <span class="small text-muted ms-1">คนชื่นชอบ</span>
            </button>
            <span class="badge rounded-pill bg-warning text-dark">+<?= (int) $model->points_given ?> คะแนน</span>
        </div>
    </div>
</div>
