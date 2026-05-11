<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'การตั้งค่าระบบ';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-3">
    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary p-2">
        <i class="fa-solid fa-gear fs-5"></i>
    </span>
    <div class="flex-grow-1">
        <h5 class="mb-1 fw-semibold text-body"><?= Html::encode($this->title) ?></h5>
        <small class="text-muted">จัดการข้อมูลองค์กร โมดูล และการเชื่อมต่อระบบ</small>
    </div>
</div>
<?php $this->endBlock(); ?>

<?php
$renderSectionTitle = static function (string $icon, string $title, string $subtitle): string {
    $iconHtml = Html::tag(
        'span',
        Html::tag('i', '', ['class' => $icon . ' fs-5']),
        [
            'class' => 'd-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary flex-shrink-0 p-2',
        ]
    );

    $textHtml = Html::tag(
        'div',
        Html::tag('h6', $title, ['class' => 'mb-1 fw-semibold text-body']) .
        Html::tag('small', $subtitle, ['class' => 'text-muted d-block']),
        ['class' => 'flex-grow-1']
    );

    return Html::tag('div', $iconHtml . $textHtml, ['class' => 'd-flex align-items-start gap-3 mb-3']);
};

$renderSettingCard = static function (array $item): string {
    $color = $item['color'] ?? 'primary';
    $icon = $item['icon'] ?? 'fa-solid fa-circle';
    $title = $item['title'] ?? '';
    $subtitle = $item['subtitle'] ?? '';
    $url = $item['url'] ?? '#';

    $content = Html::tag(
        'div',
        Html::tag(
            'span',
            Html::tag('i', '', ['class' => $icon . ' fs-5']),
            [
                'class' => 'd-inline-flex align-items-center justify-content-center rounded-circle bg-' . $color . ' bg-opacity-10 text-' . $color . ' flex-shrink-0 p-3',
            ]
        ) .
        Html::tag(
            'div',
            Html::tag('div', $title, ['class' => 'fw-semibold text-body mb-1']) .
            Html::tag('small', $subtitle, ['class' => 'text-muted d-block']),
            ['class' => 'flex-grow-1']
        ) .
        Html::tag('i', '', ['class' => 'fa-solid fa-chevron-right text-muted flex-shrink-0 mt-1']),
        ['class' => 'card-body p-3 p-lg-4 d-flex align-items-start gap-3']
    );

    return Html::a(
        Html::tag('div', $content, ['class' => 'card border-0 shadow-sm rounded-4 h-100 overflow-hidden']),
        $url,
        ['class' => 'text-decoration-none d-block h-100']
    );
};
?>

