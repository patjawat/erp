<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\modules\usermanager\components\RouteRule;
use app\modules\usermanager\components\Configs;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\modules\usermanager\models\searchs\AuthItem */
/* @var $context app\modules\usermanager\components\ItemController */

$context = $this->context;
$labels = $context->labels();

$this->params['breadcrumbs'][] = $this->title;

$rules = array_keys(Configs::authManager()->getRules());
$rules = array_combine($rules, $rules);
unset($rules[RouteRule::RULE_NAME]);
$this->title = "กำหนดบทบาท";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-right-left"></i> <?=$this->title;?>  
<?php $this->endBlock(); ?>
<div class="card">
    <div class="card-body d-flex justify-content-between">
        <?=Html::a('<i class="fas fa-plus"></i> สร้างใหม่',['/usermanager/role/create'],['class'=>"btn btn-outline-primary"]);?>
        <?=$this->render('../default/navlink')?>
    </div>
</div>

<style>
#role-grid table thead {
    background-color: #fff;
}
#role-grid-container{
  height:480px;
}
</style>
<div class="d-flex justify-content-between align-items-center">

</div>

<div class="clearfix"></div>

              <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'id' => 'role-grid',
        'pjax' => true,
        'showHeader' => true,
        'showPageSummary' => false,
        'layout' => '{items}{pager}',
        'floatHeader' => true,
        'floatHeaderOptions' => ['scrollingTop' => '20'],
        'perfectScrollbar' => true,
        'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration: underline; position: absolute'],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'label' => 'ชื่อบทบาท',
            ],
            [
                'attribute' => 'ruleName',
                'label' => 'ชื่อกฏ',
                'filter' => $rules
            ],
            [
                'attribute' => 'description',
                'label' =>'รายละเอียด',
            ],
            [
              'class' => 'app\modules\usermanager\grid\ActionColumn',
              'header' => '<center>ดำเนินการ<center>',
              'width' => '130px',
              'dropdown' => false,
              'vAlign' => 'middle',
            ],
        ],
    ])
    ?>