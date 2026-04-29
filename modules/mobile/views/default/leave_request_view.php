<?php

use app\components\ThaiDateHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\leave\models\Leave $model */

$this->params['current_page'] = $current_page ?? 'profile';
$this->params['mobileTitle'] = 'รายละเอียดใบลา';
$this->params['mobileSubtitle'] = 'ติดตามสถานะและตรวจสอบรายละเอียดคำขอลา';

$attachments = $model->getAttachmentList();
$createdAtText = '-';
if (!empty($model->created_at)) {
    $createdAtText = ThaiDateHelper::formatThaiDate((string) $model->created_at, 'long')
        . ' ' . date('H:i', strtotime((string) $model->created_at)) . ' น.';
}
$contactPhone = $model->data_json['phone'] ?? $model->data_json['leave_contact_phone'] ?? '-';
$contactAddress = $model->data_json['address'] ?? '-';
$approvals = $model->listApprove();
?>

<div class="d-flex flex-column gap-3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 d-flex flex-column gap-3">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="small text-muted">สถานะล่าสุด</div>
                    <div class="fw-semibold text-dark"><?= Html::encode($model->leaveType->title ?? 'ใบลา') ?></div>
                </div>
                <div><?= $model->viewStatus() ?></div>
            </div>

            <div class="row g-3 small">
                <div class="col-6">
                    <div class="text-muted">เลขที่คำขอ</div>
                    <div class="fw-semibold text-dark"><?= Html::encode((string) $model->id) ?></div>
                </div>
                <div class="col-6">
                    <div class="text-muted">วันที่ส่งคำขอ</div>
                    <div class="fw-semibold text-dark"><?= Html::encode($createdAtText) ?></div>
                </div>
                <div class="col-6">
                    <div class="text-muted">ประเภทการลา</div>
                    <div class="fw-semibold text-dark"><?= Html::encode($model->leaveType->title ?? '-') ?></div>
                </div>
                <div class="col-6">
                    <div class="text-muted">จำนวนวันลา</div>
                    <div class="fw-semibold text-dark"><?= (float) $model->total_days ?> วัน</div>
                </div>
                <div class="col-12">
                    <div class="text-muted">ช่วงเวลาที่ลา</div>
                    <div class="fw-semibold text-dark"><?= $model->showLeaveDate() ?></div>
                </div>
                <div class="col-12">
                    <div class="text-muted">เหตุผลการลา</div>
                    <div class="fw-semibold text-dark"><?= Html::encode($model->data_json['reason'] ?? '-') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 d-flex flex-column gap-3">
            <div class="fw-semibold">ลำดับการอนุมัติ</div>
            <?php if (!empty($approvals)): ?>
                <?php foreach ($approvals as $item): ?>
                    <div class="border rounded-3 px-3 py-3">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="min-w-0">
                                <div class="fw-medium text-dark"><?= Html::encode($item->title ?: ($item->data_json['label'] ?? 'ผู้อนุมัติ')) ?></div>
                                <div class="small text-muted"><?= Html::encode($item->employee->fullname ?? 'รอมอบหมาย') ?></div>
                                <?php if ($item->viewApproveDate()): ?>
                                    <div class="small text-muted mt-1">ทำรายการเมื่อ <?= Html::encode($item->viewApproveDate()) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="text-end"><?= $item->viewApproveStatus() ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-3">ยังไม่มีข้อมูลลำดับการอนุมัติ</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 d-flex flex-column gap-3">
            <div class="fw-semibold">ข้อมูลการติดต่อระหว่างลา</div>
            <div class="row g-3 small">
                <div class="col-12">
                    <div class="text-muted">เบอร์โทรศัพท์</div>
                    <div class="fw-semibold text-dark"><?= Html::encode((string) $contactPhone) ?></div>
                </div>
                <div class="col-12">
                    <div class="text-muted">ที่อยู่</div>
                    <div class="fw-semibold text-dark"><?= Html::encode((string) $contactAddress) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 d-flex flex-column gap-3">
            <div class="fw-semibold">เอกสารแนบ</div>
            <?php if (!empty($attachments)): ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($attachments as $attachment): ?>
                        <a
                            href="<?= Html::encode(Url::to(['/leave/leave/show-file', 'id' => $attachment->id])) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="d-flex justify-content-between align-items-center text-decoration-none text-dark border rounded-3 px-3 py-3"
                        >
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">ไฟล์</span>
                                <span class="text-truncate"><?= Html::encode($attachment->file_name) ?></span>
                            </div>
                            <i class="bi bi-box-arrow-up-right text-muted"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-3">ไม่มีเอกสารแนบ</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-grid gap-2">
        <?= Html::a('กลับไปที่คำขอของฉัน', ['/mobile/default/my-requests', 'type' => 'leave'], ['class' => 'btn btn-primary btn-lg rounded-3']) ?>
        <?= Html::a('ยื่นใบลาใหม่', ['/mobile/default/leave-request'], ['class' => 'btn btn-outline-secondary btn-lg rounded-3']) ?>
    </div>
</div>
