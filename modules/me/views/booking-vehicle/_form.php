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

$formatJs = <<<'JS'
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
$resultsJs = <<<JS
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



<?php $form = ActiveForm::begin(['id' => 'vehicle-form']); ?>


<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่ต้องการเดินทาง'])->label('วันออกเดินทาง') ?>
        <?= $form->field($model, 'date_end')->textInput(['placeholder' => 'เลือกวันที่เดินทางกลับ'])->label('ถึงวันที่') ?>

    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลา') ?>
        <?= $form->field($model, 'time_end')->textInput(['type' => 'time'])->label('เวลากลับ') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'car_type_id')->widget(Select2::classname(), [
            'data' => [
            'official' => 'รถยนต์ราชการ',
            'personal' => 'รถยนต์ส่วนตัว',
            'ambulance' => 'รถพยาบาล',
            ],
            'options' => ['placeholder' => 'เลือกประเภทรถ'],
            'pluginOptions' => [
            'tags' => true,  // เปิดให้เพิ่มค่าใหม่ได้
            'allowClear' => true,
            'dropdownParent' => '#main-modal',
            ],
            
            'pluginEvents' => [
                'select2:select' => new JsExpression('function(result) { 
                    if ($(this).val() === "personal") {
                        $("#vehicle-driver_id").prop("disabled", true);
                        $("#vehicle-driver_id").val("").trigger("change");
                        $(".btn-driver, .list-car").hide();
                    } else {
                        $(".btn-driver, .list-car").show();
                        $("#vehicle-driver_id").val("").trigger("change");
                        $("#vehicle-driver_id").prop("disabled", false);
                    }
        
                    if ($(this).val() === "ambulance") {
                        $(".field-vehicle-refer_type").show();
                         $(".field-vehicle-go_type").hide();
                        $("input[name=\"Vehicle[go_type]\"][value=\'1\']").prop("checked", true);
                    } else {
                        $(".field-vehicle-refer_type").hide();
                        $(".field-vehicle-go_type").show();
                    }
                }'),
                'select2:unselecting' => new JsExpression('function() {
                    // Add any additional logic for unselecting if needed
                }'),
            ],
        ])->label('ประเภทรถที่ต้องการใช้') ?>
        <?= $form->field($model, 'go_type')->radioList([1 => 'ไปกลับ', 2 => 'ค้างคืน'], ['custom' => true, 'inline' => true])->label('ลักษณะการใช้') ?>
        

        <?= $form->field($model, 'refer_type')->widget(Select2::classname(), [
            'data' => $model->listReferType(),
            'options' => ['placeholder' => 'เลือกประเภทการส่งผู้ป่วย'],
            'pluginOptions' => [
                'tags' => true,  // เปิดให้เพิ่มค่าใหม่ได้
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                                            }',
                'select2:unselecting' => 'function() {

                                            }',
            ],
        ])->label('ประเภท REFER'); ?>

    </div>

    <div class="col-12">
        <?= $form->field($model, 'location')->widget(Select2::classname(), [
            'data' => $model->ListOrg(),
            'options' => ['placeholder' => 'เลือกหน่วยงาน'],
            'pluginOptions' => [
                'tags' => true,  // เปิดให้เพิ่มค่าใหม่ได้
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
    <div class="col-9">
        <?= $form->field($model, 'reason')->textInput(['rows' => 3])->label('วัตถุประสงค์') ?>
    </div>
    <div class="col-3">
        <?= $form->field($model, 'urgent')->widget(Select2::classname(), [
            'data' => $model->ListUrgent(),
            'options' => ['placeholder' => 'เลือกระดับความแร่งด่วน'],
            'pluginOptions' => [
                'allowClear' => true,
                // 'width' => '370px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                                            }',
                'select2:unselecting' => 'function() {}',
            ]
        ]) ?>

    </div>
</div>

<div id="officialCarOptions">
    <div class="row mb-3">
        <div class="col-md-6">
            <?php
            echo $form->field($model, 'driver_id', [
                'addon' => [
                    'append' => [
                        'content' => Html::button('<i class="bi bi-ui-checks"></i>', [
                            'class' => 'btn btn-primary btn-driver',
                            'title' => 'Mark on map',
                            'data-toggle' => 'tooltip',
                            'data-driver_id' => $model->id,
                            'data-driver_fullname' => isset($model->data_json['req_driver_fullname']) ? $model->data_json['req_driver_fullname'] : '',
                            'data-bs-toggle' => 'offcanvas',
                            'data-bs-target' => '#offcanvasRightDriver',
                            'aria-controls' => 'offcanvasRightDriver'
                        ]),
                        'asButton' => true
                    ]
                ]
            ])->widget(Select2::classname(), [
                'data' => $model->listDriver(),
                'options' => ['placeholder' => 'เลือก...'],
                'pluginOptions' => [
                    'tags' => true,
                    'allowClear' => true,
                    'dropdownParent' => '#main-modal',
                ]
            ])->label('พนักงานขับรถ');
            ?>


        </div>

        <div class="col-md-6">
            <?php
            echo $form->field($model, 'license_plate', [
                'addon' => [
                    'append' => [
                        'content' => Html::button('<i class="bi bi-ui-checks"></i>', [
                            'class' => 'btn btn-primary list-car',
                            'title' => 'Mark on map',
                            'data-toggle' => 'tooltip',
                            'data-driver_id' => $model->id,
                            'data-driver_fullname' => isset($model->data_json['req_driver_fullname']) ? $model->data_json['req_driver_fullname'] : '',
                            'data-bs-toggle' => 'offcanvas',
                            'data-bs-target' => '#offcanvasRight',
                            'aria-controls' => 'offcanvasRight'
                        ]),
                        'asButton' => true
                    ]
                ]
            ])->widget(Select2::classname(), [
                'data' => $model->ListCarItems(),
                'options' => ['placeholder' => 'เลือก...'],
                'pluginOptions' => [
                    'tags' => true,  // เปิดให้เพิ่มค่าใหม่ได้
                    'allowClear' => true,
                    'dropdownParent' => '#main-modal',
                ]
            ])->label('ทะเบียนยานพาหนะ (<code>รถยนต์ส่วนตัวกรอกทะเบียนรถ</code>)');
            ?>
        </div>
    </div>
</div>
<div class="">
    <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 3, 'placeholder' => 'ระบุชื่อ-นามสกุล ตำแหน่ง คั่นด้วยเครื่องหมาย , (ถ้ามี)'])->label('ผู้ร่วมเดินทาง') ?>
</div>
<div class="row">
    <div class="col-6">

        <?php
        try {
            if ($model->isNewRecord) {
                $initEmployee = Employees::find()->where(['id' => $model->leader_id])->one()->getAvatar(false);
            } else {
                $initEmployee = Employees::find()->where(['id' => $model->leader_id])->one()->getAvatar(false);
            }
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
<div class="row">
    <div class="col-7">
        <?php // $form->field($model, 'private_car', ['options' => ['class' => 'form-group mb-0']])->checkbox(['custom' => true, 'switch' => true, 'checked' => ($model->private_car == 1 ? true : false)])->label('ใช้รถยนต์ส่วนตัว'); ?>
    </div>
    <div class="col-5">



        <?php if ($model->car_type_id == 'ambulance'): ?>


        <!-- <div class="card border border-1">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-person-circle"></i> แพทย์,พยยาบาล,ผู้ช่วยเหลือคนไข้</h6>

                </div>
                <div class="avatar-stack"></div>            </div>
            <div class="card-footer d-flex justify-content-between">
                <?php echo Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่ม', ['/me/booking-car/add-passenger', 'booking_id' => $model->id], [
                    'class' => 'btn btn-sm btn-primary rounded-pill',
                    'id' => 'listEmployee',
                    'data-bs-toggle' => 'offcanvas',
                    'data-bs-target' => '#offcanvasRightEmployee',
                    'aria-controls' => 'offcanvasRightEmployee'
                ]) ?>            </div>
        </div> -->
        <?php endif; ?>

    </div>
