<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

$this->title = 'แก้ไขการจองรถ: ' . $model->code;
?>

<table class="table border-0 table-striped-columns mt-3">
    <tbody>
        <tr>
            <td>หมายเลขการจอง </td>
            <td> <?= $model->code ?></td>
            <td>สถานะ : </td>
            <td><?= $model->vehicleStatus?->title ?? '-' ?></td>

        </tr>
        <tr>
            <td>วัตถุประสงค์ </td>
            <td> <?= $model->reason ?></td>
            <td> ลักษณะการไป: </td>
            <td><?= $model->viewGoType() ?></td>

        </tr>
        <tr>
            <td>สถานที่ไป : </td>
            <td><?= $model->locationOrg?->title ?? '-' ?></td>
             <td>ความเร่งด่วน : </td>
            <td><?= $model->viewUrgent() ?></td>
           
        </tr>
        <tr>
            <td>วันออกเดินทาง </td>
            <td><?php echo $model->showDateRange() ?></td>
            <td>เวลา : </td>
            <td><?= $model->viewTime()['full'] ?></td>
        </tr>
        <tr>
            <td>ผู้ร่วมเดินทาง : </td>
            <td colspan="3"><?= $model->reason ?></td>
        </tr>
        <tr>
            <td>หมายเหตุ : </td>
            <td colspan="3"><?= $model->data_json['coment'] ?? '-' ?></td>
        </tr>
    </tbody>
</table>


<?php $form = ActiveForm::begin(['id' => 'booking-form']); ?>

<div class="booking-details">
    <div class="d-flex justify-content-between">
        <?= $form->field($model, 'is_shared')->checkbox(['custom' => true, 'switch' => true, 'id' => 'is-shared'])->label('จัดสรรร่วม') ?>
    </div>
    <table class="table table-bordered" id="details-table">
        <thead class="table-light">
            <tr>
                <th>วันที่</th>
                <th>รถ</th>
                <th>พนักงานขับรถ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->vehicleDetails as $index => $detail): ?>
                <tr class="detail-row">
                    <td>
                        <?= $model->go_type == 1 ? $detail->showDate() : $model->showDateRange() ?>
                        <input type="hidden" name="vehicleDetails[<?= $index ?>][id]" value="<?= $detail->id ?>">
                    </td>
                    <td>
                        <?php
                        echo Html::dropDownList(
                            "vehicleDetails[{$index}][car]",  // เปลี่ยนชื่อ name
                            $detail->license_plate,
                            $model->ListCarItems(),
                            ['class' => 'form-select form-select-sm', 'prompt' => 'เลือกทะเบียนรถ']
                        )
                        ?>
                    </td>
                    <td>
                        <?php
                        echo Html::dropDownList(
                            "vehicleDetails[{$index}][driver]", // เปลี่ยนชื่อ name
                            $detail->driver_id,
                            $model->listDriver(),
                            ['class' => 'form-select form-select-sm', 'prompt' => 'เลือกพนักงานขับรถ']
                        )
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="form-group">
    <?php echo $form->field($model, 'data_json[remarks]')->textarea(['rows' => 2])->label('หมายเหตุ') ?>
</div>
<div class="d-flex justify-content-center gap-3">
    <?= Html::submitButton('<i class="bi bi-check-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i class="fa-regular fa-circle-xmark"></i> ปิด</button>
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