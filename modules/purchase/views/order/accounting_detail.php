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
        <h5 class="mb-0" id="send-accounting-heading">ส่งเอกสารให้ฝ่ายบัญชีตรวจสอบ</h5>
    </div>
    <div class="card-body">
        <?php if ($inbox): ?>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-check-circle text-success" aria-hidden="true"></i>
                        <strong>ส่งเข้ากล่องรับงานบัญชีแล้ว</strong>
                    </div>
                    <p class="text-body-secondary mb-0">
                        ระบบเก็บสำเนาเอกสารไว้ตรวจสอบ โดยสถานะในระบบพัสดุยังไม่เปลี่ยนแปลง
                    </p>
                </div>
                <?= Html::a(
                    '<i class="bi bi-eye me-1" aria-hidden="true"></i>ดูรายการที่ส่ง',
                    ['/accounting/inbox/view', 'id' => $inbox->id],
                    ['class' => 'btn btn-outline-primary']
                ) ?>
            </div>
        <?php else: ?>
            <p class="text-body-secondary">
                ระบบจะตรวจใบสั่งซื้อ ใบตรวจรับ ผู้แทนจำหน่าย และหลักฐานรับเข้าคลังหรือทะเบียนสินทรัพย์ก่อนส่ง
            </p>
            <div class="alert alert-info d-flex gap-2 align-items-start">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                <span>ขั้นตอนนี้ยังไม่ตั้งเจ้าหนี้ ไม่ลงบัญชี และไม่เปลี่ยนสถานะเอกสารพัสดุ</span>
            </div>
            <?= Html::a(
                '<i class="bi bi-send-check me-1" aria-hidden="true"></i>ตรวจความพร้อมและส่งบัญชี',
                ['/accounting/inbox/receive-purchase', 'id' => $model->id],
                [
                    'class' => 'btn btn-primary',
                    'data-method' => 'post',
                ]
            ) ?>
        <?php endif; ?>
    </div>
</section>
