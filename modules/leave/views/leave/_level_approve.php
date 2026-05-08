<?php

use yii\web\View;
use yii\helpers\Html;
use app\components\UserHelper;

/**
 * สถานะการตรวจสอบ (ไทม์ไลน์ผู้อนุมัติ) — ใช้ข้อมูลจาก approveV2 ผ่าน $listApprove ที่ส่งเข้ามา
 * @var View $this
 * @var \app\modules\leave\models\Leave $model
 * @var \app\modules\approveV2\models\Approve[] $listApprove
 * @var string $name ชื่อฟอร์ม เช่น 'leave'
 */

$me = UserHelper::GetEmployee();
if (empty($listApprove)) {
    $listApprove = [];
}
$canChangeApprover = Yii::$app->user->can('leave');
$showChangeApprover = $canChangeApprover && in_array($model->status, ['Checking2_pass', 'Checkup_pass'], true);
$this->registerCssFile('@web/css/timeline.css');
$this->registerCss(<<<CSS
.leave-approval-panel {
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 18px;
    padding: 16px;
    box-shadow: 0 .35rem 1.25rem rgba(15, 23, 42, .04);
}

.leave-approval-panel__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}

.leave-approval-panel__timeline {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-left: 8px;
}

.leave-approval-panel__timeline::before {
    content: "";
    position: absolute;
    left: 22px;
    top: 8px;
    bottom: 8px;
    width: 2px;
    background: linear-gradient(to bottom, #dbe4f0, #eef2f7);
}

.leave-approval-step {
    position: relative;
    display: grid;
    grid-template-columns: 32px minmax(0, 1fr);
    gap: 12px;
    align-items: start;
}

.leave-approval-step__marker {
    width: 32px;
    display: flex;
    justify-content: center;
    z-index: 1;
}

.leave-approval-step__dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #dbe4f0;
    box-shadow: 0 0 0 .25rem transparent;
    transition: all .2s ease;
}

.leave-approval-step.is-active .leave-approval-step__dot {
    border-color: #0d6efd;
    box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .08);
}

.leave-approval-step__content {
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 14px;
    padding: 12px 14px;
}

.leave-approval-step.is-active .leave-approval-step__content {
    border-color: rgba(13, 110, 253, .35);
    background: rgba(13, 110, 253, .02);
}

.leave-approval-panel__footer {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #eef2f7;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.leave-approval-panel__hint {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
    font-size: .875rem;
    line-height: 1.4;
}

.leave-approval-panel__action {
    white-space: nowrap;
}

