<?php

use yii\helpers\Url;
use yii\helpers\Html;

use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\booking\models\Room;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\RoomSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Rooms';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('../meeting/menu') ?>
<?php $this->endBlock(); ?>

<div class="card shadow-none">
    <div class="card-body">

<?php Pjax::begin(['id' => 'booking', 'timeout' => 500000]); ?>


<div class="d-flex justify-content-between mb-3">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนการ<?php echo $this->title?> <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
            <?php echo html::a('<i class="fa-solid fa-plus"></i> เพิ่มห้องประชุม',['/booking/room/create','title' => '<i class="fa-solid fa-plus"></i> เพิ่มข้อมูลห้องประชุม'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>
        </div>

<div class="row">
    <?php foreach(Room::find()->where(['name' => 'meeting_room'])->all() as $item):?>
    <div class="col-lg-2 col-md-4 col-sm-3">
        <div class="card shadow-lg border rounded">
            <div class="bg-primary rounded-top" style="background-image:url(<?php echo $item->showImg()?>); height: 160px; object-fit: cover;"></div>
            <div class="card-body bg-white text-dark">
                <h1 class="d-inline-flex align-items-center fs-5 fw-semibold">
                    <?php echo $item->title?> &nbsp;
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-4 w-4">
                        <line x1="7" y1="17" x2="17" y2="7"></line>
                        <polyline points="7 7 17 7 17 17"></polyline>
                    </svg>
                </h1>
                <p class="text-muted small">ที่นั้ง : <?php echo $item->data_json['seat_capacity'] ?? '-'?></p>
                <div class="mt-4">
                    <span class="badge bg-light text-dark fw-semibold me-2">#Macbook</span>
                </div>
                <div class="d-flex justify-content-between gap-3">
                    <?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข',['/booking/room/update','id' => $item->id,'title' => 'แก้ไข'],['class' => 'btn btn-warning w-50 mt-4 open-modal rounded-pill','data' => ['size' => 'modal-lg']])?>
                    <?php echo Html::a('<i class="fa-solid fa-trash"></i> ลบ',['/booking/room/delete','id' => $item->id],['class' => 'btn btn-danger w-50 mt-4 delete-item  rounded-pill'])?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach;?>
</div>

<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>
<?php Pjax::end();?>

</div>
</div>
