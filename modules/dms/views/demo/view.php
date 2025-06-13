<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
</script>

<?php
// $this->registerJsFile('https://mozilla.github.io/pdf.js/build/pdf.js');
?>

<h3>ลากข้อความ “ตราประทับ” แล้ววางบน PDF</h3>
<div style="position: relative;">
    <canvas id="pdf-canvas"></canvas>
    <div id="stamp" contenteditable="true" style="position:absolute; top:50px; left:100px; color:red; background-color:transparent; font-weight: bold;">✔ Approved</div>
</div>

<br>
สี: <input type="color" id="colorPicker" value="#FF0000">
<button onclick="submitStamp()">บันทึก PDF พร้อมตราประทับ</button>
<?php echo $file;?>
<?php
use yii\web\View;
$js = <<< JS
const url = '/uploads/$file';

let pdfDoc = null,
    canvas = document.getElementById('pdf-canvas'),
    ctx = canvas.getContext('2d');

pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
    pdfDoc = pdfDoc_;
    return pdfDoc.getPage(1);
}).then(function(page) {
    const viewport = page.getViewport({scale: 1.5});
    canvas.height = viewport.height;
    canvas.width = viewport.width;

    return page.render({canvasContext: ctx, viewport: viewport}).promise;
});

$('#colorPicker').on('change', function(e) {
    $('#stamp').css('color', e.target.value);
});

function submitStamp() {
    const stamp = $('#stamp');
    const rect = stamp[0].getBoundingClientRect();
    const canvasRect = canvas.getBoundingClientRect();

    const x = rect.left - canvasRect.left;
    const y = rect.top - canvasRect.top;

    const data = {
        file: '$file',
        text: stamp.text(),
        color: stamp.css('color'),
        x: x,
        y: y
    };

    console.log(data);
    $.ajax({
        type: "POST",
        url: "/dms/demo/stamp",
        data: data,
        dataType: "json",
               xhrFields: {
            responseType: 'blob'
        },
    success: function(blob) {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = "stamped.pdf";
            a.click();
        }
    });

    // $.ajax({
    //     url: '/dms/demo/stamp',
    //     method: 'POST',
    //     contentType: 'json',
    //     data: {id:'111'},
    //     xhrFields: {
    //         responseType: 'blob'
    //     },
    //     success: function(blob) {
    //         const url = window.URL.createObjectURL(blob);
    //         const a = document.createElement('a');
    //         a.href = url;
    //         a.download = "stamped.pdf";
    //         a.click();
    //     }
    // });
}
JS;
$this->registerJS($js,View::POS_END);
?>
