<?php

/** @var yii\web\View $this /
/* @var string $base64Pdf /
/* @var app\models\Development $model */

use yii\helpers\Html;

$this->title = 'แสดงตัวอย่างเอกสาร: ' . ($model->id ?? 'PDF');

// ปรับแต่ง CSS เพื่อให้ PDF แสดงผลได้เต็มพื้นที่หน้าจอ
$this->registerCss("
body, html {
margin: 0;
padding: 0;
height: 100%;
overflow: hidden;
}
.pdf-viewer-wrapper {
display: flex;
flex-direction: column;
height: 100vh;
width: 100%;
background: #525659;
}
.pdf-toolbar {
background: #323639;
color: white;
padding: 10px 20px;
display: flex;
justify-content: space-between;
align-items: center;
box-shadow: 0 2px 5px rgba(0,0,0,0.3);
z-index: 10;
}
.pdf-content {
flex-grow: 1;
border: none;
width: 60%;
height:700px;
}
@media print {
.pdf-toolbar { display: none; }
}
");
?>

<div class="pdf-viewer-wrapper">
    <!-- Toolbar จำลองเพื่อให้ผู้ใช้รู้ว่ากำลังดูเอกสารอะไร -->
    <div class="pdf-toolbar">
        <div>
            <i class="bi bi-file-earmark-pdf"></i>
            <span class="ms-2"><?= Html::encode($this->title) ?></span>
        </div>
        <div>
            <button class="btn btn-sm btn-light" onclick="window.history.back()">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </button>
            <button class="btn btn-sm btn-outline-light ms-2" onclick="window.print()">
                <i class="bi bi-printer"></i> พิมพ์
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <?php if (!empty($base64Pdf)): ?>
                <iframe
                    src="data:application/pdf;base64,<?= $base64Pdf ?>#toolbar=1&navpanes=0&scrollbar=1"
                    class="pdf-content"
                    title="PDF Preview">
                    <p>เบราว์เซอร์ของคุณไม่รองรับการแสดงผล PDF <a href="data:application/pdf;base64,<?= $base64Pdf ?>" download="document.pdf">คลิกเพื่อดาวน์โหลด</a></p>
                </iframe>
            <?php else: ?>
                <div class="alert alert-danger m-3">
                    ไม่พบข้อมูล PDF กรุณาลองใหม่อีกครั้ง
                </div>
            <?php endif; ?>
        </div>
    </div>