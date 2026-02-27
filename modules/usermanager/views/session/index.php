<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\usermanager\models\SessionSearch|null */
/* @var $dataProvider yii\data\DataProviderInterface */
/* @var bool $sessionTableExists */
/* @var array $browsers */

$this->title = 'เซสชัน / User Online';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if (!$sessionTableExists): ?>
<div class="alert alert-info border-0 rounded-3 mb-4" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    <strong>ไม่มีตาราง session ในฐานข้อมูล</strong> — ระบบอาจใช้ session แบบ file หรือ cache จึงไม่สามารถแสดงรายการเซสชันที่เข้าสู่ระบบได้
</div>
<?php endif; ?>

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
            'headerOptions' => ['style' => 'width: 5%'],
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                if (isset($model['browser'])) {
                    if ($model['browser'] == 'chrome') {
                        return Html::img('@web/img/browser-icon/chrome.svg', ['width' => '50']);
                    }
                    if ($model['browser'] == 'safari') {
                        return Html::img('@web/img/browser-icon/safari.svg', ['width' => '50']);
                    }
                    if ($model['browser'] == 'firefox') {
                        return Html::img('@web/img/browser-icon/firefox.svg', ['width' => '50']);
                    }
                    if ($model['browser'] == 'internet-explorer') {
                        return Html::img('@web/img/browser-icon/internet_explorer.svg', ['width' => '50']);
                    }
                }
                return $model && is_object($model) && isset($model->browser) ? Html::encode($model->browser) : '-';
            },
        ],
        [
            'header' => 'ผู้ใช้งาน',
            'format' => 'raw',
            'headerOptions' => ['style' => 'width: 40%'],
            'value' => function ($model) {
                $name = is_object($model) && $model->user ? $model->user->fullname : '-';
                $ip = is_object($model) && isset($model->ip_address) ? $model->ip_address : '-';
                $data = is_object($model) && isset($model->data) ? $model->data : '';
                return $name . ' (<code>' . Html::encode($ip) . '</code>)<br>' . Html::encode($data);
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