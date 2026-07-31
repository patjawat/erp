<?php
use yii\helpers\Html;

/** @var array $comments */
?>

<section class="border-top mt-4 pt-4" aria-labelledby="repair-comments-title">
    <h3 class="h6 fw-bold mb-3" id="repair-comments-title">การให้คะแนนและความคิดเห็นของผู้แจ้ง</h3>
    <div>
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
                    $rating = 0;

                    if (is_object($c)) {
                        $isStaff = (bool) ($c->is_staff ?? false);
                        $name = $c->user->name ?? ($c->name ?? '');
                        $createdAt = $c->created_at ?? null;
                        $message = $c->message ?? '';
                        $rating = (int) ($c->rating ?? 0);
                    } elseif (is_array($c)) {
                        $isStaff = (bool) ($c['is_staff'] ?? false);
                        $name = $c['name'] ?? '';
                        $createdAt = $c['created_at'] ?? null;
                        $message = $c['message'] ?? '';
                        $rating = (int) ($c['rating'] ?? 0);
                    }
                    $rating = max(0, min(5, $rating));
                    ?>

                    <div class="d-flex <?= $isStaff ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="comment-box <?= $isStaff ? 'bg-primary-subtle text-primary-emphasis border border-primary-subtle' : 'bg-body-tertiary border' ?> rounded-3 p-3 w-100">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                <div class="fw-medium small"><?= Html::encode($name ?: ($isStaff ? 'เจ้าหน้าที่' : 'ผู้แจ้ง')) ?></div>
                                <div class="text-muted small">
                                    <?= Html::encode($createdAt ? \Yii::$app->formatter->asDatetime($createdAt) : '-') ?>
                                </div>
                            </div>
                            <?php if ($rating > 0): ?>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="text-warning" role="img" aria-label="<?= 'คะแนนความพึงพอใจ ' . $rating . ' จาก 5' ?>">
                                        <?php for ($s = 1; $s <= 5; $s++): ?><i class="fa-solid fa-star<?= $s <= $rating ? '' : ' text-body-tertiary opacity-50' ?>" aria-hidden="true"></i><?php endfor; ?>
                                    </span>
                                    <span class="small text-muted"><?= $rating ?>/5</span>
                                </div>
                            <?php endif; ?>
                            <?php if (trim((string) $message) !== ''): ?>
                                <div class="mb-0"><?= Html::encode($message) ?></div>
                            <?php elseif ($rating === 0): ?>
                                <div class="mb-0">-</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
