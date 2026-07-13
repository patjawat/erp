<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\ElearningMaterial $model */
/** @var app\modules\hr\models\ElearningCourse $course */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'ห้องเรียน E-learning', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $course->title, 'url' => ['view', 'id' => $course->id]];
$this->params['breadcrumbs'][] = $model->title;

// ตรวจสอบและแปลง URL YouTube
$embedUrl = '';
$isYoutube = false;
if ($model->type === 'video_url') {
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $model->file_path, $match)) {
        $youtubeId = $match[1];
        $embedUrl = "https://www.youtube.com/embed/" . $youtubeId;
        $isYoutube = true;
    }
}
?>

<div class="elearning-study-material">
    <?php $this->beginBlock('page-title'); ?>
    สื่อการเรียนรู้หลักสูตร
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('page-action'); ?>
    <?= Html::a('<i class="fa-solid fa-chevron-left me-1"></i> กลับห้องเรียน', ['view', 'id' => $course->id], ['class' => 'btn btn-outline-primary']) ?>
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 bg-light border-bottom">
            <span class="badge bg-primary-subtle text-primary mb-2">
                <?= $model->type === 'video_url' ? 'วิดีโอประกอบการเรียน' : ($model->type === 'pdf_file' ? 'เอกสารประกอบ PDF' : 'สไลด์ประกอบ') ?>
            </span>
            <h4 class="fw-bold text-dark mb-0"><?= Html::encode($model->title) ?></h4>
            <p class="text-muted mb-0 fs-8"><i class="fa-solid fa-graduation-cap me-1"></i> หลักสูตร: <?= Html::encode($course->title) ?></p>
        </div>

        <div class="card-body p-4">
            <!-- ส่วนการแสดงสื่อ -->
            <div class="media-viewport mb-4 bg-black rounded overflow-hidden">
                <?php if ($model->type === 'video_url'): ?>
                    <?php if ($isYoutube): ?>
                        <div class="ratio ratio-16x9">
                            <iframe src="<?= $embedUrl ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <div class="p-5 text-center text-white bg-dark">
                            <i class="fa-brands fa-youtube fs-1 text-danger mb-3"></i>
                            <h5>วิดีโอศึกษาภายนอกระบบ</h5>
                            <p class="text-white-50 fs-7">กรุณากดปุ่มด้านล่างเพื่อเปิดรับชมวิดีโอในหน้าต่างใหม่</p>
                            <?= Html::a('<i class="fa-solid fa-up-right-from-square me-1"></i> เปิดดูวิดีโอเรียนรู้', $model->file_path, ['class' => 'btn btn-danger rounded-pill px-4', 'target' => '_blank']) ?>
                        </div>
                    <?php endif; ?>
                
                <?php elseif ($model->type === 'pdf_file'): ?>
                    <div class="ratio ratio-16x9 d-none d-md-block" style="height: 600px;">
                        <iframe src="<?= Html::encode($model->file_path) ?>" width="100%" height="100%" style="border: none;"></iframe>
                    </div>
                    <!-- บนมือถือให้มีปุ่มกดดาวน์โหลดแทน -->
                    <div class="p-5 text-center text-white bg-dark d-block d-md-none">
                        <i class="fa-regular fa-file-pdf fs-1 text-danger mb-3"></i>
                        <h5>ไฟล์เอกสารประกอบการศึกษา</h5>
                        <p class="text-white-50 fs-7">หน้าจอมือถือของคุณเล็กเกินไปสำหรับการอ่านในระบบคุณสามารถดาวน์โหลดไว้อ่านได้</p>
                        <?= Html::a('<i class="fa-solid fa-download me-1"></i> ดาวน์โหลดไฟล์ PDF', $model->file_path, ['class' => 'btn btn-primary rounded-pill px-4', 'target' => '_blank']) ?>
                    </div>

                <?php else: ?>
                    <div class="p-5 text-center text-white bg-dark">
                        <i class="fa-solid fa-link fs-1 text-info mb-3"></i>
                        <h5>ลิงก์สื่อภายนอกประกอบการสอน</h5>
                        <p class="text-white-50 fs-7">ลิงก์ข้อมูลเพิ่มเติมสำหรับการศึกษาด้วยตนเอง</p>
                        <?= Html::a('<i class="fa-solid fa-up-right-from-square me-1"></i> เปิดดูข้อมูลเพิ่มเติม', $model->file_path, ['class' => 'btn btn-primary rounded-pill px-4', 'target' => '_blank']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- กล่องแนะนำหลังเรียนจบ -->
            <div class="card border border-warning bg-warning-subtle text-dark p-4 rounded-3 text-center">
                <h5 class="fw-bold mb-2">เมื่อคุณศึกษาเนื้อหาของบทเรียนย่อยนี้เรียบร้อยแล้ว</h5>
                <p class="fs-7 mb-3">คุณสามารถย้อนกลับไปยังห้องเรียนของหลักสูตร เพื่อศึกษาต่อ หรือเริ่มการทำแบบทดสอบวัดระดับความรู้</p>
                <div class="d-flex justify-content-center gap-2">
                    <?= Html::a('<i class="fa-solid fa-hospital-user me-1"></i> ย้อนกลับไปห้องเรียนหลักสูตร', ['view', 'id' => $course->id], ['class' => 'btn btn-primary rounded-pill px-4']) ?>
                    <?= Html::a('เข้าทำแบบทดสอบทันที <i class="fa-solid fa-arrow-right ms-1"></i>', ['quiz', 'id' => $course->id], ['class' => 'btn btn-warning text-dark fw-bold rounded-pill px-4 border border-warning']) ?>
                </div>
            </div>

        </div>
    </div>
</div>
