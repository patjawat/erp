<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\hr\models\LeaveRole;
/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveRoleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ตั้งค่าสิทธิวันลาประจำปี';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu') ?>
<?php $this->endBlock(); ?>

<div class="leave-role-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between  align-top align-items-center mt-4">
                <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
                <?= Html::a('<i class="fa-solid fa-plus"></i> สร้างใหม่', ['/hr/leave-role/create','title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-primary shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
        </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'thai_year',
            'employee.fullname',
            'leave_limit',
            'leave_sum_days'
            
            
            
        ],
    ]); ?>


</div>
</div>

    <?php Pjax::end(); ?>

</div>

