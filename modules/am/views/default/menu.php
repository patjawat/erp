<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem;
$layout = app\components\SiteHelper::getInfo()['layout'];
$listAssetGroups = Categorise::find()
->where(['name' => 'asset_group'])
// ->andWhere(['NOT',['code'=>[1]]])
->all();
$layout = app\components\SiteHelper::getInfo()['layout'];

?>
<?php if($layout == 'horizontal'):?>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-gauge-high text-primary me-1"></i> Dashboard',['/am'],['class' => 'nav-link ' . (isset($active) && $active == 'index' ? 'active' : '')])?>
</li>


<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?=(isset($active) && $active == 'asset' ? 'active' : '')?>" href="#" id="topnav-dashboard" role="button" data-bs-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        <i class="bi bi-ui-checks text-primary me-1"></i> ทะเบียนทรัพย์สิน
        <i class="bx bx-chevron-down"></i>
    </a>
    <div class="dropdown-menu " aria-labelledby="topnav-dashboard">
        <?= Html::a('<i class="fa-solid fa-map me-2"></i> ที่ดิน', ['/am/land'], ['class' => 'dropdown-item']) ?>
        <?= Html::a('<i class="fa-solid fa-house me-2"></i> อาคาร', ['/am/building'], ['class' => 'dropdown-item']) ?>
        <?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i> สิ่งปลูกสร้าง', ['/am/construction'], ['class' => 'dropdown-item']) ?>
        <?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i> ครุภัณฑ์', ['/am/asset'], ['class' => 'dropdown-item']) ?>

    </div>
</li>

<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-chart-pie me-1"></i> รายงานค่าเสื่อม',['/am/report'],['class' => 'nav-link ' . (isset($active) && $active == 'report' ? 'active' : '')])?>
</li>
<li class="nav-item">
    </li>
    
    
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle <?=(isset($active) && $active == 'asset' ? 'active' : '')?>" href="#" id="topnav-dashboard" role="button" data-bs-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
       <i class="fa-solid fa-gear me-1"></i> ตั้งค่าทรัพย์สิน
        <i class="bx bx-chevron-down"></i>
    </a>
    <div class="dropdown-menu " aria-labelledby="topnav-dashboard">
        <?=Html::a(' <i class="bi bi-ui-checks text-primary me-1"></i> ครุุภัณฑ์',['/am/asset-item'],['class' => 'dropdown-item'])?>
        <?=Html::a(' <i class="bi bi-ui-checks text-primary me-1"></i> วัสดุ',['/am/asset-item'],['class' => 'dropdown-item'])?>


    </div>
</li>


<?php else:?>
<div class="d-flex gap-2">
    <?=Html::a('<i class="fa-solid fa-chart-simple"></i> Dashbroad',['/am'],['class' => 'btn btn-light'])?>
    <div class="btn-group">
        <?=Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนทรัพย์สิน',['/am/asset'],['class' => 'btn btn-light'])?>
    </div>
    <?=Html::a('<i class="fa-solid fa-chart-simple"></i> รายงานค่าเสื่อม',['/am/report'],['class' => 'btn btn-light'])?>
    <?=Yii::$app->user->can('admin') ? Html::a('<i class="fa-solid fa-gear me-2"></i> ตั้งค่าทรัพย์สิน',['/am/setting'],['class' => 'btn btn-light']) : ''?>
</div>
<?php endif;?>