<div class="container-fluid py-3 py-lg-4">
    <section class="mb-4">
        <?= $renderSectionTitle('fa-solid fa-building', 'องค์กรและพื้นฐาน', 'ข้อมูลหลักขององค์กรและรูปแบบหน้าตาระบบ') ?>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <?= $renderSettingCard([
                    'url' => Url::to(['/settings/company']),
                    'title' => 'ข้อมูลองค์กร',
                    'subtitle' => 'ชื่อองค์กร ที่อยู่ และข้อมูลติดต่อ',
                    'icon' => 'fa-solid fa-house-medical-flag',
                    'color' => 'primary',
                ]) ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $renderSettingCard([
                    'url' => Url::to(['/setting']),
                    'title' => 'ตั้งค่าสี',
                    'subtitle' => 'ปรับโทนสีและภาพรวมธีมระบบ',
                    'icon' => 'fa-solid fa-palette',
                    'color' => 'info',
                ]) ?>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <?= $renderSectionTitle('fa-solid fa-user-shield', 'ผู้ใช้และสิทธิ์', 'จัดการบัญชี บทบาท และสิทธิ์การเข้าถึง') ?>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-4">
                <?= $renderSettingCard([
                    'url' => Url::to(['/usermanager/user']),
                    'title' => 'ผู้ใช้งาน',
                    'subtitle' => 'บัญชีและการเข้าถึงของบุคลากร',
                    'icon' => 'fa-solid fa-user-gear',
                    'color' => 'primary',
                ]) ?>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <?= $renderSettingCard([
                    'url' => Url::to(['/usermanager/role']),
                    'title' => 'บทบาท',
                    'subtitle' => 'กำหนดโครงสร้างสิทธิ์ของผู้ใช้',
                    'icon' => 'fa-solid fa-user-tag',
                    'color' => 'info',
                ]) ?>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <?= $renderSettingCard([
                    'url' => Url::to(['/usermanager/permission']),
                    'title' => 'สิทธิ์',
                    'subtitle' => 'ควบคุมการเข้าถึงเมนูและฟังก์ชัน',
                    'icon' => 'fa-solid fa-shield-halved',
                    'color' => 'primary',
                ]) ?>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <?= $renderSectionTitle('fa-solid fa-plug', 'การเชื่อมต่อและแจ้งเตือน', 'เชื่อมต่อช่องทางสื่อสารและการแจ้งเตือน') ?>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <?= $renderSettingCard([
                    'url' => Url::to(['/settings/telegram']),
                    'title' => 'Telegram',
                    'subtitle' => 'บอทและการแจ้งเตือน',
                    'icon' => 'fa-brands fa-telegram',
                    'color' => 'primary',
                ]) ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $renderSettingCard([
                    'url' => Url::to(['/settings/line-official']),
                    'title' => 'Line Official',
                    'subtitle' => 'Line OA และ LIFF',
                    'icon' => 'fa-brands fa-line',
                    'color' => 'info',
                ]) ?>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <?= $renderSectionTitle('fa-solid fa-puzzle-piece', 'ตั้งค่าโมดูล', 'ตั้งค่าข้อมูลอ้างอิงและเทมเพลตที่ระบบใช้งาน') ?>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-4">
                <?= $renderSettingCard([
                    'url' => Url::to(['/hr/categorise', 'title' => 'การตั้งค่าบุคลากร']),
                    'title' => 'บุคลากร',
                    'subtitle' => 'ประเภท ตำแหน่ง และยศ',
                    'icon' => 'fa-solid fa-clipboard-user',
                    'color' => 'info',
                ]) ?>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <?= $renderSettingCard([
                    'url' => Url::to(['/am/setting']),
                    'title' => 'ทรัพย์สิน',
                    'subtitle' => 'ประเภทครุภัณฑ์ และรหัส FSN',
                    'icon' => 'fa-solid fa-boxes-stacked',
                    'color' => 'primary',
                ]) ?>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <?= $renderSettingCard([
                    'url' => Url::to(['/am/setting/fsn-format']),
                    'title' => 'รูปแบบ FSN ครุภัณฑ์',
                    'subtitle' => 'กำหนดรูปแบบและลำดับหมายเลข',
                    'icon' => 'fa-solid fa-hashtag',
                    'color' => 'primary',
                ]) ?>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <?= $renderSettingCard([
                    'url' => Url::to(['/gdoc/setting']),
                    'title' => 'จัดการแบบฟอร์ม',
                    'subtitle' => 'เทมเพลต Google / เอกสาร',
                    'icon' => 'fa-brands fa-google-drive',
                    'color' => 'info',
                ]) ?>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <?= $renderSettingCard([
                    'url' => Url::to(['/pdf-template/template']),
                    'title' => 'เทมเพลต PDF',
                    'subtitle' => 'ใบลา ใบรายงาน และเอกสารอื่น',
                    'icon' => 'fa-solid fa-file-pdf',
                    'color' => 'primary',
                ]) ?>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <?= $renderSectionTitle('fa-solid fa-server', 'ระบบและความปลอดภัย', 'งานดูแลระบบ สำรองข้อมูล และอัปเดต') ?>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <?= $renderSettingCard([
                    'url' => Url::to(['/backup']),
                    'title' => 'สำรองข้อมูล',
                    'subtitle' => 'Backup และกู้คืนข้อมูล',
                    'icon' => 'fa-solid fa-database',
                    'color' => 'info',
                ]) ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $renderSettingCard([
                    'url' => Url::to(['/settings/update']),
                    'title' => 'อัปเดตระบบ',
                    'subtitle' => 'อัปเกรดและรีลีสเวอร์ชันใหม่',
                    'icon' => 'fa-solid fa-arrows-rotate',
                    'color' => 'primary',
                ]) ?>
            </div>
        </div>
    </section>
</div>
