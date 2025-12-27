<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

$this->title = 'แก้ไขการจองรถ: ' . $model->code;
?>
<?php $form = ActiveForm::begin(['id' => 'booking-form']); ?>
<div class="p-4 border-bottom bg-light">
    <div class="d-flex align-items-center">
   <?= $model->userRequest()['avatar'] ?>
    </div>
</div>
<?= $model->data_json['note'] ?? '-' ?>

<div class="p-4">
    <div class="row g-3">
        <div class="col-sm-6">
            <label class="small text-muted d-block">หมายเลขการจอง</label>
            <span class="fw-medium"> <?= $model->code ?></span>
        </div>
        <div class="col-sm-6">
            <label class="small text-muted d-block">สถานะ</label>
            <?= $model->vehicleStatus?->title ?? '-' ?>
            <!-- <span class="badge bg-success-subtle text-success border border-success-subtle">จัดสรรแล้ว</span> -->
        </div>
        <div class="col-12">
            <hr class="my-1 opacity-5">
        </div>
        <div class="col-sm-6">
            <label class="small text-muted d-block">วัตถุประสงค์</label>
            <span class="fw-medium"><?= $model->reason ?></span>
        </div>
        <div class="col-sm-6">
            <label class="small text-muted d-block">สถานที่ไป</label>
            <span class="fw-medium"><?= $model->locationOrg?->title ?? '-' ?></span>
        </div>
        <div class="col-sm-6">
            <label class="small text-muted d-block">วัน/เวลา เดินทาง</label>
            <span class="fw-medium"><?php echo $model->showDateRange() ?> (<?= $model->viewTime()['full'] ?>)</span>
        </div>
        <div class="col-sm-6">
            <label class="small text-muted d-block">ความเร่งด่วน</label>
            <span class="text-primary fw-medium"><?= $model->viewUrgent() ?></span>
        </div>
    </div>

    <div class="mt-4 p-3 border rounded-3 bg-white shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold text-secondary">การจัดสรรรถและพนักงาน</h6>
            <div class="form-check form-switch">
                <?= $form->field($model, 'is_shared')->checkbox(['custom' => true, 'switch' => true, 'id' => 'is-shared'])->label('จัดสรรร่วม') ?>
            </div>
        </div>

        <?php foreach ($model->vehicleDetails as $index => $detail): ?>
            <div class="row g-2">
                <div class="col-md-2">
                    <?= $model->go_type == 1 ? $detail->showDate() : $model->showDateRange() ?>
                </div>
                <div class="col-md-5">
                    <input type="hidden" name="vehicleDetails[<?= $index ?>][id]" value="<?= $detail->id ?>">

                    <?php
                    echo Html::dropDownList(
                        "vehicleDetails[{$index}][car]",  // เปลี่ยนชื่อ name
                        $detail->license_plate,
                        $model->ListCarItems(),
                        ['class' => 'form-select', 'prompt' => 'เลือกทะเบียนรถ']
                    )
                    ?>
                </div>
                <div class="col-md-5">
                    <?php
                    echo Html::dropDownList(
                        "vehicleDetails[{$index}][driver]", // เปลี่ยนชื่อ name
                        $detail->driver_id,
                        $model->listDriver(),
                        ['class' => 'form-select', 'prompt' => 'เลือกพนักงานขับรถ']
                    )
                    ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (Yii::$app->user->can('driver')): ?>
            <div class="form-group mb-3">
                <?= $form->field($model, 'data_json[remarks]')
                    ->textarea(['rows' => 2])
                    ->label('หมายเหตุเพิ่มเติม...') ?>
            </div>
        <?php endif; ?>
    </div>



    <div class="d-flex justify-content-center gap-3 mt-3">
        <?php if (Yii::$app->user->can('driver')): ?>
            <?= Html::submitButton(
                '<i class="bi bi-check-circle"></i> บันทึก',
                ['class' => 'btn btn-primary rounded-pill shadow']
            ) ?>
        <?php endif; ?>

        <button type="button" class="btn btn-secondary rounded-pill shadow" data-bs-dismiss="modal">
            <i class="fa-regular fa-circle-xmark"></i> ปิด
        </button>
    </div>

    <?php ActiveForm::end(); ?>



    <?php
    $js = <<<JS

    handleFormSubmit('#booking-form', null, async function(response) {
        await location.reload();
    });

JS;
    $this->registerJs($js);
    ?>