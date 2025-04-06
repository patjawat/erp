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

<?php $form = ActiveForm::begin(['id' => 'booking-form']); ?>
    <div class="mb-3">
        <label class="form-label fw-bold">เลขที่คำขอ: <?php echo $model->code?></label>
        <p><?php echo $model->userRequest()['fullname'];?>
            ขอใช้<?php echo $model->carType->title;?>ไป<?php echo $model->locationOrg?->title ?? '-'?> วันที่
            <?php echo $model->showDateRange()?></p>

    </div>

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
                        <input type="hidden" name="bookingDetails[<?= $index ?>][id]" value="<?= $detail->id ?>">
                    </td>
                    <td>
                        <?php
                         echo Html::dropDownList(
                            "bookingDetails[{$index}][car]",  // เปลี่ยนชื่อ name
                            $detail->license_plate,
                            $model->ListCarItems(),
                            ['class' => 'form-select form-select-sm']
                        )
                         ?>
                    </td>
                    <td>
                        <?php
                         echo Html::dropDownList(
                            "bookingDetails[{$index}][driver]", // เปลี่ยนชื่อ name
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

<div class="d-flex justify-content-center gap-3">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <?= Html::submitButton('ยืนยันการอนุมัติ', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$('#booking-form').on('beforeSubmit', function (e) {
    var form = $(this);
    
    Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกขอใช้ยานพาหนะ!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
    }).then((result) => {
        if (result.isConfirmed) {
            // แสดงข้อมูลในคอนโซลเพื่อดีบัก
            console.log('Form data:', form.serialize());
            
            $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: function (response) {
                    console.log('Response:', response);
                    if (response.status == 'success') {
                        Swal.fire({
                            title: "สำเร็จ!",
                            text: response.message || "บันทึกข้อมูลเรียบร้อยแล้ว",
                            icon: "success",
                            timer: 1000
                        }).then(() => {
                            window.location.reload();
                            if (response.redirect) {
                                // window.location.href = response.redirect;
                            } else {
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "เกิดข้อผิดพลาด!",
                            text: response.message || "ไม่สามารถบันทึกข้อมูลได้",
                            icon: "error"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                    Swal.fire({
                        title: "เกิดข้อผิดพลาด!",
                        text: "ไม่สามารถติดต่อกับเซิร์ฟเวอร์ได้",
                        icon: "error"
                    });
                }
            });
        }
    });
    
    return false;
});

JS;
$this->registerJs($js);
?>