<?php

use yii\helpers\Html;
use app\modules\finance\models\FinanceInbox;

$this->title = 'ภาพรวมบัญชี';
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-journal-check fs-4" aria-hidden="true"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo 'ติดตามเอกสารที่รอตรวจ ตั้งเจ้าหนี้ และอนุมัติเข้าทะเบียน';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/accounting/menu', ['active' => 'dashboard']);
$this->endBlock();
?>

<div class="alert alert-info d-flex gap-2 align-items-start">
    <i class="bi bi-shield-check" aria-hidden="true"></i>
    <span>ระบบบัญชียังทำงานแบบคู่ขนาน ข้อมูลที่อนุมัติยังไม่ลงบัญชีแยกประเภท ไม่สร้างฎีกา และไม่สั่งจ่ายเงิน</span>
</div>

<section class="card border shadow-sm" aria-labelledby="accounting-work-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="accounting-work-heading">งานบัญชีที่ต้องดำเนินการ</h5></div>
    <div class="list-group list-group-flush">
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 py-3" href="<?= \yii\helpers\Url::to(['/accounting/inbox', 'status' => FinanceInbox::STATUS_PENDING_REVIEW]) ?>">
            <span><strong>ตรวจเอกสารต้นทาง</strong><span class="d-block text-body-secondary small">รายการจากพัสดุที่รอฝ่ายบัญชีตรวจรับ</span></span>
            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill"><?= number_format($inboxPending) ?></span>
        </a>
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 py-3" href="<?= \yii\helpers\Url::to(['/accounting/payable']) ?>">
            <span><strong>จัดทำหรือแก้ไขร่างเจ้าหนี้</strong><span class="d-block text-body-secondary small">ตรวจผู้ขาย ใบแจ้งหนี้ ภาษี และวันครบกำหนด</span></span>
            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill"><?= number_format($payableDraft) ?></span>
        </a>
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 py-3" href="<?= \yii\helpers\Url::to(['/accounting/payable']) ?>">
            <span><strong>ตรวจอนุมัติทะเบียนเจ้าหนี้</strong><span class="d-block text-body-secondary small">รายการที่ผู้จัดทำส่งมาและรอผู้ตรวจตัดสินใจ</span></span>
            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill"><?= number_format($payablePending) ?></span>
        </a>
        <div class="list-group-item d-flex justify-content-between align-items-center gap-3 py-3">
            <span><strong>อนุมัติเข้าทะเบียนแล้ว</strong><span class="d-block text-body-secondary small">พร้อมส่งต่อให้ขั้นตอนจัดทำรายการบัญชีในระยะถัดไป</span></span>
            <span class="badge bg-success-subtle text-success-emphasis rounded-pill"><?= number_format($payableApproved) ?></span>
        </div>
    </div>
</section>
