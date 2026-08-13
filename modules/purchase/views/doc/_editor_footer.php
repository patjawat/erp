<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\purchase\models\Doc;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Doc $model */

/**
 * ปุ่มท้ายหน้าแก้ไขเอกสาร (ถูกฉีดลง .modal-footer)
 *
 * ไม่มี <script> ในไฟล์นี้ — ตัวเปิด modal ใช้ .html() วางส่วนนี้ ซึ่ง jQuery จะ
 * รัน script ที่เจอทันทีและอยู่นอกลำดับที่ erpInjectModalContent จัดไว้ให้
 * event ทั้งหมดของปุ่มเหล่านี้ผูกอยู่ใน erpDocEditorInit() ของ views/doc/editor.php
 */

$locked = $model->status === Doc::STATUS_FINAL;
?>

<div class="d-flex w-100 flex-wrap align-items-center gap-2">
    <div class="small text-muted me-auto">
        <?php if ($locked): ?>
            <i class="bi bi-lock text-warning me-1"></i>เอกสารออกเลขแล้ว — พิมพ์ซ้ำได้ แต่แก้เนื้อความไม่ได้
        <?php else: ?>
            <i class="bi bi-pencil text-primary me-1"></i>แก้ไขในเอกสารได้โดยตรง ระบบบันทึกร่างให้อัตโนมัติ
        <?php endif; ?>
    </div>

    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button>

    <?php if (!$locked): ?>
        <button type="button" class="btn btn-outline-secondary" id="doc-save">
            <i class="bi bi-save me-1"></i>บันทึกร่าง
        </button>
    <?php endif; ?>

    <button type="button" class="btn btn-primary" id="doc-print"
        data-url="<?= Url::to(['/purchase/doc/print', 'id' => $model->id]) ?>">
        <i class="bi bi-printer me-1"></i>พริ้นท์
    </button>

    <button type="button" class="btn btn-warning" id="doc-word"
        data-url="<?= Url::to(['/purchase/doc/word', 'id' => $model->id]) ?>">
        <i class="bi bi-file-earmark-word me-1"></i>ส่งออก Word
    </button>
</div>
