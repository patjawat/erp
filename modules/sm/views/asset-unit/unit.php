<?php

use app\modules\am\components\AssetHelper;
use app\modules\am\models\Fsn;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\components\SiteHelper;
use app\components\CategoriseHelper;
/** @var yii\web\View $this */
/** @var app\modules\am\models\FsnSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'การตั้งค่าครุภัณฑ์';
$this->params['breadcrumbs'][] = $this->title;

$group = Yii::$app->request->get('group');
$category_id = Yii::$app->request->get('category_id');
$name = Yii::$app->request->get('name');

?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-gear"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'sm-container', 'enablePushState' => true, 'timeout' => 5000]);?>





<div class="row justify-content-center">

      <div class="col">
            <div class="card mb-0">
                  <div class="card-body">
                        <?=app\components\AppHelper::Btn([
                        'title' => "<i class='fa-solid fa-circle-plus'></i> สร้างหน่วย",
                        'url' =>['/sm/asset-unit/create-unit' ],
                        'modal' => true,
                        'size' => 'lg'])?>
                  </div>
            </div>
      

            <div class="card mb-0">

            <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => true,
            'layout' => '{items}',
            'columns' => [
                  [
                  'attribute' => 'title',
                  'format' => 'raw',
                  'header' => 'ชื่อหน่วย',
                  'value' => function($model){
                              return $model->title;
                  }
                  ],
                  [
                  'format' => 'raw',
                  'header' => 'ดำเนินการ',
                  'hAlign' => 'center',
                  'vAlign' => 'middle',
                  'width' => '90px',
                  'value' => function($model){
                        return '<div clas="d-flex gap-3">'.Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary open-modal me-2', 'data' => ['size' => 'modal-lg']])
                        .Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $model->id], [
                              'class' => 'btn btn-sm btn-danger',
                              'data' => [
                              'confirm' => 'Are you sure you want to delete this item?',
                              'method' => 'post',
                              ],
                        ]).'</div>';
                  }
                  ],
            
                  
            ],
      ]); ?>        
            </div>
     </div>

<?php Pjax::end();?>
<?php
$js = <<< JS


JS;
$this->registerJS($js)

?>
</div>