<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\grid\GridView;

$this->title = 'ระบบจัดการผู้ใช้งาน';
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?=$this->title;?>  
<?php $this->endBlock(); ?>
<div class="card">
  <div class="card-body d-flex justify-content-between align-items-center">
    <?=$this->render('../default/navlink')?>
      <?=$this->render('_search', ['model' =>$searchModel])?>
  </div>
</div>

<style>
#user-grid table thead {
    background-color: #fff;
}

#user-grid-container {
    height: 480px;
}
</style>



<?php
$layout = <<< HTML
<div class="clearfix"></div>

{items}
<div class="d-flex justify-content-between align-items-center">
                {summary}          
                  {pager}
</div>
HTML;

?>
<?= GridView::widget([
  'id' => 'user-grid',
  'dataProvider' => $dataProvider,
  //   'filterModel' => $searchModel,
  'pjax' => true,
  'showHeader' => true,
  'showPageSummary' => false,
  'layout' => '{items}{pager}',
  'floatHeader' => true,
  'floatHeaderOptions' => ['scrollingTop' => '20'],
  'perfectScrollbar' => true,
  'footerRowOptions' => ['style' => 'font-weight:bold;text-decoration: underline; position: absolute'],
  'layout' => $layout,
  'columns' => [
    [
      'class'=>'kartik\grid\SerialColumn',
      'contentOptions'=>['class'=>'kartik-sheet-style'],
      'width'=>'36px',
      'pageSummary'=>'Total',
      'pageSummaryOptions' => ['colspan' => 6],
      'header'=>'',
      'headerOptions'=>['class'=>'kartik-sheet-style']
  ],
    [
      'attribute' => 'username',
      'header' =>'<i class="far fa-user"></i> | ชื่อเข้าใช้งาน',
      'value' => function ($model, $key, $index, $widget) {
          return $model->username;
      }
  ],
  [
    // 'attribute' => 'email',
    'header' =>'<i class="fas fa-address-card"></i> | ชื่อ-นามสกุล',
    'value' => function ($model, $key, $index, $widget) {
      try {
        return $model->employee->fullname;
      } catch (\Throwable $th) {
        return '-';
      }
    }
],
    [
      'attribute' => 'status',
      'class' => 'kartik\grid\BooleanColumn',
      'vAlign' => 'middle',
      'header' => '<i class="fas fa-unlock-alt"></i> สถานะ',
      'format' => 'html',
      'filter' => $searchModel->itemStatus,
      'value' => function ($model) {
        return $model->statusName == 'Active' ? '<span class="text-success">' . $model->statusName . '</span>' : $model->statusName;
      }
    ],
    // 'created_at:dateTime',
    [
      'attribute' => 'created_at',
      'header' => '<i class="fas fa-calendar-alt"></i> สร้างเมื่อ',
      'format' => 'html',
      'filter' => $searchModel->itemStatus,
      'value' => function ($model) {
        return Yii::$app->thaiFormatter->asDateTime($model->created_at, 'short');
      }
    ],
    [
      'class' => 'app\modules\usermanager\grid\ActionColumn',
      'header' => '<center>ดำเนินการ<center>',
      'width' => '130px',
      'dropdown' => false,
      'vAlign' => 'middle',
    ],

  ],
]); ?>