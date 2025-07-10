<?php

use app\models\Categorise;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'แบบฟอร์มใบขอใช้รถ';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="d-flex">
    <canvas id="pdfCanvas" width="794" height="1123" style="border:1px solid #ccc"></canvas>
    <div class="">
<button id="downloadPDF" class="btn btn-danger">ดาวน์โหลด PDF</button>
</div>


</div>

<?php

$ref = $model->ref;
$templateUrl = $model->getTemplate();
$urlUpload = Url::to('/filemanager/uploads/upload-pdf');
$formName = 'vehicle_layout_form'; // ชื่อแบบฟอร์มที่ใช้สำหรับการจัดเก็บ layout
$urlGetLayout = Url::to(['/booking/vehicle-form-layout/get-layout', 'formName' => $formName]);

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



$js = <<< JS

    window.modelData = $jsModelData;
    let canvas = new fabric.Canvas('pdfCanvas');
    // Load PDF as background using pdf.js
    pdfjsLib.getDocument('$templateUrl').promise.then(function(pdf) {
        pdf.getPage(1).then(function(page) {
            var viewport = page.getViewport({ scale: 1.1 });
            var canvasEl = document.createElement('canvas');
            var context = canvasEl.getContext('2d');
            canvasEl.width = viewport.width;
            canvasEl.height = viewport.height;

            var renderContext = {
                canvasContext: context,
                viewport: viewport
            };

            page.render(renderContext).promise.then(function() {
                var imgData = canvasEl.toDataURL('image/png');
                fabric.Image.fromURL(imgData, function(img) {
                    img.scaleToWidth(canvas.width);
                    img.selectable = false;
                    canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
                });
            });
        });
    });

    // จำกัดการ move ให้อยู่ในกรอบ canvas
    canvas.on('object:moving', function(e) {
        var obj = e.target;
        // ขนาดของ object
        var objWidth = obj.getScaledWidth();
        var objHeight = obj.getScaledHeight();

        // ขนาดของ canvas
        var canvasWidth = canvas.getWidth();
        var canvasHeight = canvas.getHeight();

        // จำกัดขอบซ้ายและบน
        if (obj.left < 0) obj.left = 0;
        if (obj.top < 0) obj.top = 0;

        // จำกัดขอบขวาและล่าง
        if (obj.left + objWidth > canvasWidth) obj.left = canvasWidth - objWidth;
        if (obj.top + objHeight > canvasHeight) obj.top = canvasHeight - objHeight;
    });
    
    getLayout()
function getLayout() {
    $.ajax({
        type: "get",
        url: "$urlGetLayout",
        dataType: "json",
        success: function (res) {
            res.forEach(layout => {
                const field = layout.field;
                const value = window.modelData[field];

                // กลุ่มฟิลด์ที่เป็นรูปภาพ
                const imageFields = ['emp_signature', 'leader_signature', 'driver_signature', 'director_signature'];

                // ถ้าเป็นฟิลด์ภาพลายเซ็น
                if (imageFields.includes(field)) {
                    const url = value;

                    if (url) {
                        // console.log(`✔ Loading image field: \${field} from \${url}`);

                        fabric.Image.fromURL(url, function(img) {
                            const scale = layout.scale || 0.3;

                            img.set({
                                left: layout.x * (typeof scaleUsed !== 'undefined' ? scaleUsed : 1),
                                top: layout.y,
                                scaleX: scale,
                                scaleY: scale,
                                selectable: true,
                                hasControls: true,
                                hasBorders: true,
                                evented: true,
                                hoverCursor: 'move',

                                field: field // เก็บชื่อฟิลด์ไว้ใน object
                            });

                            canvas.add(img);
                        }, { crossOrigin: 'anonymous' });
                    } else {
                        // console.warn(`⚠ Image URL for field "\${field}" not found`);
                    }
                    return; // ข้ามการเพิ่มข้อความ
                }

                // ถ้าเป็นข้อความปกติ
                if (value !== undefined) {
                    const text = new fabric.Text(value, {
                        left: layout.x,
                        top: layout.y,
                        fontSize: layout.fontSize || 20,
                        fontFamily: layout.fontFamily || 'TH Sarabun New',
                        fontWeight: layout.fontWeight || 'bold',
                        scaleX: layout.scale || 1,
                        scaleY: layout.scale || 1,
                        fill: 'black',

                        field: field // เก็บชื่อฟิลด์ไว้ใน object (เหมือนกัน)
                    });

                    canvas.add(text);
                    // console.log(`✔ Added "\${field}" with value: \${value}`);
                } else {
                    // console.warn(`⚠ No value found in modelData for field "\${field}"`);
                }
            });
        },
        error: function (xhr, status, error) {
            console.error("❌ Error loading layout:", status, error);
        }
    });
}


    $('#addDate').on('click', function() {
        const text = new fabric.Text('{{date}}', {
            left: 100,
            top: 150,
            fontSize: 18,
            fill: 'black'
        });
        canvas.add(text);
    });

    $('#downloadPDF').on('click', function() {
        // แปลง Canvas เป็น Image
        const dataURL = canvas.toDataURL({
            format: 'png',
            multiplier: 2  // เพิ่มความละเอียด
        });

        // ใช้ jsPDF
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'pt', 'a4'); // portrait, points, A4

        // โหลดภาพลง PDF
        const imgProps = pdf.getImageProperties(dataURL);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

        pdf.addImage(dataURL, 'PNG', 0, 0, pdfWidth, pdfHeight);

        // บันทึก
        pdf.save('form-layout.pdf');
    });


JS;
$this->registerJS($js, \yii\web\View::POS_END);
?>

</div>