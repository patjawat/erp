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
    <a href="<?= Url::to(['/hr/development/dashboard']) ?>" class="btn <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        ภาพรวม
    </a>
    <a href="<?= Url::to(['/hr/development', 'status' => 'Checking']) ?>" class="btn <?= $active !== 'index' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-check-icon lucide-book-check">
            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" />
            <path d="m9 9.5 2 2 4-4" />
        </svg>
        ทะเบียนประวัติ
    </a>
</div>