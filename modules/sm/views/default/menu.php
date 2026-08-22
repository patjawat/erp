<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * เมนูหลักงานพัสดุ (ใช้ร่วมกันระหว่างโมดูล sm และ purchase)
 *
 * @var string $active 'dashboard' | 'order' | 'tor' | 'contract' | 'guarantee' | 'doc' | 'setting'
 *
 * รูปทรงปุ่มยึดตามแถบเมนูของโมดูลอื่น คือ btn-outline-primary เมื่อไม่ได้เลือก
 * และ btn-primary เมื่อเลือกอยู่ ขนาดปกติ ไม่ใส่ rounded-pill
 * (นับจากไฟล์เมนูทั้งระบบ 45 ไฟล์ มี 35 ไฟล์ที่ไม่ใช้ pill และคู่สีนี้ตรงกันทุกไฟล์
 * ที่ตรวจ — hr, dms, plan, attendance, helpdesk2, leave, inventoryV2)
 *
 * ทุกปุ่มในแถบนี้ชี้ไป controller ที่กันสิทธิ์ด้วย role 'purchase' เอง และ route ถูกใส่ไว้ใน
 * allow list ของ AccessControl ระดับแอปแล้ว (config/web.php) จึงใช้งานได้โดยไม่ต้องไปผูก
 * route ในระบบจัดการสิทธิ์ก่อน
 */
$active = $active ?? '';

/** คืน class ของปุ่มตามสถานะที่เลือกอยู่ — เขียนที่เดียวกันปุ่มหลุดมาตรฐานทีละปุ่ม */
$btn = function (string $key) use ($active): string {
    return 'btn ' . ($active === $key ? 'btn-primary' : 'btn-outline-primary');
};
?>
<nav class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end" aria-label="เมนูงานพัสดุ">
    <a href="<?= Url::to(['/sm']) ?>" class="<?= $btn('dashboard') ?>">
        <i class="bi bi-speedometer2 me-1"></i>ภาพรวม
    </a>

    <a href="<?= Url::to(['/purchase/order']) ?>" class="<?= $btn('order') ?>">
        <i class="bi bi-cart-check me-1"></i>จัดซื้อจัดจ้าง
    </a>

    <a href="<?= Url::to(['/purchase/tor']) ?>" class="<?= $btn('tor') ?>">
        <i class="bi bi-file-earmark-text me-1"></i>เขียน TOR
    </a>

    <a href="<?= Url::to(['/purchase/contract']) ?>" class="<?= $btn('contract') ?>">
        <i class="bi bi-file-earmark-check me-1"></i>บริหารสัญญา
    </a>

    <a href="<?= Url::to(['/purchase/bond']) ?>" class="<?= $btn('guarantee') ?>">
        <i class="bi bi-shield-check me-1"></i>หลักประกัน
    </a>

    <a href="<?= Url::to(['/purchase/doc']) ?>" class="<?= $btn('doc') ?>">
        <i class="bi bi-printer me-1"></i>พิมพ์เอกสาร
    </a>

    <div class="dropdown">
        <button type="button" class="<?= $btn('setting') ?> dropdown-toggle"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-gear me-1"></i>ตั้งค่า
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><?= Html::a('<i class="bi bi-shop me-2"></i>ผู้แทนจำหน่าย', ['/sm/vendor'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-box-seam me-2"></i>วัสดุ', ['/sm/product', 'title' => 'ตั้งค่าวัสดุ'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-pc-display me-2"></i>ครุภัณฑ์', ['/sm/asset-item', 'title' => 'ตั้งค่าครุภัณฑ์'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-tags me-2"></i>ประเภทวัสดุ', ['/sm/product-type', 'title' => 'ตั้งค่าประเภทวัสดุ'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-rulers me-2"></i>หน่วยนับ', ['/sm/product-unit', 'title' => 'หน่วยนับ'], ['id' => 'unit', 'class' => 'dropdown-item']) ?></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><?= Html::a('<i class="bi bi-percent me-2"></i>อัตราภาษีหัก ณ ที่จ่าย', ['/purchase/wht-rate'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-shield-exclamation me-2"></i>เกณฑ์หลักประกัน', ['/purchase/bond-policy'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>
</nav>
