<?php

/**
 * @var yii\web\View $this
 * @var string $name
 * @var string $message
 * @var \Throwable|null $exception
 */

use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

$is404 = $exception instanceof NotFoundHttpException
    || ($exception instanceof HttpException && (int) $exception->statusCode === 404);

$this->title = $is404 ? 'ไม่พบหน้า (404)' : $name;
?>

<div class="row justify-content-center py-4 py-md-5 g-0">
    <div class="col-11 col-sm-10 col-md-8 col-lg-6 col-xl-5">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body text-center py-5 px-3 px-md-4">
                <?php if ($is404): ?>
                    <div class="d-flex align-items-center justify-content-center gap-1 mb-3">
                        <span class="display-3 fw-bold text-primary">4</span>
                        <span class="display-3 fw-bold text-danger" aria-hidden="true">0</span>
                        <span class="display-3 fw-bold text-primary">4</span>
                    </div>
                    <h2 class="h5 fw-semibold mb-2">ไม่พบหน้าที่คุณต้องการ</h2>
                    <p class="text-muted small mb-4">
                        ลิงก์อาจหมดอายุ ถูกย้าย หรือพิมพ์ที่อยู่ไม่ถูกต้อง
                    </p>
                <?php else: ?>
                    <div class="d-inline-flex align-items-center justify-content-center p-4 mb-3 rounded-circle bg-danger bg-opacity-10 text-danger">
                        <span class="fw-bold fs-4 lh-1" aria-hidden="true">!</span>
                    </div>
                    <h2 class="h5 fw-semibold mb-2"><?= Html::encode($name) ?></h2>
                    <p class="text-muted small mb-4"><?= nl2br(Html::encode($message)) ?></p>
                <?php endif; ?>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center align-items-stretch align-items-sm-center">
                    <?= Html::a('กลับหน้าหลัก', ['/mobile/default/index'], [
                        'class' => 'btn btn-primary rounded-pill px-4',
                    ]) ?>
                    <?php if (Yii::$app->user->isGuest): ?>
                        <?= Html::a('เข้าสู่ระบบ', ['/mobile/auth/login'], [
                            'class' => 'btn btn-outline-secondary rounded-pill px-4',
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
