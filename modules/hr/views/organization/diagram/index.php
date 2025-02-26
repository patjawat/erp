<?php
use app\models\Tree;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\tree\TreeView;


use kartik\tree\TreeViewInput;

// use muhsamsul\treeimage\TreeImage;
use app\widgets\orgchart\TreeImage;
use app\modules\hr\models\Organization;
$this->title = "ผังโครงสร้างองค์กร";

$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-diagram-3"></i> <?=$this->title;?>  
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/hr/views/employees/menu') ?>
<?php $this->endBlock(); ?>
<style>
    .kv-tree-root.kv-root-heading {
    font-weight: 300;
}
.kv-detail-crumbs .kv-crumb-active {
    font-weight: 300;
}
.kv-detail-container {
    border-radius: 9px;
    background-color: white;
}
</style>
<?php Pjax::begin(['id' => 'hr-container','enablePushState' => true,'timeout' => 50000 ]); ?>
<?php // $this->render('../menu')?>

<?php

echo TreeView::widget([
    'query' => Organization::find()->where(['tb_name' => 'diagram'])->addOrderBy('root, lft'), 
    'headingOptions' => ['label' => 'Store'],
    'rootOptions' => ['label'=>'<span class="text-primary">'.$this->title.'</span>'],
    'topRootAsHeading' => true, // this will override the headingOptions
    'fontAwesome' => true,
    'isAdmin' => false,
    'showIDAttribute' => false,
    'showNameAttribute' => false,
    'softDelete' => true,  
    'displayValue' => '0',
    'nodeAddlViews' => [kartik\tree\Module::VIEW_PART_1 => '@app/modules/hr/views/organization/diagram/_form'],   
    // 'nodeView' => '@app/modules/hr/views/categorise/_form',['model' => $model],
    'iconEditSettings'=> [
        'show' => 'list',
        'show' => 'none',
        'listData' => [
            'folder' => 'Folder',
            'file' => 'File',
            'mobile' => 'Phone',
            'bell' => 'Bell',
        ]
    ],

    'softDelete' => true,
    'cacheSettings' => ['enableCache' => true],
    'mainTemplate'=>'<div class="row">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                            {wrapper}
                        </div>
                        <div class="col-xl-9 col-lg-9 col-md-6 col-sm-12">
                            {detail}
                        </div>
                     </div>',


]);
$js = <<< JS


JS;
$this->registerJS($js);
?>
<?php Pjax::end();?>
