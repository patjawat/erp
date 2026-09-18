<?php
use yii\helpers\Url;

/**
 * เมนูหลักของโมดูลซักฟอก (page-nav มาตรฐาน ERP — btn-primary/outline + Bootstrap Icons)
 * @var string $active dashboard|collection|inspect|processing|finish|issue|stock|report|setting
 *
 * แท็บที่ยังไม่สร้าง route ในเฟสปัจจุบันจะเป็นปุ่ม disabled (เติมเมื่อถึงเฟสนั้น)
 */
$active = $active ?? '';
$btn = fn(string $key) => 'btn ' . ($active === $key ? 'btn-primary' : 'btn-outline-primary');
?>
<nav class="laundry-nav mb-3" aria-label="เมนูงานซักฟอก">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= Url::to(['/laundry/dashboard/index']) ?>" class="<?= $btn('dashboard') ?>">
            <i class="bi bi-speedometer2 me-1"></i>ภาพรวม
        </a>
        <a href="<?= Url::to(['/laundry/collection/index']) ?>" class="<?= $btn('collection') ?>">
            <i class="bi bi-basket3 me-1"></i>รับผ้า
        </a>
        <a href="<?= Url::to(['/laundry/collection/inspect']) ?>" class="<?= $btn('inspect') ?>">
            <i class="bi bi-clipboard-check me-1"></i>ตรวจรับผ้า
        </a>
        <a href="<?= Url::to(['/laundry/processing/index', 'stage' => 'WASH']) ?>" class="<?= $btn('wash') ?>">
            <i class="bi bi-droplet-half me-1"></i>ซักผ้า
        </a>
        <a href="<?= Url::to(['/laundry/processing/index', 'stage' => 'DRY']) ?>" class="<?= $btn('dry') ?>">
            <i class="bi bi-wind me-1"></i>อบผ้า
        </a>
        <a href="<?= Url::to(['/laundry/inventory/index', 'section' => 'finish']) ?>" class="<?= $btn('finish') ?>">
            <i class="bi bi-ui-checks me-1"></i>นับ–รีด–QC
        </a>
        <span class="btn btn-outline-secondary disabled" title="อยู่ระหว่างพัฒนา (เฟส 4)">
            <i class="bi bi-box-arrow-right me-1"></i>ส่งผ้า/เบิกจ่าย
        </span>
        <div class="dropdown">
            <button type="button" class="btn <?= $active === 'stock' ? 'btn-primary' : 'btn-outline-primary' ?> dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-shop me-1"></i>คลังผ้า
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/stock/main']) ?>"><i class="bi bi-building me-2"></i>คลังหลัก</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/stock/sub']) ?>"><i class="bi bi-diagram-3 me-2"></i>คลังย่อยหน่วยงาน</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/annual-count/index']) ?>"><i class="bi bi-calendar-check me-2"></i>สอบยอดสิ้นปี</a></li>
            </ul>
        </div>
        <div class="dropdown">
            <button type="button" class="btn <?= $active === 'report' ? 'btn-primary' : 'btn-outline-primary' ?> dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-journal-text me-1"></i>รายงาน
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/collection/report']) ?>"><i class="bi bi-speedometer me-2"></i>น้ำหนักผ้ารายหน่วยงาน</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/processing/history', 'stage' => 'WASH']) ?>"><i class="bi bi-clock-history me-2"></i>ประวัติรอบซัก</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/processing/history', 'stage' => 'DRY']) ?>"><i class="bi bi-clock-history me-2"></i>ประวัติรอบอบ</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/machine-report/index']) ?>"><i class="bi bi-gear-wide-connected me-2"></i>ประสิทธิภาพเครื่อง</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/procurement/index']) ?>"><i class="bi bi-cart-plus me-2"></i>ส่วนขาด/จัดซื้อ</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/audit/index']) ?>"><i class="bi bi-clipboard2-pulse me-2"></i>ตรวจสอบยอดผ้า</a></li>
            </ul>
        </div>
        <div class="dropdown">
            <button type="button" class="btn <?= $active === 'setting' ? 'btn-primary' : 'btn-outline-secondary' ?> dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear me-1"></i>ตั้งค่า
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/setting/unit']) ?>"><i class="bi bi-diagram-3 me-2"></i>หน่วยงานซักฟอก</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/inventory/index', 'section' => 'setup']) ?>"><i class="bi bi-collection me-2"></i>ประเภทผ้า / ยอดตั้งต้น</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/laundry/setting/machine']) ?>"><i class="bi bi-cpu me-2"></i>เครื่องซัก–อบ</a></li>
            </ul>
        </div>
    </div>
</nav>
