<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

$this->title = 'แก้ไขการจองรถ: ' . $model->code;
?>


<!-- <table class="table border-0 table-striped-columns mt-3">
    <tbody>
        <tr>
            <td>ผู้ขอ </td>
            <td><?php echo $model->userRequest()['avatar']; ?></td>
            <td>เวลา : </td>
            <td><?= $model->viewTime()['full'] ?></td>
        </tr>
                <tr>
            <td>วันทาง </td>
            <td><?php echo $model->showDateRange() ?></td>
            <td>เวลา : </td>
            <td><?= $model->viewTime()['full'] ?></td>
        </tr>
        <tr>
            <td> ลักษณะการไป: </td>
            <td><?=$model->viewGoType()?></td>
            <td>สถานที่ไป : </td>
            <td><?=$model->locationOrg?->title ?? '-'?></td>
        </tr>

        <tr>
            <td>วัตถุประสงค์ : </td>
            <td><?=$model->reason?></td>
            <td>ความเร่งด่วน : </td>
            <td><?=$model->data_json['note'] ?? '-'?></td>
        </tr>
          <tr>
            <td>ผู้ร่วมเดินทาง : </td>
            <td><?=$model->reason?></td>

            <td>หมายเหตุ : </td>
            <td><?=$model->data_json['coment'] ?? '-'?></td>
        </tr>
    </tbody>
</table> -->


<?php $form = ActiveForm::begin(['id' => 'booking-form']); ?>
<div class="mb-3">
    <?php echo $model->userRequest()['avatar']; ?>

</div>

<div class="mb-3 p-3 rounded">
    <div class="d-flex justify-content-between align-items-center mb-2">

        <h6 class="mb-0">
            ขอใช้<?php echo $model->carType?->title; ?>ไป<?php echo $model->locationOrg?->title ?? '-' ?> </h6>
        <p class="text-muted mb-0 fs-13"></p>

    </div>
    <p>วันที่ <?php echo $model->showDateRange() ?> เวลา <?= $model->viewTime()['full'] ?></p>
</div>
<div class="booking-details">
    <div class="d-flex justify-content-between">
        <?= $form->field($model, 'is_shared')->checkbox(['custom' => true, 'switch' => true, 'id' => 'is-shared'])->label('จัดสรรร่วม') ?>
        <label class="form-label">ประเภทการไป (<code><?php echo $model->viewGoType() ?></code>)</label>
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