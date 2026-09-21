<?php

use yii\helpers\Url;
use yii\helpers\Html;

/** @var string $active */
$active = $active ?? '';
$expenseKeys = ['overview', 'parcel', 'personnel', 'expenses'];
$expenseActive = in_array($active, $expenseKeys, true);
?>

<nav class="d-flex flex-wrap justify-content-end gap-2" aria-label="เมนูแผนงบประมาณ">
    <a href="<?= Url::to(['/plan/dashboard']) ?>"
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

    <a href="<?= Url::to(['/plan/annual']) ?>"
        class="btn <?= $active !== 'annual' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-calendar3 me-1"></i>แผนประจำปี
    </a>

    <a href="<?= Url::to(['/plan/annual/income']) ?>"
        class="btn <?= $active !== 'income' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-cash-coin me-1"></i>แผนรายรับ
    </a>

    <div class="dropdown">
        <button class="btn <?= $expenseActive ? 'btn-primary' : 'btn-outline-primary' ?> dropdown-toggle"
            type="button" id="planExpenseMenu" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-receipt me-1"></i>แผนรายจ่าย
        </button>
        <ul class="dropdown-menu" aria-labelledby="planExpenseMenu">
            <li><?= Html::a('<i class="bi bi-clipboard-data me-2"></i> ภาพรวมรายจ่าย', ['/plan/overview'], ['class' => 'dropdown-item' . ($active === 'overview' ? ' active' : '')]) ?></li>
            <li><hr class="dropdown-divider"></li>
            <li><?= Html::a('<i class="bi bi-box-seam me-2"></i> แผนพัสดุ', ['/plan/parcel'], ['class' => 'dropdown-item' . ($active === 'parcel' ? ' active' : '')]) ?></li>
            <li><?= Html::a('<i class="bi bi-person-plus me-2"></i> แผนบุคลากร', ['/plan/personnel'], ['class' => 'dropdown-item' . ($active === 'personnel' ? ' active' : '')]) ?></li>
            <li><?= Html::a('<i class="bi bi-cash-stack me-2"></i> แผนค่าใช้สอย', ['/plan/expenses'], ['class' => 'dropdown-item' . ($active === 'expenses' ? ' active' : '')]) ?></li>
        </ul>
    </div>

    <div class="dropdown">
        <button class="btn <?= $active !== 'setting' ? 'btn-outline-primary' : 'btn-primary' ?> dropdown-toggle"
            type="button" id="planSettingMenu" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
                <circle cx="12" cy="12" r="3" />
            </svg>
            <span class="d-none d-sm-inline">ตั้งค่า</span>
        </button>
        <ul class="dropdown-menu" aria-labelledby="planSettingMenu">
            <li><?= Html::a('<i class="fa-solid fa-calendar-check me-2"></i> รอบทำแผน', ['/plan/plan-period'], ['class' => 'dropdown-item']) ?></li>
            <li><hr class="dropdown-divider"></li>
            <li><?= Html::a('<i class="fa-solid fa-caret-right me-2"></i> ประเภท', ['/plan/plan-type'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="fa-solid fa-caret-right me-2"></i> หมวดหมู่', ['/plan/plan-category'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="fa-solid fa-caret-right me-2"></i> รายการ', ['/plan/plan-item'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>
</nav>
