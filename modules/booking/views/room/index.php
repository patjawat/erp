<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'ห้องประชุม';
?>
<?php // Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('../conference/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนการ<?php echo $this->title?> <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
            <?php echo html::a('<i class="fa-solid fa-plus"></i> เพิ่มห้องประชุม',['/booking/room/create','title' => '<i class="fa-solid fa-plus"></i> เพิ่มข้อมูลห้องประชุม'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>

        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">ชื่อห้องประชุม</th>
                    <th scope="col">จองล่วงหน้า/วัน</th>
                    <th scope="col">สถานที่ตั้ง</th>
                    <th scope="col">ความจุ/คน</th>
                    <th scope="col">ผู้รับผิดชอบ</th>
                    <th scope="col">หมายเหตุ</th>
                    <th scope="col">เปิดใช้งาน</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
               <tr>
                <td><?php echo $item->title?></td>
                <td><?php echo $item->data_json['advance_booking'] ?? '-'?></td>
                <td><?php echo $item->data_json['location'] ?? '-'?></td>
                <td><?php echo $item->data_json['seat_capacity'] ?? '-'?></td>
                <td><?php echo $item->data_json['owner'] ?? '-'?></td>
                <td><?php echo $item->description?></td>
                <td><?php echo $item->active?></td>
                <td class="text-center">
                <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/booking/room/view','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                <?php echo Html::a('<i class="fa-solid fa-pencil fa-2x text-warning"></i>',['/booking/room/update','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
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
