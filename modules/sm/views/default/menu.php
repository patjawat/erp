<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * เมนูหลักงานพัสดุ (ใช้ร่วมกันระหว่างโมดูล sm และ purchase)
 *
 * @var string $active 'dashboard' | 'order' | 'tor' | 'contract' | 'guarantee' | 'setting'
 *
 * ปุ่ม บริหารสัญญา / หลักประกัน ยังปิดไว้ (ยังไม่มี controller)
 * เมื่อสร้าง controller ใน modules/purchase แล้ว ให้เปลี่ยน <span> เป็น <a href="..."> ได้เลย
 * และต้องลงทะเบียน route ใหม่กับ role 'purchase' ในระบบจัดการสิทธิ์ก่อน (AccessControl อ่านสิทธิ์จากฐานข้อมูล)
 * มิฉะนั้นจะถูกปฏิเสธการเข้าถึง
 */
$active = $active ?? '';
?>
<nav class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end" aria-label="เมนูงานพัสดุ">
    <a href="<?= Url::to(['/sm']) ?>"
        class="btn btn-sm <?= $active === 'dashboard' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">
        <i class="bi bi-speedometer2 me-1"></i>ภาพรวม
    </a>

    <a href="<?= Url::to(['/purchase/order']) ?>"
        class="btn btn-sm <?= $active === 'order' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">
        <i class="bi bi-cart-check me-1"></i>จัดซื้อจัดจ้าง
    </a>

    <a href="<?= Url::to(['/purchase/tor']) ?>"
        class="btn btn-sm <?= $active === 'tor' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">
        <i class="bi bi-file-earmark-text me-1"></i>เขียน TOR
    </a>

    <span class="btn btn-sm btn-outline-secondary rounded-pill px-3 disabled" aria-disabled="true"
        title="อยู่ระหว่างพัฒนา">
        <i class="bi bi-file-earmark-check me-1"></i>บริหารสัญญา
    </span>

    <span class="btn btn-sm btn-outline-secondary rounded-pill px-3 disabled" aria-disabled="true"
        title="อยู่ระหว่างพัฒนา">
        <i class="bi bi-shield-check me-1"></i>หลักประกัน
    </span>

    <div class="dropdown">
        <button type="button"
            class="btn btn-sm <?= $active === 'setting' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3 dropdown-toggle"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-gear me-1"></i>ตั้งค่า
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><?= Html::a('<i class="bi bi-shop me-2"></i>ผู้แทนจำหน่าย', ['/sm/vendor'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-box-seam me-2"></i>วัสดุ', ['/sm/product', 'title' => 'ตั้งค่าวัสดุ'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-pc-display me-2"></i>ครุภัณฑ์', ['/sm/asset-item', 'title' => 'ตั้งค่าครุภัณฑ์'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-tags me-2"></i>ประเภทวัสดุ', ['/sm/product-type', 'title' => 'ตั้งค่าประเภทวัสดุ'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-rulers me-2"></i>หน่วยนับ', ['/sm/product-unit', 'title' => 'หน่วยนับ'], ['id' => 'unit', 'class' => 'dropdown-item']) ?></li>
        </ul>
    </div>
</nav>