</div>

<?php if ($model->car_type_id == 'ambulance'): ?>
<div class="card mb-2 border-2 border-primary" style="border-style:dashed">
    <div class="card-body">

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

    </div>
</div>
<?php endif; ?>

<?php if ($model->car_type_id == 'general'): ?>
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
<?php endif; ?>
<div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>
<?= $form->field($model, 'data_json[req_driver_fullname]')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'driver_id')->hiddenInput(['maxlength' => true])->label(false) ?>


<?php ActiveForm::end(); ?>


<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">ทะเบียนยานพาหนะ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
    <div id="showListCar"></div>
        <?PHP // echo $this->render('list_cars', ['model' => $model]) ?>
    </div>
</div>



<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRightDriver"
    aria-labelledby="offcanvasRightLabelDriver">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabeDriver">พนักงานขับ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?PHP echo $this->render('list_drivers', ['model' => $model]) ?>
    </div>
</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRightCar"
    aria-labelledby="offcanvasRightLabelCar">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabeCar"><i class="bi bi-person-circle"></i>
            แพทย์,พยยาบาล,ผู้ช่วยเหลือคนไข้</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="showListCar"></div>
    </div>
</div>

<?php

$js = <<<JS

    thaiDatepicker('#vehicle-date_start,#vehicle-date_end')

    \$(document).ready(function () {
    if(\$('#vehicle-car_type_id').val() == ''){
            \$('.field-vehicle-refer_type').hide();
      }
        if(\$('#vehicle-car_type_id').val() == 'ambulance'){
            $(".field-vehicle-go_type").hide();
        }else{
            $(".field-vehicle-go_type").show();
            \$('.field-vehicle-refer_type').hide();
        }
    });

      \$('#vehicle-form').on('beforeSubmit', function (e) {
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
                        Swal.fire({
                            title: "สำเร็จ!",
                            text: "บันทึกข้อมูลเรียบร้อยแล้ว",
                            icon: "success",
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                            // window.location.href = "/me/booking-vehicle";
                        });
                    }
                }
            });
        }
        });
        return false;
    });

    function loadEmployee()
    {
        \$.ajax({
            type: "get",
            url: "/me/booking-car/list-employee",
            dataType: "dataType",
            success: function (response) {
                
            }
        });
    }

    \$("body").on("click", ".select-driver", function (e) {
        e.preventDefault();
        let driver_id = \$(this).data("driver_id"); // ดึงค่าจาก data-license_plate
        let driver_fullname = \$(this).data("driver_fullname"); // ดึงค่าจาก data-license_plate
        \$('#vehicle-driver_id').val(driver_id).trigger('change');
        \$("#offcanvasRightDriver").offcanvas("hide"); // ปิด Offcanvas
        success('เลือกรถที่ต้องการใช้งานเรียบร้อยแล้ว')
    });

    \$("body").on("click", ".list-car", function (e) {
        e.preventDefault();
        
       \$.ajax({
        type: "get",
        url: "/me/booking-vehicle/list-cars",
        data: {
            car_type_id: \$('#vehicle-car_type_id').val(),
        },
        dataType: "json",
        success: function (res) {
            \$('#showListCar').html(res.content);

        }
       });
        
    });

    \$("body").on("click", ".select-car", function (e) {
        e.preventDefault();
        let license_plate = \$(this).data("license_plate"); // ดึงค่าจาก data-license_plate
        \$('#vehicle-license_plate').val(license_plate).trigger('change');
        \$("#offcanvasRight").offcanvas("hide"); // ปิด Offcanvas
        success('เลือกรถที่ต้องการใช้งานเรียบร้อยแล้ว')
    });


    JS;
$this->registerJS($js, View::POS_END);

?>