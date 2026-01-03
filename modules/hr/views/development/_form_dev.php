<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */

use yii\web\View;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\components\CategoriseHelper;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */
/** @var yii\widgets\ActiveForm $form */
$emp = UserHelper::GetEmployee();
$listDocumentMe  = $emp->listDocumentMe();

$this->title = 'แก้ไข อบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = ['label' => 'บริการ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check">
            <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
            <path d="m9 14 2 2 4-4" />
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?= $this->render('@app/components/ui/btnReturn') ?>
</div>
<?php $this->endBlock(); ?>

<?php $form = ActiveForm::begin(['id' => 'form-development']); ?>
<div class="row g-4">
    <div class="col-lg-8 d-flex flex-column gap-4">

        <div class="card overflow-hidden">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="file-search-corner"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">01 ข้อมูลเอกสารอ้างอิง</h6>
            </div>
            <div class="card-body p-4 p-md-5">
                <div class="row g-4">
                    <div class="col-md-4">
                        <?= $form->field($model, 'thai_year')->textInput() ?>
                    </div>
                    <div class="col-md-4">
                        <?php

                        $listDocumentData = ArrayHelper::map($listDocumentMe, 'id', function ($model) {
                            return [
                                'text' => $model['topic'] ?? null,
                                'doc_number' => $model['doc_number'] ?? null,
                            ];
                        });

                        echo $form->field($model, 'document_id')->widget(Select2::classname(), [
                            'data' => ArrayHelper::map($listDocumentMe, 'id', 'topic'),
                            'options' => ['placeholder' => 'เลือกหนังสืออ้างอิง ...'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                // 'dropdownParent' => '#main-modal',
                            ],
                            'pluginEvents' => [
                                'select2:select' =>  new JsExpression("function(e) {
                                   var data = e.params.data;
                                    $('#development-topic').val(data.text);
                                }"),
                            ]
                        ])->label('หนังสืออ้างอิง');
                        ?>
                        <!-- <label class="form-label text-uppercase fw-bold text-muted" style="font-size: 11px;">หนังสืออ้างอิง</label>
                        <select class="form-select bg-light border-0 rounded-3 py-2 fw-bold">
                            <option>เลือกหนังสืออ้างอิง...</option>
                        </select> -->
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'data_json[doc_number]')->textInput()->label('เลขที่หนังสือ') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="radar"></i>
                </div>
                <h6 class="text-uppercase m-0">02 รายละเอียดการพัฒนา</h6>
            </div>
            <div class="card-body p-4 p-md-5 d-flex flex-column gap-4">
                <div>
                    <?= $form->field($model, 'topic')->textArea(['rows' => 3, 'placeholder' => 'หัวข้อ/โครงการ/ประชุม/ดูงาน', 'class' => 'form-control bg-light border-0 rounded-4 p-3 fw-bold fs-5']) ?>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <?= $form->field($model, 'date_start')->textInput(['class' => 'form-control bg-light border-0 rounded-3 py-2 fw-bold', 'placeholder' => 'วว/ดด/ปปปป']) ?>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <?= $form->field($model, 'date_end')->textInput(['class' => 'form-control bg-light border-0 rounded-3 py-2 fw-bold', 'placeholder' => 'วว/ดด/ปปปป']) ?>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <?php
                        echo $form->field($model, 'data_json[development_go_type_name]')->widget(Select2::classname(), [
                            'data' => CategoriseHelper::DevelopmentGoType(true),
                            'options' => ['placeholder' => 'เลือกลักษณะ'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label('ลักษณะการเข้าร่วม');
                        ?>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <?php
                        echo $form->field($model, 'development_type_id')->widget(Select2::classname(), [
                            'data' => CategoriseHelper::DevelopmentType(),
                            'options' => ['placeholder' => 'เลือกประเภทการพัฒนา'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label('ประเภทการพัฒนา');
                        ?>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <!-- <label class="form-label text-uppercase fw-bold text-muted" style="font-size: 11px;">การเบิกเงิน</label>
                        <select class="form-select bg-light border-0 rounded-3 py-2 fw-bold">
                            <option>เลือกการเบิกเงิน...</option>
                        </select> -->
                        <?php
                        echo $form->field($model, 'data_json[development_level_name]')->widget(Select2::classname(), [
                            'data' => CategoriseHelper::DevelopmentLevel(true),
                            'options' => ['placeholder' => 'เลือกระดับการพัฒนา'],
                            'pluginOptions' => [
                                // 'dropdownParent' => '#main-modal',
                                'allowClear' => true,
                            ],
                        ])->label('ระดับการพัฒนา');
                        ?>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label text-uppercase fw-bold text-muted" style="font-size: 11px;">ระดับการพัฒนา</label>
                        <select class="form-select bg-light border-0 rounded-3 py-2 fw-bold">
                            <option>ไม่ระบุ</option>
                        </select>
                    </div>
                </div>

                <div class="p-4 rounded-4 bg-info bg-opacity-10 border border-info border-opacity-10">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="d-flex align-items-center gap-1 text-info text-uppercase fw-bold mb-2" style="font-size: 11px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m16 11 2 2 4-4"></path>
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg> หัวหน้างาน
                            </label>
                            <div class="bg-white p-3 rounded-4 d-flex align-items-center gap-3 shadow-sm">
                                <div class="bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">ส</div>
                                <div class="overflow-hidden">
                                    <p class="m-0 fw-black text-truncate">นางสุวสิณี สายบุญตง</p>
                                    <p class="m-0 text-muted fw-bold text-uppercase text-truncate">นักวิชาการสาธารณสุข</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-header bg-opacity-10 border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="map"></i>
                </div>
                <h6 class="text-uppercase m-0">03 สถานที่และการเดินทาง</h6>
            </div>
            <div class="card-body p-4 p-md-5">
                <div class="row g-5">
                    <div class="col-md-6 d-flex flex-column gap-4">
                        <div>
                            <?php
                            echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::ListLocationOrg(true),
                                'options' => ['placeholder' => 'เลือกสถานที่'],
                                'pluginOptions' => [
                                    'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                    'allowClear' => true,
                                ],
                                'pluginEvents' => [
                                    'select2:select' => 'function(result) { 
                                            }',
                                    'select2:unselecting' => 'function() {

                                            }',
                                ],

                            ])->label('สถานที่จัดงาน'); ?>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <?php
                                echo $form->field($model, 'data_json[province_name]')->widget(Select2::classname(), [
                                    'data' => CategoriseHelper::ListProvinceName(true),
                                    'options' => ['placeholder' => 'เลือกจังหวัด'],
                                    'pluginOptions' => [
                                        // 'dropdownParent' => '#main-modal',
                                    ],
                                ])->label('จังหวัด');
                                ?>
                            </div>
                            <div class="col-6">
                                <?php
                                $items = [
                                    'ในจังหวัด' => 'ในจังหวัด',
                                    'ต่างจังหวัด' => 'ต่างจังหวัด',
                                    'ต่างประเทศ' => 'ต่างประเทศ',
                                ];

                                echo $form->field($model, 'data_json[location_org_type]', [
                                    // ปรับโครงสร้าง Label และ Input
                                    'template' => "{label}\n<div class='pt-2'>{input}</div>\n{error}",
                                    'labelOptions' => [
                                        'class' => 'form-label text-uppercase fw-bold text-muted',
                                        'style' => 'font-size: 11px;'
                                    ]
                                ])->dropDownList($items, [
                                    'prompt' => 'เลือกประเภทสถานที่', // ข้อความเริ่มต้น (Optional)
                                    'class' => 'form-select form-select-sm fw-bold', // ใช้ class ของ Bootstrap 5 สำหรับ Dropdown
                                    'style' => 'max-width: 200px;' // กำหนดความกว้างตามความเหมาะสม
                                ])->label('ประเภทสถานที่');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 bg-primary bg-opacity-50 rounded-4 p-4 d-flex flex-column gap-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label text-uppercase fw-bold text-muted" style="font-size: 11px;">วัน-เวลาเดินทางไป</label>
                                <div class="d-flex gap-2">
                                    <?= $form->field($model, 'vehicle_date_start')->textInput(['class' => 'form-control', 'placeholder' => 'วว/ดด/ปปปป'])->label(false) ?>
                                    <?= $form->field($model, 'data_json[vehicle_time_start]')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label(false) ?>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-uppercase fw-bold text-muted" style="font-size: 11px;">วัน-เวลาเดินทางกลับ</label>
                                <div class="d-flex gap-2">
                                        <?= $form->field($model, 'vehicle_date_end')->textInput(['class' => 'form-control', 'placeholder' => 'วว/ดด/ปปปป'])->label(false) ?>
                                        <?= $form->field($model, 'data_json[vehicle_time_end]')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label(false) ?>

                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label text-uppercase fw-bold text-muted" style="font-size: 11px;">พาหนะเดินทาง</label>
                                <select class="form-select border-0 fw-bold">
                                    <option>รถยนต์ราชการ</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-uppercase fw-bold text-muted" style="font-size: 11px;">ทะเบียนรถ</label>
                                <input class="form-control border-0 fw-bold" type="text" placeholder="ระบุ...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 d-flex flex-column gap-4">

        <div class="card overflow-hidden">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="erp-icon-box bg-primary bg-opacity-10">
                        <i data-lucide="users"></i>
                    </div>
                    <h6 class="text-uppercase m-0">คณะผู้เดินทาง</h6>
                </div>
                <button class="btn btn-sm btn-primary bg-opacity-10 text-primary border-0 rounded-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="19" x2="19" y1="8" y2="14"></line>
                        <line x1="22" x2="16" y1="11" y2="11"></line>
                    </svg></button>
            </div>
            <div class="card-body p-4 d-flex flex-column gap-3 overflow-auto" style="max-height: 300px;">
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border border-transparent hover-border-primary transition-all group">
                    <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 40px; height: 40px;">
                        <img class="w-100 h-100 object-fit-cover" src="https://picsum.photos/id/64/40/40">
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <input class="form-control-plaintext p-0 fw-bold text-dark" style="font-size: 14px;" value="นางสุวสิณี สายบุญตง">
                        <input class="form-control-plaintext p-0 text-muted fw-bold text-uppercase" style="font-size: 10px;" value="นักวิชาการสาธารณสุข ชำนาญการ">
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-sticky" style="top: 1.5rem;">
            <div class="card-header bg-opacity-10 border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="calculator"></i>
                </div>
                <h6 class="text-uppercase m-0" style="font-size: 11px; letter-spacing: 0.1em;">ประมาณการค่าใช้จ่าย</h6>
            </div>
            <div class="card-body p-4 d-flex flex-column gap-4">
                <div class="d-flex flex-column gap-2 overflow-auto" style="max-height: 350px;">
                    <div class="p-3 bg-light rounded-4 border border-light">
                        <div class="d-flex justify-content-between mb-2">
                            <input class="form-control-plaintext p-0 fw-black text-dark" value="ค่าลงทะเบียน">
                            <button class="btn btn-link btn-sm text-muted p-0"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                </svg></button>
                        </div>
                        <div class="row g-2 align-items-end">
                            <div class="col">
                                <label class="text-muted fw-bold text-uppercase" style="font-size: 9px;">จำนวน</label>
                                <input class="form-control form-control-sm text-primary fw-bold" type="number" value="1">
                            </div>
                            <div class="col-auto pb-1 text-muted fw-bold">×</div>
                            <div class="col text-end">
                                <label class="text-muted fw-bold text-uppercase" style="font-size: 9px;">ราคา</label>
                                <input class="form-control form-control-sm text-end fw-bold" type="number" value="0">
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-outline-success border-2 border-dashed rounded-4 py-2 text-uppercase fw-black" style="font-size: 10px; letter-spacing: 0.1em;">
                    + เพิ่มรายการใหม่
                </button>

                <div class="p-4 rounded-4 bg-dark text-white shadow-lg overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e293b, #312e81) !important;">
                    <p class="text-uppercase fw-bold opacity-50 mb-1" style="font-size: 10px; letter-spacing: 0.1em;">ยอดรวมเงินงบประมาณทั้งสิ้น</p>
                    <h6 class="display-6 fw-black m-0">฿0</h6>
                    <div class="mt-3 d-flex align-items-center gap-1 text-info fw-medium fst-italic" style="font-size: 9px;">
                        <i data-lucide="circle-dollar-sign"></i>
                        ราคานี้รวมภาษีมูลค่าเพิ่มแล้ว
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<style>
    /* คืนค่าความ Black ของ Font ที่ Bootstrap ไม่มีให้ */
    .fw-black {
        font-weight: 900 !important;
    }

    .rounded-4 {
        border-radius: 1rem !important;
    }

    .border-dashed {
        border-style: dashed !important;
    }

    .group:hover {
        border-color: #0d6efd !important;
        cursor: pointer;
    }
</style>


<?php

$js = <<<JS

    thaiDatepicker('#development-date_start,#development-date_end,#development-vehicle_date_start,#development-vehicle_date_end');

      \$('#form-development').on('beforeSubmit', function (e) {
        var form = \$(this);

        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกขออบรม/ประชุม/ดูงาน!",
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
                        });
                    }
                }
            });
        }
        });
        return false;
    });

    JS;
$this->registerJS($js, View::POS_END);

?>