<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'ตั้งค่าแบบฟอร์มขอใช้รถยนต์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php $this->beginBlock('sub-title'); ?>
ทะเบียนใช้รถยนต์ทั่วไป
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('menu',['active' => 'setting'])?>
<?php $this->endBlock(); ?>



<div class="d-flex">
    <div>

        <!-- Canvas สำหรับ PDF (พื้นหลัง) -->
<canvas id="pdfCanvas" style="position:absolute; z-index:0;"></canvas>

<!-- Canvas สำหรับ Fabric.js (overlay) -->
<canvas id="fabricCanvas" style="position:absolute; z-index:1;"></canvas>

<!-- Container -->
<div id="canvasContainer" style="position: relative;"></div>

    </div>
    <div class="container">
        <div class="d-grid gap-2 mx-auto">
             <div class="position-relative">
                        <div class="file-upload-btnxx btn btn-primary shadow rounded-pill">
                            <i class="fa-solid fa-upload"></i>
                            <span>คลิกอัปโหลดไฟล์ที่นี่</span>
                        </div>
                        <input type="file" class="file-upload-input" id="my_file" accept="application/image">
                    </div>

        </div>

        <button id="downloadPDF" class="btn btn-danger">ดาวน์โหลด PDF</button>

        <button id="exportLayout">บันทึก Layout</button>
        <pre id="output"></pre>

    </div>
</div>
<?php
$ref = Yii::$app->request->get('ref', 0);
$urlUpload = Url::to('/filemanager/uploads/upload-pdf');
// $file = Yii::$app->request->get('file_name');
$file = 'vehicle_form.pdf';

$jsModelData = json_encode([
    'full_name' => 'นายสมชาย ทดสอบระบบ',
    'date' => '25/10/20568',
], JSON_UNESCAPED_UNICODE);

$formName = 'leave-request-form';
$urlGetLayout = Url::to(['/7/booking/vehicle/form-layouts/get-layout', 'formName' => $formName]);

$pdfFileUrl = Url::to("@web/files/$file", true); // PDF ที่จะโหลดจากเซิร์ฟเวอร์

$js = <<<JS
window.modelData = $jsModelData;

const pdfCanvas = document.getElementById('pdfCanvas');
const fabricCanvasEl = document.getElementById('fabricCanvas');
const container = document.getElementById('canvasContainer');
container.appendChild(pdfCanvas);
container.appendChild(fabricCanvasEl);

let fabricCanvas = new fabric.Canvas('fabricCanvas');
let scale = 1.5;

// โหลด PDF ด้วย PDF.js
pdfjsLib.getDocument('$pdfFileUrl').promise.then(pdf => {
    return pdf.getPage(1);
}).then(page => {
    const viewport = page.getViewport({ scale });
    pdfCanvas.height = viewport.height;
    pdfCanvas.width = viewport.width;

    fabricCanvasEl.height = viewport.height;
    fabricCanvasEl.width = viewport.width;

    fabricCanvas.setHeight(viewport.height);
    fabricCanvas.setWidth(viewport.width);

    return page.render({
        canvasContext: pdfCanvas.getContext('2d'),
        viewport: viewport
    }).promise;
}).then(() => {
    // โหลด layout และวางข้อความลงบน Fabric canvas
    fetch('$urlGetLayout')
        .then(res => res.json())
        .then(layout => {
            if (Array.isArray(layout)) {
                layout.forEach(obj => {
                    let content = window.modelData[obj.field] ?? `{{\${obj.field}}}`;
                    const text = new fabric.Text(content, {
                        left: obj.x,
                        top: obj.y,
                        fontSize: obj.fontSize,
                        fill: 'black'
                    });
                    fabricCanvas.add(text);
                });
            } else {
                console.warn("Layout not found or invalid");
            }
        });
});

// ดาวน์โหลด PDF (ภาพรวมจาก Fabric Canvas รวมกับ PDF)
$('#downloadPDF').on('click', function () {
    const mergedCanvas = document.createElement('canvas');
    mergedCanvas.width = pdfCanvas.width;
    mergedCanvas.height = pdfCanvas.height;
    const ctx = mergedCanvas.getContext('2d');

    ctx.drawImage(pdfCanvas, 0, 0);
    ctx.drawImage(fabricCanvasEl, 0, 0);

    const finalImage = mergedCanvas.toDataURL('image/png');

    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p', 'pt', 'a4');

    const imgProps = pdf.getImageProperties(finalImage);
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

    pdf.addImage(finalImage, 'PNG', 0, 0, pdfWidth, pdfHeight);
    pdf.save('filled-form.pdf');
});

// บันทึก Layout
$('#exportLayout').on('click', function () {
    const objects = fabricCanvas.getObjects('text');
    const layout = objects.map(obj => ({
        field: obj.text.replace(/[{}]/g, ''),
        x: obj.left,
        y: obj.top,
        fontSize: obj.fontSize
    }));

    $('#output').text(JSON.stringify(layout, null, 2));

    $.post('/formtemplate/save-layout', {
        layout: layout,
        form_name: '$formName'
    }, function (res) {
        if (res.success) {
            alert('บันทึก Layout เรียบร้อยแล้ว');
        } else {
            alert('ไม่สามารถบันทึก Layout ได้: ' + JSON.stringify(res.message));
        }
    });
});

// อัปโหลด PDF
$('#my_file').on('change', function (e) {
    const file = this.files[0];
    if (!file) return;

    if (file.type !== 'application/pdf') {
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: 'กรุณาเลือกไฟล์ PDF เท่านั้น'
        });
        $(this).val('');
        return;
    }

    Swal.fire({
        title: 'ยืนยันการอัปโหลด?',
        text: 'คุณต้องการอัปโหลดไฟล์ PDF นี้หรือไม่',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ใช่, อัปโหลด',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
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
                    Swal.fire({
                        icon: 'success',
                        title: 'อัปโหลดสำเร็จ',
                        showConfirmButton: false,
                        timer: 1200
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถอัปโหลดไฟล์ได้'
                    });
                }
            });
        } else {
            $('#my_file').val('');
        }
    });
});
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>


