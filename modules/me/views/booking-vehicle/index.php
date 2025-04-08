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
<?php // echo $this->render('menu') ?>
<?php $this->endBlock(); ?>



<div class="container my-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <div>
                    <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?> <span
                            class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span>
                        รายการ</h6>
                    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
                </div>

            <?php echo Html::a('<i class="bi bi-plus-circle me-1"></i>สร้างคำขอใหม่',['/me/booking-vehicle/create','title' => 'แบบขอใช้รถยนต์'],['class' => 'btn btn-primary open-modal rounded-pill shadow','data' => ['size' => 'modal-lg']])?>
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
                            <td><?php echo $item->viewStatus() ?? '-'?></td>
                            <td>

                                <?=Html::a('<i class="bi bi-eye"></i>',['/me/booking-vehicle/view','id' => $item->id,'title' => '<i class="fa-solid fa-circle-info text-primary"></i> แสดงข้อมูลแบบขอใช้รถยนต์'],['class' => 'btn btn-sm btn-outline-primary me-1 open-modal','data' => ['size' => 'modal-lg']])?>
                                <?=Html::a('<i class="bi bi-pencil"></i>',['/me/booking-vehicle/update','id' => $item->id,'title' => '<i class="bi bi-pencil"></i> แก้ไขแบบขอใช้รถยนต์'],['class' => 'btn btn-sm btn-outline-warning me-1 open-modal','data' => ['size' => 'modal-lg']])?>
                                <?=Html::a('<i class="bi bi-trash"></i>',['/me/booking-vehicle/delete','id' => $item->id],['class' => 'btn btn-sm btn-outline-danger delete-item'])?>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>