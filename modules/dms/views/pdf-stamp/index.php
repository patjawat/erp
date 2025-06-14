
<?php
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js', ['position' => \yii\web\View::POS_HEAD]);
?>

<div class="row">
<div class="col-8">
    <canvas id="pdfCanvas"></canvas>
</div>
<div class="col-4">
<!-- <input type="text" id="stampText" placeholder="ใส่ข้อความตราประทับ">
<button id="saveStamp">บันทึก</button> -->

<input type="text" id="receiptNumber" placeholder="รับที่ : เช่น 1204/2567" />
<input type="text" id="date" placeholder="วันที่ : เช่น 14 มิ.ย. 2567" />
<input type="text" id="time" placeholder="เวลา : เช่น 09:45 น." />
<button id="saveStamp">บันทึกตราประทับ</button>



</div>
</div>




<?php
$js = <<< JS
const canvas = new fabric.Canvas('pdfCanvas');
canvas.setHeight(800);
canvas.setWidth(1000);
canvas.setZoom(1);

function loadPDF() {
    const url = '/pdfs/document2.pdf';
    pdfjsLib.getDocument(url).promise.then(pdf => {
        pdf.getPage(1).then(page => {
            const viewport = page.getViewport({ scale: 1.5 });
            const tempCanvas = document.createElement('canvas');
            const context = tempCanvas.getContext('2d');
            tempCanvas.height = viewport.height;
            tempCanvas.width = viewport.width;

            page.render({
                canvasContext: context,
                viewport: viewport
            }).promise.then(() => {
                const img = new fabric.Image(tempCanvas, {
                    selectable: false
                });
                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
            });
        });
    });
}

let stampGroup = null;

// ฟังก์ชันสร้างกลุ่มตราประทับแบบราชการ
function addGovernmentStamp(receiptNumber, date, time) {
    if(stampGroup) {
        canvas.remove(stampGroup);
    }

        // สร้างกล่องพื้นหลัง (Rect) มี border, border-radius
    const background = new fabric.Rect({
        left: 0,
        top: 0,
        width: 280,
        height: 110,
        fill: 'white',
        stroke: 'black',
        strokeWidth: 2,
        rx: 10,   // border-radius x
        ry: 10    // border-radius y
    });
    
    const texts = [
        { text: 'โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย', fontSize: 18, top: 0, fontWeight: 'bold' },
        { text: 'รับที่ : ' + receiptNumber, fontSize: 16, top: 30 },
        { text: 'วันที่ : ' + date, fontSize: 16, top: 55 },
        { text: 'เวลา : ' + time, fontSize: 16, top: 80 },
    ];

    const objects = texts.map(t => new fabric.Text(t.text, {
        left: 10,
        top: t.top,
        fontSize: t.fontSize,
        fill: 'black',
        fontWeight: t.fontWeight || 'normal',
        fontFamily: 'TH Sarabun New'
    }));

    stampGroup = new fabric.Group([background, ...objects], {
        left: 750,
        top: 20,
        hasControls: true,
        lockScalingFlip: true,
        borderColor: 'gray',
        cornerColor: 'blue',
        cornerSize: 8,
    });

        // รวม background + ข้อความ เป็นกลุ่มเดียวกัน
    // stampGroup = new fabric.Group([background, ...textObjects], {
    //     left: 750,
    //     top: 20,
    //     hasControls: true,
    //     lockScalingFlip: true,
    //     borderColor: 'gray',
    //     cornerColor: 'blue',
    //     cornerSize: 8,
    // });
    

    canvas.add(stampGroup);
    canvas.setActiveObject(stampGroup);
    canvas.renderAll();
}

// โหลด PDF ตอนเริ่มต้น
loadPDF();

// ดักจับ event จาก input form
$('#receiptNumber, #date, #time').on('input', function() {
    const receiptNumber = $('#receiptNumber').val() || '1204/2567';
    const date = $('#date').val() || '14 มิ.ย. 2567';
    const time = $('#time').val() || '09:45 น.';
    addGovernmentStamp(receiptNumber, date, time);
});




function saveStamp() {
    if (!stampGroup) {
        alert('กรุณาเพิ่มตราประทับก่อน');
        return;
    }

    // เก็บข้อมูลข้อความทั้งหมดในกลุ่ม
    const objects = stampGroup._objects;
    // เราจะดึงข้อความทั้งหมดมาเป็น array
    const texts = objects
        .filter(obj => obj.type === 'text')
        .map(obj => obj.text);

    // ข้อมูลตำแหน่งกลุ่มตราประทับบน canvas
    const data = {
        texts: texts,
        left: stampGroup.left,
        top: stampGroup.top,
        scaleX: stampGroup.scaleX,
        scaleY: stampGroup.scaleY,
        angle: stampGroup.angle
    };
    console.log(data);
    

    $.ajax({
        url: '/dms/pdf-stamp/stamp-save',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(response) {
            alert('บันทึกตราประทับเรียบร้อยแล้ว');
        },
        error: function(err) {
            alert('เกิดข้อผิดพลาดขณะบันทึก');
            console.error(err);
        }
    });
}


$('#saveStamp').on('click', function() {

    saveStamp();
    
 });

JS;

$this->registerJS($js);
?>

