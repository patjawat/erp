<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem;

$listAssetGroups = Categorise::find()
    ->where(['name' => 'asset_group'])
    // ->andWhere(['NOT',['code'=>[1]]])
    ->all();

$layout = app\components\SiteHelper::getInfo()['layout'];
?>


    <div class="d-flex gap-2">
        <a href="<?= Url::to(['dashboard']) ?>" class="btn <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                <rect width="7" height="5" x="3" y="16" rx="1"></rect>
            </svg>
            ภาพรวม
        </a>
        <a href="<?= Url::to(['index']) ?>" class="btn <?= $active !== 'index' ? 'btn-outline-primary' : 'btn-primary' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-check-icon lucide-book-check">
                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"></path>
                <path d="m9 9.5 2 2 4-4"></path>
            </svg>
            ทะเบียนงานซ่อม
        </a>
        <a href="<?= Url::to(['asset']) ?>" class="btn <?= $active !== 'asset' ? 'btn-outline-primary' : 'btn-primary' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 12h4"></path>
                <path d="M10 8h4"></path>
                <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
                <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
                <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
            </svg>
            ทะเบียนครุภัณฑ์
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
                    <?= Html::a('<i class="fa-solid fa-caret-right me-2"></i> ประเภทอุปกรณ์', ['/helpdesk/device-type'], ['class' => 'dropdown-item']) ?>
                </li>
                <li>
                    <?= Html::a('<i class="fa-solid fa-caret-right me-2"></i> แบบฟอร์มแจ้งซ่อม', ['/helpdesk/service/form-layout-service-setting'], ['class' => 'dropdown-item']) ?>
                </li>

            </ul>
        </div>
    </div>