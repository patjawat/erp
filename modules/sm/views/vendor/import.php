<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
$this->title = 'นำเข้า Vendor (ผู้แทนจำหน่าย)';
?>
<div class="vendor-import-modal">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <?= Html::a(
            '<i class="bi bi-table me-2"></i> ดาวน์โหลด Template (Google Sheet)',
            'https://docs.google.com/spreadsheets/d/1ofAIy6K0JG1zm2FZO9w42wPx9-2LD1rLxb5NZOt5iL0/edit?usp=sharing',
            ['class' => 'btn btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener']
        ) ?>
        <small class="text-muted">รองรับ .csv และ .xlsx (คอลัมน์: รหัสตัวแทนจำหน่าย, ชื่อผู้แทนจำหน่าย, ชื่อผู้ติดต่อ, โทรศัพท์, อีเมล, ที่อยู่, เลขประจำตัวผู้เสียภาษี, สถานะ, ชื่อบัญชี, เลขบัญชี, ชื่อธนาคาร, ตำแหน่งผู้ติดต่อ, แฟกซ์)</small>
    </div>

    <div class="card border border-2 border-primary border-opacity-25 mb-3" id="drop-zone">
        <div class="card-body text-center py-5">
            <input type="file" id="importFile" name="importFile" accept=".csv,.xlsx,.xls" class="d-none">
            <p class="mb-2 text-muted"><i class="bi bi-cloud-arrow-up fs-3"></i></p>
            <p class="mb-2 fw-medium">ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</p>
            <p class="mb-0 small text-muted" id="fileName"></p>
            <p class="mb-0 mt-2 d-none text-primary" id="loadingText"><span class="spinner-border spinner-border-sm me-1"></span> กำลังโหลด...</p>
        </div>
    </div>

    <div id="preview-section" class="d-none">
        <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">ทั้งหมด: <span id="statTotal">0</span></span>
            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">ผ่าน: <span id="statValid">0</span></span>
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1">ไม่ผ่าน: <span id="statError">0</span></span>
            <?= Html::button('<i class="bi bi-download me-1"></i> ส่งออกแถวที่ error', [
                'class' => 'btn btn-sm btn-outline-danger',
                'id' => 'btnExportErrors',
                'style' => 'display:none',
            ]) ?>
        </div>
        <div class="table-responsive" style="max-height: 320px; overflow: auto;">
            <table class="table table-sm table-hover align-middle mb-0" id="previewTable">
                <thead class="bg-body-tertiary sticky-top">
                    <tr>
                        <th class="text-center" style="width: 50px;">ลำดับ</th>
                        <th>รหัส</th>
                        <th>ชื่อ</th>
                        <th>ผู้ติดต่อ</th>
                        <th>โทรศัพท์</th>
                        <th>อีเมล</th>
                        <th>สถานะ</th>
                        <th class="text-center" style="width: 120px;">ข้อผิดพลาด</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider align-middle" id="previewTableBody"></tbody>
            </table>
        </div>
        <div class="mt-3 d-flex justify-content-center">
            <?= Html::button('<i class="bi bi-box-arrow-in-down me-2"></i> นำเข้าข้อมูล', [
                'class' => 'btn btn-success',
                'id' => 'btnDoImport',
                'disabled' => true,
            ]) ?>
        </div>
    </div>

    <input type="hidden" id="importFilePath" name="filePath" value="">
    <input type="hidden" id="importErrorRows" name="errorRows" value="">
</div>

