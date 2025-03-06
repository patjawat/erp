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

<?php Pjax::begin(['id' => 'leave-container', 'timeout' => 500000]); ?>
<?php  echo $this->render('card_summary', ['searchModel' => $searchModel]); ?>

<div class="card text-start">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา <span class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(),0)?></span> รายการ</h6>
        </div>
        <div class="d-flex justify-content-between  align-top align-items-center mt-4">
                <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
                <?= Html::a('<i class="fa-solid fa-plus"></i> ขอลา', ['/me/leave/create','title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
        </div>


<?php echo  $this->render('@app/modules/hr/views/leave/list', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider
    ]);
      ?>

</div>
</div>
<?php Pjax::end(); ?>