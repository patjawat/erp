<?php

use app\modules\usermanager\models\Session;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\usermanager\models\SessionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

// $this->title = 'User Session';
$this->params['breadcrumbs'][] = $this->title;
$browsers = Session::find()
    ->select('browser')
    ->addSelect('count(id) as total')
    ->groupBy('browser')
    ->createCommand()->queryAll();
?>

<div class="card">
    <div class="card-header text-success">
        <i class="fas fa-user-lock"></i> User Online
    </div>
    <div class="card-body">


        <div class="session-index">

            <?php Pjax::begin(['enablePushState' => false, 'enableReplaceState' => false]);?>
            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

            <?=GridView::widget([
    'dataProvider' => $dataProvider,
    // 'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'header' => 'Browser',
            'format' => 'raw',
            'width' => '5%',
            'hAlign' => 'center',
            'vAlign' => 'middle',
            'value' => function ($model) {
                if ($model['browser'] == 'chrome') {
                    return Html::img('@web/img/browser-icon/chrome.svg',['width' => '50']);
                    
                } else if ($model['browser'] == 'safari') {
                    return Html::img('@web/img/browser-icon/safari.svg',['width' => '50']);

                } else if ($model['browser'] == 'firefox') {
                    return Html::img('@web/img/browser-icon/firefox.svg',['width' => '50']);

                } else if ($model['browser'] == 'internet-explorer') {
                    return Html::img('@web/img/browser-icon/internet_explorer.svg',['width' => '50']);

                }
            },
        ],
        [
            'header' => 'ผู้ใช้งาน',
            'format' => 'raw',
            'width' => '40%',
            'value' => function ($model) {
                return $model->user->fullname . ' (<code>' . $model->ip_address . '</code>)<br>' .
                $model->data;
            },
        ],
        'login_time',

        // [
        // 'header' => 'ดำเนินการ',
        // 'class' => 'app\grid\ActionColumn',
        // 'template' => '{logout}',
        // 'buttons'=>[
        // 'logout' => function($url,$model,$key){
        // return Html::a('logout', ['logout', 'id' => $model->id], [
        // 'class' => 'btn btn-danger',
        // 'data' => [
        // 'confirm' => 'Are you sure',
        // // 'method' => 'post',
        // ],
        // ]);
        // }
        // ]
        // ],
    ],
]);?>
            <?php Pjax::end();?>
        </div>

    </div>
    <div class="card-footer text-muted">
        Footer
    </div>
</div>