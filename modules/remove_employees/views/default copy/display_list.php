<?php
use app\components\SiteHelper;
use frontend\models\Employee;;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use common\components\HrHelper;
use app\models\Employees;
use app\themes\assets\AppAsset;
$assets = AppAsset::register($this);

?>


<?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'layout' => "{summary}\n{items}\n<div class='d-flex justify-content-center'>{pager}</div>",
        'pager' => [
            'class' => 'yii\bootstrap5\LinkPager'
        ],
        'tableOptions' => ['class' => 'table table-striped project-list-table  align-middle table-borderless table-hover'],

        'columns' => [
            [
                'attribute' => 'lname',
                'header' => '   ขื่อ-สกุล',
                'format' => 'raw',
                'value' => function ($model) {
                    return '<h2 class="table-avatar">
            '.Html::a(Html::img('@web/img/profiles/avatar-13.jpg'),['view','id' => $model->id],['class' => 'avatar']).
            Html::a($model->fname.' '.$model->lname,['view','id' => $model->id]).'
												</h2>';
                    // return $model->lname.
                    // '<img src="https://bootdey.com/img/Content/avatar/avatar.png" alt="" class="avatar-sm rounded-circle me-2" />';
                }
            ],
            [
                'attribute' => 'emp_id',
                'format' => 'raw',
                'label' => 'รหัสพนักงาน',
                'value'=>function($model, $key, $index, $column){
                    // return HrHelper::getNamePosition_Department($model->position_id)  ;
                  }
            ],
            'อีเมลย์',
            [
                'attribute' => 'position_id',
                'format' => 'raw',
                'label' => 'ที่อยู่',
                'value'=>function($model, $key, $index, $column){
                    // return HrHelper::getNamePosition_Department($model->position_id)  ;
                  }
            ],
            [
                'attribute' => 'โทรศัพท์',
                'format' => 'text',
                'value'=>function($model, $key, $index, $column){
                    // return HrHelper::getNamePosition_Department($model->department)  ;
                  }
            ],
            [
                'attribute' => 'เข้าทำงาน',
                'format' => 'text',
                'value' => function($model, $key, $index, $column){
                    // return isset($model->district) ? (HrHelper::getLocation($model->district,$model->amphure,$model->province)):''   ;
                  }
            ],
            [
                'attribute' => 'ตำแหน่ง',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column){
                    return '<span class="badge bg-inverse-info">Client</span>';
                    // return isset($model->district) ? (HrHelper::getLocation($model->district,$model->amphure,$model->province)):''   ;
                  }
            ],
            [
                'header' => 'ดำเนินการ',
                'headerOptions' => ['style' => 'text-align: center !important;'],
                'format' => 'raw',
                'value' => function($model, $key, $index, $column){
                    return SiteHelper::ActionColumn('employees',$model->id);

                }
            ]
        ]
            
    ]); ?>
