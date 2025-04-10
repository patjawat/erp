<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\booking\models\Vehicle;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ระบบขอใช้ยานพาหนะ';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu') ?>
<?php $this->endBlock(); ?>



<div class="container-fluid">
    <div class="card shadow-sm mb-4">
        
        <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">

<div>
    <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?> <span
            class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span>
        รายการ</h6>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
</div>

<?php echo Html::a('<i class="bi bi-plus-circle me-1"></i>สร้างคำขอใหม่',['/me/booking-vehicle/create','title' => 'แบบขอใช้รถยนต์'],['class' => 'btn btn-primary open-modal rounded-pill shadow','data' => ['size' => 'modal-lg']])?>
</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>เลขที่</th>
                            <th>วันที่ขอใช้</th>
                            <th>จุดหมาย</th>
                            <th>ประเภทรถ</th>
                            <th>สถานะ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach($dataProvider->getModels() as $item):?>
                        <tr>
                            <td>
                                <p class="mb-0 fw-semibold"><?=$item->code?></p>
                                <p class="fs-13 mb-0">
                                    <?php echo Yii::$app->thaiDate->toThaiDate($item->created_at, true, true)?></p>
                            </td>
                            <td>
                                <p class="mb-0"><?php echo $item->viewGoType()?></p>
                                <p class="mb-0 fw-semibold"><?php echo $item->showDateRange()?></p>
                            </td>
                            <td><?php echo $item->locationOrg?->title ?? '-'?></td>
                            <td><?php echo $item->carType?->title ?? '-'?></td>
                            <td><?php echo $item->viewStatus()['view'] ?? '-'?></td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-3">
                                    <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/me/booking-vehicle/view','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                                    <?php echo Html::a('<i class="fa-solid fa-pen-to-square fa-2x text-warning"></i>',['/me/booking-vehicle/update','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                                    <?php echo Html::a('<i class="fa-regular fa-trash-can fa-2x text-danger"></i>',['/me/booking-vehicle/delete','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'delete-item','data' => ['size' => 'modal-lg']])?>
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