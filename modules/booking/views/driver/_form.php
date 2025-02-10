<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Booking $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php DetailView::widget([
        'model' => $model,
        'attributes' => [
            'location',
            'reason',
            'date_start',
            'time_start',
            'date_end',
            'time_end',
            'driver_id',
            'leader_id',
            'status',
           
        ],
    ]) ?>


<table class="table border-0 table-striped-columns mt-3">
    <tbody>
        <tr>
            <td class="text-dark fw-semibold">เรื่อง </td>
            <td><?php echo $model->reason?></td>

            <td class="text-dark fw-semibold">สถานที่ไป : </td>
            <td><?php echo $model->location?></span></td>
        </tr>
        <tr>
            <td class="text-dark fw-semibold">วันออกเดินทาง : </td>
            <td>13 ก.ย. 2528 12:00</td>

            <td class="text-dark fw-semibold">วันกลับ : </td>
            <td>13 ก.ย. 2528 16:00</td>
        </tr>

        <tr>
            <td class="text-dark fw-semibold">ขอใช้รถทะเบียน : </td>
            <td><?php echo $model->data_json['req_license_plate'] ?? '-'?></td>

            <td class="text-dark fw-semibold">พนักงานขับที่ร้องขอ : </td>
            <td><?php 
            try {
                echo $model->reqDriver()->getAvatar(false);
            } catch (\Throwable $th) {
                //throw $th;
            }
            ?>
            </td>
        </tr>
        <tr>
            <td class="text-dark fw-semibold">ผู้ร่วมเดินทาง : </td>
            <td><?php echo $model->data_json['total_person_count'] ?? '-'?></td>

            <td class="text-dark fw-semibold">พนักงานขับที่ร้องขอ : </td>
            <td></td>
        </tr>
        <tr>
            <td class="text-dark fw-semibold">หนังสืออ้างอืง : </td>
            <td colspan="4"></td>
        </tr>

    </tbody>
</table>
<div class="booking-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'license_plate')->textInput() ?>
        </div>
        <div class="col-6">
            <button class="mt-3 btn btn-primary rounded-pill shadow" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                <i class="fa-solid fa-circle-plus"></i> เลือกรถเพื่อจัดสรร
            </button>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'driver_id')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-6">
            <?php echo $form->field($model, 'status')->widget(Select2::classname(), [
                    'data' => $model->listStatus(),
                    'options' => ['placeholder' => 'เลือกสถานะ ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('สถานะ');
                ?>
        </div>

    </div>


    <div class="collapse" id="collapseExample">
        <?php echo $this->render('ready_car_list',['model' => $model])?>
    </div>



    <div class="form-group mt-3 d-flex justify-content-center gap-3">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
                class="fa-regular fa-circle-xmark"></i> ปิด</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>