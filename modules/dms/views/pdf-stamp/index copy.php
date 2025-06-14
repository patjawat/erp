
<?php
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js', ['position' => \yii\web\View::POS_HEAD]);
?>

<div class="row">
<div class="col-8">
    <canvas id="pdfCanvas"></canvas>
</div>
<div class="col-4">
<input type="text" id="stampText" placeholder="ใส่ข้อความตราประทับ">
<button id="saveStamp">บันทึก</button>
</div>
</div>


<?php
$js = <<< JS
    
const canvas = new fabric.Canvas('pdfCanvas');
canvas.setHeight(800);
canvas.setWidth(1000);
canvas.setZoom(1);

let stampObj;

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

$('#stampText').on('input', function() {
    const text = $(this).val();
    if (stampObj) {
        stampObj.text = text;
        canvas.renderAll();
    } else {
        stampObj = new fabric.Text(text, {
            left: 800, // ขวาบน
            top: 20,
            fontSize: 20,
            fill: 'red',
            fontWeight: 'bold',
        });
        canvas.add(stampObj);
    }
});

$('#saveStamp').on('click', function() {
    const data = {
        text: stampObj.text,
        left: stampObj.left,
        top: stampObj.top
    };
  
    
    $.post('/dms/pdf-stamp/stamp-save', data, function(response) {
        alert('บันทึกแล้ว');
    });
});

loadPDF();

JS;
$this->registerJS($js);
?>
