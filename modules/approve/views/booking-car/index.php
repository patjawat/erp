<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'อนุมัติการลา ';
$msg = 'ขอ';
?>
<?php // Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-calendar-day"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>
<?php if($dataProvider->getTotalCount() > 0):?>
<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?> <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
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
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td class="text-center fw-semibold"><?php echo $item->bookCar->thai_year?></td>
                    <td class="text-truncate" style="max-width: 230px;">
                        <a href="<?php echo Url::to(['/hr/leave/view','id' => $item->bookCar->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'])?>"
                            class="open-modal" data-size="modal-xl">
                            <?php // $item->bookCar->getAvatar(false)['avatar']?>
                        </a>
                    </td>
                    <td class="text-center fw-semibold"><?php // echo $item->bookCar->total_days?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($item->bookCar->date_start, 'medium')?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($item->bookCar->date_end, 'medium')?></td>
                    <td class="text-start text-truncate" style="max-width:150px;">
                        <?php // $item->bookCar->getAvatar(false)['department']?>

                    </td>
                    </td>
                    <td><?php  echo $item->stackChecker()?>
                        <?php
                    try {
                        $data =  $item->bookCar->checkerName(1)['employee'];
                    } catch (\Throwable $th) {
                    }
            ?>
                    </td>


                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">

                            <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/approve/booking-car/update', 'id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
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
<?php else:?>
    <h5 class="text-center">ไม่มีรายการ</h5>
<?php endif?>
<?php // Pjax::end(); ?>