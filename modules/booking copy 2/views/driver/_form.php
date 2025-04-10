<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\widgets\ActiveForm;


/** @var yii\web\View $this */
/** @var app\modules\booking\models\Booking $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
    .list-items > .card{
        border-style: dashed;   
    }
</style>
<?php $form = ActiveForm::begin([
        'id' => 'booking-form'
    ]); ?>

<div class="card">
    <div class="card-body">

<div class="row">
    <div class="col-12">


        <table class="table border-0 table-striped-columns mt-3">
            <tbody>
                <tr>
                    <td class="text-dark fw-semibold">เรื่อง </td>
                    <td colspan="3"><?php echo $model->reason?></td>
                </tr>
                <tr>
                    <td class="text-dark fw-semibold">วันออกเดินทาง : </td>
                    <td><?php echo Yii::$app->thaiFormatter->asDate($model->date_start, 'medium');?> เวลา
                        <?php echo $model->time_start?></td>

                    <td class="text-dark fw-semibold">วันกลับ : </td>
                    <td><?php echo Yii::$app->thaiFormatter->asDate($model->date_end, 'medium');?> เวลา
                        <?php echo $model->time_end?></td>
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
<?php Pjax::begin(['id' => 'detailContainer','timeout' => 50000 ]); ?>
<div class="table-responsive">
    <table class="table table-primary align-middle">
        <thead>
            <tr>
                <th scope="col" style="width:220px;">วันที่</th>
                <th scope="col">พนักงานขับ</th>
                <th scope="col">รถยนต์</th>
                <th  style="width:100px;">จัดสสร</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($model->listDriverDetails() as $item):?>
                <?php
                $itemDate = Yii::$app->thaiDate->toThaiDate($item->date_start, false, false);
                    ?>
            <tr class="">
                <td scope="row"><?php echo $itemDate;?> </td>
                <td></td>
                <td></td>
                <td>
                    <div class="d-flex gap-3">
                        <?php echo Html::a('<i class="fa-solid fa-pen-to-square fs-3"></i>',['/booking/driver-items/update','id' => $item->id,'title' => '<i class="fa-solid fa-calendar-day"></i> จัดสรรวันที่ '.$itemDate],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                        <?php echo Html::a('<i class="fa-solid fa-trash fs-3"></i>',['/booking/driver-items/delete','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                    </div>
                </td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>

<?php Pjax::end(); ?>



<div class="collapse" id="collapseExample">
    <?php // echo $this->render('ready_car_list',['model' => $model])?>
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
        <button type="button" class="btn-close" data-bs-dismiss="offcanvasDriver" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
       
    </div>
</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRightDriver"
    aria-labelledby="offcanvasRightLabelDriver">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabeDriver"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
    <div id="showListItem"></div>
        <?PHP //  echo $this->render('list_drivers',['model' => $model])?>
    </div>
</div>

<div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>
<?php ActiveForm::end(); ?>

        
</div>
</div>



<?php
$js = <<<JS
        
      thaiDatepicker('#booking-date_start,#booking-date_end')


    $("body").on("click", ".list-items", function (e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            dataType: "json",
            success: function (res) {
             console.log(res);
             $('#showListItem').html(res.content)
             $('#offcanvasRightLabeDriver').text($(this).data("title"))
             console.log($(this).data("title"));
             
            }
        });
    });


    $("body").on("click", ".select-item", function (e) {
        e.preventDefault();
        let id = $(this).data("id"); // ดึงค่าจาก data-booking_id
        let booking_id = $(this).data("booking_id"); // ดึงค่าจาก data-booking_id
        let license_plate = $(this).data("license_plate"); // ดึงค่าจาก data-booking_id
        let driver_id = $(this).data("driver_id"); // ดึงค่าจาก data-booking_id
        let cloned = $(this).clone(); // Clone ตัวเอง
        let cloneSrc = $(this).data("type")
        $.ajax({
            type: "post",
            url: $(this).attr('href'),
            dataType: "json",
            data:{
                id:id,
                booking_id:booking_id,
                license_plate:license_plate,
                driver_id:driver_id
            },
            success: function (res) {

                 cloned.attr({
                    "data-bs-toggle": "offcanvas",
                    "data-bs-target": "#offcanvasRight",
                    "aria-controls": "offcanvasRight",
                    "data-id":id,
                });
                $("#"+cloneSrc).html(cloned); // ใส่ใน container
             
            }
        });
    });
        
    $("body").on("click", ".show-driver", function (e) {
        id = $(this).data('id');
    });

    
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

    //โหลดรายการที่เลือก
    function loadDetailItems()
    {
        $.ajax({
            type: "get",
            url: "/booking/driver/list",
            data: "data",
            dataType: "dataType",
            success: function (response) {
                
            }
        });
    }

        
        // $('#req_license_plate').val(licensePlate)
        // $('#booking-license_plate').val(licensePlate)
        
        // // $("#car-container .card").removeClass("border-2 border-primary");

    
        // // $(this).find(".card").addClass("border-2 border-primary");
        // // $(this).find(".checked").html('<i class="fa-solid fa-circle-check text-success fs-4"></i>')
        // $("#offcanvasRight").offcanvas("hide"); // ปิด Offcanvas
        // success('เลือกรถที่ต้องการใช้งานเรียบร้อยแล้ว')

        // let cloned = $(this).clone(); // Clone ตัวเอง
        // // ลบคลาส select-car
        // cloned.removeClass("select-car hover-card");
        // cloned.addClass("border-2 border-primary show-car");

        // // เพิ่ม attributes ที่ต้องการ
        // cloned.attr({
        //     "data-bs-toggle": "offcanvas",
        //     "data-bs-target": "#offcanvasRight",
        //     "aria-controls": "offcanvasRight",
        //     "data-id":id,
        // });
        // $("#selectCar"+id).html(cloned); // ใส่ใน container

    // });


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
            "aria-controls": "offcanvasRight",
             "data-id":id,
        });
        $("#showSelectDriver"+id).html(cloned); // ใส่ใน container

    });
    
    
    JS;
$this->registerJS($js, View::POS_END);

?>