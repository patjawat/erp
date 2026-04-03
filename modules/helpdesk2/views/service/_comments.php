<?php
use yii\helpers\Html;

/** @var array $comments */
?>

<div class="card shadow-sm mt-3">
    <div class="card-header fw-bold">การให้คะแนนและการลงความเห็นของผู้แจ้ง</div>
    <div class="card-body p-4">
        <?php if (empty($comments)): ?>
            <div class="text-muted">ยังไม่มีการให้คะแนนหรือความเห็นจากผู้แจ้ง</div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($comments as $c): ?>
                    <?php
                    $isStaff = false;
                    $name = '';
                    $createdAt = null;
                    $message = '';

                    if (is_object($c)) {
                        $isStaff = (bool) ($c->is_staff ?? false);
                        $name = $c->user->name ?? ($c->name ?? '');
                        $createdAt = $c->created_at ?? null;
                        $message = $c->message ?? '';
                    } elseif (is_array($c)) {
                        $isStaff = (bool) ($c['is_staff'] ?? false);
                        $name = $c['name'] ?? '';
                        $createdAt = $c['created_at'] ?? null;
                        $message = $c['message'] ?? '';
                    }
                    ?>

                    <div class="d-flex <?= $isStaff ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="comment-box <?= $isStaff ? 'bg-primary bg-opacity-10 text-primary border border-primary-subtle' : 'bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle' ?> rounded-3 p-3 w-75">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                                <div class="fw-medium small"><?= Html::encode($name ?: ($isStaff ? 'เจ้าหน้าที่' : 'ผู้แจ้ง')) ?></div>
                                <div class="text-muted small">
                                    <?= Html::encode($createdAt ? \Yii::$app->formatter->asDatetime($createdAt) : '-') ?>
                                </div>
                            </div>
                            <div class="mb-0"><?= Html::encode($message ?: '-') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

