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

$this->title = 'ทะเบียนใช้รถพยาบาล';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-truck-medical"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php $this->beginBlock('sub-title'); ?>
ทะเบียนใช้รถพยาบาล
<?php $this->endBlock(); ?>
<?php echo $this->render('menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('menu',['active' => 'ambulance'])?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
             <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks me-1"></i> คำขอรอจัดสรร <span
                    class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
        </div>
        </div>
    </div>


    <div class="card-body">
        <table class="table table-hover pb-5">
            <thead>
                <tr>
                    <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                    <th class="fw-semibold" style="width: 180px;">เลขที่/ความเร่งด่วน</th>
                    <th class="fw-semibold">เหตุผล/จุดหมาย</th>
                    <th class="fw-semibold" style="width: 400px;">เหตุผล/วันที่ขอใช้</th>
                    <th class="fw-semibold">ผู้ขอ</th>
                    <th class="fw-semibold">สถานะ</th>
                    <th class="fw-semibold text-end" style="width:150px;">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td class="text-center fw-semibold"><?=(($dataProvider->pagination->offset + 1)+$key)?></td>
                    <td>
                        <p class="text-muted mb-0 fs-13"><?php echo $item->viewUrgent()?></p>
                        <p class="mb-0 fw-semibold fs-13"><?=$item->code?></p>
                    </td>
                    <td>
                        <div class="avatar-detail text-truncate">
                            <p class="fs-13 mb-0"><?php echo $item->viewGoType() ?> :
                                <?php echo $item->locationOrg?->title ?? '-' ?></p>
                            <h6 class="text-muted mb-0 fs-13"><i class="fa-solid fa-circle-info text-primary"></i>
                                <?= $item->reason; ?></h6>

                        </div>
                    </td>
                    <td>
                        <div class="avatar-detail">
                            <p class="mb-0 fw-semibold fs-13"><i class="fa-solid fa-calendar-day"></i>
                                <?php echo $item->showDateRange()?></p>
                            <p class="mb-0 fw-semibold fs-13"> เวลา <?php echo $item->viewTime()?></p>
                        </div>
                    </td>
                    <td> <?=$item->userRequest()['avatar']?></td>
                    <td><?php echo $item->viewStatus()['view'] ?? '-'?></td>

                    <td class="fw-light text-end">
                        <div class="btn-group">
                            <?php echo Html::a('<i class="fa-solid fa-pen-to-square"></i>', ['/booking/vehicle/approve', 'id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไขข้มูลขอใช้รถ'], ['class' => 'btn btn-light w-100 open-modal', 'data' => [ 'size' => 'modal-lg']])?>
                            <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><?php echo Html::a('<i class="fa-regular fa-circle-xmark me-1"></i> ยกเลิก', ['/booking/vehicle/cancel', 'id' => $item->id], ['class' => 'dropdown-item cancel-order','data' => ['size' => 'modal-lg']])?>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
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
</div>
<?php
$js = <<< JS
// $(document).on('click', '.cancel-order', function(e) {
//     e.preventDefault();
//     let url = $(this).attr('href');
//     Swal.fire({
//         title: 'คุณแน่ใจหรือไม่?',
//         text: "คุณต้องการยกเลิกคำขอนี้หรือไม่?",
//         icon: 'warning',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         confirmButtonText: 'ใช่, ยกเลิก!',
//         cancelButtonText: 'ยกเลิก'
//     }).then((result) => {
//         if (result.isConfirmed) {
//             $.ajax({
//                 url: url,
//                 type: 'POST',
//                 success: function(response) {
//                     Swal.fire(
//                         'ยกเลิกสำเร็จ!',
//                         'คำขอของคุณถูกยกเลิกแล้ว.',
//                         'success'
//                     ).then(() => {
//                         location.reload(); // Reload the page to reflect changes
//                     });
//                 },
//                 error: function() {
//                     Swal.fire(
//                         'เกิดข้อผิดพลาด!',
//                         'ไม่สามารถยกเลิกคำขอได้.',
//                         'error'
//                     );
//                 }
//             });
//         }
//     });
// });
JS;
$this->registerJS($js, \yii\web\View::POS_END);
?>