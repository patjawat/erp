<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\depdrop\DepDrop;
use kartik\widgets\Select2;
use yii\widgets\ActiveForm;
use app\modules\am\components\AssetHelper;

$this->title = 'นำเข้าไฟล์ CSV';
?>

<details class="alert alert-warning mb-3" open>
    <summary class="fw-semibold" style="cursor:pointer;">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>เงื่อนไขสำคัญก่อนนำเข้า (กรุณาอ่าน)
    </summary>
    <hr class="my-2">
    <div class="row small">
        <div class="col-md-6">
            <div class="fw-semibold mb-1"><i class="fa-solid fa-arrows-rotate me-1"></i>กรณีรหัสครุภัณฑ์ซ้ำกับในระบบ</div>
            <ul class="mb-2 ps-3">
                <li>ระบบจะ <span class="text-success fw-semibold">อัปเดตทับ</span> รายการเดิม (ไม่สร้างใหม่)</li>
                <li>อัปเดตได้เฉพาะรายการที่อยู่ใน <u>กลุ่มครุภัณฑ์</u> เท่านั้น — ถ้ารหัสตรงกับกลุ่มอื่น (ที่ดิน/อาคาร/สิ่งก่อสร้าง) จะถูกปฏิเสธ</li>
                <li><span class="text-danger fw-semibold">ช่องที่เว้นว่างใน CSV จะไม่ลบค่าเดิมทิ้ง</span> (เช่น ยี่ห้อ รุ่น Serial วันหมดอายุ ฯลฯ)</li>
                <li>สถานะ (asset_status) และสภาพ (asset_condition) ของเดิม <u>จะไม่ถูกแก้ไข</u></li>
            </ul>
            <div class="fw-semibold mb-1"><i class="fa-solid fa-file-circle-plus me-1"></i>กรณีรหัสยังไม่มีในระบบ / รหัสว่าง</div>
            <ul class="mb-0 ps-3">
                <li>ไม่มีในระบบ → สร้างใหม่</li>
                <li><span class="text-danger">ถ้าไม่ระบุรหัสในไฟล์ CSV จะสร้างใหม่ทุกครั้ง (เสี่ยงซ้ำซ้อน)</span></li>
            </ul>
        </div>
        <div class="col-md-6">
            <div class="fw-semibold mb-1"><i class="fa-solid fa-list-check me-1"></i>คอลัมน์บังคับในไฟล์ CSV</div>
            <ul class="mb-2 ps-3">
                <li>หมายเลขครุภัณฑ์, ชื่อ, มูลค่า, อายุการใช้งาน — ขาดข้อใดข้อหนึ่ง <span class="text-danger">ปฏิเสธทั้งไฟล์</span></li>
            </ul>
            <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-info me-1"></i>เงื่อนไขอื่น</div>
            <ul class="mb-0 ps-3">
                <li>ต้องเลือก <u>ประเภท</u> และ <u>หมวด</u> ของครุภัณฑ์ก่อนอัปโหลด (จะถูกใช้กับทุกแถวในไฟล์)</li>
                <li>รหัสซ้ำกันภายในไฟล์เดียว — ระบบจะเก็บเฉพาะ <span class="fw-semibold">แถวล่างสุด</span> (overwrite แถวก่อนหน้า)</li>
                <li>หน่วยงาน/ผู้รับผิดชอบ ใช้การค้นแบบตรงตัว (exact match) — ถ้าหาไม่พบจะ <u>เว้นว่าง</u> และแจ้งเตือน (ไม่ปฏิเสธ)</li>
                <li>ถ้ามีแถวใดบันทึกไม่สำเร็จ ระบบจะ <span class="text-danger fw-semibold">ยกเลิกทั้งหมด</span> (rollback) — ไม่มีการบันทึกบางส่วน</li>
                <li>วันที่รองรับทั้ง ค.ศ. และ พ.ศ. (YYYY-MM-DD หรือ DD/MM/YYYY)</li>
            </ul>
        </div>
    </div>
