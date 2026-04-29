<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <a href="<?= Url::to(['/am']) ?>" class="btn <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i data-lucide="layout-grid"></i>
        ภาพรวม
    </a>
    <div class="dropdown d-inline-block">
        <button class="btn <?= in_array($active, ['land','building','structure','equip'], true) ? 'btn-primary' : 'btn-outline-primary' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-star text-warning me-1"></i>
            <span class="d-none d-sm-inline">ทะเบียนทรัพย์สิน</span>
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a('<i data-lucide="map-pin-house" class="me-2" style="width:1rem;height:1rem;"></i> ที่ดิน', ['/am/land'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="building-2" class="me-2" style="width:1rem;height:1rem;"></i> อาคาร', ['/am/building'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="construction" class="me-2" style="width:1rem;height:1rem;"></i> สิ่งปลูกสร้าง', ['/am/structure'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="package" class="me-2" style="width:1rem;height:1rem;"></i> ครุภัณฑ์', ['/am/equip'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>

    <div class="dropdown d-inline-block">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 3v12" />
                <path d="m8 11 4 4 4-4" />
                <path d="M8 5H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-4" />
            </svg>
            งานครุภัณฑ์
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a('<i data-lucide="package-plus" class="me-2" style="width:1rem;height:1rem;"></i> รับครุภัณฑ์หลายเครื่อง', ['/am/asset/bulk-create'], ['class' => 'dropdown-item']) ?></li>
            <!-- <li>
                <hr class="dropdown-divider">
            </li> -->
            <!-- <li><?= Html::a('<i data-lucide="arrow-left-right" class="me-2" style="width:1rem;height:1rem;"></i> โอนย้าย', ['/am/asset/transfer'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="wrench" class="me-2" style="width:1rem;height:1rem;"></i> ส่งซ่อม', ['/am/asset/repair'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="trash-2" class="me-2" style="width:1rem;height:1rem;"></i> จำหน่าย', ['/am/asset/dispose'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="qr-code" class="me-2" style="width:1rem;height:1rem;"></i> พิมพ์ QR', ['/am/asset/print-qr'], ['class' => 'dropdown-item']) ?></li> -->
            <!-- <li>
                <hr class="dropdown-divider">
            </li> -->
            <li><?= Html::a('<i data-lucide="calendar" class="me-2" style="width:1rem;height:1rem;"></i> ประมวลผลรายเดือน', ['/am/depreciation/monthly-processing'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="file-text" class="me-2" style="width:1rem;height:1rem;"></i> รายงานค่าเสื่อมรายเดือน', ['/am/report/monthly-depreciation'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="file-check" class="me-2" style="width:1rem;height:1rem;"></i> ตรวจนับพัสดุประจำปี', ['/am/audit'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="trash-2" class="me-2" style="width:1rem;height:1rem;"></i> จำหน่ายพัสดุ', ['/am/disposal'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>

    <!-- <a href="<?= Url::to(['/am/report']) ?>" class="btn <?= $active !== 'report' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 12h4"></path>
            <path d="M10 8h4"></path>
            <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
            <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
            <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
        </svg>
        รายงานค่าเสื่อม
    </a> -->


    <div class="dropdown">
        <button class="btn <?= $active !== 'setting' ? 'btn-outline-primary' : 'btn-primary' ?> dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings">
                <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
                <circle cx="12" cy="12" r="3" />
            </svg>
            <span class="d-none d-sm-inline">ตั้งค่า</span>
        </button>

        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            <li>
                <?= Html::a(' <i class="bi bi-ui-checks text-primary me-1"></i> กลุ่ม', ['/am/asset-group'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a(' <i class="bi bi-ui-checks text-primary me-1"></i> ประเภท', ['/am/asset-type'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a(' <i class="bi bi-ui-checks text-primary me-1"></i> หมวดหมู่', ['/am/asset-category'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a(' <i class="bi bi-ui-checks text-primary me-1"></i> FSN', ['/am/fsn'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a(' <i class="bi bi-ui-checks text-primary me-1"></i> กำหนดชื่อครุภัณฑ์', ['/am/asset-item'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-hashtag me-1"></i> รูปแบบ FSN ครุภัณฑ์', ['/am/setting/fsn-format'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่าทรัพย์สิน (ทั้งหมด)', ['/am/setting'], ['class' => 'dropdown-item']) ?>
            </li>

        </ul>
    </div>


</div>