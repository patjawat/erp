<?php

use yii\helpers\Html;

/**
 * คะแนนความพึงพอใจรายพนักงานขับรถ + ข้อเสนอแนะล่าสุดจากผู้ขอ
 *
 * @var yii\web\View $this
 * @var app\modules\booking\models\VehicleSearch $searchModel
 */

$drivers = $searchModel->satisfactionDriverSummary(5);
$comments = $searchModel->satisfactionComments(4);

$stars = static function (float $score): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $icon = $score >= $i ? 'bi-star-fill' : ($score >= $i - 0.5 ? 'bi-star-half' : 'bi-star');
        $html .= '<i class="bi ' . $icon . '"></i>';
    }

    return '<span class="vd-stars">' . $html . '</span>';
};
?>

<section class="card border-0 shadow-sm vd-card" aria-labelledby="vd-feedback-heading">
    <div class="card-header bg-primary-gradient text-white">
        <h4 id="vd-feedback-heading" class="h6 text-white mb-0">
            <i class="bi bi-chat-left-quote me-1" aria-hidden="true"></i>เสียงจากผู้ขอใช้รถ
        </h4>
        <p class="small text-white-50 mb-0">คะแนนรายพนักงานขับรถและข้อเสนอแนะล่าสุด</p>
    </div>

    <div class="card-body">
        <?php if (empty($drivers) && empty($comments)): ?>
            <div class="text-center text-body-secondary py-4">
                <i class="bi bi-chat-left fs-3 d-block mb-1" aria-hidden="true"></i>
                <div class="small">ยังไม่มีผลประเมินในปีงบประมาณนี้</div>
            </div>
        <?php else: ?>
            <?php if (!empty($drivers)): ?>
                <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                    <?php foreach ($drivers as $driver): ?>
                        <?php
                        $name = trim((string) ($driver['fullname'] ?? ''));
                        $name = $name !== '' ? $name : 'ไม่ระบุชื่อ (#' . $driver['driver_id'] . ')';
                        $score = (float) $driver['avg_score'];
                        ?>
                        <li class="d-flex align-items-center justify-content-between gap-2">
                            <span class="text-truncate small" title="<?= Html::encode($name) ?>"><?= Html::encode($name) ?></span>
                            <span class="text-nowrap small">
                                <?= $stars($score) ?>
                                <span class="fw-semibold ms-1 vd-num"><?= number_format($score, 2) ?></span>
                                <span class="text-body-tertiary vd-num">(<?= number_format((int) $driver['total']) ?>)</span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($comments)): ?>
                <?php if (!empty($drivers)): ?>
                    <hr class="my-3 opacity-25">
                <?php endif; ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($comments as $comment): ?>
                        <div class="vd-quote">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                <span class="small"><?= $stars((float) $comment['score']) ?></span>
                                <span class="text-body-tertiary" style="font-size:.72rem">
                                    <?= Html::encode((string) ($comment['code'] ?? '')) ?>
                                    <?php if (!empty($comment['driver_name'])): ?>
                                        · <?= Html::encode((string) $comment['driver_name']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="small"><?= Html::encode((string) $comment['comment']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
