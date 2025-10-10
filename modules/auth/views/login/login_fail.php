<?php
use yii\helpers\Html;

$this->title = 'เข้าสู่ระบบล้มเหลว';
?>

<div class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="text-center p-5 rounded shadow-sm bg-white" style="max-width: 420px;">
        <div class="mb-4">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
        </div>
        <h4 class="mb-3 text-danger">ไม่พบข้อมูลพนักงานในระบบ</h4>
        <p class="text-muted mb-4">
            กรุณาติดต่อผู้ดูแลระบบเพื่อขอสิทธิ์การเข้าใช้งาน
        </p>

        <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับไปหน้าเข้าสู่ระบบ', ['/auth/login'], [
            'class' => 'btn btn-outline-primary w-100',
        ]) ?>
    </div>
</div>
