<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;
use app\modules\dms\models\DocumentsDetail;

$me = UserHelper::GetEmployee();
$documents = DocumentsDetail::find()->where(['name' => 'comment', 'to_id' => $me->id])->all();
$list = ArrayHelper::map($documents, 'id', function ($model) {
    return $model->document->topic;
});



$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
        // console.log(repo);
        var markup =
    '<div class="row">' +
        '<div class="col-12">' +
            '<span>' + repo.avatar + '</span>' +
        '</div>' +
    '</div>';
        if (repo.description) {
          markup += '<p>' + repo.avatar + '</p>';
        }
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) {
        return repo.avatar || repo.avatar;
    }
    JS;

// Register the formatting script
$this->registerJs($formatJs, View::POS_HEAD);

// script to parse the results into the format expected by Select2
$resultsJs = <<< JS
    function (data, params) {
        params.page = params.page || 1;
        return {
            results: data.results,
            pagination: {
                more: (params.page * 30) < data.total_count
            }
        };
    }
    JS;
    
/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
.select2-container--krajee-bs5 .select2-selection--single .select2-selection__placeholder {
    font-weight: 300;
    font-size: medium;
}

.input-lg.select2-container--krajee-bs5 .select2-selection--single,
:not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    padding: .2rem 0.6rem !important;
}


.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #d4e1f2;
    color: #111111;
}
</style>
<?php $form = ActiveForm::begin([
            'id' => 'booking-form',
            'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
            'validationUrl' => ['/me/booking-car/validator']
        ]); ?>



<?php
$start = new DateTime('2024-01-01');
$end = new DateTime('2024-01-03');
$end->modify('+1 day'); // เพิ่ม 1 วัน เพื่อให้รวมวันที่สิ้นสุด

$interval = new DateInterval('P1D'); // ระยะห่าง 1 วัน
$period = new DatePeriod($start, $interval, $end);

$dates = [];
foreach ($period as $date) {
    $dates[] = $date->format('Y-m-d');
}

print_r($dates);
?>


