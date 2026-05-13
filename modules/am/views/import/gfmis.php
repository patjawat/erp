<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */

$this->title = 'นำเข้าอัปเดต GFMIS';
?>

<div class="am-gfmis-import">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h5 class="mb-1"><i class="fa-solid fa-file-import me-2"></i><?= Html::encode($this->title) ?></h5>
            <small class="text-muted">อัปเดตค่า GFMIS ให้กับทรัพย์สินที่มีหมายเลขครุภัณฑ์ (code) ตรงกัน โดยไฟล์ Excel ต้องมีอย่างน้อย 2 คอลัมน์: หมายเลขครุภัณฑ์ (code) และ GFMIS</small>
        </div>
        <?= Html::a(
            '<i class="fa-solid fa-download me-2"></i>ดาวน์โหลด Template Excel',
            ['/am/import/download-gfmis-template'],
            ['class' => 'btn btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener']
        ) ?>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        <div class="fw-semibold mb-1">คำแนะนำ</div>
        <ul class="mb-0 ps-3">
            <li>ใช้ไฟล์ `.xlsx` ที่มีคอลัมน์หมายเลขครุภัณฑ์ (code) และ GFMIS</li>
            <li>ถ้าหมายเลขครุภัณฑ์ (code) ไม่พบในระบบ หรือมีรหัสซ้ำในไฟล์ ระบบจะไม่ให้ยืนยันนำเข้า</li>
            <li>ถ้าค่า GFMIS เท่ากับข้อมูลเดิม ระบบจะแสดงเป็นรายการเดิมและข้ามการบันทึก</li>
        </ul>
    </div>

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data', 'id' => 'gfmis-upload-form'],
    ]) ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-8">
                    <label class="form-label fw-semibold">ไฟล์ Excel</label>
                    <?= Html::fileInput('xlsxFile', null, [
                        'id' => 'xlsxFile',
                        'class' => 'form-control',
                        'accept' => '.xlsx,.xls,.csv',
                    ]) ?>
                </div>
                <div class="col-12 col-lg-4 text-lg-end">
                    <div class="small text-muted">รองรับ Excel และ CSV หากต้องการใช้ไฟล์เดิมจากแผนกอื่น</div>
                </div>
            </div>

            <div id="summary-box" class="row g-2 mt-4"></div>

            <div id="preview-table" class="mt-3"></div>

            <div id="import-btn" class="d-none d-flex justify-content-end mt-4">
                <button class="btn btn-success" id="btn-import" type="submit">
                    <i class="fa-solid fa-file-import me-2"></i>ยืนยันอัปเดต GFMIS
                </button>
                <?= Html::hiddenInput('filePath', null, ['id' => 'filePath']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end() ?>

    <div id="gfmis-message" class="mt-3 d-none"></div>
</div>

<?php
$previewUrl = Url::to(['/am/import/preview-gfmis']);
$importUrl = Url::to(['/am/import/import-gfmis']);
$js = <<<JS
    function renderGfmisSummary(summary) {
        var items = [
            { label: 'รวมทั้งหมด', value: summary.total || 0, cls: 'primary' },
            { label: 'พร้อมอัปเดต', value: summary.ready || 0, cls: 'success' },
            { label: 'ค่าเดิม', value: summary.same || 0, cls: 'secondary' },
            { label: 'ข้อผิดพลาด', value: summary.error || 0, cls: 'danger' }
        ];

        var html = '';
        items.forEach(function(item) {
            html += '<div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-3">';
            html += '<div class="text-muted small">' + item.label + '</div>';
            html += '<div class="fs-4 fw-bold text-' + item.cls + '">' + item.value + '</div>';
            html += '</div></div></div>';
        });
        $('#summary-box').html(html);
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function renderGfmisPreview(res) {
        var rows = res.rows || [];
        var summary = res.summary || {};
        renderGfmisSummary(summary);

        var html = '<div class="table-responsive" style="max-height: 420px; overflow: auto;">';
        html += '<table class="table table-sm table-bordered align-middle mb-0">';
        html += '<thead class="table-light sticky-top"><tr>';
        (res.headers || []).forEach(function(h) {
            html += '<th class="small text-nowrap">' + escapeHtml(h || '') + '</th>';
        });
        html += '</tr></thead><tbody>';

        rows.forEach(function(row, idx) {
            var badgeClass = 'bg-secondary';
            if (row.status_key === 'update') badgeClass = 'bg-success';
            if (row.status_key === 'same') badgeClass = 'bg-secondary';
            if (row.status_key === 'error') badgeClass = 'bg-danger';
            var errorText = '';
            if (row.errors) {
                var parts = [];
                for (var attr in row.errors) {
                    parts.push((row.errors[attr] || []).join(', '));
                }
                errorText = parts.join('; ');
            }
            html += '<tr' + (row.status_key === 'error' ? ' class="table-danger"' : '') + '>';
            html += '<td class="text-center small">' + escapeHtml(idx + 1) + '</td>';
            html += '<td class="small text-nowrap">' + escapeHtml(row.code || '') + '</td>';
            html += '<td class="small">' + escapeHtml(row.asset_name || '-') + '</td>';
            html += '<td class="small text-nowrap">' + escapeHtml(row.current_gfmis || '-') + '</td>';
            html += '<td class="small text-nowrap">' + escapeHtml(row.new_gfmis || '-') + '</td>';
            html += '<td class="small">';
            html += '<span class="badge ' + badgeClass + ' rounded-pill">' + escapeHtml(row.status_label || '-') + '</span>';
            if (errorText) {
                html += '<div class="text-danger small mt-1">' + escapeHtml(errorText) + '</div>';
            }
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $('#preview-table').html(html);
    }

    $('#xlsxFile').on('change', function() {
        var file = this.files[0];
        if (!file) return;

        var formData = new FormData();
        formData.append('xlsxFile', file);

        $('#gfmis-message').addClass('d-none').empty();
        $('#import-btn').addClass('d-none');
        $('#preview-table').empty();

        $.ajax({
            url: '{$previewUrl}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl").addClass('modal-xl');
                    $('#filePath').val(res.filePath || '');
                    renderGfmisPreview(res);

                    if ((res.errors || []).length > 0) {
                        var html = '<div class="alert alert-danger border-0 shadow-sm mb-0"><div class="fw-semibold mb-2">พบข้อผิดพลาดในไฟล์</div><ul class="mb-0 ps-3">';
                        (res.errors || []).forEach(function(err) {
                            var messages = [];
                            for (var attr in err.errors) {
                                messages.push((err.errors[attr] || []).join(', '));
                            }
                            html += '<li>แถว ' + err.row + ' (รหัส: ' + (err.code || '-') + '): ' + messages.join('; ') + '</li>';
                        });
                        html += '</ul></div>';
                        $('#gfmis-message').removeClass('d-none').html(html);
                    } else {
                        $('#import-btn').removeClass('d-none');
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

    $('body').on('beforeSubmit', '#gfmis-upload-form', function (e) {
        e.preventDefault();
        var filePath = $('#filePath').val();
        if (!filePath) {
            Swal.fire('ผิดพลาด', 'ไม่พบไฟล์', 'error');
            return false;
        }

        if ($(this).find('.has-error').length) {
            return false;
        }

        Swal.fire({
            title: 'ยืนยันการอัปเดต GFMIS?',
            text: 'ระบบจะอัปเดตเฉพาะรายการที่หมายเลขครุภัณฑ์ (code) ตรงกัน',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'กำลังอัปเดต...',
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
                        Swal.fire('สำเร็จ', res.message || 'อัปเดต GFMIS เรียบร้อย', 'success').then(function() {
                            window.location.reload(true);
                        });
                    } else if (res.status === 'error' && res.errors) {
                        let html = '<ul style="text-align:left;">';
                        res.errors.forEach(function(err){
                            let messages = [];
                            for (let attr in err.errors) {
                                messages.push((err.errors[attr] || []).join(', '));
                            }
                            html += '<li>แถว ' + err.row + ' (รหัส: ' + (err.code || '-') + '): ' + messages.join('; ') + '</li>';
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
