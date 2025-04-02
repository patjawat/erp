<?php
use app\models\Car;
use yii\helpers\Html;
use app\models\Driver;

use yii\jui\DatePicker;
use app\models\Categorise;
use app\models\LocationOrg;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;

$this->title = 'แก้ไขการจองรถ: ' . $model->code;
?>

<div class="booking-update">
    <div class="mb-3">
        <label class="form-label fw-bold">เลขที่คำขอ: <?php echo $model->code?></label>
        <p><?php echo $model->userRequest()['fullname'];?>
            ขอใช้<?php echo $model->carType->title;?>ไป<?php echo $model->locationOrg?->title ?? '-'?> วันที่
            <?php echo $model->showDateRange()?></p>

    </div>
    <?php $form = ActiveForm::begin(['id' => 'booking-form']); ?>

    <div class="booking-details">
        <label class="form-label">จัดสรรรถ</label>
        <table class="table table-bordered" id="details-table">
            <thead class="table-light">
                <tr>
                    <th>วันที่</th>
                    <th>รถ</th>
                    <th>พนักงานขับรถ</th>
                    <!-- <th>การจัดการ</th> -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($model->bookingDetails as $index => $detail): ?>
                <tr class="detail-row">
                    <td>
                        <?=$detail->showDate()?>
                    </td>
                    <td>
                        <?php
                         echo Html::dropDownList(
                            'cars[]',
                            $detail->license_plate,
                            $model->ListCarItems(),
                            ['class' => 'form-select form-select-sm']
                        )
                         ?>
                    </td>
                    <td>
                        <?php
                         echo Html::dropDownList(
                            'drivers[]',
                            $detail->driver_id,
                            $model->listDriver(),
                            ['class' => 'form-select form-select-sm']
                        )
                         ?>
                    </td>
                    <!-- <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button>
                    </td> -->
                </tr>
                <?php endforeach; ?>

                <?php if (empty($model->bookingDetails)): ?>
                <tr class="detail-row">
                    <td>
                        <?= DatePicker::widget([
                            'name' => 'dates[]',
                            'language' => 'th',
                            'dateFormat' => 'yyyy-MM-dd',
                            'options' => ['class' => 'form-control'],
                        ]) ?>
                    </td>
                    <td>
                        <?php
                        echo Html::dropDownList(
                            'cars[]',
                            null,
                            $model->ListCarItems(),
                            ['class' => 'form-select form-select-sm']
                        )
                         ?>
                    </td>
                    <td>
                        <?php
                        echo Html::dropDownList(
                            'drivers[]',
                            null,
                            $model->listDriver(),
                            ['class' => 'form-select form-select-sm']
                        ) 
                        ?>
                    </td>
                    <!-- <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button>
                    </td> -->
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="form-group">
        <?php echo $form->field($model, 'data_json[remarks]')->textarea(['rows' => 2]) ?>
    </div>
</div>

<div class="d-flex justify-content-center gap-3">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <?= Html::submitButton('ยืนยันการอนุมัติ', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
// $js = <<<JS
// $('#booking-form').on('beforeSubmit', function (e) {
//         var form = \$(this);
//         Swal.fire({
//         title: "ยืนยัน?",
//         text: "บันทึกขอใช้ยานพาหนะ!",
//         icon: "warning",
//         showCancelButton: true,
//         confirmButtonColor: "#3085d6",
//         cancelButtonColor: "#d33",
//         cancelButtonText: "ยกเลิก!",
//         confirmButtonText: "ใช่, ยืนยัน!"
//         }).then((result) => {
//         if (result.isConfirmed) {
            
//             \$.ajax({
//                 url: form.attr('action'),
//                 type: 'post',
//                 data: form.serialize(),
//                 dataType: 'json',
//                 boforeSubmit: function(){
//                     beforLoadModal()
//                 },
//                 success: function (response) {
//                     console.log(response);
//                 }
//             });
//         }
//         });
//         return false;
//     });

// JS;
// $this->registerJs($js);
?>