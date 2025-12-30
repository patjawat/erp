<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $form = ActiveForm::begin([
    'id' => 'form',
    // 'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    // 'validationUrl' => ['/approve/asset-move/approve-validator'],
])
?>


<div class="row">
    <!-- รายละเอียดครุภัณฑ์ -->
    <div class="col-md-7 border-end">
        <h6 class="fw-bold text-muted mb-3"><i class="bi bi-info-circle me-1"></i> ข้อมูลครุภัณฑ์</h6>
        <div class="asset-preview-box mb-4">
            <div class="row align-items-center">
                <div class="col-auto text-primary">
                    <?= Html::img($model->asset->showImg()['image'], ['class' => 'w-100 h-100 object-fit-cover', 'style' => 'max-width: 76px;']) ?>
                </div>
                <div class="col">
                    <h5 class="fw-bold mb-1"><?= $model->asset->asset_name ?? '-' ?></h5>
                    <p class="text-muted mb-0">หมายเลขครุภัณฑ์: <?= $model->asset->code ?? '' ?> | ยี่ห้อ: <?= $model->asset->data_json['brand'] ?? '' ?></p>
                    <p class="text-muted mb-0">สถานะปัจจุบัน: <?= $model->asset->viewstatus() ?></p>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-6">
                <label class="text-muted d-block">สถานที่ต้นทาง</label>
                <span class="fw-bold"><?= $model->asset->data_json['location'] ?? '-' ?></span>
            </div>
            <div class="col-6 text-primary">
                <label class="text-muted d-block">สถานที่ปลายทาง</label>
                <span class="fw-bold"><i class="bi bi-geo-alt-fill me-1"></i><?= $model->data_json['location'] ?? '-' ?></span>
            </div>
            <div class="col-6 text-muted">
                <label class="text-muted d-block">ผู้แจ้งเคลื่อนย้าย</label>
                <span class="fw-bold"><?= $model->createdBy->employees->fullname ?? '-' ?></span>
            </div>
            <div class="col-6">
                <label class="text-muted d-block">วันที่ต้องการย้าย</label>
                <span class="fw-bold"><?= Yii::$app->thaiDate->toThaiDate($model->date_start, false, false); ?></span>
            </div>
            <div class="col-12">
                <label class="text-muted d-block">เหตุผลการเคลื่อนย้าย</label>
                <div class="bg-light p-3 rounded mt-1">
                    <?= $model->getReasonLabel() ?>
                    <p class="mb-0"><?= $model->data_json['remask'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- สถานะและผู้อนุมัติ -->
    <div class="col-md-5">

        <div class="bg-light p-3 rounded border">
            <?= $form->field($model, 'data_json[leader_remask]')->textArea(["rows" => 3, "placeholder" => "ระบุเหตุผลในการอนุมัติ หรือเหตุผลที่ไม่อนุมัติ...", 'style' => 'height:173px;'])->label('ความเห็นผู้อนุมัติ') ?>
            <?= $form->field($model, 'data_json[leader_status]')->hiddenInput()->label(false) ?>

            <div class="d-grid gap-2">
                <button type="button" class="btn-action btn btn-outline-success btn-sm py-2 rounded-pill" data-status="Pass">
                    <i class="bi bi-check-circle me-1"></i> อนุมัติการเคลื่อนย้าย
                </button>

                <button type="button" class="btn-action btn btn-outline-danger btn-sm py-2 rounded-pill" data-status="Reject">
                    <i class="bi bi-x-circle me-1"></i> ไม่อนุมัติ / ให้แก้ไข
                </button>
            </div>
        </div>
    </div>
</div>


<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

        $('.btn-action').click(function (e) {
            var status = $(this).data('status');
            var label = (status === 'Pass') ? 'อนุมัติ' : 'ไม่อนุมัติ';
            var color = (status === 'Pass') ? '#28a745' : '#dc3545';

            Swal.fire({
                title: 'ยืนยันการทำรายการ?',
                text: "คุณต้องการเลือกสถานะ: " + label,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: color,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // ทำงานตามโค้ดด้านบน...
                    $('#assetdetail-data_json-leader_status').val(status);
                    $('#form').submit();
                      
                }
            });
        });

        handleFormSubmit('#form', null, async function(response) {
                            await location.reload();
                        });

    JS;
$this->registerJS($js, View::POS_END)
?>