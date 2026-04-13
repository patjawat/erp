<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
$this->title = 'นำเข้า ทะเบียนอาคาร';
?>

<div class="am-building-import">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <?= Html::a(
            '<i class="fa-solid fa-download me-2"></i>ดาวน์โหลดเทมเพลต CSV',
            ['/am/building/download-template'],
            ['class' => 'btn btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener']
        ) ?>
        <small class="text-muted">เทมเพลตสำหรับทะเบียนอาคารเท่านั้น — สิ่งปลูกสร้างใช้เมนูทะเบียนสิ่งปลูกสร้าง</small>
    </div>

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data', 'id' => 'building-upload-form'],
    ]) ?>

    <div class="mt-2">
        <?= Html::fileInput('csvFile', null, ['id' => 'csvFile', 'accept' => '.csv']) ?>
    </div>

    <div id="preview-table" class="mt-3"></div>

    <div id="import-btn" class="d-none d-flex justify-content-center mt-4">
        <button class="btn btn-success" id="btn-import" type="submit">
            <i class="fa-solid fa-file-import me-2"></i>ยืนยันนำเข้า
        </button>
        <?= Html::hiddenInput('filePath', null, ['id' => 'filePath']) ?>
    </div>

    <?php ActiveForm::end() ?>

    <div class="d-flex justify-content-center d-none mt-3" id="container-duplicate">
        <div class="alert alert-danger d-flex align-items-center mb-0" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <div>พบรหัสซ้ำ</div>
        </div>
    </div>
</div>

<?php
$previewUrl = Url::to(['/am/building/preview']);
$importUrl = Url::to(['/am/building/import-csv']);
$js = <<<JS
    // AJAX preview
    $('#csvFile').on('change', function() {
        var file = this.files[0];
        if (!file) return;

        var formData = new FormData();
        formData.append('csvFile', file);

        $.ajax({
            url: '{$previewUrl}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl").addClass('modal-xxl');

                    var duplicateRows = [];
                    var html = '<div class="table-responsive" style="max-height: 360px; overflow:auto;">';
                    html += '<table class="table table-striped table-bordered table-sm mb-0"><thead class="table-light sticky-top"><tr>';
                    (res.preview[0] || []).forEach(function(h){ html += '<th class="small">' + (h || '') + '</th>'; });
                    html += '</tr></thead><tbody class="table-group-divider align-middle">';

                    res.preview.slice(1).forEach(function(row){
                        var isDuplicate = res.duplicates.some(function(dup){
                            return JSON.stringify(dup) === JSON.stringify(row);
                        });
                        if (isDuplicate) duplicateRows.push(row);
                        html += '<tr' + (isDuplicate ? ' class="table-danger"' : '') + '>';
                        (row || []).forEach(function(cell){ html += '<td class="small">' + (cell || '') + '</td>'; });
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    $('#preview-table').html(html);
                    $('#filePath').val(res.filePath || '');

                    if (duplicateRows.length > 0) {
                        $('#import-btn').hide();
                        $('#container-duplicate').removeClass('d-none')
                            .find('div:last')
                            .text('พบรหัสซ้ำในระบบ ' + duplicateRows.length + ' รายการ');
                    } else {
                        $('#import-btn').removeClass('d-none').show();
                        $('#container-duplicate').addClass('d-none');
                    }
                } else {
                    Swal.fire('ผิดพลาด', res.message || 'เกิดข้อผิดพลาดในการอัปโหลด', 'error');
                }
            },
            error: function() {
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการอัปโหลด', 'error');
            }
        });
    });

    $('body').on('beforeSubmit', '#building-upload-form', function (e) {
        e.preventDefault();
        var filePath = $('#filePath').val();
        if (!filePath) {
            Swal.fire('ผิดพลาด', 'ไม่พบไฟล์', 'error');
            return false;
        }

        Swal.fire({
            title: 'ยืนยันการนำเข้า?',
            text: 'เมื่อยืนยันแล้วจะบันทึกข้อมูลเข้าระบบ',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'กำลังนำเข้า...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: '{$importUrl}',
                type: 'POST',
                dataType: 'json',
                data: { filePath: filePath },
                success: function(res) {
                    Swal.close();
                    if (res.status === 'success') {
                        Swal.fire('สำเร็จ', res.message || 'นำเข้าข้อมูลเรียบร้อย', 'success').then(function() {
                            window.location.reload(true);
                        });
                    } else if (res.status === 'error' && res.errors) {
                        let html = '<ul style="text-align:left;">';
                        res.errors.forEach(function(err){
                            let messages = [];
                            for (let attr in err.errors) {
                                messages.push((err.errors[attr] || []).join(', '));
                            }
                            if (messages.length > 0) {
                                html += `<li>แถว \${err.row} (รหัส: \${err.code || '-' }): \${messages.join('; ')}</li>`;
                            }
                        });
                        html += '</ul>';
                        Swal.fire({ title: 'พบข้อผิดพลาด', html: html, icon: 'error', width: 700 });
                    } else {
                        Swal.fire('ผิดพลาด', res.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        });

        return false;
    });
JS;
$this->registerJs($js);
?>

