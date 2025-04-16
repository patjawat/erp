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
<i class="fa-solid fa-car fs-1 white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ทะเบียนขอใช้รถยนต์
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('menu')?>
<?php $this->endBlock(); ?>


<div class="container-fluid">
    <?=$this->render('@app/modules/booking/views/vehicle/summary',['model' => $searchModel]) ?>
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
                            <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                            <th>เลขที่/ขอใช้รถ</th>
                            <th>จุดหมาย/วันที่ขอใช้</th>
                            <th>วัตถุประสงค์/ความเร่งด่วน</th>
                            <th>ผู้ขอ</th>
                            <th>สถานะ</th>
                            <th class="text-center" style="width:180px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach($dataProvider->getModels() as $key => $item):?>
                        <tr>
                            <td class="text-center fw-semibold">
                                <?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                            <td>
                                <p class="mb-0 fw-semibold"><?=$item->code?></p>
                                <p class="fs-13 mb-0">
                                    <?php echo $item->viewCarType()?>
                                </p>
                            </td>
                            <td>
                                <div class="avatar-detail">
                                    <h6 class="mb-0 fs-13"><?php echo $item->viewGoType()?> :
                                        <?php echo $item->locationOrg?->title ?? '-'?></h6>
                                    <p class="text-muted mb-0 fs-13">
                                        <?php echo $item->showDateRange()?> เวลา <?php // $item->viewMeetingTime()?>
                                    </p>
                                </div>
                            </td>
                            <td>
                                <div class="avatar-detail">
                                    <h6 class="mb-0 fs-13"><?=$item->reason;?></h6>
                                    <p class="text-muted mb-0 fs-13">
                                        <?php echo $item->viewUrgent()?>
                                    </p>
                                </div>


                            </td>
                            <td> <?=$item->userRequest()['avatar']?></td>
                            <td><?php echo $item->viewStatus()['view'] ?? '-'?></td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-3">
                                    <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/me/booking-vehicle/view','id' => $item->id,'title' => 'แสดงข้มูลขอใช้รถ'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                                    <?php echo Html::a('<i class="fa-solid fa-pen-to-square fa-2x text-warning"></i>',['/me/booking-vehicle/update','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขข้มูลขอใช้รถ'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                                    <?php echo Html::a('<i class="fa-regular fa-trash-can fa-2x text-danger"></i>',['/me/booking-vehicle/delete','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> ลบ'],['class' => 'delete-item','data' => ['size' => 'modal-lg']])?>
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