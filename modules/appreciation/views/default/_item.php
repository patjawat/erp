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
$badgeLabel = $badgeLabels[$model->badge_type] ?? $model->badge_type;
$badgeEmoji = Appreciation::badgeEmojis()[$model->badge_type] ?? '🤝';
$likeCount = $model->getLikeCount();
$isLiked = $me ? $model->isLikedBy($me->id) : false;
$timeAgo = ThaiDateHelper::formatThaiDate($model->created_at, 'short') ?? $model->created_at;
$frameStyle = isset(Appreciation::frameLabels()[$model->frame_style]) ? $model->frame_style : Appreciation::FRAME_CLASSIC;
?>

<article class="appreciation-post" id="appreciation-<?= (int) $model->id ?>">
    <header class="appreciation-post__header">
        <?= Html::img($from ? $from->showAvatar() : '', [
            'class' => 'appreciation-avatar appreciation-avatar--post',
            'width' => '48',
            'height' => '48',
            'alt' => '',
            'loading' => 'lazy',
        ]) ?>
        <div class="appreciation-post__identity">
            <h3><?= $from ? Html::encode($from->fullname()) : 'เพื่อนร่วมงาน' ?></h3>
            <p>
                <span>ชื่นชม</span>
                <strong><?= $to ? Html::encode($to->fullname()) : 'เพื่อนร่วมงาน' ?></strong>
                <span aria-hidden="true">·</span>
                <time><?= Html::encode($timeAgo) ?></time>
            </p>
        </div>
    </header>

    <div class="appreciation-post__body">
        <p class="appreciation-post__message"><?= nl2br(Html::encode($model->message)) ?></p>

        <?php if ($badgeLabel): ?>
            <div class="appreciation-value"><span aria-hidden="true"><?= $badgeEmoji ?></span><?= Html::encode($badgeLabel) ?></div>
        <?php endif; ?>

        <?php if ($model->image_path): ?>
            <figure class="appreciation-frame appreciation-frame--<?= Html::encode($frameStyle) ?>">
                <?= Html::img(Url::to($model->image_path), [
                    'class' => 'appreciation-frame__image',
                    'alt' => 'ภาพประกอบคำขอบคุณจาก ' . ($from ? $from->fullname() : 'เพื่อนร่วมงาน'),
                    'loading' => 'lazy',
                ]) ?>
            </figure>
        <?php endif; ?>
    </div>

    <footer class="appreciation-post__footer">
        <button type="button"
                class="btn-appreciation-like<?= $isLiked ? ' is-liked' : '' ?>"
                data-id="<?= (int) $model->id ?>"
                aria-pressed="<?= $isLiked ? 'true' : 'false' ?>"
                aria-label="ชื่นชมคำขอบคุณนี้">
            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="<?= $isLiked ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
            <span>ชื่นชม</span>
            <span class="like-count"><?= (int) $likeCount ?></span>
        </button>
        <span class="appreciation-points">ได้รับ +<?= (int) $model->points_given ?> คะแนน</span>
    </footer>
</article>
