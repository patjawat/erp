<?php

use yii\web\View;
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
                    <?php echo Html::a('<i class="fa-solid fa-upload"></i> อัพโหลดไฟล์', ['/dms/documents/upload-file','id' => $model->id], ['class' => 'btn btn-primary shadow rounded-pill open-modal']) ?>
                </div>
                <?php Pjax::begin(['id' => 'showDocument']); ?>
                <iframe src="<?= Url::to(['/dms/documents/show', 'ref' => $model->ref]); ?>&embedded=true" width='100%'
                    height='1000px' frameborder="0"></iframe>

                <?php Pjax::end(); ?>
            </div>
            <div class="col-xl-5 col-lg-5 col-md-6 col-sm-12 px-5 pt-3">



                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-general-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-general" type="button" role="tab" aria-controls="pills-general"
                            aria-selected="true"><i class="fa-solid fa-circle-info"></i> ข้อมูลรายละเอียดของหนังสือ</button>
                            
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-send-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-send" type="button" role="tab" aria-controls="pills-send"
                            aria-selected="false"><i class="fa-solid fa-user-tag"></i> ส่งต่อ</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-clip-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-clip" type="button" role="tab" aria-controls="pills-clip"
                            aria-selected="false"><i class="fas fa-paperclip"></i> ไฟล์แนบ</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-disabled-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-disabled" type="button" role="tab" aria-controls="pills-disabled"
                            aria-selected="false" disabled>Disabled</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-general" role="tabpanel" aria-labelledby="pills-general-tab" tabindex="0">
                    <?php echo $this->render('_form_general',['form' => $form,'model' => $model]);?>
                    </div>
                    <div class="tab-pane fade" id="pills-send" role="tabpanel" aria-labelledby="pills-send-tab"
                        tabindex="0">
                        <?php echo $this->render('_form_send',['form' => $form,'model' => $model]);?>
                    </div>
                    <div class="tab-pane fade" id="pills-clip" role="tabpanel" aria-labelledby="pills-clip-tab"
                        tabindex="0">
                        <?php echo $model->UploadClipFile('document_clip')?>
                    </div>
                    <div class="tab-pane fade" id="pills-disabled" role="tabpanel" aria-labelledby="pills-disabled-tab"
                        tabindex="0">...</div>
                </div>




                <!-- <h6><i class="fa-solid fa-circle-info text-primary fs-3"></i> ข้อมูลรายละเอียดของหนังสือ</h6> -->


                
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$url = Url::to(['/dms/documents/get-items']);
$js = <<< JS

    $(document).ready(function () {
        $.ajax({
            url: '$url',
            type: 'GET',
            success: function (data) {
                var dualListbox = $('#myDualListbox');
                data.forEach(function (item) {
                    dualListbox.append('<option value=\"' + item.id + '\">' + item.name + '</option>');
                });
                dualListbox.bootstrapDualListbox('refresh');
            
            },
            error: function (xhr, status, error) {
                console.error('Error loading items:', error);
            }
        });
    });

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
        

        function reloadPdf()
        {
            $.pjax.reload({ container:"#showDocument", history:false,replace: false,timeout: false}); 
           
             
        }
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


            \$("#documents-doc_transactions_date").datetimepicker({
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
$this->registerJS($js,View::POS_END);
?>