@media (max-width: 575.98px) {
    .leave-approval-panel__header,
    .leave-approval-panel__footer {
        flex-direction: column;
        align-items: stretch;
    }

    .leave-approval-panel__action {
        width: 100%;
        justify-content: center;
    }
}
CSS);
?>
<div class="leave-approval-panel">
    <div class="leave-approval-panel__header">
        <div>
            <h6 class="mb-1 fw-bold text-body d-flex align-items-center gap-2">
                <i class="bi bi-clock text-primary" aria-hidden="true"></i>
                สถานะการตรวจสอบ
            </h6>
            <div class="small text-muted">ตรวจสอบลำดับผู้อนุมัติและสถานะปัจจุบัน</div>
        </div>
        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle">
            <?= count($listApprove) ?> ขั้น
        </span>
    </div>

    <div class="leave-approval-panel__timeline">
        <?php if (empty($listApprove)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-clock-history d-block fs-3 mb-2 opacity-50"></i>
                <small>ไม่มีรายการสถานะการอนุมัติ</small>
            </div>
        <?php else: ?>
            <?php foreach ($listApprove as $item): ?>
                <?php
                $userIsChecker = Yii::$app->user->can($name);
                $userIsOwner = ($item->emp_id == $me->id);
                $isPending = $item->status === 'Pending';
                ?>
                <div class="leave-approval-step <?= $isPending ? 'is-active' : '' ?>">
                    <div class="leave-approval-step__marker">
                        <span class="leave-approval-step__dot" aria-hidden="true"></span>
                    </div>
                    <div class="leave-approval-step__content">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                            <div class="d-flex gap-3 align-items-start flex-grow-1">
                                <div class="flex-shrink-0">
                                    <?php if ($item->status == 'Pass'): ?>
                                        <?= $item->getAvatar($item->viewApproveDate())['avatar']; ?>
                                    <?php else: ?>
                                        <?php if (empty($item->emp_id)): ?>
                                            <div class="d-flex gap-3 align-items-start bg-white z-1">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 border border-2 bg-light border-light text-muted" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-file-text small"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-0 small fw-bold text-muted">จนท.ตรวจสอบ</p>
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">รอตรวจสอบ</small>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <?= $item->getAvatar($item->title)['avatar']; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap justify-content-end">
                                <?php if ($isPending && ($userIsOwner || (empty($item->emp_id) && $userIsChecker))): ?>
                                    <?= Html::a(
                                        '<i class="fa-solid fa-circle-check"></i> ' . ($item->data_json['label'] ?? ''),
                                        ['/leave/leave/approve-update', 'id' => $item->id],
                                        ['class' => 'btn btn-sm btn-primary rounded-pill shadow btn-approve', 'data' => ['id' => $item->id, 'status' => 'Pass', 'label' => ($item->data_json['label'] ?? '')]]
                                    ); ?>
                                    <?= Html::a(
                                        '<i class="fa-solid fa-circle-xmark"></i> ไม่' . ($item->data_json['label'] ?? ''),
                                        ['/leave/leave/approve-update', 'id' => $item->id],
                                        ['class' => 'btn btn-sm btn-outline-danger rounded-pill border-1 shadow btn-approve', 'data' => ['id' => $item->id, 'status' => 'Reject', 'label' => 'ไม่' . ($item->data_json['label'] ?? '')]]
                                    ); ?>
                                <?php else: ?>
                                    <?= $item->viewApproveStatus() ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($showChangeApprover): ?>
        <div class="leave-approval-panel__footer">
            <div class="leave-approval-panel__hint">
                <i class="bi bi-info-circle-fill text-warning flex-shrink-0"></i>
                <span>เปลี่ยนผู้อนุมัติ(ผู้ปฏิบัติหน้าที่แทน ผอ.)</span>
            </div>
            <?= Html::a(
                '<i class="bi bi-person-gear me-2"></i> เปลี่ยนผู้อนุมัติ',
                [
                    '/leave/approver/change-approver',
                    'id' => $model->id,
                    'title' => '<i class="bi bi-person-gear me-1"></i> เปลี่ยนผู้อนุมัติ'
                ],
                ['class' => 'btn btn-warning rounded-pill shadow-sm d-inline-flex align-items-center gap-2 leave-approval-panel__action open-modal', 'data' => ['size' => 'modal-lg'], 'data-pjax' => '0']
            ) ?>
        </div>
    <?php endif; ?>
</div>

<?php
$js = <<<JS
$("body").on("click", ".btn-approve", function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var topic = $(this).data('label');
    var status = $(this).data('status');
    var url = $(this).attr('href');
    Swal.fire({
        title: 'ยืนยัน?',
        text: topic + " ใช่หรือไม่!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: url,
                data: { id: id, status: status },
                dataType: "json",
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'กำลังบันทึกข้อมูล...',
                            allowOutsideClick: false,
                            timer: 1000,
                            didOpen: () => { Swal.showLoading(); }
                        }).then(() => {
                            Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', showConfirmButton: false, timer: 1000 }).then(() => { location.reload(); });
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: response.message || 'โปรดลองอีกครั้ง' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'โปรดลองอีกครั้ง' });
                }
            });
        }
    });
});
JS;
$this->registerJs($js, View::POS_END);
