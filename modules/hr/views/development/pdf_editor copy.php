<style>
    .main-header {
        background: #ffffff;
        border-bottom: 1px solid #dee2e6;
        padding: 10px 20px;
    }

    /* sidebar-panel สไตล์ Yii2 Admin */
    .sidebar-panel {
        background: #ffffff;
        border-left: 1px solid #dee2e6;
        height: calc(100vh - 60px);
        overflow-y: auto;
        padding: 20px;
    }

    /* พื้นที่วาง PDF */
    .canvas-area {
        height: calc(100vh - 60px);
        overflow: auto;
        padding: 40px;
        display: flex;
        justify-content: center;
        background: #525659;
        /* สีพื้นหลังแบบ Google PDF Viewer */
    }

    #pdf-render-container {
        position: relative;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
        background: white;
    }

    canvas {
        display: block;
    }

    /* สไตล์ Label ที่ลากได้ */
    .draggable-label {
        position: absolute;
        padding: 2px 6px;
        background: rgba(255, 255, 0, 0.4);
        /* สีเหลืองไฮไลท์ */
        border: 1px dashed #ff9800;
        color: #000;
        cursor: move;
        font-size: 13px;
        white-space: nowrap;
        z-index: 100;
        user-select: none;
    }

    .draggable-label.active {
        background: rgba(13, 110, 253, 0.3);
        border: 1px solid #0d6efd;
        box-shadow: 0 0 5px rgba(13, 110, 253, 0.5);
    }

    /* ส่วน Upload */
    .upload-zone {
        border: 2px dashed #0d6efd;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        background: #f8f9fa;
        margin-bottom: 20px;
        transition: 0.3s;
    }

    .upload-zone:hover {
        background: #e9ecef;
    }

    .field-card {
        border-radius: 8px;
        margin-bottom: 12px;
        transition: 0.2s;
    }

    .field-card:hover {
        border-color: #0d6efd;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <strong class="text-primary me-3">Layout Designer</strong>
                        <span class="badge bg-secondary" id="file-name-display">ยังไม่ได้เลือกไฟล์</span>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary btn-sm me-2" onclick="location.reload()">ล้างข้อมูล</button>
                        <button class="btn btn-primary btn-sm px-4" id="btn-save-all">บันทึกตำแหน่งลงฐานข้อมูล</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ฝั่งซ้าย: PDF Preview Area -->
    <div class="col-md-9 canvas-area">
        <div id="pdf-render-container">
            <canvas id="pdf-canvas"></canvas>
            <!-- Labels จะถูกสร้างขึ้นที่นี่ด้วย JS -->
            <div id="labels-layer"></div>
        </div>
    </div>

    <!-- ฝั่งขวา: Config sidebar-panel -->
    <div class="col-md-3 sidebar-panel">
        <!-- 1. ส่วนอัปโหลด -->
        <div class="upload-zone">
            <h6>1. อัปโหลดเทมเพลต PDF</h6>
            <input type="file" id="pdf-upload" class="form-control form-control-sm" accept="application/pdf">
            <p class="small text-muted mt-2 mb-0">เลือกไฟล์ PDF เพื่อกำหนดตำแหน่งพิกัด</p>
        </div>

        <!-- 2. รายการฟิลด์ -->
        <h6>2. กำหนดตำแหน่งฟิลด์</h6>
        <div id="fields-list">
            <!-- Field Item: ส่วนราชการ -->
            <div class="card field-card shadow-sm" data-field="dept_name">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold">ส่วนราชการ</span>
                        <span class="badge bg-light text-dark border" style="font-size: 10px;">ID: dept_name</span>
                    </div>
                    <div class="row g-1">
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">X</span>
                                <input type="number" class="form-control coord-x" value="50">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Y</span>
                                <input type="number" class="form-control coord-y" value="50">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Field Item: เลขที่ส่งซ่อม -->
            <div class="card field-card shadow-sm" data-field="repair_id">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold">เลขที่ส่งซ่อม</span>
                        <span class="badge bg-light text-dark border" style="font-size: 10px;">ID: repair_id</span>
                    </div>
                    <div class="row g-1">
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">X</span>
                                <input type="number" class="form-control coord-x" value="100">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Y</span>
                                <input type="number" class="form-control coord-y" value="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i> <b>คำแนะนำ:</b> ลาก Label สีเหลืองในหน้ากระดาษเพื่อปรับตำแหน่ง พิกัดจะอัปเดตอัตโนมัติ
        </div>
    </div>
</div>

<?php

use yii\helpers\Url;
// ลงทะเบียน JavaScript สำหรับ PDF.js และ jQuery UI (ใน Yii2 ควรใช้ AssetBundle)
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js');
$this->registerJsFile('https://code.jquery.com/ui/1.13.2/jquery-ui.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$saveUrl = Url::to(['/hr/development/save-coords']);
$js = <<<JS
    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    let pdfDoc = null;
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');

    // จัดการการอัปโหลดและแสดง PDF
    $('#pdf-upload').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function() {
                const typedarray = new Uint8Array(this.result);
                renderPDF(typedarray);
            };
            reader.readAsArrayBuffer(file);
        }
    });

    async function renderPDF(data) {
        pdfDoc = await pdfjsLib.getDocument(data).promise;
        const page = await pdfDoc.getPage(1);
        const viewport = page.getViewport({ scale: 1.5 });
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        await page.render({ canvasContext: ctx, viewport: viewport }).promise;
        initLabels();
    }

    function initLabels() {
        $('#labels-layer').empty();
        $('.field-card').each(function() {
            const id = $(this).data('field');
            const x = $(this).find('.coord-x').val();
            const y = $(this).find('.coord-y').val();
            const label = $('<div class="draggable-label"></div>')
                .text(id)
                .attr('id', 'lbl-' + id)
                .data('target', id)
                .css({ left: x + 'px', top: y + 'px' });
            $('#labels-layer').append(label);
        });
        
        $(".draggable-label").draggable({
            containment: "#pdf-render-container",
            stop: function(event, ui) {
                const id = $(this).data('target');
                const card = $('.field-card[data-field="' + id + '"]');
                card.find('.coord-x').val(Math.round(ui.position.left));
                card.find('.coord-y').val(Math.round(ui.position.top));
            }
        });
    }

    // ส่งข้อมูลบันทึกผ่าน AJAX พร้อม CSRF Token
    $('#btn-save').on('click', function() {
        const data = [];
        $('.field-card').each(function() {
            data.push({
                name: $(this).data('field'),
                x: $(this).find('.coord-x').val(),
                y: $(this).find('.coord-y').val()
            });
        });

        $.ajax({
            url: '{$saveUrl}',
            type: 'POST',
            data: {data: data},
            success: function(res) {
                alert(res.message);
            }
        });
    });
JS;
$this->registerJs($js);
?>