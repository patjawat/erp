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


<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/booking/views/vehicle/menu',['active' => 'setting'])?>
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
                <input type="file" class="file-upload-input" id="my_file" accept="pdf/*">
            </div>

        </div>

        <button id="downloadPDF" class="btn btn-danger">ดาวน์โหลด PDF</button>

        <button id="exportLayout">บันทึก Layout</button>
        <pre id="output"></pre>

    </div>
</div>
<?php

$ref = $model->ref;
$urlUpload = Url::to('/filemanager/uploads/upload-pdf');
// $file = Yii::$app->request->get('file_name');
$file = 'vehicle_form.pdf';

$jsModelData = json_encode([
    'director' => 'ผู้อำนวยการโรงพยาบาล ERP',
    'fullname' => 'นายสมชาย ทดสอบระบบ',
    'fullname_' => 'นายสมชาย ทดสอบระบบ',
    'date' => '1 ตุลาคม 2568',
    'position' => 'นักวิชาการคอมพิวเตอร์',
    'department' =>'งานสารสนเทศ',
    'location' => 'มหาวิทยาลัยขอนแก่น',
    'passenger' => '2',
    'phone' => '0808188216',
    'reason' => 'เพื่อใช้ในการทดสอบระบบ',
    'date_start' => '1 ตุลาคม 2568',
    'date_end' => '2 ตุลาคม 2568',
    'time_start' => '08:00',
    'time_end' => '17:00',
    'vehicle_type' => 'รถยนต์ส่วนกลาง',
    'license_plate' => 'กข 1234 ขอนแก่น',
    'driver_name' => 'นายสมชาย ขับรถ',
    'driver_name_' => 'นายสมชาย ขับรถ',
    'leader_name' => 'นายหัวหน้า ผู้ขอใช้รถ',
    'driver_leader_name' => 'นายหัวหน้า พขร.',
    'mileage_start' => '10000',
    'mileage_end' => '10100',
     'emp_signature' => Yii::getAlias('@web') . '/images/signature.png',
    'leader_signature' => Yii::getAlias('@web') . '/images/signature.png',
    'driver_signature' => Yii::getAlias('@web') . '/images/signature.png',
    'director_signature' => Yii::getAlias('@web') . '/images/signature.png',

], JSON_UNESCAPED_UNICODE);

$formName = 'vehicle_layout_form'; // ชื่อแบบฟอร์มที่ใช้สำหรับการจัดเก็บ layout
$urlGetLayout = Url::to(['/booking/vehicle-form-layout/get-layout', 'formName' => $formName]);
$pdfFileUrl = Url::to(['/dms/documents/show', 'ref' => $model->ref]);

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
              const imageFields = ['emp_signature', 'leader_signature', 'driver_signature', 'director_signature'];

                layout.forEach(obj => {
                    // ถ้าเป็นลายเซ็น
                    if (imageFields.includes(obj.field)) {
                        const url = window.modelData[obj.field];
                        if (url) {
                            fabric.Image.fromURL(url, function(img) {
                                const scale = obj.scale || 0.3;
                                img.set({
                                        left: obj.x,
                                        top: obj.y,
                                        scaleX: scale,
                                        scaleY: scale,

                                        // เปิดการลาก/เลือก
                                        selectable: true,
                                        hasControls: true,
                                        hasBorders: true,
                                        evented: true,
                                        hoverCursor: 'move',  
                                         // ✅ ใส่ field ลงไปใน image object โดยตรง
                                        field: obj.field
                                        
                                    });
                                fabricCanvas.add(img);
                            }, { crossOrigin: 'anonymous' });
                        }
                        return; // ข้ามการวางข้อความ
                    }

                    // สำหรับ field ปกติ (ไม่ใช่ลายเซ็น)
                    let content = window.modelData[obj.field] ?? `{{\${obj.field}}}`;
                    const text = new fabric.Text(content, {
                        left: obj.x,
                        top: obj.y,
                        fontSize: obj.fontSize,
                        fill: 'black',
                        fontFamily: obj.fontFamily || 'Arial',
                        fontWeight: obj.fontWeight || 'normal'
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
    const log = fabricCanvas.getObjects();

    // ดึงทั้ง text และ image (signature) object
    const layout = fabricCanvas.getObjects().map(obj => {
        // ถ้าเป็น text
        if (obj.type === 'text') {
            // พยายามหา field name จาก modelData ที่ตรงกับค่า text
            let field = null;
            for (const key in window.modelData) {
                if (window.modelData[key] === obj.text) {
                    field = key;
                    break;
                }
            }
            // ถ้าไม่เจอ field ให้ใช้ข้อความเดิม
            if (!field) {
                field = obj.text.replace(/[{}]/g, '');
            }
            return {
                field: field,
                x: obj.left,
                y: obj.top,
                fontSize: obj.fontSize,
                fontFamily: obj.fontFamily || 'Arial',
                fontWeight: obj.fontWeight || 'normal'
            };
        }
        // ถ้าเป็นรูปภาพ (signature)
            if (obj.type === 'image') {
                return {
                    field: obj.field || 'signature', // ✅ ดึงจากค่าที่ฝังไว้ตอนสร้างภาพ
                    x: obj.left,
                    y: obj.top,
                    scale: obj.scaleX || 1
                };
            }
                    return null;
                }).filter(Boolean);

                $('#output').text(JSON.stringify(layout, null, 2));

                $.post('/booking/vehicle-form-layout/save-layout', {
                    layout: layout,
                    form_name: '$formName'
                }, function (res) {
                    if (res.success) {
                        alert('บันทึก Layout เรียบร้อยแล้ว');
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
            formData.append("$formName", file);
            formData.append("id", 1);
            formData.append("ref", '$ref');
            formData.append("name", '$formName');

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