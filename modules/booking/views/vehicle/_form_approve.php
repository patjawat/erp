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
<?php
// echo "<pre>";
// print_r($model->vehicleDetails);
// echo "</pre>";

?>

<?php $form = ActiveForm::begin(['id' => 'booking-form']); ?>
    <div class="mb-3">
        <?php echo $model->userRequest()['avatar'];?>
        <p class="mt-3">
            ขอใช้<?php echo $model->carType?->title;?>ไป<?php echo $model->locationOrg?->title ?? '-'?> วันที่
            <?php echo $model->showDateRange()?> </p>
    </div>

    <div class="booking-details">
        <div class="d-flex justify-content-between">
            <?= $form->field($model, 'is_shared')->checkbox(['custom' => true, 'switch' => true])->label('จัดสรรร่วม') ?>
            <label class="form-label">ประเภทการไป  (<code><?php echo $model->viewGoType()?></code>)</label>
        </div>
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
                <?php foreach ($model->vehicleDetails as $index => $detail): ?>
                <tr class="detail-row">
                    <td>
                        
                        <?=$model->go_type == 1 ? $detail->showDate() : $model->showDateRange()?>
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
                    <!-- <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button>
                    </td> -->
                </tr>
                <?php endforeach; ?>

               
            </tbody>
        </table>
    </div>
    <div class="form-group">
        <?php echo $form->field($model, 'data_json[remarks]')->textarea(['rows' => 2]) ?>
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