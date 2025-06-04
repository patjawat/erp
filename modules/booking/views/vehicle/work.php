<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\ThaiDateHelper;
use app\modules\booking\models\Vehicle;

$this->title = 'ภาระกิจ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-user-tag fs-1x me-2"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ทะเบียนจัดสรรรถยนต์
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('menu',['active' => 'work'])?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'vehicles-container', 'timeout' => 500000]); ?>
<div class="card shadow-sm">
<div class="card-header bg-white">
       <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks me-1"></i> คำขอรอจัดสรร <span
                    class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ</h6>
            <?php echo $this->render('_search_work', ['model' => $searchModel]); ?>
        </div>
    </div>
    <div class="card-body p-0">
    <div class="table-responsive pb-5">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th class="fw-semibold" style="width: 180px;">เลขที่/ความเร่งด่วน</th>
                        <th class="fw-semibold">เหตุผล/จุดหมาย</th>
                        <th class="fw-semibold" style="width: 400px;">พขร./วันที่ขอใช้</th>
                        <th class="fw-semibold">ผู้ขอ</th>
                        <th class="fw-semibold">เลขไมล์ออกเดินทาง</th>
                        <th class="fw-semibold">เลขไมล์หลังเดินทาง</th>
                        <th class="fw-semibold">สถานะ</th>
                        <th class="fw-semibold text-center" style="width:150px;">ดำเนินการ</th>
                    </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <tr>
                            <td class="text-center fw-semibold">
                                <?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                                <td>
                            <p class="text-muted mb-0 fs-13"><?php echo $item->vehicle->viewUrgent() ?></p>
                            <p class="mb-0 fw-semibold fs-13"><?= $item->vehicle->code ?></p>
                        </td>
                        <td>
                            <div class="avatar-detail text-truncate">
                                <p class="fs-13 mb-0"><?php echo $item->vehicle->viewGoType() ?> : <?php echo $item->vehicle->locationOrg?->title ?? '-' ?></p>
                                <h6 class="text-muted mb-0 fs-13"><i class="fa-solid fa-circle-info text-primary"></i> <?= $item->vehicle->reason; ?></h6>
                              
                            </div>
                        </td>
                        <td>
                            <?php
                            $msg = '<i class="fa-solid fa-calendar-day me-1"></i><span class="fw-semibold fs-13 text-primary">'.$item->vehicle->showDateRange().' เวลา '.$item->vehicle->viewTime().'</span>';
                            echo $item->showDriver($msg)['avatar']?>
                          
                        </td>
                            <td> <?=$item->vehicle->userRequest()['avatar']?></td>
                            <td><?=$item->mileage_start?></td>
                            <td><?=$item->mileage_end?></td>
                            <td><?php echo $item->vehicle->getStatus($item->status)['view'] ?? '-'?></td>
                            <td class="fw-light text-center">
                            <div class="btn-group">
                            <?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/booking/vehicle/work-update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> บันทึกภาระกิจการใช้รถยนต์'], ['class' => 'btn btn-light w-100  open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                <?php // echo Html::a('<i class="fa-solid fa-pen-to-square"></i>', ['/booking/vehicle/approve', 'id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไขข้มูลขอใช้รถ'], ['class' => 'btn btn-light w-100 open-modal', 'data' => [ 'size' => 'modal-lg']]) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?php echo Html::a('<i class="fa-regular fa-circle-xmark me-1"></i> ยกเลิก', ['/booking/vehicle-detail/cancel', 'id' => $item->id], ['class' => 'dropdown-item cancel-order', 'data' => ['size' => 'modal-lg']]) ?></li>
                                </ul>
                            </div>
                        </td>
                
                    </tr>
                    <?php endforeach; ?>

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
<?php
$js = <<< JS
$(document).on('click', '.cancel-order', function(e) {
    e.preventDefault();
    let url = $(this).attr('href');
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณต้องการยกเลิกคำขอนี้หรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ยกเลิก!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'POST',
                success: function(response) {
                    Swal.fire(
                        'ยกเลิกสำเร็จ!',
                        'คำขอของคุณถูกยกเลิกแล้ว.',
                        'success'
                    ).then(() => {
                        location.reload(); // Reload the page to reflect changes
                    });
                },
                error: function() {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถยกเลิกคำขอได้.',
                        'error'
                    );
                }
            });
        }
    });
});
JS;
$this->registerJS($js, \yii\web\View::POS_END);
?>


<?php Pjax::end(); ?>