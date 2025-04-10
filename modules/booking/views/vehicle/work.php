<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\ThaiDateHelper;
use app\modules\booking\models\Vehicle;

$this->title = 'ERP - ภาระกิจ';
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu')?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'vehicles-container', 'timeout' => 500000]); ?>
<div class="card shadow-sm">
<div class="card-header bg-white">
        <div>
            <h6><i class="bi bi-ui-checks me-1"></i> คำขอรอจัดสรร <span
                    class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
            <?php echo $this->render('_search_work', ['model' => $searchModel]); ?>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                            <th>เลขที่/ขอใช้รถ</th>
                            <th>จุดหมาย/วันที่ขอใช้</th>
                            <th>วัตถุประสงค์/ความเร่งด่วน</th>
                            <th>ผู้ขอ</th>
                            <th>ผู้ปฏิบัติหน้าที่</th>
                            <th>สถานะ</th>
                            <th class="text-center" style="width:200px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach($dataProvider->getModels() as $key => $item):?>
                        <tr>
                            <td class="text-center fw-semibold">
                                <?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                            <td>
                                <p class="mb-0 fw-semibold"><?=$item->vehicle->code?></p>
                                <p class="fs-13 mb-0">
                                <?php echo $item->vehicle->viewCarType()?>
                                </p>
                            </td>
                            <td>
                                <div class="avatar-detail">
                                    <h6 class="mb-0 fs-13"><?php echo $item->vehicle->viewGoType()?> :
                                        <?php echo $item->vehicle->locationOrg?->title ?? '-'?></h6>
                                    <p class="text-muted mb-0 fs-13">
                                        <?php echo $item->vehicle->showDateRange()?> เวลา <?php echo $item->vehicle->viewTime()?>
                                    </p>
                                </div>
                            </td>
                            <td>
                                <div class="avatar-detail">
                                    <h6 class="mb-0 fs-13"><?=$item->vehicle->reason;?></h6>
                                    <p class="text-muted mb-0 fs-13">
                                        <?php echo $item->vehicle->viewUrgent()?>
                                    </p>
                                </div>


                            </td>
                            <td> <?=$item->vehicle->userRequest()['avatar']?></td>
                            <td> <?=$item->driver->getAvatar(false)?></td>
                            <td><?php echo $item->vehicle->getStatus($item->status)['view'] ?? '-'?></td>
                        <td class="text-center">
                            <?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>', ['/booking/vehicle/work-update', 'id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> บันทึกภาระกิจ'], ['class' => 'open-modal', 'data' => [ 'size' => 'modal-lg']])?>
                        </td>
                    </tr>
                    <?php endforeach;?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center">
    <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'class' => 'pagination pagination-sm',
                ],
            ]); ?>

</div>
<?php Pjax::end();?>