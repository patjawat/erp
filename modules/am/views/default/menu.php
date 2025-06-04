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

$menus = [
    [
    'title' => 'Dashboard',
     'active' => 'index',
    'url' => ['/am'],
    'icon' => '<i class="fa-solid fa-gauge-high text-primary me-1"></i>'
    ],
        [
        'title' => 'ทะเบียนทรัพย์สิน',
        'active' => 'asset',
        'url' => ['/am/asset'],
        'icon' => '<i class="bi bi-ui-checks text-primary me-2"></i>'
        ],
        [
        'title' => 'รายงานค่าเสื่อม',
        'active' => 'report',
        'url' => ['/am/report'],
        'icon' => '<i class="fa-solid fa-chart-simple text-primary me-2"></i>'
        ],

];
?>
<?php if($layout == 'horizontal'):?>
<?php foreach($menus as $menu):?>
<li class="nav-item">
    <?=Html::a($menu['icon'].$menu['title'],$menu['url'],['class' => 'nav-link ' . (isset($active) && $active == $menu['active'] ? 'active' : '')])?>
</li>
<?php endforeach;?>

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