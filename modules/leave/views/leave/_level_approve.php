<?php

use yii\web\View;
use yii\helpers\Html;
use app\components\UserHelper;

/**
 * สถานะการตรวจสอบ (ไทม์ไลน์ผู้อนุมัติ) — ใช้ข้อมูลจาก approveV3 ผ่าน $listApprove ที่ส่งเข้ามา
 * @var View $this
 * @var \app\modules\leave\models\Leave $model
 * @var \app\modules\approveV3\models\Approve[] $listApprove
 * @var string $name ชื่อฟอร์ม เช่น 'leave'
 */

$me = UserHelper::GetEmployee();
if (empty($listApprove)) {
    $listApprove = [];
}
$this->registerCssFile('@web/css/timeline.css');
?>
<h6 class="mb-4 d-flex align-items-center gap-2">
    <i class="bi bi-clock text-primary" aria-hidden="true"></i> สถานะการตรวจสอบ
</h6>
<div class="position-relative ps-2">
    <div class="position-absolute top-0 bottom-0 start-0 border-start border-2 border-light ms-4" style="z-index: 0;"></div>
    <div class="d-flex flex-column gap-4 position-relative">
        <?php foreach ($listApprove as $item): ?>
            <div class="d-flex gap-3 align-items-center bg-white z-1 p-2 <?= $item->status == 'Pending' ? 'border border-1 rounded-2 border-primary' : '' ?>">
                <div class="d-flex align-items-center flex-grow-1">
                    <div>
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
                <div class="d-flex gap-2">
                    <?php
                    $userIsChecker = Yii::$app->user->can($name);
                    $userIsOwner = ($item->emp_id == $me->id);
                    ?>
                    <?php if ($item->status == 'Pending' && ($userIsOwner || (empty($item->emp_id) && $userIsChecker))): ?>
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
        <?php endforeach; ?>
    </div>
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
