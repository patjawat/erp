<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$status = $model->status;
$statusMeta = [
    'DS1' => ['label' => 'ลงทะเบียนรับ', 'pill' => 'text-bg-secondary', 'tone' => 'secondary', 'icon' => 'fa-inbox'],
    'DS2' => ['label' => 'ส่งหน่วยงาน', 'pill' => 'text-bg-info', 'tone' => 'info', 'icon' => 'fa-share-from-square'],
    'DS3' => ['label' => 'รอผู้อำนวยการลงความเห็น', 'pill' => 'text-bg-warning', 'tone' => 'warning', 'icon' => 'fa-hourglass-half'],
    'DS4' => ['label' => 'ผู้อำนวยการลงความเห็นแล้ว', 'pill' => 'text-bg-success', 'tone' => 'success', 'icon' => 'fa-circle-check'],
    'DS5' => ['label' => 'อ่านแล้ว', 'pill' => 'text-bg-success', 'tone' => 'success', 'icon' => 'fa-eye'],
];
$meta = $statusMeta[$status] ?? ['label' => $status, 'pill' => 'text-bg-light', 'tone' => 'secondary', 'icon' => 'fa-file'];
?>

<?php Pjax::begin(['id' => 'approval-card', 'timeout' => false]); ?>
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="bg-primary bg-opacity-10 px-4 py-3 border-bottom border-primary-subtle">
        <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle" style="width:32px;height:32px;">
                <i class="fa-solid fa-user-pen"></i>
            </span>
            <div class="flex-grow-1">
                <div class="text-uppercase small text-primary fw-semibold opacity-75" style="letter-spacing:.05em;">การอนุมัติ</div>
                <div class="fw-bold text-dark">สถานะปัจจุบัน</div>
            </div>
            <span class="badge <?= $meta['pill'] ?> rounded-pill px-3 py-2">
                <i class="fa-solid <?= $meta['icon'] ?> me-1"></i><?= Html::encode($meta['label']) ?>
            </span>
        </div>
    </div>

    <div class="card-body p-4 bg-white">
        <?php $stack = $model->StackDocumentTags('req_approve'); ?>
        <?php if (!empty(trim(strip_tags($stack)))): ?>
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="text-muted small fw-semibold">ผู้เกี่ยวข้อง:</span>
                <?= $stack ?>
            </div>
        <?php endif; ?>

        <div class="d-grid">
            <?php if ($status === 'DS1'): ?>
                <?= Html::a(
                    '<i class="fa-solid fa-file-signature me-2"></i> เสนอผู้อำนวยการ',
                    ['/dms/documents/req-approve'],
                    [
                        'class' => 'btn btn-primary rounded-pill py-2 fw-semibold req-approve',
                        'data' => ['document_id' => $model->id, 'ref' => $model->ref, 'name' => 'req_approve'],
                    ]
                ) ?>
            <?php elseif ($status === 'DS3'): ?>
                <div class="text-center py-2">
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-3 py-2 fs-6">
                        <i class="fa-solid fa-hourglass-half me-1"></i> รอผู้อำนวยการลงความเห็น
                    </span>
                    <div class="small text-muted mt-2">
                        ผู้อำนวยการสามารถลงความเห็นผ่านช่องความเห็นด้านล่างได้ตามปกติ
                    </div>
                </div>
            <?php elseif ($status === 'DS4'): ?>
                <div class="text-center py-2">
                    <span class="badge text-bg-success rounded-pill px-3 py-2 fs-6">
                        <i class="fa-solid fa-circle-check me-1"></i> ลงความเห็นเรียบร้อยแล้ว
                    </span>
                </div>
            <?php else: ?>
                <div class="text-center text-muted small py-2">
                    <i class="fa-regular fa-circle-check me-1"></i> ไม่มีการดำเนินการที่ต้องทำ
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>

<?php
$js = <<< 'JS'
$('.req-approve').off('click').on('click', function (e) {
    e.preventDefault();
    var $btn = $(this);
    Swal.fire({
        title: 'ยืนยัน',
        html: '<i class="fa-solid fa-file-circle-check fs-1 text-primary"></i><div class="mt-2">นำเสนอผู้อำนวยการ</div>',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        cancelButtonText: '<i class="bi bi-x-circle"></i> ยกเลิก',
        confirmButtonText: '<i class="bi bi-check-circle"></i> ยืนยัน',
    }).then(function (result) {
        if (!result.isConfirmed) { return; }
        $.ajax({
            type: 'post',
            url: $btn.attr('href'),
            beforeSend: function () { if (typeof beforLoadModal === 'function') { beforLoadModal(); } },
            data: {
                name: $btn.data('name'),
                document_id: $btn.data('document_id'),
                ref: $btn.data('ref'),
            },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'error' && typeof warning === 'function') { warning(); }
                if (res.status === 'success') { window.location.reload(true); }
            }
        });
    });
});
JS;
$this->registerJs($js, View::POS_END);
?>
