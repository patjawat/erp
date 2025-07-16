<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\lm\models\Leave;
/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ทะเบียนประวัติการลา';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('@app/modules/me/views/leave/_sub_menu',['active' => 'index'])?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'leave-container', 'timeout' => 500000]); ?>
<?php  // echo $this->render('card_summary', ['searchModel' => $searchModel]); ?>



<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card text-start">
        <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> ระบบการ
            </h6>
            <div class="d-flex justify-content-between">
               <?php echo Html::a('<i class="bi bi-person-fill-gear"></i> วันหยุดของฉัน',['/me/holidays','title' => '<i class="bi bi-person-fill-gear"></i> วันหยุดของฉัน'],['id' => 'calendar-me','class' => 'btn btn-light open-modal','data' => ['size' => 'modal-xl']])?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php echo  $this->render('@app/modules/hr/views/leave/list', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider
                ]);
                ?>

    </div>
</div>
<?php Pjax::end(); ?>