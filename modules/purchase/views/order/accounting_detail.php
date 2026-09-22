<?php

use yii\helpers\Html;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\services\PurchaseFinanceSnapshotBuilder;

$sourceType = PurchaseFinanceSnapshotBuilder::classify((string) $model->category_id, (string) $model->group_id);
$inbox = FinanceInbox::find()->where([
    'source_system' => PurchaseFinanceSnapshotBuilder::SOURCE_SYSTEM,
    'source_type' => $sourceType,
    'source_id' => (string) $model->id,
    'source_version' => 1,
])->orderBy(['id' => SORT_DESC])->one();
?>

<section class="card border shadow-sm mt-3" aria-labelledby="send-accounting-heading">
    <div class="card-header bg-body">
        <h5 class="mb-0" id="send-accounting-heading">ส่งข้อมูลเจ้าหนี้ให้การเงิน</h5>
    </div>
    <div class="card-body">
        <?php if ($inbox): ?>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-check-circle text-success" aria-hidden="true"></i>
                        <strong>ส่งเข้ากล่องรอรับของการเงินแล้ว</strong>
                    </div>
                    <p class="text-body-secondary mb-0">
                        ระบบเก็บสำเนาเอกสารไว้ให้การเงินตรวจสอบ และเปลี่ยนสถานะพัสดุเป็น "ส่งบัญชี" แล้ว
                    </p>
                </div>
                <?php if (Yii::$app->user->can('financeView')): ?>
                    <?= Html::a(
                        '<i class="bi bi-eye me-1" aria-hidden="true"></i>ดูรายการที่ส่ง',
                        ['/finance/inbox/view', 'id' => $inbox->id],
                        ['class' => 'btn btn-outline-primary']
                    ) ?>
                <?php endif; ?>
            </div>
        <?php elseif ((int) $model->status < 6): ?>
            <div class="alert alert-warning d-flex gap-2 align-items-start mb-0">
                <i class="bi bi-hourglass-split" aria-hidden="true"></i>
                <span>ต้องรับวัสดุเข้าคลังให้เรียบร้อย (สถานะ "วัสดุเข้าคลัง") ก่อนจึงจะส่งการเงินได้</span>
            </div>
        <?php else: ?>
            <p class="text-body-secondary">
                ระบบจะตรวจใบสั่งซื้อ ใบตรวจรับ ผู้แทนจำหน่าย และหลักฐานรับเข้าคลังหรือทะเบียนสินทรัพย์ก่อนส่ง
            </p>
            <div class="alert alert-info d-flex gap-2 align-items-start">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                <span>กดส่งแล้วระบบจะเก็บสำเนาเข้ากล่องรอรับของการเงิน และเปลี่ยนสถานะพัสดุเป็น "ส่งบัญชี" (ยังไม่ตั้งเจ้าหนี้/ไม่ลงบัญชี)</span>
            </div>
            <?php if (Yii::$app->user->can('accountingInboxReceive')): ?>
                <?= Html::a(
                    '<i class="bi bi-send-check me-1" aria-hidden="true"></i>ตรวจความพร้อมและส่งการเงิน',
                    ['/finance/inbox/receive-purchase', 'id' => $model->id],
                    ['class' => 'btn btn-primary', 'data-method' => 'post']
                ) ?>
            <?php else: ?>
                <div class="text-body-secondary d-flex gap-2 align-items-start">
                    <i class="bi bi-lock" aria-hidden="true"></i><span>คุณไม่มีสิทธิ์ส่งเอกสารเข้ากล่องรอรับของการเงิน</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
