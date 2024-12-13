<?php
use yii\web\View;
use yii\helpers\Url;

$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<div class="docx-preview">
    <h2>Document Preview</h2>
    <div id="docx-output"></div>

    <!-- ปุ่มพิมพ์ -->
    <button onclick="printDocument()">Print Document</button>
</div>

<?php
$urlPath = Url::to(['/msword/results/leave/LT3.docx'], true); // true for absolute URL
$js = <<< JS
fetch("$urlPath")
    .then(response => response.arrayBuffer()) // ดึงไฟล์เป็น arrayBuffer
    .then(arrayBuffer => {
        mammoth.convertToHtml({ arrayBuffer: arrayBuffer }) // แปลง arrayBuffer เป็น HTML
            .then(function(result){
                document.getElementById('docx-output').innerHTML = result.value; // แสดงผล HTML ที่แปลงได้
            })
            .catch(function(error) {
                console.error("Error converting DOCX to HTML:", error);
            });
    })
    .catch(function(error) {
        console.error("Error fetching the DOCX file:", error);
    });

// ฟังก์ชันพิมพ์
function printDocument() {
    var printContents = document.getElementById('docx-output').innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
JS;
$this->registerJS($js, View::POS_END);
?>
