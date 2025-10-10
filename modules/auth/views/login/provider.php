<?php
use yii\helpers\Html;

$this->title = 'อยู่ในช่วงปรับปรุงระบบ';
?>

<div class="d-flex flex-column justify-content-center align-items-center vh-100 bg-light text-center">
    <div class="p-4 rounded shadow-sm bg-white" style="max-width: 480px;">
        <div class="mb-4">
            <i class="bi bi-tools text-primary" style="font-size: 3rem;"></i>
        </div>
        <h3 class="fw-bold text-dark mb-3">ระบบกำลังอยู่ในช่วงพัฒนาปรับปรุง</h3>
        <p class="text-muted mb-4">
            ขณะนี้ทีมพัฒนา<span class="fw-semibold">กำลังปรับปรุงและอัปเดตระบบ</span>  
            เพื่อให้การใช้งานดียิ่งขึ้น กรุณากลับมาใหม่อีกครั้งในภายหลัง
        </p>

        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>

        <?= Html::a('<i class="bi bi-arrow-clockwise me-1"></i> กลับหน้าหลัก', ['/auth/login'], [
            'class' => 'btn btn-outline-primary w-100',
        ]) ?>

        <p class="mt-4 small text-muted">
            © <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?> | ทีมพัฒนา IT
        </p>
    </div>
</div>
