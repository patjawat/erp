<?php

use yii\helpers\Html;
use kartik\grid\GridView;

$this->title = 'ผู้ใช้งานระบบ';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
.panel-primary {
    border-color: #9e9e9e3b;;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,.15);
}
.panel-primary > .panel-heading {
    color: #fff;
    background-color: #017bfe;
    border-color: #017bfd;
}

</style>
<?=Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'],
                ['role'=>'modal-remote','title'=> 'สร้าง'.$this->title,'class'=>'btn btn-default']);?>
<div class="user-index">
        <?=GridView::widget([
          'id'=>'crud-datatable',
          'dataProvider' => $dataProvider,
          'filterModel' => $searchModel,
          'pjax'=>true,
          'toolbar'=> [
            ['content'=>
                Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'],
                ['role'=>'modal-remote','title'=> 'สร้าง'.$this->title,'class'=>'btn btn-default']).
                Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                ['data-pjax'=>1, 'class'=>'btn btn-default', 'title'=>'Reset Grid']).
                '{toggleData}'.
                '{export}'
            ],
        ],          
        'striped' => true,
        'condensed' => true,
        'responsive' => true,          
        // 'panel' => [
        //     'type' => 'primary', 
        //     'heading' => '<i class="glyphicon glyphicon-list"></i> รายการ'.$this->title,
        //     'before'=>'<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
        //     'after'=>'<div class="clearfix"></div>',
        //                     ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            'username',
            //'auth_key',
            //'password_hash',
            //'password_reset_token',
             'email:email',
             //'status',
             [
               'attribute'=>'status',
               'format'=>'html',
               'filter'=>$searchModel->itemStatus,
               'value'=>function($model){
                 return $model->statusName=='Active' ?'<span class="text-success">'.$model->statusName.'</span>' : $model->statusName ;
               }
             ],
             'created_at:dateTime',
            // 'updated_at',

          //   [
          //     'class' => 'yii\grid\ActionColumn',
          //     'options'=>['style'=>'width:120px;'],
          //     'buttonOptions'=>['class'=>'btn btn-default'],
          //     'template'=>'<div class="btn-group btn-group-sm text-center" role="group"> {view} {update} {delete} </div>'
          //  ],
        ],
    ]); ?>
</div>
