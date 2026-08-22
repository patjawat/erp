<?php

use yii\helpers\Html;

/**
 * ภาพรวมความพึงพอใจการใช้รถ — คะแนนเฉลี่ย การกระจาย และอัตราการตอบกลับ
 * คะแนนรายพนักงานขับรถและข้อเสนอแนะอยู่ในการ์ด _dash_feedback.php
 *
 * @var yii\web\View $this
 * @var app\modules\booking\models\VehicleSearch $searchModel
 */

$summary = $searchModel->satisfactionSummary();
$avg = (float) $summary['avg'];
$total = (int) $summary['total'];
$success = (int) $summary['success'];

$stars = static function (float $score): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $icon = $score >= $i ? 'bi-star-fill' : ($score >= $i - 0.5 ? 'bi-star-half' : 'bi-star');
        $html .= '<i class="bi ' . $icon . '"></i>';
    }

    return '<span class="vd-stars">' . $html . '</span>';
};
?>

<section class="card border-0 shadow-sm vd-card" aria-labelledby="vd-rating-heading">
    <div class="card-header bg-primary-gradient text-white">
        <h4 id="vd-rating-heading" class="h6 text-white mb-0">
            <i class="bi bi-star me-1" aria-hidden="true"></i>ความพึงพอใจการใช้รถ
        </h4>
        <p class="small text-white-50 mb-0">ผู้ขอประเมินหลังเสร็จสิ้นภารกิจ</p>
    </div>

    <div class="card-body">
        <?php if ($total === 0): ?>
            <div class="text-center text-body-secondary py-4">
                <i class="bi bi-star fs-3 d-block mb-1" aria-hidden="true"></i>
                <div class="small">ยังไม่มีผลประเมินในปีงบประมาณนี้</div>
                <?php if ($success > 0): ?>
                    <div class="small mt-1">
                        เสร็จสิ้นภารกิจแล้ว <span class="vd-num"><?= number_format($success) ?></span> รายการ · รอผู้ขอประเมิน
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="text-center">
                    <div class="vd-rating__score text-warning-emphasis"><?= number_format($avg, 2) ?></div>
                    <div class="fs-5"><?= $stars($avg) ?></div>
                </div>
                <div class="small text-body-secondary">
                    จาก <span class="vd-num fw-semibold"><?= number_format($total) ?></span> การให้คะแนน<br>
                    ตอบกลับ <span class="vd-num fw-semibold"><?= (int) $summary['rate'] ?>%</span>
                    ของ <span class="vd-num"><?= number_format($success) ?></span> ภารกิจ
                </div>
            </div>

            <?php foreach ($summary['items'] as $item): ?>
                <div class="vd-rating__row">
                    <span class="text-body-secondary text-truncate" title="<?= Html::encode($item['title']) ?>">
                        <span class="vd-stars"><?= (int) $item['code'] ?><i class="bi bi-star-fill ms-1"></i></span>
                        <?= Html::encode($item['title']) ?>
                    </span>
                    <span class="vd-rank__track">
                        <span class="vd-rank__bar bg-warning d-block" style="width:<?= (int) $item['p'] ?>%"></span>
                    </span>
                    <span class="text-end text-body-secondary vd-num"><?= number_format((int) $item['count']) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
