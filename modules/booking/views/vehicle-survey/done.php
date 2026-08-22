<?php

use yii\helpers\Html;
use app\modules\booking\models\VehicleDetail;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleDetail $model */

$score = $model->satisfactionScore();
$comment = (string) ($model->satisfaction()['comment'] ?? '');
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-9 col-lg-7">
        <div class="card border-0 shadow rounded-4">
            <div class="card-body p-4 text-center">

                <div class="display-6 text-success mb-2"><i class="bi bi-check-circle-fill"></i></div>
                <h5 class="mb-1">ขอบคุณสำหรับการประเมิน</h5>
                <p class="text-muted small">ระบบได้บันทึกความคิดเห็นของท่านเรียบร้อยแล้ว</p>

                <div class="fs-3 text-warning my-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?= $i <= $score ? '-fill' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
                <div class="fw-medium mb-3"><?= Html::encode(VehicleDetail::ratingTitle($score)) ?></div>

                <?php if ($comment !== ''): ?>
                    <div class="alert alert-light text-start mb-3">
                        <div class="small text-muted mb-1"><i class="bi bi-chat-left-text me-1"></i>ข้อเสนอแนะของท่าน</div>
                        <?= nl2br(Html::encode($comment)) ?>
                    </div>
                <?php endif; ?>

                <div class="text-start">
                    <?= $this->render('_trip', ['model' => $model]) ?>
                </div>

            </div>
        </div>
    </div>
</div>
