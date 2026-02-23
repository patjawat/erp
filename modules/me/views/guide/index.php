<?php

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'คู่มือให้งาน';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/me/guide']];
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
        </svg>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0 small">คู่มือและขั้นตอนการใช้งานระบบสำหรับบุคลากร</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/me/menu', ['active' => 'guide']) ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-12 col-lg-8">
        <!-- คู่มือการตั้งค่า PWA -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                <h5 class="mb-0 d-flex align-items-center gap-2">
                    <span class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    </span>
                    คู่มือการตั้งค่า PWA (ติดตั้งแอปบนหน้าจอ)
                </h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-4">ระบบ ERP รองรับการติดตั้งเป็นแอปบนมือถือหรือแท็บเล็ต (PWA) เพื่อเปิดใช้จากไอคอนบนหน้าจอและรับการแจ้งเตือนได้</p>

                <h6 class="fw-semibold mb-2">บน Android (Chrome / Edge)</h6>
                <ol class="mb-4 ps-3">
                    <li class="mb-2">เปิดเว็บ ERP ผ่านเบราว์เซอร์ <strong>Chrome</strong> หรือ <strong>Edge</strong></li>
                    <li class="mb-2">ที่มุมขวาบนของหน้าเว็บ ให้กดปุ่ม <strong>ดาวน์โหลด</strong> <i class="fa-solid fa-download text-primary"></i> (ปุ่มติดตั้งแอป PWA)</li>
                    <li class="mb-2">เลือก <strong>「ติดตั้ง」</strong> หรือ <strong>「Add to Home screen」</strong> ตามที่เบราว์เซอร์แสดง</li>
                    <li>เมื่อติดตั้งแล้ว จะมีไอคอนแอป ERP บนหน้าจอหลัก เปิดใช้ได้จากไอคอนนี้</li>
                </ol>

                <h6 class="fw-semibold mb-2">บน iPhone / iPad (Safari)</h6>
                <ol class="mb-4 ps-3">
                    <li class="mb-2">เปิดเว็บ ERP ผ่านเบราว์เซอร์ <strong>Safari</strong> เท่านั้น (Chrome บน iOS ใช้การติดตั้งผ่าน Safari)</li>
                    <li class="mb-2">กดปุ่ม <strong>แชร์</strong> <i class="fa-solid fa-share-nodes text-primary"></i> ด้านล่างหรือมุมบนของ Safari</li>
                    <li class="mb-2">เลื่อนหาแล้วกด <strong>「เพิ่มไปยังหน้าจอหลัก」</strong> (Add to Home Screen)</li>
                    <li class="mb-2">ตั้งชื่อแอป (หรือใช้ชื่อเดิม) แล้วกด <strong>「เพิ่ม」</strong></li>
                    <li>จะเห็นไอคอนแอป ERP บนหน้าจอหลัก เปิดจากไอคอนนี้เพื่อใช้และรับการแจ้งเตือนได้</li>
                </ol>

                <div class="alert alert-light border mb-0">
                    <small class="text-muted">
                        <strong>หมายเหตุ:</strong> ต้องเปิดเว็บผ่าน <strong>HTTPS</strong> การแจ้งเตือน (Push) จะทำงานได้หลังติดตั้งแอปแล้ว และบน iPhone/iPad ต้องเปิดจากไอคอนแอปที่เพิ่มไว้ ไม่ใช่จาก Safari โดยตรง
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open text-primary opacity-75">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </div>
                <h6 class="text-body mb-2">คู่มือให้งาน</h6>
                <p class="text-muted small mb-3">คู่มือและขั้นตอนการใช้งานระบบสำหรับบุคลากร</p>
                <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> กลับไปภาพรวม', ['/me'], ['class' => 'btn btn-outline-primary btn-sm rounded-pill']) ?>
            </div>
        </div>
    </div>
</div>
