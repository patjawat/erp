<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\widgets\ActiveForm;


/** @var yii\web\View $this */
/** @var app\modules\booking\models\Booking $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
        'id' => 'booking-form'
    ]); ?>

<div class="row">
    <div class="col-7">

    <?= $form->field($model, 'mileage_start')->textInput(['type' => 'number','step' => '0.01'])->label() ?>
    <?= $form->field($model, 'mileage_end')->textInput(['type' => 'number','step' => '0.01'])->label() ?>
    
    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'oil_liter')->textInput(['type' => 'number','step' => '0.01'])->label() ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'oil_price')->textInput(['type' => 'number','step' => '0.01'])->label() ?>
</div>
    </div>
        

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
    <div class="col-5">

        <div class="d-flex flex-column gap-1 mt-1">

            <div>
                <h6 class="mb-0"><i class="fa-solid fa-circle-info text-primary"></i> เลือกคนขับ</h6>
                <div id="showSelectDriver">
                    <?php if($model->showDriver()):?>

                    <a href="#" data-driver_id="<?php  echo $model->id?>"
                        data-driver_fullname="<?php echo $model->data_json['req_driver_fullname'];?>"
                        data-bs-toggle="offcanvas" data-bs-target="#offcanvasRightDriver"
                        aria-controls="offcanvasRightDriver">
                        <div class="card mb-2 border-2 border-primary">
                            <div class="card-body">
                                <div class="d-flex">
                                    <?php echo Html::img($model->driver->ShowAvatar(),['class' => 'avatar'])?>
                                    <div class="avatar-detail">
                                        <h6 class="mb-1 fs-15"><?php echo $model->driver->fullname?>
                                        </h6>
                                        <p class="text-muted mb-0 fs-13"><?php echo $model->driver->positionName()?></p>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </a>
                    <?php else:?>
                    <div class="card mb-3 border-2 border-primary">
                        <div class="card-body p-2 d-flex justify-content-center">
                            <a href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRightDriver"
                                aria-controls="offcanvasRightDriver"> <i
                                    class="bi bi-plus-circle fs-1 text-primary"></i></a>
                        </div>
                    </div>
                    <?php endif;?>
                </div>
            </div>

            <div>
                <h6 class="mb-0"><i class="fa-solid fa-circle-info text-primary"></i> เลือกรถยนต์</h6>
                <div id="selectCar">
                    <?php if(isset($model->car->id)):?>

                    <a href="#" data-license_plate="<?php  echo $model->license_plate?>" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                        <div class="card border-2 border-primary">
                            <div class="row g-0">
                                <div class="col-md-3">
                                    <?php  echo  Html::img($model->car->showImg(),['class' => 'img-fluid rounded'])?>
                                </div>
                                <div class="col-md-9">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php  echo $model->license_plate?></h5>
                                        <p class="card-text"><small class="text-muted">จำนวนที่นั่ง
                                                <?php echo $model->car->data_json['seat_size'] ?? '-'?></small></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php else:?>
                    <div class="card mb-3 border-2 border-primary">
                        <div class="card-body p-2 d-flex justify-content-center">
                            <a href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                                aria-controls="offcanvasRight"> <i class="bi bi-plus-circle fs-1 text-primary"></i></a>
                        </div>
                    </div>
                    <?php endif;?>
                </div>
            </div>

        </div>



       
        
    </div>
</div>



<table class="table border-0 table-striped-columns mt-3">
            <tbody>
                <tr>
                    <td class="text-dark fw-semibold">เรื่อง </td>
                    <td colspan="3"><?php echo $model->reason?></td>
                </tr>
                <tr>
                    <td class="text-dark fw-semibold">วันออกเดินทาง : </td>
                    <td>13 ก.ย. 2528 12:00</td>

                    <td class="text-dark fw-semibold">วันกลับ : </td>
                    <td>13 ก.ย. 2528 16:00</td>
                </tr>
                <tr>
                    <td class="text-dark fw-semibold">สถานที่ไป : </td>
                    <td><?php echo $model->location?></span></td>
                    <td class="text-dark fw-semibold">ผู้ร่วมเดินทาง : </td>
                    <td><?php echo $model->data_json['total_person_count'] ?? '-'?></td>
                </tr>
                <!-- <tr>
                    <td class="text-dark fw-semibold">รถที่ร้องขอ : </td>
                    <td colspan="3"><?php echo $model->data_json['req_license_plate'] ?? '-'?></td>
                </tr>
                <tr>
                    <td class="text-dark fw-semibold">พนักงานขับที่ร้องขอ : </td>
                    <td colspan="3">
                        <?php 
                                try {
                                    echo $model->reqDriver()->getAvatar(false);
                                } catch (\Throwable $th) {
                                    //throw $th;
                                }
                                ?>
                </tr> -->
                <tr>
                    <td class="text-dark fw-semibold">หนังสืออ้างอืง : </td>
                    <td colspan="4"></td>
                </tr>

            </tbody>
        </table>
        

    <div class="collapse" id="collapseExample">
        <?php echo $this->render('ready_car_list',['model' => $model])?>
    </div>

    <?= $form->field($model, 'car_type')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'license_plate')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput(['value' => 'driver_service'])->label(false) ?>
    <?= $form->field($model, 'data_json[req_license_plate]')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'data_json[req_driver_id]')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'data_json[req_driver_fullname]')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'driver_id')->hiddenInput(['maxlength' => true])->label(false) ?>



<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">ทะเบียนยานพาหนะ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvasDriver" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?PHP echo $this->render('list_cars',['model' => $model])?>
    </div>
</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRightDriver"
    aria-labelledby="offcanvasRightLabelDriver">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabeDriver">พนักงานขับ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?PHP echo $this->render('list_drivers',['model' => $model])?>
    </div>
</div>

<div class="form-group mt-3 d-flex justify-content-center gap-3">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
                class="fa-regular fa-circle-xmark"></i> ปิด</button>
    </div>
    <?php ActiveForm::end(); ?>




<?php
$js = <<<JS

      thaiDatepicker('#booking-date_start,#booking-date_end')

      \$('#booking-date_start').on('change', function() {
            var dateStart = \$('#booking-date_start').val();
            var dateEnd = \$('#booking-date_end').val();
            listCars(dateStart,dateEnd)
        });

        

      \$('#booking-form').on('beforeSubmit', function (e) {
        var form = \$(this);

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
            
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                boforeSubmit: function(){
                    beforLoadModal()
                },
                success: function (response) {
                    // form.yiiActiveForm('updateMessages', response, true);
                    if(response.status == 'success') {
                        closeModal()
                        location.reload(true)
                        // success()
                        // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });
        }
        });
        return false;
    });


    $("body").on("click", ".select-car", function (e) {
        e.preventDefault();
        let licensePlate = $(this).data("license_plate"); // ดึงค่าจาก data-license_plate
        $('#req_license_plate').val(licensePlate)
        $('#booking-license_plate').val(licensePlate)
        
        // $("#car-container .card").removeClass("border-2 border-primary");

    
        $(this).find(".card").addClass("border-2 border-primary");
        $(this).find(".checked").html('<i class="fa-solid fa-circle-check text-success fs-4"></i>')
        $("#offcanvasRight").offcanvas("hide"); // ปิด Offcanvas
        success('เลือกรถที่ต้องการใช้งานเรียบร้อยแล้ว')

        let cloned = $(this).clone(); // Clone ตัวเอง
        // ลบคลาส select-car
        cloned.removeClass("select-car hover-card");
        cloned.addClass("border-2 border-primary");

        // เพิ่ม attributes ที่ต้องการ
        cloned.attr({
            "data-bs-toggle": "offcanvas",
            "data-bs-target": "#offcanvasRight",
            "aria-controls": "offcanvasRight"
        });
        $("#selectCar").html(cloned); // ใส่ใน container

    });


    $("body").on("click", ".select-driver", function (e) {
        e.preventDefault();
        let driver_id = $(this).data("driver_id"); // ดึงค่าจาก data-license_plate
        let driver_fullname = $(this).data("driver_fullname"); // ดึงค่าจาก data-license_plate
        $('#booking-driver_id').val(driver_id)

        
        $("#offcanvasRightDriver").offcanvas("hide"); // ปิด Offcanvas
        success('เลือกรถที่ต้องการใช้งานเรียบร้อยแล้ว')

        
        // เพิ่ม class border-2 border-primary ให้กับ .card ที่อยู่ภายใน <a> ที่ถูกคลิก
        $(this).find(".card").addClass("border-2 border-primary");
        $(this).find(".checked").html('<i class="fa-solid fa-circle-check text-success fs-4"></i>')
        let cloned = $(this).clone(); // Clone ตัวเอง
        // ลบคลาส select-car
        cloned.removeClass("select-driver hover-card");
        cloned.addClass("border-2 border-primary");
        // เพิ่ม attributes ที่ต้องการ
        cloned.attr({
            "data-bs-toggle": "offcanvas",
            "data-bs-target": "#offcanvasRightDriver",
            "aria-controls": "offcanvasRight"
        });
        $("#showSelectDriver").html(cloned); // ใส่ใน container

    });
    
    
    JS;
$this->registerJS($js, View::POS_END);

?>