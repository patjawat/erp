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
use app\modules\filemanager\components\FileManagerHelper;
// $this->registerJsFile('@web/js/float-type.js', ['depends' => [\yii\web\JqueryAsset::class]]);

if($model->document_group == 'receive'){
    $this->title = 'ออกเลขหนังสือรับ';
}
if($model->document_group == 'send')
{
    $this->title = 'ออกเลขหนังสือส่ง';
    
}
$this->params['breadcrumbs'][] = $this->title;

// use iamsaint\datetimepicker\DateTimePickerAsset::register($this);

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $this->beginBlock('page-title'); ?>
<?php if($model->document_group == 'receive'):?>
<i class="fa-solid fa-download"></i></i> <?= $this->title; ?>
<?php endif; ?>
<?php if($model->document_group == 'send'):?>
<i class="fa-solid fa-paper-plane"></i></i> <?= $this->title; ?>
<?php endif; ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/dms/menu') ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$model,'active' => 'send']) ?>
<?php $this->endBlock(); ?>

<?php $this->endBlock(); ?>
<style>
.form-label {
    font-weight: 600 !important;
}


</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-document',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/dms/documents/validator']
]); ?>

<?= $form->field($model, 'document_group')->hiddenInput(['maxlength' => 50])->label(false); ?>
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

                                <?= $form->field($model, 'doc_regis_number')->textInput(['maxlength' => true])->label('เลขส่ง') ?>
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
                  'dropdownParent' => '#main-modal',
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
                      'dropdownParent' => '#main-modal',
                ],
            ])->label('ชั้นความลับ');
            ?>
                            </div>

                            <div class="col-6">
                                <?php echo $form->field($model, 'doc_transactions_date')->textInput(['placeholder' => 'เลือกวันที่ส่ง'])->label('ส่งวันที่') ?>

                            </div>
                            <div class="col-6">
                                <?= $form->field($model, 'doc_time')->widget(\yii\widgets\MaskedInput::className(), [
                        'mask' => '99:99',
                    ]) ?>

                            </div>


                            <div class="col-6">
                                <?= $form->field($model, 'doc_number')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-6">
                                <?php
            echo $form->field($model, 'doc_date')->textInput(['placeholder' => 'เลือกวันที่หนังสือ'])->label('วันที่หนังสือ')
            ?>
                            </div>


                            <div class="col-12">
                                <?php
            echo $form->field($model, 'document_org')->widget(Select2::classname(), [
                'data' => $model->ListDocumentOrg(),
                'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                'pluginOptions' => [
                    'allowClear' => true,
                      'dropdownParent' => '#main-modal',
                    'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                    // 'width' => '370px',
                ],
                'pluginEvents' => [
                    'select2:select' => 'function(result) { 
                                }',
                    'select2:unselecting' => 'function() {

                                }',
                ]
            ])->label('ส่งถึงหน่วยงาน');
            ?>
                            </div>

                     <div class="col-12">
                                <div class="textarea-wrapper">
                                    <?= $form->field($model, 'topic')->textArea(['rows' => 5])->label('เรื่อง') ?>
                                    <div class="autocomplete-dropdown" id="documents-topic-dropdown"></div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="textarea-wrapper">
                                    <?= $form->field($model, 'data_json[des]')->textArea(['rows' => 5])->label('รายละเอียด') ?>
                                    <div class="autocomplete-dropdown" id="documents-res-dropdown"></div>
                                </div>
                            </div>

                        </div>
                        <?= $form->field($model, 'data_json[send_line]')->checkbox(['custom' => true, 'switch' => true, 'checked' => $model->req_approve == 1 ? true : false])->label('ส่งการแจ้งเตือนผ่าน Line'); ?>
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

                    <div class="tab-pane fade" id="pills-clip" role="tabpanel" aria-labelledby="pills-clip-tab"
                        tabindex="0">
                        <?php echo $model->UploadClipFile('document_clip')?>
                    </div>

                </div>




                <!-- <h6><i class="fa-solid fa-circle-info text-primary fs-3"></i> ข้อมูลรายละเอียดของหนังสือ</h6> -->



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
                list($initialPreview, $initialPreviewConfig) = FileManagerHelper::getInitialPreview($model->ref,$name);
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
$showPdfUrl = Url::to(['/dms/documents/show?ref='.$model->ref]);
$js = <<< JS
    loadPdf()



    thaiDatepicker('#documents-doc_transactions_date,#documents-doc_date,#leave-date_end,#documents-doc_expire,#documents-data_json-event_date')
        handleFormSubmit('#form-document', null, async function(response) {
        await location.reload();
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

                }
            },
            error: function () {

            }
            });
        }

        $('#my_file').on('change', function (e) {
        const file = this.files[0];
        if (!file) return;

        // Check file type (PDF only)
        if (file.type !== 'application/pdf') {
            alert("กรุณาเลือกไฟล์ PDF เท่านั้น");
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
                // You can update the preview or reload the document list here
                // For example: loadPdf();
            }
        });
    });


                // Initialize FloatType instances
        $(document).ready(function() {
            // Demo 4: AJAX simulation
            new FloatType('#documents-topic', '#documents-topic-dropdown', {
                triggers: {
                    '': { 
                       url: '/dms/documents/get-keyword',  // This would be your real endpoint
                        method: 'GET'
                    }
                },
                debounceTime: 500
            });
            
            new FloatType('#documents-data_json-des', '#documents-res-dropdown', {
                triggers: {
                    '': { 
                        url: '/dms/documents/get-keyword',  // This would be your real endpoint
                        method: 'GET'
                    }
                },
                debounceTime: 500
            });
            
        });
    
    JS;
$this->registerJS($js,View::POS_END);
?>