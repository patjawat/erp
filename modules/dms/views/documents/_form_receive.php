<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\file\FileInput;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
// use softark\duallistbox\DualListbox;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentsDetail;
// use iamsaint\datetimepicker\Datetimepicker;
use app\modules\filemanager\components\FileManagerHelper;

$this->registerCssFile('https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css');
$this->registerJsFile('https://code.jquery.com/jquery-3.7.1.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

if ($model->document_group == 'receive') {
    $this->title = 'ออกเลขหนังสือรับ';
}
if ($model->document_group == 'send') {
    $this->title = 'ออกเลขหนังสือส่ง';
}
$this->params['breadcrumbs'][] = $this->title;

// use iamsaint\datetimepicker\DateTimePickerAsset::register($this);

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $this->beginBlock('page-title'); ?>
<?php if ($model->document_group == 'receive'): ?>
<i class="fa-solid fa-download"></i></i> <?= $this->title; ?>
<?php endif; ?>
<?php if ($model->document_group == 'send'): ?>
<i class="fa-solid fa-paper-plane"></i></i> <?= $this->title; ?>
<?php endif; ?>
<style>
.form-label {
    font-weight: 600 !important;
}

.file-upload-btn {
    height: 100% !important;
}

.file-upload {
    height: 800px !important;
}
</style>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/dms/menu', ['model' => $model, 'active' => 'receive']) ?>
<?php $this->endBlock(); ?>



<?php $form = ActiveForm::begin([
    'id' => 'form-document',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/dms/documents/validator']
]); ?>

<?= $form->field($model, 'document_group')->hiddenInput(['maxlength' => 50])->label(false); ?>
<?= $form->field($model, 'data_json[file_name]')->hiddenInput(['maxlength' => 50])->label(false); ?>
<div class="card">
    <div class="card-body">

        <div class="row">
            <div class="col-xl-7 col-lg-7 col-md-6 col-sm-12 pt-3">

                <div class="d-flex justify-content-between align-item-middle mb-3">
                    <h6><i class="fa-solid fa-file-pdf text-danger fs-3"></i> ข้อมูลไฟล์เอกสาร</h6>
                    <div class="position-relative">
                        <div class="file-upload-btnxx btn btn-primary shadow rounded-pill">
                            <i class="fa-solid fa-upload"></i>
                            <span>คลิกอัปโหลดไฟล์ที่นี่</span>
                        </div>
                        <input type="file" class="file-upload-input" id="my_file" accept="pdf/*">
                    </div>

                </div>




                <div class="card">
                    <div class="card-body">

                        <div class="mb-3">
                            <div class="pdf-preview" id="editPdfPreview" data-isfile="" data-newfile="false"
                                style="display: none;">
                                <embed id="editPreviewPdf" src="" type="application/pdf" width="100%" height="800px" />
                                <div class="file-remove" id="editRemovePdf" style="display: none;" data-id="">
                                    <i class="bi bi-x"></i>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>


                <!-- <div class="d-flex justify-content-between align-item-middle mb-3">
                    <h6><i class="fa-solid fa-file-pdf text-danger fs-3"></i> ข้อมูลไฟล์เอกสาร</h6>
                    <button type="button" class="btn btn-primary shadow rounded-pill" data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop">
                        <i class="fa-solid fa-upload"></i> อัพโหลดไฟล์
                    </button>

                    <?php // echo Html::a('<i class="fa-solid fa-upload"></i> อัพโหลดไฟล์', ['/dms/documents/upload-file','ref' => $model->ref], ['class' => 'btn btn-primary shadow rounded-pill open-modal']) 
                    ?>
                </div> -->
                <?php Pjax::begin(['id' => 'showDocument']); ?>
                <!-- <iframe src="<?php //  Url::to(['/dms/documents/show', 'ref' => $model->ref]); 
                                    ?>&embedded=true" width='100%'
                    height='1000px' frameborder="0"></iframe> -->
                <!-- <iframe id="myIframe" width="100%" height="1000px" frameborder="0"></iframe> -->
                <!-- <iframe id="myIframe" width="100%" height="1000px" frameborder="0"></iframe> -->
                <?php Pjax::end(); ?>

            </div>
            <div class="col-xl-5 col-lg-5 col-md-6 col-sm-12 px-5 pt-3">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-general-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-general" type="button" role="tab" aria-controls="pills-general"
                            aria-selected="true"><i class="fa-solid fa-circle-info"></i>
                            ข้อมูลรายละเอียดของหนังสือ</button>

                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-send-tab" data-bs-toggle="pill" data-bs-target="#pills-send"
                            type="button" role="tab" aria-controls="pills-send" aria-selected="false"><i
                                class="fa-solid fa-user-tag"></i> ส่งต่อ</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-clip-tab" data-bs-toggle="pill" data-bs-target="#pills-clip"
                            type="button" role="tab" aria-controls="pills-clip" aria-selected="false"><i
                                class="fas fa-paperclip"></i> ไฟล์แนบ</button>
                    </li>

                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-general" role="tabpanel"
                        aria-labelledby="pills-general-tab" tabindex="0">


                        <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => 50])->label(false); ?>
                        <div class="row">

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
                            <div class="col-3">

                                <?= $form->field($model, 'doc_regis_number')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-3">
                                <?= $form->field($model, 'thai_year')->textInput(['maxlength' => true]) ?>
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
                                <div class="d-flex gap-2">
                                    <?php echo $form->field($model, 'doc_transactions_date')->textInput(['placeholder' => 'เลือกลงรับวันที่'])->label('ลงรับวันที่') ?>
                                    <?= $form->field($model, 'doc_time')->widget(\yii\widgets\MaskedInput::className(), [
                                        'mask' => '99:99',
                                    ]) ?>

                                </div>
                            </div>


                            <div class="col-6">
                                <?php echo $form->field($model, 'doc_expire')->textInput(['placeholder' => 'เลือกวันหมดอายุ'])->label('วันหมดอายุ') ?>
                            </div>

                            <div class="col-6">
                                <?= $form->field($model, 'doc_number')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-6">
                                <?= $form->field($model, 'doc_date')->textInput(['placeholder' => 'เลือกวันที่หนังสือ'])->label('วันที่หนังสือ')
                                ?>
                            </div>
                            <div class="col-12">

                                <?php
                                echo $form->field($model, 'document_org')->widget(Select2::classname(), [
                                    'data' => $model->ListDocumentOrg(),
                                    'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
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

                            <div class="col-12">
                                <?= $form->field($model, 'topic')->textArea(['rows' => 5])->label(Html::a('<i class="fa-solid fa-magnifying-glass"></i> เรื่อง', ['/dms/documents/list-topic'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-xl']])) ?>
                            </div>

                            <div class="col-12">
                                <?= $form->field($model, 'data_json[des]')->textArea(['rows' => 5])->label('รายละเอียด') ?>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-send" role="tabpanel" aria-labelledby="pills-send-tab"
                        tabindex="0">

                        <?php
                        echo $form->field($model, 'tags_department')->widget(\kartik\tree\TreeViewInput::className(), [
                            'query' => Organization::find()->addOrderBy('root, lft'),
                            'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                            'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                            'fontAwesome' => true,
                            'asDropdown' => true,
                            'multiple' => true,
                            'options' => ['disabled' => false],
                        ])->label('ส่งหน่วยงาน');
                        ?>

                        <?php

                        // $tags = DocumentsDetail::find()->where(['name' => 'comment','document_id' => $model->id])->all();
                        // $list = ArrayHelper::map($tags, 'to_id','to_id');
                        // $model->tags_employee = $list;
                        // echo $form->field($model, 'tags_employee')->widget(Select2::classname(), [
                        //     'data' => $model->listEmployeeSelectTag(),
                        //     'options' => ['placeholder' => 'Select a state ...'],
                        //     'pluginOptions' => [
                        //         'allowClear' => true,
                        //     'multiple' => true,
                        //     ],
                        // ])->label('ส่งต่อ');

                        ?>


                        <?php //  $form->field($model, 'data_json[send_line]')->checkbox(['custom' => true, 'switch' => true, 'checked' => $model->req_approve == 1 ? true : false])->label('ส่งการแจ้งเตือนผ่าน Line'); 
                        ?>
                    </div>
                    <div class="tab-pane fade" id="pills-clip" role="tabpanel" aria-labelledby="pills-clip-tab"
                        tabindex="0">
                        <?php echo $model->UploadClipFile('document_clip') ?>
                    </div>

                </div>




                <!-- <h6><i class="fa-solid fa-circle-info text-primary fs-3"></i> ข้อมูลรายละเอียดของหนังสือ</h6> -->

                <div class="d-flex justify-content-center align-top align-items-center mt-5">
                    <div class="form-group mt-3 d-flex justify-content-center gap-2">
                        <?php echo Html::button('<i class="fa-solid fa-chevron-left"></i> ย้อนกลับ', [
                            'class' => 'btn btn-secondary rounded-pill shadow me-2',
                            'onclick' => 'window.history.back()',
                        ]); ?>
                        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
                        <?= Html::a('<i class="fa-solid fa-trash"></i> ลบทั้ง', ['delete', 'id' => $model->id], ['class' => 'btn btn-danger rounded-pill shadow delete-item']) ?>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">ไฟล์เอกสาร</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                $name  = 'document';
                list($initialPreview, $initialPreviewConfig) = FileManagerHelper::getInitialPreview($model->ref, $name);
                echo  FileInput::widget([
                    'name' => 'upload_ajax[]',
                    'options' => ['multiple' => true, 'accept' => '*'],
                    'pluginOptions' => [
                        'overwriteInitial' => false,
                        'initialPreviewShowDelete' => true,
                        'initialPreviewAsData' => true,
                        'initialPreview' => $initialPreview,
                        'initialPreviewConfig' => $initialPreviewConfig,
                        'initialPreviewDownloadUrl' => Url::to(['@web/visit/{filename}']),
                        'uploadUrl' => Url::to(['/filemanager/uploads/upload-ajax']),
                        'uploadExtraData' => [
                            'ref' => $model->ref,
                            'name' => $name,
                        ],
                        'maxFileCount' => 100,
                        'previewFileIconSettings' => [
                            // configure your icon file extensions
                            'doc' => '<i class="fas fa-file-word text-primary"></i>',
                            'docx' => '<i class="fa-regular fa-file-word"></i>',
                            'xls' => '<i class="fas fa-file-excel text-success"></i>',
                            'ppt' => '<i class="fas fa-file-powerpoint text-danger"></i>',
                            'pdf' => '<i class="fas fa-file-pdf text-danger"></i>',
                            'zip' => '<i class="fas fa-file-archive text-muted"></i>',
                            'htm' => '<i class="fas fa-file-code text-info"></i>',
                            'txt' => '<i class="fas fa-file-alt text-info"></i>',
                            'mov' => '<i class="fas fa-file-video text-warning"></i>',
                            'mp3' => '<i class="fas fa-file-audio text-warning"></i>',
                            'jpg' => '<i class="fas fa-file-image text-danger"></i>',
                            'gif' => '<i class="fas fa-file-image text-muted"></i>',
                            'png' => '<i class="fas fa-file-image text-primary"></i>',
                        ],

                    ],
                    'pluginEvents' => [
                        'fileuploaded' => 'function(event, data, previewId, index) {
                                                // loadPdf()
                                            }',
                        'filebatchuploadsuccess' => 'function(event, data) {
                                                loadPdf()
                                            }',
                    ],
                ]);
                ?>

            </div>

        </div>
    </div>
</div>


<?php ActiveForm::end(); ?>

<?php
$ref = $model->ref;
$urlUpload = Url::to('/filemanager/uploads/upload-pdf');
$file = Yii::$app->request->get('file_name');
$url = Url::to(['/dms/documents/get-items']);
$showPdfUrl = Url::to(['/dms/documents/show', 'ref' => $model->ref, 'file_name' => $file]);
$js = <<< JS
    loadPdf()


    $('#my_file').on('change', function (e) {
        const file = this.files[0];
        if (!file) return;

        // Check file type (PDF only)
        if (file.type !== 'application/pdf') {
            alert("กรุณาเลือกไฟล์ PDF เท่านั้น");
            $(this).val('');
            return;
        }

        // Check file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert("ขนาดไฟล์ต้องไม่เกิน 5MB");
            $(this).val('');
            return;
        }

        const formData = new FormData();
        formData.append("document", file);
        formData.append("id", 1);
        formData.append("ref", '$ref');
        formData.append("name", 'document');

        $.ajax({
            url: '$urlUpload',
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                   loadPdf()
                    success()
                // You can update the preview or reload the document list here
                // For example: loadPdf();
            }
        });
    });



    $('#form-document').on('beforeSubmit', function (e) {
        var form = $(this);
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
                $.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        form.yiiActiveForm('updateMessages', response, true);

                    },
                    error: function(xhr, status, error) {
                        // Swal.fire({
                        //     icon: 'error',
                        //     title: 'เกิดข้อผิดพลาด',
                        //     text: xhr.responseText || error,
                        // });
                    }
                });
            }
        });
        return false;
    });
        

        function loadPdf() {
            // Call AJAX to check if PDF file exists and get its URL
            $.ajax({
            url: '$showPdfUrl',
            type: 'GET',
            success: function (data) {
                // Assume if data is not empty, PDF exists
                console.log(data);
                
                if (data) {
                $('#editPreviewPdf').attr('src', '$showPdfUrl');
                $('#editPdfPreview').show();
                $('#editPdfPreview').attr('data-isfile', '1');
                $('#editPdfPreview').attr('data-newfile', 'true');
                } else {
                // $('#editPdfPreview').hide();
                // $('#editPreviewPdf').attr('src', '');
                // $('#editPdfPreview').attr('data-isfile', '');
                // $('#editPdfPreview').attr('data-newfile', 'false');
                // $('#editRemovePdf').hide();
                }
            },
            error: function () {
                // $('#editPdfPreview').hide();
                // $('#editPreviewPdf').attr('src', '');
                // $('#editPdfPreview').attr('data-isfile', '');
                // $('#editPdfPreview').attr('data-newfile', 'false');
                // $('#editRemovePdf').hide();
            }
            });
        }


        $('#editRemovePdf').click(function (e) { 
            e.preventDefault();
            Swal.fire({
            title: "ยืนยันการลบ?",
            text: "คุณต้องการลบไฟล์เอกสารฉบับนี้หรือไม่?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "ยกเลิก",
            confirmButtonText: "ใช่, ลบไฟล์!"
            }).then((result) => {
            if (result.isConfirmed) {
                // TODO: เพิ่ม AJAX สำหรับลบไฟล์ที่ backend
                // ตัวอย่าง:
                $.ajax({
                url: '/filemanager/uploads/delete-pdf', // เปลี่ยน URL ตาม backend
                type: 'POST',
                data: {
                    ref: '$ref',
                    name: 'document'
                },
                success: function(res) {
                    Swal.fire("ลบแล้ว!", "ไฟล์ถูกลบเรียบร้อย", "success");
                    $('#editPdfPreview').hide();
                    $('#editPreviewPdf').attr('src', '');
                    $('#editPdfPreview').attr('data-isfile', '');
                    $('#editPdfPreview').attr('data-newfile', 'false');
                    $('#editRemovePdf').hide();
                   
                },
                error: function() {
                    Swal.fire("ผิดพลาด!", "ไม่สามารถลบไฟล์ได้", "error");
                }
                });
            }
            });
        });

        thaiDatepicker('#documents-doc_transactions_date,#documents-doc_expire,#documents-doc_date');
           
            
    JS;
$this->registerJS($js, View::POS_END);
?>