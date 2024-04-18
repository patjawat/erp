<?php
use app\components\SiteHelper;
use frontend\models\Employee;;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use common\components\HrHelper;
use app\models\Employees;
// use app\themes\assets\AppAsset;
// $assets = AppAsset::register($this);

?>

<style>
/* table {
    border-collapse: separate;
    border-spacing: 0 15px;
}

th,
td {
    vertical-align: middle;
    padding: 5px;
}

tr {
    border-radius: 11px;
} */

.grid-view table thead {
    background-color: #FF0000;
}
</style>


<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{summary}\n{items}\n<div class='d-flex justify-content-center'>{pager}</div>",
        'pager' => [
            'class' => 'yii\bootstrap5\LinkPager'
        ],
        'responsive' => false,
    'bordered' => false,
    'striped' => true,
    'condensed' => true,
    'hover' => true,
        // 'tableOptions' => ['class' => 'table table-striped project-list-table  align-middle table-borderless table-hover'],

        'columns' => [
            [
                'attribute' => 'lname',
                'header' => '   ขื่อ-สกุล',
                'format' => 'raw',
                'value' => function ($model) {
                    return '<div class="d-flex justify-content-start align-items-center user-name"><div class="avatar-wrapper"><div class="avatar me-2"><img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/avatars/7.png" alt="Avatar" class="rounded-circle"></div></div><div class="d-flex flex-column"><span class="emp_name text-truncate">Olivette Gudgin</span><small class="emp_post text-truncate text-muted">Paralegal</small></div></div>';
            //         return '<h2 class="table-avatar">
            // '.Html::a(Html::img('@web/img/profiles/avatar-13.jpg',['class'=> 'em-avata rounded-circle','width' => '50px;']),['view','id' => $model->id],['class' => 'avatar']).
            // Html::a($model->fname.' '.$model->lname,['view','id' => $model->id]).'
			// 									</h2>';
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