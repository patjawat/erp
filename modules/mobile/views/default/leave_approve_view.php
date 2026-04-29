<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\leave\models\Leave $model */
/** @var app\modules\approveV2\models\Approve $approve */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle'] = 'อนุมัติใบลา';
$this->params['mobileSubtitle'] = 'ตรวจสอบรายละเอียดและบันทึกผลการพิจารณา';
?>

<div class="d-flex flex-column gap-3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <div class="small text-muted">ผู้ขอ</div>
                    <div class="fw-bold"><?= Html::encode($model->employee->fullname ?? '-') ?></div>
                    <div class="small text-muted"><?= Html::encode($model->employee->positionName() ?? '') ?></div>
                </div>
                <div><?= $model->viewStatus() ?></div>
            </div>

            <div class="row g-3 small">
                <div class="col-6">
                    <div class="text-muted">ประเภทการลา</div>
                    <div class="fw-semibold text-dark"><?= Html::encode($model->leaveType->title ?? '-') ?></div>
                </div>
                <div class="col-6">
                    <div class="text-muted">จำนวนวัน</div>
                    <div class="fw-semibold text-dark"><?= (float) $model->total_days ?> วัน</div>
                </div>
                <div class="col-12">
                    <div class="text-muted">ช่วงเวลาที่ลา</div>
                    <div class="fw-semibold text-dark"><?= $model->showLeaveDate() ?></div>
                </div>
                <div class="col-12">
                    <div class="text-muted">เหตุผล</div>
                    <div class="fw-semibold text-dark"><?= Html::encode($model->data_json['reason'] ?? '-') ?></div>
                </div>
                <div class="col-12">
                    <div class="text-muted">ขั้นตอนที่รออนุมัติ</div>
                    <div class="fw-semibold text-primary"><?= Html::encode($approve->data_json['label'] ?? $approve->title ?? '-') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="fw-semibold mb-3">ลำดับการอนุมัติ</div>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($model->listApprove() as $item): ?>
                    <div class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2">
                        <div>
                            <div class="fw-medium"><?= Html::encode($item->title ?: ($item->data_json['label'] ?? 'ผู้อนุมัติ')) ?></div>
                            <div class="small text-muted"><?= Html::encode($item->employee->fullname ?? 'รอมอบหมาย') ?></div>
                        </div>
                        <div><?= $item->viewApproveStatus() ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2">
        <button type="button" class="btn btn-primary btn-lg rounded-3 approve-action" data-status="Pass">
            อนุมัติ
        </button>
        <button type="button" class="btn btn-outline-danger btn-lg rounded-3 approve-action" data-status="Reject">
            ไม่อนุมัติ
        </button>
    </div>
</div>

<?php
$updateUrl = Url::to(['/mobile/default/approve-leave-update', 'id' => $approve->id]);
$redirectUrl = Url::to(['/mobile/default/index']);
$js = <<<JS
$('body').on('click', '.approve-action', function () {
    const status = $(this).data('status');
    const label = status === 'Pass' ? 'อนุมัติ' : 'ไม่อนุมัติ';

    Swal.fire({
        title: 'ยืนยันการทำรายการ',
        text: label + 'ใบลานี้ใช่หรือไม่',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: '{$updateUrl}',
            data: {status: status},
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกสำเร็จ',
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect || '{$redirectUrl}';
                    });
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: response.message || 'ไม่สามารถบันทึกข้อมูลได้'
                });
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับระบบได้'
                });
            }
        });
    });
});
JS;
$this->registerJs($js, View::POS_END);
?>
