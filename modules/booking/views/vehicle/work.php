<?php

use yii\helpers\Html;
use yii\widgets\Pjax;


$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('page-title'); ?>
<?=$icon?> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/booking/vehicle_menu',['active' => $vehicle_type]) ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'vehicles-container', 'timeout' => 500000]); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search_work', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary-gradient">
        <div class="d-flex justify-content-between">
            <h6 class="text-white"><i class="bi bi-ui-checks me-1"></i> การจัดสรร
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1 ms-1">
                    <?= $dataProvider->getTotalCount() ?> รายการ
                </span>
            </h6>
        </div>
    </div>
    <div class="card-body p-2">
        <div class="overflow-auto" style="max-height: 68vh;">
            <div class="row g-2">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <?php
                    $vehicle = $item->vehicle;
                    $requester = $vehicle?->userRequest() ?? [];
                    $driver = $item->showDriver();
                    $statusView = $item->viewStatus()['view'] ?? '-';
                    $queueNo = ($dataProvider->pagination->offset + 1) + $key;
                    $showDate = $vehicle ? $vehicle->showDateRange() : '-';
                    $showTime = $vehicle ? ($vehicle->viewTime()['full'] ?? '-') : '-';
                    $goType = $vehicle ? $vehicle->viewGoType() : '-';
                    $locTitle = $vehicle?->locationOrg?->title ?? '-';
                    $reason = $vehicle?->reason ?? '-';
                    $cancelUrl = ['/booking/vehicle-detail/cancel', 'id' => $item->id];
                    $workUpdateUrl = ['/booking/vehicle/work-update', 'id' => $item->id, 'title' => 'บันทึกภาระกิจการใช้รถยนต์'];
                    $viewUrl = ['view', 'id' => $item->id];
                    ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                    <div class="fw-bold text-primary small">
                                        #<?= (int) $queueNo ?> · <?= Html::encode($vehicle->code ?? '-') ?>
                                    </div>
                                    <div><?= $statusView ?></div>
                                </div>

                                <div class="row g-2 align-items-start">
                                    <div class="col-12 col-lg-3">
                                        <div class="d-flex align-items-center">
                                            <?php
                                            $photoSrc = $requester['photo'] ?? '';
                                            if ($photoSrc !== ''):
                                            ?>
                                                <img src="<?= Html::encode($photoSrc) ?>" width="32" height="32" class="rounded-3 me-2 shadow-sm" alt="" />
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold mb-0 small"><?= Html::encode($requester['fullname'] ?? '-') ?></div>
                                                <small class="text-primary d-block"><?= Html::encode($requester['department'] ?? '-') ?></small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg-4">
                                        <div class="small text-muted">
                                            <i class="bi bi-calendar-check me-1"></i><?= Html::encode($showDate) ?> เวลา <?= Html::encode($showTime) ?>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <?= Html::encode($goType) ?> : <?= Html::encode($locTitle) ?>
                                        </div>
                                        <div class="small text-truncate mt-1" style="max-width: 100%;">
                                            <?= Html::encode($reason) ?>
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg-3">
                                        <div class="fw-bold text-truncate small mb-1">
                                            <i class="bi bi-people me-1"></i><?= Html::encode($driver['fullname'] ?? '-') ?>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-car-front me-1"></i>ทะเบียนรถ: <?= Html::encode($item->car?->license_plate ?? '-') ?>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            ไมล์ออก: <?= Html::encode((string) ($item->mileage_start ?? '-')) ?>
                                        </div>
                                        <div class="small text-muted">
                                            ไมล์หลัง: <?= Html::encode((string) ($item->mileage_end ?? '-')) ?>
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg-2">
                                        <div class="d-flex flex-column justify-content-between" style="min-height: 120px;">
                                            <div class="small text-muted mb-1 text-end">
                                                ดำเนินการ
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                                <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>บันทึก', $workUpdateUrl, ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                                <?= Html::a('<i class="fa-solid fa-eye me-1"></i>แสดง', $viewUrl, ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                                <?= Html::a('<i class="fa-regular fa-circle-xmark me-1"></i>ยกเลิก', $cancelUrl, ['class' => 'btn btn-sm btn-outline-danger cancel-order', 'data' => ['size' => 'modal-lg']]) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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