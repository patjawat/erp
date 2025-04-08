<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'อนุมัติขอใช้รถยนต์';
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
<?php if($dataProvider->getTotalCount() >= 1):?>




<div class="container my-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <div>
                    <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?> <span
                            class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span>
                        รายการ</h6>
                </div>

        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>เลขที่</th>
                            <th>วันที่ขอใช้</th>
                            <th>จุดหมาย</th>
                            <th>ประเภทรถ</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach($dataProvider->getModels() as $item):?>
                        <tr>
                            <td>
                                <p class="mb-0 fw-semibold"><?=$item->vehicle->code?></p>
                                <p class="fs-13 mb-0">
                                    <?php echo Yii::$app->thaiDate->toThaiDate($item->vehicle->created_at, true, true)?></p>
                            </td>
                            <td>
                                <p class="mb-0"><?php echo $item->vehicle->viewGoType()?></p>
                                <p class="mb-0 fw-semibold"><?php echo $item->vehicle->showDateRange()?></p>
                            </td>
                            <td><?php echo $item->vehicle->locationOrg?->title ?? '-'?></td>
                            <td><?php echo $item->vehicle->carType?->title ?? '-'?></td>
                            <td><?php echo $item->vehicle->viewStatus() ?? '-'?></td>
                            <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">

                            <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/approve/vehicle/update', 'id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
                        </div>

                    </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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

<?php else:?>
    <h5 class="text-center">ไม่มีรายการ</h5>
<?php endif?>
<?php // Pjax::end(); ?>