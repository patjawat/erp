<?php

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * หน้าเชื่อมบัญชี Telegram เพื่อรับแจ้งเตือนงาน
 *
 * รองรับสองทาง: กดลิงก์จากมือถือ หรือสแกน QR ถ้านั่งอยู่หน้าคอม
 * ระบบถามสถานะเป็นระยะ จึงขึ้นว่าสำเร็จเองโดยผู้ใช้ไม่ต้องรีเฟรช
 *
 * @var yii\web\View $this
 * @var app\modules\usermanager\models\User|null $user
 * @var bool $linked
 * @var string|null $deepLink
 * @var string|null $botUsername
 * @var int $ttlMinutes
 */
$this->title = 'เชื่อมต่อ Telegram';

$qrDataUri = null;
if (!$linked && $deepLink) {
    try {
        $qrDataUri = Builder::create()->writer(new PngWriter())->data($deepLink)->size(220)->build()->getDataUri();
    } catch (\Throwable $e) {
        $qrDataUri = null;
    }
}

$statusUrl = Url::to(['/profile/telegram-status']);
$unlinkUrl = Url::to(['/profile/telegram-unlink']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
?>
<div class="container-fluid px-0" id="tg-connect">

    <?php if ($linked): ?>

        <section class="card bg-body border shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle-fill fs-1 text-success-emphasis d-block mb-3" aria-hidden="true"></i>
                <h1 class="h4 mb-2">เชื่อมต่อ Telegram แล้ว</h1>
                <p class="text-body-secondary mb-4">
                    ระบบจะแจ้งเตือนงานที่มอบหมายถึงคุณผ่าน Telegram
                </p>
                <button type="button" class="btn btn-outline-danger btn-sm" id="tg-unlink">
                    <i class="bi bi-x-circle me-1" aria-hidden="true"></i>ยกเลิกการเชื่อมต่อ
                </button>
            </div>
        </section>

    <?php elseif (!$botUsername): ?>

        <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
            ยังไม่ได้ตั้งค่าชื่อบอทในระบบ กรุณาแจ้งผู้ดูแลระบบให้ตั้งค่าที่หน้าตั้งค่า Telegram ก่อน
        </div>

    <?php else: ?>

        <div class="row g-3">
            <div class="col-12 col-lg-7">
                <section class="card bg-body border shadow-sm h-100">
                    <div class="card-body">
                        <h1 class="h5 mb-1">รับแจ้งเตือนงานผ่าน Telegram</h1>
                        <p class="text-body-secondary small mb-4">
                            เชื่อมครั้งเดียว ใช้ได้ตลอด ยกเลิกเมื่อไหร่ก็ได้
                        </p>

                        <ol class="list-unstyled mb-0 d-flex flex-column gap-3">
                            <li class="d-flex gap-3">
                                <span class="badge bg-primary-subtle text-primary-emphasis rounded-circle flex-shrink-0">1</span>
                                <div>
                                    <div class="fw-semibold">เปิดแชทกับบอทของโรงพยาบาล</div>
                                    <div class="text-body-secondary small mb-2">
                                        บนมือถือกดปุ่มนี้ได้เลย ถ้าอยู่หน้าคอมให้สแกน QR ทางขวา
                                    </div>
                                    <a href="<?= Html::encode($deepLink) ?>" target="_blank" rel="noopener"
                                       class="btn btn-primary" data-pjax="0">
                                        <i class="bi bi-telegram me-1" aria-hidden="true"></i>เปิด Telegram
                                    </a>
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <span class="badge bg-primary-subtle text-primary-emphasis rounded-circle flex-shrink-0">2</span>
                                <div>
                                    <div class="fw-semibold">กดปุ่ม เริ่ม (Start) ในแอป Telegram</div>
                                    <div class="text-body-secondary small">ระบบจะผูกบัญชีให้อัตโนมัติ ไม่ต้องพิมพ์อะไร</div>
                                </div>
                            </li>
                            <li class="d-flex gap-3">
                                <span class="badge bg-primary-subtle text-primary-emphasis rounded-circle flex-shrink-0">3</span>
                                <div>
                                    <div class="fw-semibold">กลับมาที่หน้านี้</div>
                                    <div class="text-body-secondary small">หน้าจะขึ้นว่าเชื่อมต่อสำเร็จเอง ไม่ต้องกดรีเฟรช</div>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <div class="card-footer bg-body-tertiary d-flex align-items-center gap-2">
                        <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
                        <span class="small text-body-secondary" id="tg-wait">กำลังรอการเชื่อมต่อ...</span>
                    </div>
                </section>
            </div>

            <div class="col-12 col-lg-5">
                <section class="card bg-body border shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <?php if ($qrDataUri): ?>
                            <?php // รูปถูกสร้างที่ 220px อยู่แล้ว img-fluid จึงคุมขนาดได้โดยไม่ต้องใส่ inline style ?>
                            <img src="<?= $qrDataUri ?>" alt="QR สำหรับเชื่อมต่อ Telegram"
                                 class="img-fluid mx-auto mb-3">
                            <p class="text-body-secondary small mb-1">สแกนด้วยกล้องมือถือ</p>
                        <?php else: ?>
                            <p class="text-body-secondary mb-2">สร้าง QR ไม่สำเร็จ ใช้ปุ่มเปิด Telegram แทนได้</p>
                        <?php endif ?>
                        <p class="text-body-tertiary small mb-0">
                            ลิงก์นี้ใช้ได้ <?= (int) $ttlMinutes ?> นาที ถ้าหมดอายุให้กดรีเฟรชหน้าเพื่อสร้างใหม่
                        </p>
                    </div>
                </section>
            </div>
        </div>

    <?php endif ?>
</div>

<script>
(function () {
    var root = document.getElementById('tg-connect');
    if (!root || root.dataset.bound === '1') { return; }
    root.dataset.bound = '1';

    var STATUS_URL = <?= json_encode($statusUrl) ?>;
    var UNLINK_URL = <?= json_encode($unlinkUrl) ?>;
    var CSRF_PARAM = <?= json_encode($csrfParam) ?>;
    var CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
    var LINKED = <?= $linked ? 'true' : 'false' ?>;

    // ถามสถานะเป็นระยะ จะได้ขึ้นว่าสำเร็จทันทีที่ผูกเสร็จในแอป
    if (!LINKED) {
        var tries = 0;
        var timer = setInterval(function () {
            tries++;
            if (tries > 100) { clearInterval(timer); return; }
            fetch(STATUS_URL, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d && d.linked) { clearInterval(timer); window.location.reload(); }
                })
                .catch(function () {});
        }, 3000);
    }

    var unlink = document.getElementById('tg-unlink');
    if (unlink) {
        unlink.addEventListener('click', function () {
            unlink.disabled = true;
            var body = new FormData();
            body.append(CSRF_PARAM, CSRF_TOKEN);
            fetch(UNLINK_URL, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function () { window.location.reload(); })
                .catch(function () { unlink.disabled = false; });
        });
    }
})();
</script>
