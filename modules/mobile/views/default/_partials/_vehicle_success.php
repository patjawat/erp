<?php

use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var string $flashCode */

$flashCode = (string) ($flashCode ?? '');
?>

<section class="bv-mode bv-mode-success" data-mode-section="success">
    <div class="bv-success">
        <div class="bv-success-card">
            <span class="bv-success-icon" aria-hidden="true">
                <i data-lucide="check"></i>
            </span>
            <h2 class="bv-success-title">บันทึกคำขอเรียบร้อย</h2>
            <p class="bv-success-text">คำขอจองรถถูกส่งให้เจ้าหน้าที่ตรวจสอบแล้ว ระบบจะแจ้งผลผ่านการแจ้งเตือน</p>
            <?php if ($flashCode !== ''): ?>
                <span class="bv-success-code">
                    <i data-lucide="hash" aria-hidden="true"></i>
                    <?= Html::encode($flashCode) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="bv-success-fineprint">
            <strong>ต่อไปนี้คืออะไร</strong><br>
            เจ้าหน้าที่งานยานพาหนะจะตรวจสอบและจัดสรรรถให้ตามเวลาที่ระบุ
            คุณสามารถดูสถานะคำขอได้จากหน้ารายการ
        </div>
    </div>
</section>
