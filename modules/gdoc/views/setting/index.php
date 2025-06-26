<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use kartik\widgets\ActiveForm;
$service = Categorise::find()->where(['name' => 'google','code' => 'account_service'])->one();
$drive = Categorise::find()->where(['name' => 'google','code' => 'gdrive'])->one();
$this->title = "Google API Configuration";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('_sub_menu',['active' => 'index'])?>
<?php $this->endBlock(); ?>

<style>

</style>
<div class="container">
    <!-- Credentials Upload Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card settings-card">
                <div class="card-header bg-primary">
                    <h5 class=" text-white">
                        <i class="bi bi-key me-2"></i>อัปโหลดไฟล์ Credentials
                    </h5>

                </div>
                <div class="card-body">
                    <?php if(!$service):?>
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-info-circle me-2"></i>
                                อัปโหลดไฟล์ credentials.json ที่ได้จาก Google Cloud Console เพื่อเชื่อมต่อกับ Google API
                            </div>
                            <?=Html::a('<i class="fa-solid fa-terminal"></i> ไปที่ Google Console','https://console.cloud.google.com/',['class' =>'btn btn-outline-primary','target' => '_blank'])?>
                        </div>
                    </div>
                    <?php endif;?>
                    <div class="mt-3">
                        <?php if($service):?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                            <div>
                                ไฟล์ credentials.json ถูกอัปโหลดแล้ว
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">รายละเอียดไฟล์ Credentials</label>
                            <div class="bg-light rounded p-3 border">
                                <?php
                                        $data = $service->data_json;
                                        if (isset($data['private_key'])) {
                                            $data['private_key'] = '*** (hidden) ***';
                                        }
                                        echo '<pre class="mb-0 text-break" style="white-space: pre-wrap; word-break: break-all;">';
                                        echo htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                        echo '</pre>';
                                    ?>
                            </div>
                        </div>
                        <?php else:?>
                        <div class="file-upload">
                            <div class="file-upload-btn" id="editUploadBtn">
                                <i class="bi bi-cloud-arrow-up fs-3 mb-2"></i>
                                <span>คลิกหรือลากไฟล์มาวางที่นี่</span>
                                <small class="d-block text-muted mt-2">รองรับไฟล์ JSON</small>
                            </div>
                            <?php
                                    $form = ActiveForm::begin([
                                        'id' => 'credentials-upload-form',
                                        'action' => Url::to(['/gdoc/setting/upload-credentials']),
                                        'options' => ['enctype' => 'multipart/form-data'],
                                    ]);
                                ?>
                            <input type="file" name="credentialsFile" id="my_file" class="file-upload-input"
                                accept=".json" style="display:none;">
                            <?php ActiveForm::end();?>

                        </div>

                        <div id="fileInfo" class="mt-2" style="display:none;">
                            <div class="alert alert-secondary py-2 px-3 mb-2">
                                <strong>ไฟล์ที่เลือก:</strong> <span id="selectedFileName"></span>
                                <span class="ms-3"><strong>ขนาด:</strong> <span id="selectedFileSize"></span> KB</span>
                            </div>
                            <pre id="jsonPreview" class="bg-light p-2 small"
                                style="max-height:150px;overflow:auto;"></pre>
                        </div>

                        <?php endif;?>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <span class="fw-bold">สถานะ: </span>
                                <?php if($service):?>
                                <span class="badge bg-success text-white" id="credentialsStatus">พร้อมใช้งาน</span>
                                <?php else:?>
                                <span class="badge bg-danger text-white" id="credentialsStatus">ไม่ใช้งาน</span>
                                <?php endif;?>
                            </div>
                            <div>
                                <button class="btn btn-primary" id="uploadCredentialsBtn">
                                    <i class="bi bi-upload me-1"></i>อัปโหลด
                                </button>
                                <?= $service ? Html::a(' <i class="bi bi-trash me-1"></i>ลบ',['/gdoc/setting/delete-credentials','id' => $service->id],['class' => 'delete-item btn btn-outline-danger']) : ''?>
                            </div>
                        </div>
                    </div>




                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card settings-card">
                <div class="card-header bg-primary">
                    <h5 class="text-white">
                        <i class="bi bi-gear me-2"></i>ตั้งค่าการเชื่อมต่อ
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                                    $form = ActiveForm::begin([
                                        'id' => 'systemSettingsForm',
                                        'action' => Url::to(['/gdoc/setting/update-drive']),
                                        // 'options' => ['enctype' => 'multipart/form-data'],
                                    ]);
                                ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="apiTimeout" class="form-label"><i class="fa-brands fa-google-drive"></i> Google
                                Drive ID</label>
                            <input type="text" class="form-control" id="gdrive_id" name="gdrive_id" value="<?=$drive->data_json['drive_id'] ?? ''?>">
                        </div>
                    </div>

                    <!-- <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enableNotifications" checked="">
                            <label class="form-check-label" for="enableNotifications">เปิดใช้งานการแจ้งเตือน</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enableAutoSync" checked="">
                            <label class="form-check-label"
                                for="enableAutoSync">เปิดใช้งานการซิงค์ข้อมูลอัตโนมัติ</label>
                        </div> -->
                    <div class="text-end">
                        <!-- <button type="button" class="btn btn-secondary me-2" id="resetSettingsBtn">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>คืนค่าเริ่มต้น
                        </button> -->
                        <button type="submit" class="btn btn-primary" id="saveSettingsBtn">
                            <i class="bi bi-save me-1"></i>บันทึกการตั้งค่า
                        </button>
                    </div>
                    <?php ActiveForm::end();?>
                </div>
            </div>
        </div>
    </div>

