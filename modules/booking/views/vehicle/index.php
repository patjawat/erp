<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\ThaiDateHelper;
use app\modules\booking\models\Vehicle;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ERP - ระบบจัดการรถยนต์';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
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
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th>เลขที่</th>
                        <th>ประเภท</th>
                        <th>ผู้ขอ</th>
                        <th>วันที่ขอใช้</th>
                        <th>จุดหมาย</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $key => $item):?>
                    <tr class="align-middle">
                        <td class="text-center fw-semibold">
                            <?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                        <td>
                            <p class="mb-0 fw-semibold"><?=$item->code?></p>
                            <p class="fs-13 mb-0">
                                <?php echo Yii::$app->thaiDate->toThaiDate($item->created_at, true, true)?></p>
                        </td>
                        <td><?php echo $item->carType?->title ?? '-'?></td>
                        <td><?php echo $item->userRequest()['avatar'];?></td>
                        <td>
                            <p class="mb-0 fw-semibold"><?php echo $item->showDateRange()?></p>
                        </td>
                        <td>
                            <p class="mb-0"><?php echo $item->viewGoType()?></p>
                            <p class="mb-0"><?php echo $item->locationOrg?->title ?? '-'?></p>
                        </td>
                        <td>
                            <?=$item->viewStatus()['view']?>
                        </td>
                        <td>
                            <?php if($item->status == 'Pending'):?>
                            <?php echo Html::a('<i class="bi bi-check-circle me-1"></i> จัดสรร', ['/booking/vehicle/approve', 'id' => $item->id,'title' => '<i class="bi bi-check-circle me-1"></i> อนุมัติการจัดสรรรถ'], ['class' => 'btn btn-sm btn-success me-1 open-modal', 'data' => [ 'size' => 'modal-lg']])?>
                            <?php echo Html::a('<i class="bi bi-x-circle me-1"></i> ปฏิเสธ', ['/booking/vehicle/reject', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger'])?>
                            <?php else:?>
                            <?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/booking/vehicle/approve', 'id' => $item->id,'title' => '<i class="bi bi-check-circle me-1"></i> อนุมัติการจัดสรรรถ'], ['class' => 'btn btn-sm btn-warning me-1 open-modal', 'data' => [ 'size' => 'modal-lg']])?>
                            <?php echo Html::a('<i class="fa-regular fa-circle-xmark"></i> ยกเลิก', ['/booking/vehicle/reject', 'id' => $item->id], ['class' => 'btn btn-sm btn-secondary'])?>
                            <?php endif;?>
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