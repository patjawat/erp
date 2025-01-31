<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'ยานพาหนะ ';
?>
<?php // Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-calendar-day"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/booking/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'booking', 'timeout' => 500000]); ?>
<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?> <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
            <?php echo html::a('<i class="fa-solid fa-plus"></i> เพิ่มข้อมูลขอใช้รถยนต์',['/booking/booking-cars-items/create','title' => '<i class="fa-solid fa-plus"></i> เพิ่มข้อมูลรถยนต์'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>


        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">เลขทะเบียน</th>
                    <th scope="col">ยี่ห้อ</th>
                    <th scope="col">รายละเอียด</th>
                    <th scope="col">เชื้อเพลิง</th>
                    <th class="text-start" scope="col">ซ่อมครั้งล่าสุด</th>
                    <th scope="col">พรบ.หมดอายุ</th>
                    <th scope="col">ต่อภาษี</th>
                    <th class="text-center">กรมธรรม์หมดอายุ</th>
                    <th class="text-center">ผู้รับผิดชอบ</th>
                    <th class="text-center">เปิดใช้งาน</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td><?php echo $item->Avatar()?></td>
                    <td class="text-truncate"></td>
                    <td class="text-center fw-semibold"></td>
                    <td></td>
                    <td class="text-start text-truncate" style="max-width:150px;"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/booking/booking-cars-items/view', 'id' => $item->id])?>
                            <?php echo Html::a('<i class="fa-solid fa-pencil fa-2x text-warning"></i>',['/booking/booking-cars-items/update', 'id' => $item->id,'title' => '<i class="fa-solid fa-pencil"></i> แก้ไข'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                        </div>

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
<?php  Pjax::end(); ?>