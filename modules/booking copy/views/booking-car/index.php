<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'จองรถยนต์ ';
?>
<?php // Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/booking/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนการ<?php echo $this->title?> <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
            <?php echo html::a('<i class="fa-solid fa-plus"></i> เพิ่มข้อมูลขอใช้รถยนต์',['/me/booking-car/create','title' => '<i class="fa-solid fa-plus"></i> เพิ่มข้อมูลขอใช้รถยนต์'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-xl']])?>


        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">ปีงบประมาณ</th>
                    <th scope="col">ผู้ขออนุมัติการลา</th>
                    <th class="text-center" scope="col">วัน</th>
                    <th scope="col">จากวันที่</th>
                    <th scope="col">ถึงวันที่</th>
                    <th class="text-start" scope="col">หนวยงาน</th>
                    <!-- <th scope="col">มอบหมาย</th> -->
                    <th scope="col">ผู้ตรวจสอบและอนุมัติ</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th scope="col">ผู้ขออนุมัติการลา</th>
                    <th scope="col">วันที่ไป</th>
                    <th scope="col">เวลาไป</th>
                    <th scope="col">ถึงวันที่</th>
                    <th scope="col">เวลากลับ</th>
                    <th scope="col">ผู้ตรวจสอบและอนุมัติ</th>
                    <th scope="col">สถานะ</th>
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
                <?php echo Html::a('<i class="fa-solid fa-pencil fa-2x text-warning"></i>',['/me/booking-car/update','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
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
