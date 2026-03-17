<?php

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'การตั้งค่าระบบ';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <span class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary p-2">
        <i class="fa-solid fa-gear fs-5"></i>
    </span>
    <div>
        <h5 class="mb-0 fw-semibold text-body"><?= Html::encode($this->title) ?></h5>
        <small class="text-muted">จัดการข้อมูลองค์กร โมดูล และการเชื่อมต่อระบบ</small>
    </div>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">

    <!-- องค์กรและลักษณะระบบ -->
    <div class="mb-4">
        <h6 class="text-uppercase fw-semibold text-muted mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-building"></i> องค์กรและลักษณะระบบ
        </h6>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary flex-shrink-0 p-2">
                                <i class="fa-solid fa-house-medical-flag fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">ข้อมูลองค์กร</div>
                                <small class="text-muted">ชื่อองค์กร ที่อยู่ การติดต่อ</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/settings/company']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-info bg-opacity-10 text-info flex-shrink-0 p-2">
                                <i class="fa-solid fa-palette fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">ตั้งค่าสี</div>
                                <small class="text-muted">ธีมสีของระบบ</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/setting']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
        </div>
    </div>

    <!-- ผู้ใช้และสิทธิ์ -->
    <div class="mb-4">
        <h6 class="text-uppercase fw-semibold text-muted mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-shield"></i> ผู้ใช้และสิทธิ์
        </h6>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-success bg-opacity-10 text-success flex-shrink-0 p-2">
                                <i class="fa-solid fa-user-gear fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">ผู้ใช้งาน</div>
                                <small class="text-muted">บัญชีและสิทธิ์การเข้าถึง</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/usermanager/user']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
        </div>
    </div>

    <!-- การเชื่อมต่อและแจ้งเตือน -->
    <div class="mb-4">
        <h6 class="text-uppercase fw-semibold text-muted mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-plug"></i> การเชื่อมต่อและแจ้งเตือน
        </h6>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary flex-shrink-0 p-2">
                                <i class="fa-brands fa-telegram fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">Telegram</div>
                                <small class="text-muted">บอทและแจ้งเตือน</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/settings/telegram']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-success bg-opacity-10 text-success flex-shrink-0 p-2">
                                <i class="fa-brands fa-line fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">Line Official</div>
                                <small class="text-muted">Line OA และ LIFF</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/settings/line-official']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
        </div>
    </div>

    <!-- ตั้งค่าโมดูล -->
    <div class="mb-4">
        <h6 class="text-uppercase fw-semibold text-muted mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-puzzle-piece"></i> ตั้งค่าโมดูล
        </h6>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-warning bg-opacity-10 text-warning flex-shrink-0 p-2">
                                <i class="fa-solid fa-clipboard-user fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">บุคลากร</div>
                                <small class="text-muted">ประเภท ตำแหน่ง ยศ</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/hr/categorise', 'title' => 'การตั้งค่าบุคลากร']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-secondary bg-opacity-10 text-secondary flex-shrink-0 p-2">
                                <i class="fa-solid fa-boxes-stacked fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">ทรัพย์สิน</div>
                                <small class="text-muted">ประเภทครุภัณฑ์ รหัส FSN</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/am/setting']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary flex-shrink-0 p-2">
                                <i class="fa-solid fa-hashtag fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">รูปแบบ FSN ครุภัณฑ์</div>
                                <small class="text-muted">กำหนดรูปแบบและลำดับหมายเลข</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/am/setting/fsn-format']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-danger bg-opacity-10 text-danger flex-shrink-0 p-2">
                                <i class="fa-brands fa-google-drive fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">จัดการแบบฟอร์ม</div>
                                <small class="text-muted">เทมเพลต Google / เอกสาร</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/gdoc/setting']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-info bg-opacity-10 text-info flex-shrink-0 p-2">
                                <i class="fa-solid fa-file-pdf fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">เทมเพลต PDF</div>
                                <small class="text-muted">เทมเพลตใบลา ใบรายงาน ฯลฯ</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/pdf-template/template']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
        </div>
    </div>

    <!-- ระบบและความปลอดภัย -->
    <div class="mb-4">
        <h6 class="text-uppercase fw-semibold text-muted mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-server"></i> ระบบและความปลอดภัย
        </h6>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-info bg-opacity-10 text-info flex-shrink-0 p-2">
                                <i class="fa-solid fa-database fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">สำรองข้อมูล</div>
                                <small class="text-muted">Backup และกู้คืน</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/backup']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <?= Html::a(
                    '<div class="card border-0 shadow-sm h-100 rounded-2 overflow-hidden">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary flex-shrink-0 p-2">
                                <i class="fa-solid fa-arrows-rotate fs-4"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-body">อัปเดตระบบ</div>
                                <small class="text-muted">อัปเดตและอัปเกรด</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted ms-auto flex-shrink-0"></i>
                        </div>
                    </div>',
                    Url::to(['/settings/update']),
                    ['class' => 'text-decoration-none']
                ) ?>
            </div>
        </div>
    </div>

</div>
