<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\components\CategoriseHelper;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="development-form">

    <?php $form = ActiveForm::begin(['id' => 'form-development']); ?>

    <?php
    echo $form->field($model, 'document_id')->widget(Select2::classname(), [
        'data' => [],
        'options' => ['placeholder' => 'เลือกหนังสืออ้างอิง ...'],
        'pluginOptions' => [
            'allowClear' => true,
            'dropdownParent' => '#main-modal',
        ],
    ])->label('หนังสืออ้างอิง');
?>
<?= $form->field($model, 'topic')->textInput(['maxlength' => true]) ?>

<div class="row">
<div class="col-6">
    <?= $form->field($model, 'date_start')->textInput() ?>
</div>
<div class="col-6">
    <?= $form->field($model, 'date_end')->textInput() ?>
</div>
</div>



<div class="row">
<div class="col-6">
<?php
                                        echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
                                            'data' => CategoriseHelper::ListLocationOrg(true),
                                            'options' => ['placeholder' => 'เลือกสถานที่ไป'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('สถานที่ไป');
                                        ?>
</div>
<div class="col-6">
<?php
                                        echo $form->field($model, 'data_json[province_name]')->widget(Select2::classname(), [
                                            'data' => CategoriseHelper::ListProvinceName(),
                                            'options' => ['placeholder' => 'เลือกจังหวัด'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('จังหวัด');
                                        ?>
 </div>
<div class="col-12">
    <?php
                                        echo $form->field($model, 'data_json[location_org]')->widget(Select2::classname(), [
                                            'data' => CategoriseHelper::ListLocationOrg(),
                                            'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('หน่วยงานที่จัด');
                                        ?>

<?php
                                        echo $form->field($model, 'data_json[location_org_type]')->widget(Select2::classname(), [
                                            'data' => [
                                                'ในจังหวัด' => 'ในจังหวัด',
                                                'ต่างจังหวัด' => 'ต่างจังหวัด',
                                                'ต่างประเทศ' => 'ต่างประเทศ',
                                            ],
                                            'options' => ['placeholder' => 'เลือกประเภทสถานที่ประชุม'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('ประเภทสถานที่ประชุม');
                                        ?>

</div>
</div>

<?php
                                        echo $form->field($model, 'data_json[development_type_name]')->widget(Select2::classname(), [
                                            'data' => CategoriseHelper::DevelopmentType(true),
                                            'options' => ['placeholder' => 'เลือกประเภทการพัฒนา'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('ประเภทการพัฒนา');
                                        ?>

   

<?php
                                        echo $form->field($model, 'data_json[vehicle_type_name]')->widget(Select2::classname(), [
                                            'data' => CategoriseHelper::VehicleType(true),
                                            'options' => ['placeholder' => 'เลือกพาหนะเดินทาง'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('พาหนะเดินทาง');
                                        ?>
                                        
                                        <div class="row">
<div class="col-6">
    <?= $form->field($model, 'vehicle_date_start')->textInput() ?>
</div>
<div class="col-6">
    <?= $form->field($model, 'vehicle_date_end')->textInput() ?>
</div>
</div>



                                        <?php
                                        echo $form->field($model, 'data_json[claim_type_name]')->widget(Select2::classname(), [
                                            'data' => CategoriseHelper::DevelopmentClaimType(true),
                                            'options' => ['placeholder' => 'เลือกการเบิกเงิน'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('การเบิกเงิน');
                                        ?>
                                        

                                        <?php
                                        echo $form->field($model, 'data_json[development_level_name]')->widget(Select2::classname(), [
                                            'data' => CategoriseHelper::DevelopmentLevel(true),
                                            'options' => ['placeholder' => 'เลือกระดับการพัฒนา'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('ระดับการพัฒนา');
                                        ?>
                                        
                                           <?php
                                        echo $form->field($model, 'data_json[development_go_type_name]')->widget(Select2::classname(), [
                                            'data' => CategoriseHelper::DevelopmentGoType(true),
                                            'options' => ['placeholder' => 'เลือกลักษณะการไป'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('ลักษณะการไป');
                                        ?>
 <?php
                                        echo $form->field($model, 'vehicle_type_id')->widget(Select2::classname(), [
                                            'data' => CategoriseHelper::DevelopmentGoType(true),
                                            'options' => ['placeholder' => 'เลือกพาหนะเดินทาง'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('พาหนะเดินทาง');
                                        ?>

<?php
                                        echo $form->field($model, 'data_json[time_slot]')->widget(Select2::classname(), [
                                            'data' => [
                                                'เต็มวัน' => 'เต็มวัน',
                                                'ครั้งวันเช้า' => 'ครั้งวันเช้า',
                                                'ครั้งวันบ่าย' => 'ครั้งวันบ่าย',
                                            ],
                                            'options' => ['placeholder' => 'เลือกประเภท'],
                                            'pluginOptions' => [
                                                'dropdownParent' => '#main-modal',
                                                'allowClear' => true,
                                                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                                // 'width' => '370px',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => 'function(result) { 
                                                            }',
                                                'select2:unselecting' => 'function() {

                                                            }',
                                            ]
                                        ])->label('ประเภท');
                                        ?>
                                        


    <?= $form->field($model, 'leader_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'assigned_to')->textInput() ?>
    <?= $form->field($model, 'thai_year')->textInput() ?>

    <?= $form->field($model, 'status')->hiddenInput(['maxlength' => true,'value' => 'Pending'])->label(false) ?>
    <?= $form->field($model, 'emp_id')->hiddenInput(['maxlength' => true,'value' => 1])->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


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