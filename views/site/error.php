<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Throwable */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\HttpException;

// กันหน้า error พังซ้ำเมื่อถูก render จากที่อื่นที่ไม่ได้ส่งตัวแปรมาครบ
$exception = $exception ?? null;
$message = $message ?? '';
$name = $name ?? 'เกิดข้อผิดพลาด';

$statusCode = $exception instanceof HttpException ? (int) $exception->statusCode : 500;
$isClientError = $statusCode >= 400 && $statusCode < 500;

$states = [
    400 => ['title' => 'คำขอไม่ถูกต้อง', 'defaultMessage' => 'ข้อมูลที่ส่งมาไม่ครบถ้วนหรือไม่ถูกต้อง กรุณาย้อนกลับและตรวจสอบอีกครั้ง', 'icon' => 'bi-exclamation-circle', 'tone' => 'warning'],
    401 => ['title' => 'กรุณาเข้าสู่ระบบ', 'defaultMessage' => 'เซสชันอาจหมดอายุ กรุณาเข้าสู่ระบบแล้วลองใหม่', 'icon' => 'bi-person-lock', 'tone' => 'warning'],
    403 => ['title' => 'ไม่มีสิทธิ์ดำเนินการ', 'defaultMessage' => 'บัญชีของคุณไม่มีสิทธิ์ใช้งานรายการนี้', 'icon' => 'bi-shield-lock', 'tone' => 'danger'],
    404 => ['title' => 'ไม่พบหน้าหรือข้อมูลที่ต้องการ', 'defaultMessage' => 'รายการนี้อาจถูกลบ ย้าย หรืออยู่นอกขอบเขตที่คุณเข้าถึงได้', 'icon' => 'bi-file-earmark-question', 'tone' => 'secondary'],
];

$state = $states[$statusCode] ?? [
    'title' => 'ระบบไม่สามารถทำรายการนี้ได้',
    'defaultMessage' => 'กรุณาลองใหม่อีกครั้ง หากยังพบปัญหาจึงค่อยติดต่อผู้ดูแลระบบ',
    'icon' => 'bi-exclamation-triangle',
    'tone' => 'danger',
];

// HttpException เป็นคำอธิบายที่ controller ตั้งใจส่งให้ผู้ใช้ ส่วน error ภายในจะไม่เผยรายละเอียดทางเทคนิค
$safeMessage = $isClientError && trim((string) $message) !== ''
    ? trim((string) $message)
    : $state['defaultMessage'];

$this->title = $state['title'];
?>

<main class="container py-4 py-md-5" aria-labelledby="error-title">
    <section class="card bg-body border shadow-sm mx-auto error-state-card">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-start gap-3 gap-md-4">
                <div class="flex-shrink-0 rounded-3 bg-<?= Html::encode($state['tone']) ?>-subtle text-<?= Html::encode($state['tone']) ?>-emphasis p-3" aria-hidden="true">
                    <i class="bi <?= Html::encode($state['icon']) ?> fs-2"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-body-secondary fw-semibold mb-2">ข้อผิดพลาด <?= Html::encode((string) $statusCode) ?></div>
                    <h1 id="error-title" class="h3 fw-semibold mb-3"><?= Html::encode($state['title']) ?></h1>
                    <p class="text-body-secondary mb-4"><?= Html::encode($safeMessage) ?></p>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button type="button" class="btn btn-primary" onclick="history.back()">
                            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>ย้อนกลับไปหน้าก่อน
                        </button>
                        <?= Html::a('<i class="bi bi-house me-2" aria-hidden="true"></i>กลับหน้าหลัก', Url::to(['/me']), ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
$this->registerCss('.error-state-card { max-width: 48rem; }');
?>
