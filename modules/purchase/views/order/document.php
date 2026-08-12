<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\modules\purchase\components\LegacyDocCatalog;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Order $model */

/**
 * เมนูพิมพ์เอกสารของใบขอซื้อ 1 ใบ
 *
 * เอกสารแต่ละใบมีสองทางไปได้
 *
 *   แปลงเป็นแม่แบบ HTML แล้ว -> ไปหน้าแก้ไขบนกระดาษ A4 แก้ได้ก่อนพริ้นท์
 *   ยังไม่แปลง               -> ใช้ทางเดิม /ms-word/purchase_N คือได้ไฟล์ .docx
 *
 * ทำสองทางเพราะแปลงเอกสารเป็น HTML ต้องทำทีละใบและต้องเทียบกับกระดาษจริง
 * ถ้ารอให้ครบ 13 ใบก่อนจึงเปลี่ยน ผู้ใช้จะไม่ได้ใช้ของใหม่เลยจนกว่าจะเสร็จหมด
 * ตัวที่บอกว่าใบไหนแปลงแล้วคือ purchase_doc_template.legacy_key
 *
 * ทางเดิมยังทำงานเหมือนเดิมทุกอย่าง ไม่ได้แก้ MsWordController หรือไฟล์ .docx
 * ข้อจำกัดที่ยังอยู่กับทางเดิม: กล่องพรีวิวใช้ Google Docs Viewer ซึ่งต้องเข้าถึงไฟล์
 * จากอินเทอร์เน็ตได้ ERP ที่รันในอินทราเน็ตจึงขึ้น "ไม่มีตัวอย่างที่ใช้ได้" เสมอ
 * ผู้ใช้ต้องกดดาวน์โหลดแล้วเปิดใน Word เอง
 *
 * หมายเหตุที่ยกมาจากของเดิมโดยไม่แก้: "คำสั่งจังหวัด...?" กับ "ขอความเห็นชอบและ
 * รายงานผล" ชี้ไปที่ purchase_2 ไฟล์เดียวกันทั้งคู่ ซึ่งน่าจะเป็นความพลาดเดิม
 * แต่ไม่แก้ในงานนี้เพราะการเปลี่ยนว่าปุ่มไหนเปิดไฟล์ไหนคือการเปลี่ยนพฤติกรรม
 * ที่งานพัสดุใช้อยู่ทุกวัน ต้องให้เจ้าของงานยืนยันก่อน
 */

$documents = LegacyDocCatalog::resolved();
$progress = LegacyDocCatalog::progress();
$columns = array_chunk($documents, (int) ceil(count($documents) / 2), true);
?>

<?php if ($progress['done'] > 0): ?>
    <div class="alert alert-success py-2 small mb-3">
        <i class="bi bi-pencil-square me-1"></i>
        เอกสารที่มีป้าย <span class="badge text-bg-success">แก้ไขได้</span>
        จะเปิดเป็นกระดาษ A4 ให้แก้บนจอก่อนพริ้นท์
        (<?= $progress['done'] ?> จาก <?= $progress['total'] ?> ใบ)
        ส่วนใบที่เหลือยังเป็นการดาวน์โหลดไฟล์ Word แบบเดิม
    </div>
<?php endif; ?>

<div class="row">
    <?php $running = 0; ?>
    <?php foreach ($columns as $column): ?>
        <div class="col-md-6">
            <div class="d-flex flex-column gap-2">
                <?php foreach ($column as $doc): ?>
                    <?php
                    $running++;

                    if ($doc['converted']) {
                        $url = Url::to([
                            '/purchase/doc/quick',
                            'template_id' => $doc['template']->id,
                            'ref_id' => $model->id,
                        ]);
                        $size = 'modal-xl';
                    } else {
                        $url = Url::to(['/ms-word/' . $doc['key'], 'id' => $model->id]);
                        $size = $doc['size'];
                    }
                    ?>
                    <div class="d-flex align-items-center bg-primary bg-opacity-10 p-2 rounded">
                        <span class="badge rounded-pill bg-primary text-white me-2"><?= $running ?></span>
                        <?= Html::a(Html::encode($doc['label']), $url, [
                            'class' => 'open-modal text-decoration-none',
                            'data' => ['size' => $size],
                        ]) ?>
                        <?php if ($doc['converted']): ?>
                            <span class="badge text-bg-success ms-2">แก้ไขได้</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="d-flex justify-content-between align-items-center bg-primary p-2 rounded mt-3">
    <?= Html::a('ดาวน์โหลดทั้งหมด', ['/purchase/document/download-file', 'id' => $model->id], [
        'class' => 'text-white download-btn',
    ]) ?>
    <i class="bi bi-arrow-down-circle fs-3 text-white"></i>
</div>

<?php
$js = <<<'JS'
$('.download-btn').click(function (e) {
    e.preventDefault();
    beforLoadModal();

    const filename = 'myfile.zip';

    $.ajax({
        url: $(this).attr('href'),
        type: 'GET',
        xhrFields: {
            responseType: 'blob'
        },
        success: function (data) {
            const url = window.URL.createObjectURL(new Blob([data]));

            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();

            window.URL.revokeObjectURL(url);

            $("#main-modal").modal("toggle");
            Swal.fire({
                icon: "success",
                title: "ดาวน์โหลดสำเร็จ!",
                showConfirmButton: false,
                timer: 1500,
            });
        },
        error: function () {
            alert('Failed to download the file.');
        }
    });
});
JS;
$this->registerJs($js, View::POS_END);
?>
