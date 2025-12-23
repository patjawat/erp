<?php

use yii\helpers\Url;
use yii\helpers\Html;

use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\booking\models\RoomType;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\RoomSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ระเบียบสำนักนายกรัฐมนตรีว่าด้วยงานสารบรรณ';
$this->params['breadcrumbs'][] = 'ระบบสารบรรณ';
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/dms/document/dashboard']];
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">

    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-icon lucide-book-open">
            <path d="M12 7v14" />
            <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z" />
        </svg>
        <?= $this->title ?>
    </h4>
</div>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>


 <div class="row g-4">

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 drive-card shadow-sm text-center p-3">
                <div class="card-body">
                    <h5 class="card-title">Video บรรยาย</h5>
                    <p class="text-muted small">วิดีโอแนะนำระเบียบฉบับล่าสุด</p>
                    <a href="https://drive.google.com/file/d/1uBn5y9qAkX1ooqZYmHPPUg1qIknZVmdz/view?usp=drive_link" target="_blank" class="btn btn-danger w-100">เล่นวิดีโอ</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 drive-card shadow-sm text-center p-3">
                <div class="card-body">
                    <h5 class="card-title">Podcast เสียง</h5>
                    <p class="text-muted small">ฟังเทคนิคการเขียนหนังสือราชการ</p>
                    <a href="https://drive.google.com/file/d/19zfDEgbIR0rChkFvKJbbQ5ekykVykJQy/view?usp=drive_link" target="_blank" class="btn btn-success w-100">ฟังเสียง</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 drive-card shadow-sm text-center p-3">
                <div class="card-body">
                    <h5 class="card-title">ระเบียบฉบับเต็ม (PDF)</h5>
                    <p class="text-muted small">ดาวน์โหลดไฟล์ระเบียบ พ.ศ. 2526</p>
                    <a href="https://drive.google.com/file/d/1Tke9BsdFpn3hQfCDgYUdNYCLCsn6Pt82/view?usp=drive_link" target="_blank" class="btn btn-dark w-100">ดาวน์โหลด PDF</a>
                </div>
            </div>
        </div>

    </div>

    <section class="mb-5">
        <h2 class="section-title">ไฮไลท์สำคัญจากระเบียบใหม่</h2>
        <div class="row align-items-center bg-white p-4 rounded-4 shadow-sm">
            <div class="col-lg-12">
                <ul class="list-group list-group-flush mt-3">
                    <li class="list-group-item"><strong>ระบบ Cloud:</strong> รองรับการใช้บริการ Cloud Computing ในการจัดเก็บข้อมูล </li>
                    <li class="list-group-item"><strong>การจัดเก็บ:</strong> ต้องสำรองข้อมูลและเก็บไว้อย่างน้อย 10 ปี </li>
                    <li class="list-group-item"><strong>รูปแบบไฟล์:</strong> แนะนำ PDF/A ความละเอียดไม่น้อยกว่า 200 dpi </li>
                    <li class="list-group-item"><strong>การทำลาย:</strong> ทำลายต้นฉบับได้เมื่อแปลงเป็นไฟล์อิเล็กทรอนิกส์แล้ว </li>
                </ul>
            </div>
        </div>
    </section>

    <div class="mt-5">
        <h3>Infographic สรุปสาระสำคัญ</h3>
        <div class="ratio ratio-16x9 shadow rounded overflow-hidden">
            <img src="https://drive.google.com/thumbnail?id=1-7l3JG3oiXFbvLaGsrV2FrjBPgz4Bgi5&sz=w1000"
                class="img-fluid rounded-3 shadow" alt="Sarabun Infographic">
        </div>
    </div>