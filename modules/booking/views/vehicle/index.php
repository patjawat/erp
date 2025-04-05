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

$this->title = 'Vehicles';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'vehicles-container', 'timeout' => 500000]); ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center p-2">
        <h5 class="card-title"><i class="fa-solid fa-calendar-day"></i> ปฏิทินการจัดสรรรถ</h5>
        <div>
    <?=Html::a('<i class="bi bi-calendar-check"></i> วันนี้', ['index', 'date' => date('Y-m-d')], ['class' => 'btn btn-sm btn-outline-primary'])?>
            <div class="btn-group">
                <?= Html::a(' <i class="bi bi-chevron-left"></i>', ['index', 'date' => $previousDate], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <button class="btn btn-sm btn-outline-secondary disabled"><?= $thaiMonthYear ?></button>
                <?= Html::a('<i class="bi bi-chevron-right"></i>', ['index', 'date' => $nextDate], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%">รถ / วันที่</th>
                        <!-- <th>1 ม.ค. 68</th> -->
                        <?php foreach ($days as $day): ?>
                        <th class="text-center">
                            <?= ThaiDateHelper::formatThaiDate($day) ?>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProviderDetail->getModels() as $item): ?>
                    <tr>
                        <td class="table-light">
                            <div class="d-flex">
                                <?=Html::img($item->car->ShowImg(),['class' => 'avatar rounded border-secondary'])?>
                                <div class="avatar-detail">
                                    <div class="d-flex flex-column">
                                        <p class="mb-0"><?=$item->car->data_json['brand'];?></p>
                                        <p class="mb-0 fw-semibold text-primary"><?=$item->license_plate?></p>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <?php foreach ($days as $day): ?>
                        <td>
                            <?php if($day == $item->date_start):?>
                        <td>
                            <a href="<?=Url::to(['/booking/vehicle/approve','id' => $item->vehicle_id,'title' => 'แสดงข้อมูลการจัดสรร'])?>"
                                class="open-modal" data-size="modal-lg">
                                <div class="badge-soft-success p-2 rounded">
                                    <?=$item->driver->getAvatar(false,($item->vehicle->locationOrg->title))?>
                                </div>
                            </a>
                        </td>
                        <?php endif;?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div>
            <h6><i class="bi bi-ui-checks me-1"></i> คำขอรอจัดสรร <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
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
                        <th>ผู้ขอ</th>
                        <th>วันที่ขอใช้</th>
                        <th>ประเภทรถ</th>
                        <th>จุดหมาย</th>
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
                        <td><?php echo $item->userRequest()['avatar'];?></td>
                        <td>
                            <p class="mb-0"><?php echo $item->viewGoType()?></p>
                            <p class="mb-0 fw-semibold"><?php echo $item->showDateRange()?></p>
                        </td>
                        <td><?php echo $item->carType?->title ?? '-'?></td>
                        <td><?php echo $item->locationOrg?->title ?? '-'?></td>
                        <td>
                            <?php if($item->status == 'Pending'):?>
                            <?php echo Html::a('<i class="bi bi-check-circle me-1"></i> อนุมัติ', ['/booking/vehicle/approve', 'id' => $item->id,'title' => '<i class="bi bi-check-circle me-1"></i> อนุมัติการจัดสรรรถ'], ['class' => 'btn btn-sm btn-success me-1 open-modal', 'data' => [ 'size' => 'modal-lg']])?>
                            <?php echo Html::a('<i class="bi bi-x-circle me-1"></i> ปฏิเสธ', ['/booking/vehicle/reject', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger'])?>
<?php else:?>
    <?=$item->viewStatus()?>
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