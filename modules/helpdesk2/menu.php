<?php
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
<?php if($layout == 'horizontal'):?>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-gauge-high me-1"></i> Dashboard',['dashboard'],['class' => 'nav-link ' . (isset($active) && $active == 'dashboard' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="bi bi-ui-checks me-1"></i> ทะเบียนงานซ่อม',['index'],['class' => 'nav-link ' . (isset($active) && $active =='index' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="bi bi-ui-checks me-1"></i> ทะเบียนครุภัณฑ์',['asset'],['class' => 'nav-link ' . (isset($active) && $active =='asset' ? 'active' : '')])?>
</li>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?=(isset($active) && $active == 'setting' ? 'active' : '')?>" href="#"
        id="topnav-dashboard" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa-solid fa-gear me-1"></i> ตั้งค่า
        <i class="bx bx-chevron-down"></i>
    </a>
    <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
        <?=Html::a('<i class="fa-solid fa-caret-right me-2"></i> ประเภทอุปกรณ์',['/helpdesk/device-type'],['class' => 'dropdown-item'])?>

    </div>
</li>


<li class="nav-item">
    <?php else:?>
        
        <div class="d-flex gap-2">
            <?=Html::a('<i class="fa-solid fa-gauge-high me-1"></i> Dashboard',['dashboard'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="bi bi-ui-checks me-1"></i> ทะเบียนงานซ่อม',['index'],['class' => 'btn btn-light'])?>

</div>
<?php endif;?>