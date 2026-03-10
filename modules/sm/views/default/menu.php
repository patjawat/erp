<?php

use yii\helpers\Url;
use yii\helpers\Html;

$layout = app\components\SiteHelper::getInfo()['layout'];
$active = $active ?? '';
?>

<div class="d-flex gap-2">
    <a href="<?= Url::to(['/sm']) ?>"
        class="btn <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        ภาพรวม
    </a>
    <a href="<?= Url::to(['/purchase/order']) ?>"
        class="btn <?= $active !== 'order' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-check-icon lucide-book-check">
            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"></path>
            <path d="m9 9.5 2 2 4-4"></path>
        </svg>
        ทะเบียนประวัติ
    </a>
    <div class="dropdown">
        <button class="btn <?= $active !== 'setting' ? 'btn-outline-primary' : 'btn-primary' ?> dropdown-toggle"
            type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-settings-icon lucide-settings">
                <path
                    d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
                <circle cx="12" cy="12" r="3" />
            </svg>
            <span class="d-none d-sm-inline">ตั้งค่า</span>
        </button>

        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            <li>
                <?= Html::a('<i class="fa-solid fa-caret-right me-1"></i> ผู้แทนจำหน่าย', ['/sm/vendor'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-caret-right me-1"></i> วัสดุ', ['/sm/product', 'title' => 'ตั้งค่่าวัสดุ'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-caret-right me-1"></i> ประเภทวัสดุ', ['/sm/product-type', 'title' => 'ตั้งค่่าประเภทวัสดุ'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-caret-right me-1"></i> หน่วยนับ', ['/sm/product-unit', 'title' => 'หน่วยนับ'], ['id' => 'unit', 'class' => 'dropdown-item']) ?>
            </li>


        </ul>
    </div>
</div>