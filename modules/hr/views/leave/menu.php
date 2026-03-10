<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;

$listAssetGroups = Categorise::find()
    ->where(['name' => 'asset_group'])
    ->all();
?>

<div class="d-flex gap-2">
    <a href="<?= Url::to(['/hr/leave/dashboard']) ?>" class="btn <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
<i data-lucide="layout-grid"></i>  
        ภาพรวม
    </a>
    <a href="<?= Url::to(['/hr/leave/index']) ?>" class="btn <?= $active !== 'list' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-check-icon lucide-book-check">
            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"></path>
            <path d="m9 9.5 2 2 4-4"></path>
        </svg>
        ทะเบียนประวัติการลา
    </a>

    <a href="<?= Url::to(['/hr/leave/report']) ?>" class="btn <?= $active !== 'report' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-pie-icon lucide-chart-pie">
            <path d="M21 12c.552 0 1.005-.449.95-.998a10 10 0 0 0-8.953-8.951c-.55-.055-.998.398-.998.95v8a1 1 0 0 0 1 1z" />
            <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
        </svg>
        รายงานวันลา
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
                <?= Html::a('<i class="fa-solid fa-caret-right  me-1"></i> ประเภทการลา', ['/hr/leave-type'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-caret-right  me-1"></i> นโยบายการลา', ['/hr/leave-policies'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-caret-right  me-1"></i> กำหนดสิทธิลาพักผ่อน', ['/hr/leave-entitlements'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-caret-right  me-1"></i> กำหนดเวร 8', ['/hr/work-shift'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-caret-right  me-1"></i> วันหยุด', ['/hr/holiday'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-caret-right  me-1"></i> แบบฟอร์มใบลา', ['/formtemplate/leave-template'], ['class' => 'dropdown-item']) ?>
            </li>
<li>
    <?= Html::a(
    '<i class="bi bi-file-earmark-pdf me-1"></i> ตั้งค่าเทมเพลตใบลา (ใหม่)',
    ['/hr/leave-setting/leave-template-index'],
    ['class' => 'dropdown-item']
) ?>
</li>
        </ul>
    </div>

</div>