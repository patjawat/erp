<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <a href="<?= Url::to(['/health/default/index']) ?>" class="btn <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
<i data-lucide="layout-grid"></i>  
        ภาพรวม
    </a>
    <a href="<?= Url::to(['/health/health-screen']) ?>" class="btn <?= $active !== 'list' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i data-lucide="scan-heart"></i> 
        ทะเบียนข้อมูลสุขภาพ
    </a>

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
                <?= Html::a('<i class="fa-solid fa-microscope me-1"></i> Lab', ['/health/health-lab'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fas fa-dna me-1"></i> โรคประวัติครอบครัว', ['/health/health-family-disease'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fas fa-heartbeat me-1"></i> ประวัติเจ็บป่วยปีก่อน', ['/health/health-chronic-disease'], ['class' => 'dropdown-item']) ?>
            </li>
        </ul>
    </div>
</div>