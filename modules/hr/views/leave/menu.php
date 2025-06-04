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
$menus = [
    [
    'title' => 'Dashboard',
     'active' => 'dashboard',
    'url' => ['/hr/leave/dashboard'],
    'icon' => '<i class="fa-solid fa-gauge-high text-primary me-1"></i>'
    ],
        [
        'title' => 'ทะเบียนประวัติ',
        'active' => 'index',
        'url' => ['/hr/leave','status' => 'Checking'],
        'icon' => '<i class="fa-solid fa-list-ul text-primary me-1"></i>'
        ],
    [
    'title' => 'รายงานวันลา',
    'active' => 'report',
    'url' => ['/hr/leave/report'],
    'icon' => '<i class="fa-solid fa-chart-simple text-primary me-1"></i>'
    ],
        [
    'title' => 'ตั้งค่า',
    'active' => 'setting',
    'url' => ['/hr/leave-policies'],
    'icon' => '<i class="fa-solid fa-gear text-primary me-1"></i>'
    ]
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
    <?=Html::a('<i class="fa-solid fa-gauge-high me-1"></i> Dashboard',['/hr/leave/dashboard'],['class' => 'btn btn-light'])?>
        <?=Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติ',['/hr/leave','status' => 'Checking'],['class' => 'btn btn-light'])?>
        <?=Html::a('<i class="fa-solid fa-chart-simple me-1"></i> รายงานวันลา',['/hr/leave/report'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-gear"></i> ตั้งค่า',['/hr/leave-policies'],['class' => 'btn btn-light'])?>
</div>
<?php endif;?>