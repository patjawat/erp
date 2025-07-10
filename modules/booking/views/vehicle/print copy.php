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
            <div class="ms-5 mt-5">
                <div class="conteiner">

                
                        <br>
                        <button id="addName">เพิ่มชื่อ</button>
                        <button id="addDate">เพิ่มวันที่</button>
                        <button id="exportLayout">บันทึก Layout</button>
                        <pre id="output"></pre>


                        <button id="downloadPDF">ดาวน์โหลด PDF</button>


                </div>
            </div>
                

    </div>

<?php
$fullname = $model->employee->fullname;

$fullname = $model->employee->fullname;
$date = Yii::$app->formatter->asDate($model->date, 'php:d/m/Y');

$jsModelData = json_encode([
    'full_name' => $fullname,
    'date' => $date,
], JSON_UNESCAPED_UNICODE);

$templateUrl = Yii::getAlias("@web/document-template/leave.png");
$js = <<< JS
    let canvas = new fabric.Canvas('pdfCanvas');

    // Load background (รูปแบบฟอร์มราชการ)
    fabric.Image.fromURL('$templateUrl', function(img) {
        img.scaleToWidth(canvas.width);
        img.selectable = false;
        canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
    });

    // เพิ่ม field ตัวอย่าง
    $('#addName').on('click', function() {
        const text = new fabric.Text('$fullname', {
            left: 275,
            top: 216,
            fontSize: 15,
            fill: 'black'
        });
        canvas.add(text);
    });

    $('#addDate').on('click', function() {
        const text = new fabric.Text('{{date}}', {
            left: 100,
            top: 150,
            fontSize: 18,
            fill: 'black'
        });
        canvas.add(text);
    });

    // Export Layout
    $('#exportLayout').on('click', function() {
        const objects = canvas.getObjects('text');
        const layout = objects.map(obj => ({
            field: obj.text.replace(/[{}]/g, ''),
            x: obj.left,
            y: obj.top,
            fontSize: obj.fontSize
        }));
        $('#output').text(JSON.stringify(layout, null, 2));
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