</div>
<?php
$js = <<<JS
// Show file dialog when clicking upload area
$('#editUploadBtn').on('click', function() {
    $('#my_file').click();
});

// Show file info and preview when file selected
$('#my_file').on('change', function(e) {
    var file = this.files[0];
    if (!file) return;
    $('#selectedFileName').text(file.name);
    $('#selectedFileSize').text((file.size/1024).toFixed(2));
    $('#fileInfo').show();
    // Preview JSON content (first 2KB)
    var reader = new FileReader();
    reader.onload = function(evt) {
        var text = evt.target.result;
        try {
            var json = JSON.parse(text);
            $('#jsonPreview').text(JSON.stringify(json, null, 2));
        } catch (err) {
            $('#jsonPreview').text('ไม่สามารถอ่านไฟล์ JSON ได้');
        }
    };
    reader.readAsText(file.slice(0, 2048));
});
// Handle file upload via AJAX
$('#uploadCredentialsBtn').on('click', function(e) {
    e.preventDefault();
    var fileInput = $('#my_file')[0];
    var file = fileInput.files[0];
    if (!file) {
        alert('กรุณาเลือกไฟล์ credentials.json ก่อน');
        return;
    }
    var formData = new FormData();
    formData.append('credentialsFile', file);

    $.ajax({
        url: $('#credentials-upload-form').attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#uploadCredentialsBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังอัปโหลด...');
        },
        success: function(response) {
            $('#credentialsStatus').removeClass('bg-danger').addClass('bg-success').text('อัปโหลดสำเร็จ');
            // Optionally show a toast or alert
        },
        complete: function() {
            $('#uploadCredentialsBtn').prop('disabled', false).html('<i class="bi bi-upload me-1"></i>อัปโหลด');
        }
    });
    return false;
});

$('#systemSettingsForm').on('submit', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'ยืนยันการบันทึก',
        text: 'คุณต้องการบันทึกการตั้งค่าหรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'บันทึก',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    Swal.fire('สำเร็จ', 'บันทึกการตั้งค่าสำเร็จ', 'success');
                },
                error: function() {
                    Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการบันทึก', 'error');
                }
            });
        }
    });
});

JS;
$this->registerJs($js);
?>