</details>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <?= Html::a(
        '<i class="fa-solid fa-download me-2"></i>ดาวน์โหลดเทมเพลต CSV',
        ['/am/import/download-template'],
        ['class' => 'btn btn-outline-primary', 'target' => '_blank']
    ) ?>
    <small class="text-muted">ใช้เทมเพลตเพื่อดูรูปแบบคอลัมน์และนำเข้าข้อมูลครุภัณฑ์</small>
</div>

<?php $form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data', 'id' => 'upload-form'],
    'enableAjaxValidation' => true,
    'validationUrl' => Url::to(['/am/import/validate']), // action สำหรับ validate
]) ?>

<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <?php

        echo $form->field($model, 'asset_type_id')->widget(Select2::classname(), [
            'data' => AssetHelper::listAssetType(),
            'options' => [
                'placeholder' => 'ทุกประเภท',
                'id' => 'asset_type_id'
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
            'pluginEvents' => [
                "select2:select" => "function() { 
                                        // $(this).submit(); 
                                    }",
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12">

        <?php
        echo $form->field($model, 'asset_category_id')->widget(DepDrop::classname(), [
            'options' => [
                'placeholder' => 'ทุกหมวดหมู่',
                'id' => 'asset_category_id'
            ],
            'type' => DepDrop::TYPE_SELECT2,
            'select2Options' => ['pluginOptions' => ['allowClear' => true]],
            'pluginOptions' => [
                'dropdownParent' => '#main-modal',
                'depends' => ['asset_type_id'],
                'url' => Url::to(['/am/asset-item/get-asset-category']),
                'loadingText' => 'กำลังโหลด ...',
                'params' => ['depdrop_all_params' => 'assetitemsearch-asset_type_id'],
                'initDepends' => ['asset_type_id'],
                'initialize' => true,
            ],
            'pluginEvents' => [
                "select2:select" => "function() { 

                        }",
            ],

        ])->label(false); ?>
    </div>
</div>
<div class="mt-3">
    <?= Html::fileInput('csvFile', null, ['id' => 'csvFile']) ?>
</div>

<div id="preview-table"></div>

<div id="import-btn" class="d-none d-flex justify-content-center mt-5">
    <button class="btn btn-success" id="btn-import" type="submit">
        <i class="fa-solid fa-file-import me-2"></i>ยืนยันนำเข้า
    </button>
    <?= Html::hiddenInput('filePath', null, ['id' => 'filePath']) ?>
</div>

<?php ActiveForm::end() ?>
<div class="d-flex justify-content-center d-none" id="container-duplicate">
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <div>
            รหัสทรัพย์สินซ้ำ 2 รายการ
        </div>
    </div>
</div>


<?php
$js = <<<JS
// 1️⃣ AJAX preview
    $('#csvFile').on('change', function() {
        var file = this.files[0];
        if(!file) return;

        var formData = new FormData();
        formData.append('csvFile', file);

        $.ajax({
            url: '/am/import/preview',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                    if(res.status === 'success'){
                    $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl")
                                    .addClass('modal-xxl');

                    var duplicateRows = [];
                    var html = '<table class="table table-striped table-bordered table-sm"><thead><tr>';
                    res.preview[0].forEach(function(h){ html += '<th>' + h + '</th>'; });
                    html += '</tr></thead><tbody class="table-group-divider align-middle">';

                    res.preview.slice(1).forEach(function(row){
                        var isDuplicate = res.duplicates.some(function(dup){
                            return JSON.stringify(dup) === JSON.stringify(row);
                        });
                        if (isDuplicate) duplicateRows.push(row);
                        html += '<tr' + (isDuplicate ? ' class="table-danger"' : '') + '>';
                        row.forEach(function(cell){ html += '<td>' + cell + '</td>'; });
                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                    $('#preview-table').html(html);
                    $('#filePath').val(res.filePath);

                    if (duplicateRows.length > 0) {
                        $('#import-btn').hide();
                        $('#container-duplicate').removeClass('d-none').find('div:last').text('รหัสทรัพย์สินซ้ำ ' + duplicateRows.length + ' รายการ');
                    } else {
                        $('#import-btn').removeClass('d-none').show();
                        $('#container-duplicate').addClass('d-none');
                    }

                } else {
                    alert('เกิดข้อผิดพลาดในการอัปโหลด');
                }

                
            },
            error: function(){
                alert('เกิดข้อผิดพลาดในการอัปโหลด');
            }
        });
    });

    $('body').on("beforeSubmit", '#upload-form', function (e) {
        e.preventDefault();
        const form = $(this);
        var filePath = $('#filePath').val();

        if(!filePath) { 
            Swal.fire('ผิดพลาด', 'ไม่พบไฟล์', 'error');
            return false; 
        }

        // ตรวจสอบว่ามี error หรือไม่
        if (form.find('.has-error').length) return false;

        // แสดง confirm ก่อนนำเข้า
        Swal.fire({
            title: 'ยืนยันการนำเข้า?',
            text: "เมื่อยืนยันแล้วจะไม่สามารถยกเลิกได้",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {

                // แสดง loading
                Swal.fire({
                    title: 'กำลังนำเข้า...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                // ส่ง AJAX
                $.ajax({
                    url: '/am/import/import-csv',
                    type: 'POST',
                    data: { 
                        filePath: filePath,
                        asset_type_id: $('#asset_type_id').val(),
                        asset_category_id: $('#asset_category_id').val()
                    },
                    success: function(res) {
                        Swal.close(); // ปิด loading

                        if(res.status === 'success'){
                            const warnings = Array.isArray(res.warnings) ? res.warnings : [];
                            if (warnings.length > 0) {
                                let wHtml = '<div class="text-start">'
                                    + '<div class="mb-2">' + (res.message || 'นำเข้าข้อมูลเรียบร้อย') + '</div>'
                                    + '<div class="alert alert-warning small mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>มีคำเตือน ' + warnings.length + ' รายการ</div>'
                                    + '<ul class="small mb-0" style="max-height:240px;overflow:auto;">';
                                warnings.forEach(function(w){
                                    wHtml += '<li>แถว ' + w.row + ' (รหัส: ' + (w.code || '-') + '): ' + w.message + '</li>';
                                });
                                wHtml += '</ul></div>';
                                Swal.fire({
                                    title: 'สำเร็จ (มีคำเตือน)',
                                    html: wHtml,
                                    icon: 'success',
                                    width: 640,
                                }).then(function(){ window.location.reload(true); });
                            } else {
                                Swal.fire('สำเร็จ', res.message || 'นำเข้าข้อมูลเรียบร้อย', 'success')
                                    .then(function(){ window.location.reload(true); });
                            }
                            $('#preview-table').html('');
                            $('#import-btn').hide();
                            $('#csvFile').val('');

                        } else if(res.status === 'error' && res.errors){
                            let html = '<ul style="text-align:left;">';
                            res.errors.forEach(function(err){
                                let messages = [];
                                // loop ทุก attribute ของ error ยกเว้น csvFile
                                for(let attr in err.errors){
                                    if(attr !== 'csvFile'){
                                        messages.push(err.errors[attr].join(', ')); 
                                    }
                                }
                                if(messages.length > 0){
                                    html += `<li>แถว \${err.row} (รหัส: \${err.code}): \${messages.join('; ')}</li>`;
                                }
                            });
                            html += '</ul>';

                            Swal.fire({
                                title: 'พบข้อผิดพลาด',
                                html: html,
                                icon: 'error',
                                width: 600
                            });

                        } else {
                            Swal.fire('ผิดพลาด', res.message || 'เกิดข้อผิดพลาด', 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                    }
                });
            }
        });

        return false; // ป้องกัน form submit ปกติ
    });


JS;

$this->registerJs($js);
?>