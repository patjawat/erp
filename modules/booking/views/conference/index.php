<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'ระบบจองห้องประชุม ';
?>
<?php // Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
 
<i class="fa-solid fa-person-chalkboard fs-1"></i><?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนการ<?php echo $this->title?> <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>


        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">ห้องประชุม</th>
                    <th class="text-center" scope="col">เรื่อง</th>
                    <th scope="col">จากวันที่</th>
                    <th scope="col">เวลา</th>
                    <th scope="col">ถึงวันที่</th>
                    <th scope="col">เวลา</th>
                    <th class="text-start" scope="col">ผู้จอง</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
               <tr>
                <td><?php echo $item->reason;?></td>
                <td><?php echo $item->car->Avatar();?></td>
                <td><?=Yii::$app->thaiFormatter->asDate($item->date_start, 'medium')?></td>
                <td><?php echo $item->time_start?></td>
                <td><?=Yii::$app->thaiFormatter->asDate($item->date_end, 'medium')?></td>
                <td><?php echo $item->time_end?></td>
                <td><?php echo $item->leader_id?></td>
                <td></td>
                <td class="text-center">
                <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/booking/booking-car/view','id' => $item->id],['class' => 'open-modal-x','data' => ['size' => 'modal-xl']])?>
                </td>
               </tr>
                <?php endforeach;?>
            </tbody>
        </table>
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

    </div>
</div>
