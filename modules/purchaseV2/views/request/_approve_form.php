<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\modules\purchaseV2\models\PurchaseRequestApproval;

/** @var PurchaseRequestApproval $model */
$request = $model->request;

?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-4">
            <div>
                <div class="text-muted small mb-1">คำขอ</div>
                <div class="fw-bold text-primary fs-5"><?= Html::encode($request?->getDisplayReference() ?? '-') ?></div>
                <div class="text-muted small"><?= Html::encode($request?->request_title ?? '-') ?></div>
            </div>
            <div class="text-sm-end">
                <?= $request?->statusBadge() ?>
                <div class="text-muted small mt-2">ขั้นตอน: <?= Html::encode($model->role_name ?: 'อนุมัติ') ?></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-4 bg-body-tertiary h-100">
                    <div class="text-muted small">ผู้อนุมัติ</div>
                    <div class="fw-semibold"><?= Html::encode($model->approver_name ?: '-') ?></div>
                    <div class="text-muted small"><?= Html::encode($model->approver_position ?: '-') ?></div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-4 bg-body-tertiary h-100">
                    <div class="text-muted small">สถานะขั้นตอน</div>
                    <div class="fw-semibold"><?= Html::encode($model->statusMeta()['label'] ?? 'รอดำเนินการ') ?></div>
                    <div class="text-muted small">บันทึกผลอนุมัติในหน้าต่างเดียว</div>
                </div>
            </div>
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'purchase-approve-form',
            'options' => ['class' => 'd-grid gap-3'],
        ]); ?>

        <?= Html::hiddenInput('decision', 'approve', ['id' => 'approval-decision']) ?>

        <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-4 mb-0">
            <div class="fw-semibold mb-1">แนวทางใช้งาน</div>
            <div class="small text-body-secondary">
                หากเลือก <span class="fw-semibold text-success">อนุมัติ</span> ระบบจะบันทึกผลทันที
                และหากเลือก <span class="fw-semibold text-danger">ไม่อนุมัติ</span> กรุณาระบุเหตุผลประกอบให้ชัดเจน
            </div>
        </div>

        <?= $form->field($model, 'comment')->textarea([
            'rows' => 4,
            'placeholder' => 'เพิ่มความคิดเห็นหรือเหตุผลประกอบการตัดสินใจ',
            'class' => 'form-control rounded-3',
        ])->label('ความคิดเห็น / เหตุผล') ?>

        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 pt-2">
            <?= Html::button('<i data-lucide="x-circle" class="me-1"></i> ไม่อนุมัติ', [
                'class' => 'btn btn-outline-danger rounded-3 fw-semibold order-2 order-sm-1',
                'type' => 'submit',
                'onclick' => "document.getElementById('approval-decision').value='reject';",
            ]) ?>
            <?= Html::button('<i data-lucide="badge-check" class="me-1"></i> อนุมัติ', [
                'class' => 'btn btn-success rounded-3 fw-semibold order-1 order-sm-2',
                'type' => 'submit',
                'onclick' => "document.getElementById('approval-decision').value='approve';",
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<<JS
(function () {
    var form = $('#purchase-approve-form');
    if (!form.length) return;

    form.on('beforeSubmit', function (e) {
        e.preventDefault();
        var currentForm = $(this);
        $.ajax({
            url: currentForm.attr('action'),
            type: 'post',
            data: currentForm.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    if (res.redirect) {
                        window.location.href = res.redirect;
                    }
                    return;
                }
                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: res.message || 'เกิดข้อผิดพลาด' });
                } else {
                    alert(res.message || 'เกิดข้อผิดพลาด');
                }
            },
            error: function () {
                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: 'เกิดข้อผิดพลาดระหว่างบันทึก' });
                } else {
                    alert('เกิดข้อผิดพลาดระหว่างบันทึก');
                }
            }
        });
        return false;
    });
})();
JS;
$this->registerJs($js);
?>