<div class="row">
    <div class="col-7">

        <div class="row">
            <div class="col-8">
                <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่ต้องการเดินทาง'])->label('ต้องการใช้รถตั้งแต่วันที่') ?>
                <?= $form->field($model, 'date_end')->textInput(['placeholder' => 'เลือกวันที่เดินทางกลับ'])->label('ถึงวันที่') ?>

            </div>
            <div class="col-4">
                <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', ['mask' => '99:99']) ?>
                <?= $form->field($model, 'time_end')->widget('yii\widgets\MaskedInput', ['mask' => '99:99']) ?>
            </div>

        </div>
        <?= $form->field($model, 'reason')->textInput(['rows' => 3])->label('เหตุผล') ?>
       

        <div class="row">
            <div class="col-6">
                <?= $form->field($model, 'location')->widget(Select2::classname(), [
                        'data' => $model->ListOrg(),
                        'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                        'pluginOptions' => [
                            'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                        ],
                        'pluginEvents' => [
                            'select2:select' => 'function(result) { 
                                            }',
                            'select2:unselecting' => 'function() {

                                            }',
                        ],
                
                    ]) ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'urgent')->widget(Select2::classname(), [
                        'data' => $model->ListUrgent(),
                        'options' => ['placeholder' => 'เลือกระดับความแร้งด่วน'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            // 'width' => '370px',
                        ],
                        'pluginEvents' => [
                            'select2:select' => 'function(result) { 
                                            }',
                            'select2:unselecting' => 'function() {

                                            }',
                        ]
                    ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <?= $form->field($model, 'data_json[total_person_count]')->textInput(['type' => 'number'])->label('จำนวนผู้ร่วมเดินทาง') ?>
            </div>
            <div class="col-8">
                <div class="mb-3 highlight-addon field-booking-data_json-req_license_plate">
                    <label class="form-label has-star"
                        for="booking-data_json-req_license_plate">หมายเลขทะเบียนรถที่เลือก</label>
                    <input type="text" id="req_license_plate" class="form-control" disabled="true"
                        value="<?php echo $model->license_plate?>">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <?php if($model->car_type == 'ambulance'):?>
            <div class="row">
                <div class="col-6">
                    <?= $form->field($model, 'data_json[patient_fullname]')->textInput(['placeholder' => 'ระบุบชื่อคนไข้...'])->label('ชื่อคนไข้') ?>
                    <?= $form->field($model, 'data_json[patient_age]')->textInput(['placeholder' => 'ระบุบอายุ...'])->label('อายุ') ?>
                    <?= $form->field($model, 'data_json[patient_nationality]')->textInput(['placeholder' => 'ระบุบเชื้อชาติ...'])->label('เชื้อชาติ') ?>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'data_json[patient_hn]')->textInput(['placeholder' => 'ระบุบ HN'])->label('HN') ?>
                    <?= $form->field($model, 'data_json[patient_cid]')->textInput(['placeholder' => 'ระบุบเลขบัตรประชาชน'])->label('CID') ?>
                    <?= $form->field($model, 'data_json[patient_citizenship]')->textInput(['placeholder' => 'ระบุบสัญชาติ...'])->label('สัญชาติ') ?>
                </div>
            </div>
    <?= $form->field($model, 'data_json[patient_symptom]')->textInput(['placeholder' => 'ระบุบป่วยด้วยโรค...'])->label('ป่วยด้วยโรค') ?>
    <?php endif;?>



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

        <?php
                        try {
                            //code...
                            if($model->isNewRecord){
                                $initEmployee =  Employees::find()->where(['id' => $model->leader_id])->one()->getAvatar(false);    
                            }else{
                                $initEmployee =  Employees::find()->where(['id' => $model->leader_id])->one()->getAvatar(false);    
                            }
                            // $initEmployee =  Employees::find()->where(['id' => $model->Approve()['leader']['id']])->one()->getAvatar(false);
                        } catch (\Throwable $th) {
                            $initEmployee = '';
                        }

                        echo $form->field($model, 'leader_id')->widget(Select2::classname(), [
                            'initValueText' => $initEmployee,
                            // 'initValueText' => $model->Approve()['leader']['avatar'],
                            'options' => ['placeholder' => 'เลือกรายการ...'],
                            'size' => Select2::LARGE,
                            'pluginEvents' => [
                                'select2:unselect' => 'function() {
                                    $("#order-data_json-board_fullname").val("")
                                    }',
                                'select2:select' => 'function() {
                                            var fullname = $(this).select2("data")[0].fullname;
                                            // var position_name = $(this).select2("data")[0].position_name;
                                            // $("#order-data_json-board_fullname").val(fullname)
                                            // $("#order-data_json-position_name").val(position_name)
                                        
                                    }',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'dropdownParent' => '#main-modal',
                                'minimumInputLength' => 1,
                                'ajax' => [
                                    'url' => Url::to(['/depdrop/employee-by-id']),
                                    'dataType' => 'json',
                                    'delay' => 250,
                                    'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                                    'processResults' => new JsExpression($resultsJs),
                                    'cache' => true,
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                                'templateResult' => new JsExpression('formatRepo'),
                            ],
                        ])->label('หัวหน้างาน')
                        ?>


    </div>
</div>

<?php if($model->car_type == 'general'):?>
<?php
                    echo $form->field($model, 'document_id')->widget(Select2::classname(), [
                        'data' => $list,
                        'options' => ['placeholder' => 'เลือกหนังสืออ้างอิง ...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                        ],
                    ])->label('หนังสืออ้างอิง');
                    ?>
<?php endif;?>

<?= $form->field($model, 'data_json[note]')->textArea(['rows' => 6])->label('หมายเหตุเพิ่มเติม...') ?>

<div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>


<?= $form->field($model, 'car_type')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'license_plate')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput(['value' => 'driver_service'])->label(false) ?>
<?= $form->field($model, 'data_json[req_license_plate]')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'data_json[req_driver_id]')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'data_json[req_driver_fullname]')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'driver_id')->hiddenInput(['maxlength' => true])->label(false) ?>


<?php ActiveForm::end(); ?>



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
        $('#booking-data_json-req_license_plate').val(licensePlate)
        
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
        $('#booking-data_json-req_driver_id').val(driver_id)
        $('#booking-data_json-req_driver_fullname').val(driver_fullname)
        
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