<?php
$previewUrl = Url::to(['/sm/vendor/preview']);
$doImportUrl = Url::to(['/sm/vendor/do-import']);
$exportErrorsUrl = Url::to(['/sm/vendor/export-errors']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$js = <<<JS
(function() {
    var filePath = '';
    var previewData = null;

    $('#drop-zone').on('click', function(e) {
        if (!$(e.target).is('input')) $('#importFile').trigger('click');
    });
    $('#drop-zone').on('dragover dragenter', function(e) {
        e.preventDefault(); e.stopPropagation();
        $(this).addClass('border-primary');
    });
    $('#drop-zone').on('dragleave drop', function(e) {
        e.preventDefault(); e.stopPropagation();
        $(this).removeClass('border-primary');
        if (e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files.length) {
            var input = document.getElementById('importFile');
            input.files = e.originalEvent.dataTransfer.files;
            $('#fileName').text(input.files[0].name);
            $('#preview-section').addClass('d-none');
            filePath = '';
            loadPreview();
        }
    });
    $('#importFile').on('change', onFileSelected);

    function onFileSelected() {
        var f = document.getElementById('importFile').files[0];
        if (!f) return;
        $('#fileName').text(f.name);
        $('#preview-section').addClass('d-none');
        $('#btnDoImport').prop('disabled', true);
        filePath = '';
        loadPreview();
    }

    function loadPreview() {
        var f = document.getElementById('importFile').files[0];
        if (!f) return;
        var formData = new FormData();
        formData.append('importFile', f);
        $('#loadingText').removeClass('d-none');
        $.ajax({
            url: '{$previewUrl}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#loadingText').addClass('d-none');
                if (res.status === 'error') {
                    if (typeof Swal !== 'undefined') Swal.fire('ผิดพลาด', res.message || 'โหลดไม่สำเร็จ', 'error');
                    else alert(res.message || 'โหลดไม่สำเร็จ');
                    return;
                }
                filePath = res.filePath || '';
                previewData = res;
                renderPreview(res);
                $('#preview-section').removeClass('d-none');
                var canImport = res.error === 0;
                $('#btnDoImport').prop('disabled', !canImport);
                if (res.error > 0) $('#btnExportErrors').show(); else $('#btnExportErrors').hide();
            },
            error: function() {
                $('#loadingText').addClass('d-none');
                if (typeof Swal !== 'undefined') Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                else alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
            }
        });
    }

    function renderPreview(res) {
        $('#statTotal').text(res.rows ? res.rows.length : 0);
        $('#statValid').text(res.valid || 0);
        $('#statError').text(res.error || 0);
        var tbody = $('#previewTableBody').empty();
        if (!res.rows || !res.rows.length) return;
        var errorRows = [];
        res.rows.forEach(function(item) {
            var row = item.row || {};
            var errors = item.errors || [];
            var valid = item.valid;
            if (!valid) errorRows.push(item);
            var trClass = valid ? '' : 'table-danger';
            var statusHtml = valid
                ? '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">ผ่าน</span>'
                : '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1">ไม่ผ่าน</span>';
            var errHtml = errors.length ? '<span title="' + errors.join('\\n') + '">' + errors.join('; ') + '</span>' : '-';
            tr = '<tr class="' + trClass + '">' +
                '<td class="text-center">' + (item.rowNumber || '') + '</td>' +
                '<td>' + (row.vendor_code || '') + '</td>' +
                '<td>' + (row.vendor_name || '') + '</td>' +
                '<td>' + (row.contact_name || '') + '</td>' +
                '<td>' + (row.phone || '') + '</td>' +
                '<td>' + (row.email || '') + '</td>' +
                '<td>' + (row.status || '') + '</td>' +
                '<td class="small text-danger">' + errHtml + '</td></tr>';
            tbody.append(tr);
        });
        $('#importErrorRows').val(JSON.stringify(errorRows));
    }

    $('#btnDoImport').on('click', function() {
        if (!filePath) return;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการนำเข้า?',
                text: 'เมื่อยืนยันแล้วจะบันทึกเฉพาะแถวที่ผ่านการตรวจสอบ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            }).then(function(result) {
                if (result.isConfirmed) doImport();
            });
        } else {
            if (confirm('ยืนยันการนำเข้า?')) doImport();
        }
    });

    function doImport() {
        $('#btnDoImport').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> กำลังนำเข้า...');
        $.ajax({
            url: '{$doImportUrl}',
            type: 'POST',
            data: { filePath: filePath, '{$csrfParam}': '{$csrfToken}' },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('สำเร็จ', res.message || 'นำเข้าข้อมูลเรียบร้อย', 'success').then(function() {
                            if (typeof closeModal === 'function') closeModal();
                            if (typeof $.pjax !== 'undefined') $.pjax.reload({ container: '#sm-container', timeout: 5000 });
                            else window.location.reload();
                        });
                    } else {
                        alert(res.message || 'นำเข้าข้อมูลเรียบร้อย');
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('ผิดพลาด', res.message || 'นำเข้าไม่สำเร็จ', 'error');
                    else alert(res.message || 'นำเข้าไม่สำเร็จ');
                    $('#btnDoImport').prop('disabled', false).html('<i class="bi bi-box-arrow-in-down me-2"></i> นำเข้าข้อมูล');
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                else alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
                $('#btnDoImport').prop('disabled', false).html('<i class="bi bi-box-arrow-in-down me-2"></i> นำเข้าข้อมูล');
            }
        });
    }

    $('#btnExportErrors').on('click', function() {
        var errorRows = $('#importErrorRows').val();
        if (!errorRows) return;
        var form = $('<form method="post" action="{$exportErrorsUrl}"></form>');
        form.append($('<input>').attr({ type: 'hidden', name: 'filePath', value: filePath }));
        form.append($('<input>').attr({ type: 'hidden', name: 'errorRows', value: errorRows }));
        form.append($('<input>').attr({ type: 'hidden', name: '{$csrfParam}', value: '{$csrfToken}' }));
        $('body').append(form);
        form.submit();
        form.remove();
    });
})();
JS;
$this->registerJs($js);
?>
