<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
// use softark\duallistbox\DualListbox;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

// use iamsaint\datetimepicker\DateTimePickerAsset::register($this);

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>
<?php $form = ActiveForm::begin([
    'id' => 'form-document',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/dms/documents/validator']
]); ?>
<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => 50])->label(false); ?>
<?= $form->field($model, 'document_group')->hiddenInput(['maxlength' => 50])->label(false); ?>
<div class="card">
    <div class="card-body">

        <div class="row">
            <div class="col-xl-7 col-lg-7 col-md-6 col-sm-12 pt-3">
                <div class="d-flex justify-content-between align-item-middle mb-3">
                    <h6><i class="fa-solid fa-file-pdf text-danger fs-3"></i> ข้อมูลไฟล์เอกสาร</h6>
                    <?php echo Html::a('<i class="fa-solid fa-upload"></i> อัพโหลดไฟล์', ['/'], ['class' => 'btn btn-primary shadow rounded-pill']) ?>
                </div>
                <iframe src="<?= Url::to(['/dms/documents/show', 'ref' => $model->ref]); ?>&embedded=true" width='100%'
                    height='1000px' frameborder="0"></iframe>
            </div>
            <div class="col-xl-5 col-lg-5 col-md-6 col-sm-12 px-5 pt-3">
                <h6><i class="fa-solid fa-circle-info text-primary fs-3"></i> ข้อมูลรายละเอียดของหนังสือ</h6>

                <div class="row">
                    <div class="col-12">
                        <?= $form->field($model, 'topic')->textArea(['rows' => 2]) ?>
                    </div>
                    <div class="col-6">

                        <?php
                        echo $form->field($model, 'document_type')->widget(Select2::classname(), [
                            'data' => $model->ListDocumentType(),
                            'options' => ['placeholder' => 'ประเภทหนังสือ'],
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
                        ])->label('ประเภทหนังสือ');
                        ?>

                    </div>
                    <div class="col-6">
                        <?php
                        echo $form->field($model, 'document_org')->widget(Select2::classname(), [
                            'data' => $model->ListDocumentOrg(),
                            'options' => ['placeholder' => 'เลือกหน่วยงาน'],
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
                        ])->label('จากหน่วยงาน');
                        ?>
                    </div>
                    <div class="col-6">
                        <?php
                        echo $form->field($model, 'doc_date')->widget(Datetimepicker::className(), [
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y'
                            ],
                        ])->label('วันที่หนังสือ')
                        ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'doc_number')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-6">
                        <?php
                        echo $form->field($model, 'secret')->widget(Select2::classname(), [
                            'data' => $model->DocSecret(),
                            'options' => ['placeholder' => 'เลือกชั้นความลับ'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label('ชั้นความลับ');
                        ?>
                    </div>
                    <div class="col-6">
                        <?php
                    echo $form->field($model, 'doc_speed')->widget(Select2::classname(), [
                        'data' => $model->DocSpeed(),
                        'options' => ['placeholder' => 'เลือกชั้นความลับ'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ])->label('ชั้นเร็ว');
                    ?>
                    </div>

                </div>

                <div class="row">
                    <div class="col-3">
                      
                        <?= $form->field($model, 'doc_regis_number')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-3">
                        <?= $form->field($model, 'thai_year')->textInput(['maxlength' => true]) ?>
                    </div>

                    <div class="col-6">
                        <div class="d-flex gap-2">
                            <?php echo $form->field($model, 'doc_receive_date')->widget(Datetimepicker::className(), [
                                'options' => [
                                    'timepicker' => false,
                                    'datepicker' => true,
                                    'mask' => '99/99/9999',
                                    'lang' => 'th',
                                    'yearOffset' => 543,
                                    'format' => 'd/m/Y',
                                ],
                            ])->label('ลงรับวันที่') ?>
                            <?= $form->field($model, 'doc_time')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                    <div class="col-6">
                        <?php echo $form->field($model, 'doc_expire')->widget(Datetimepicker::className(), [
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y',
                            ],
                        ])->label('วันหมดอายุ') ?>
                    </div>
                </div>



            <?= $form->field($model, 'data_json[department_tag]')->widget(\kartik\tree\TreeViewInput::className(), [
                    'query' => Organization::find()->addOrderBy('root, lft'),
                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                    'fontAwesome' => true,
                    'asDropdown' => true,
                    'multiple' => true,
                    'options' => ['disabled' => false],
                ])->label('ส่งหน่วยงาน'); ?>

            <div class="d-flex justify-content-center align-top align-items-center mt-5">
                <div class="form-group mt-3 d-flex justify-content-center gap-3">
                    <?php echo Html::button('<i class="fa-solid fa-chevron-left"></i> ย้อนกลับ', [
                            'class' => 'btn btn-secondary rounded-pill shadow me-2',
                            'onclick' => 'window.history.back()',
                        ]); ?>
                    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$url = Url::to(['/dms/documents/get-items']);
$js = <<< JS

    \$('#form-document').on('beforeSubmit', function (e) {
            var form = \$(this);
            console.log('Submit');

            Swal.fire({
            title: "ยืนยัน?",
            text: "บันทึกหนังสือ!",
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
                    success: async function (response) {
                        form.yiiActiveForm('updateMessages', response, true);
                        if(response.status == 'success') {
                            closeModal()
                            // success()
                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });

            }
            });
            return false;
        });
        
    var thaiYear = function (ct) {
                var leap=3;
                var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];
                if(ct){
                    var yearL=new Date(ct).getFullYear()-543;
                    leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;
                    if(leap==2){
                        dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];
                    }
                }
                this.setOptions({
                    i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,
                })
            };


            \$("#documents-doc_receive_date").datetimepicker({
                timepicker:false,
                format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
                lang:'th',  // แสดงภาษาไทย
                onChangeMonth:thaiYear,
                onShow:thaiYear,
                yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
                closeOnDateSelect:true,
            });


            \$("#documents-doc_expire").datetimepicker({
                timepicker:false,
                format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
                lang:'th',  // แสดงภาษาไทย
                onChangeMonth:thaiYear,
                onShow:thaiYear,
                yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
                closeOnDateSelect:true,
            });
            
            
    JS;
$this->registerJS($js);
?>