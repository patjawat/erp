<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <?= $icon ?>
        <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/booking/vehicle_menu', ['active' => $vehicle_type]) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin([
    'id' => 'vehicle-index-pjax',
    'timeout' => 10000,
    'enablePushState' => true,
]); ?>

<div class="card">
    <div class="card-header">
        <h6 class="mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php
        $action = ['work-official'];
        echo $this->render('@app/modules/booking/views/vehicle/_search', [
            'model' => $searchModel,
            'action' => $action,
        ]);
        ?>
    </div>
</div>

<?php
$cacheKey = ['booking-vehicle-work-official-list', Yii::$app->request->queryParams];
if ($this->beginCache($cacheKey, ['duration' => 60])):
?>

<?php
echo $this->render('@app/modules/booking/views/vehicle/list_work_official', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'statusSummary' => $statusSummary ?? [],
    'waitingAllocationCount' => $waitingAllocationCount ?? 0,
    'allocatedCount' => $allocatedCount ?? 0,
    'title' => $title,
]);
if (false):
?>

<?php
$statusMap = $searchModel->listStatus();
$statusSummaryMap = [];
foreach (($statusSummary ?? []) as $row) {
    $statusSummaryMap[$row['status']] = (int) $row['total'];
}
$cancelledCount = (int) ($statusSummaryMap['Cancel'] ?? 0);
$statusIcons = [
    'Pending' => 'bi bi-hourglass-split',
    'Pass' => 'bi bi-check-circle',
    'Approve' => 'bi bi-patch-check',
    'Success' => 'bi bi-check2-circle',
    'Cancel' => 'bi bi-x-circle',
];
?>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="m-0">
                <i class="bi bi-ui-checks"></i> ทะเบียนการจัดสรรรถยนต์
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                    <?= number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between">
                <button class="btn btn-success export-leave"><i class="fa-solid fa-file-excel"></i> Excel</button>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="border border-danger-subtle rounded-3 p-3 h-100">
            <div class="small text-muted">งานรอดำเนินการจัดสรร</div>
            <div class="fs-5 fw-bold text-danger"><?= number_format((int) ($waitingAllocationCount ?? 0)) ?> รายการ</div>
            <div class="small text-muted">สถานะกลุ่มรออนุมัติ/รอจัดสรร และยังไม่มีรถหรือคนขับ</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="border border-success-subtle rounded-3 p-3 h-100">
            <div class="small text-muted">งานที่จัดสรรแล้ว</div>
            <div class="fs-5 fw-bold text-success"><?= number_format((int) ($allocatedCount ?? 0)) ?> รายการ</div>
            <div class="small text-muted">มีการระบุรถหรือพนักงานขับแล้ว</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="border border-secondary-subtle rounded-3 p-3 h-100">
            <div class="small text-muted">งานที่ยกเลิก</div>
            <div class="fs-5 fw-bold text-secondary"><?= number_format($cancelledCount) ?> รายการ</div>
            <div class="small text-muted">นับจากสถานะ Cancel ในรายการปัจจุบัน</div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="border border-primary-subtle rounded-3 p-3 h-100">
            <div class="small text-muted mb-2">สรุปผลการจอง</div>
            <div class="d-flex flex-wrap gap-1">
                <?php foreach ($statusSummaryMap as $statusCode => $count): ?>
                    <?php
                    $iconClass = $statusIcons[$statusCode] ?? 'bi bi-record-circle';
                    $label = $statusMap[$statusCode] ?? $statusCode;
                    $badgeClass = 'bg-primary bg-opacity-10 text-primary border border-primary-subtle';
                    if ($statusCode === 'Cancel') {
                        $badgeClass = 'bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle';
                    } elseif (in_array($statusCode, ['Pass', 'Success'], true)) {
                        $badgeClass = 'bg-success bg-opacity-10 text-success border border-success-subtle';
                    } elseif ($statusCode === 'Approve') {
                        $badgeClass = 'bg-success bg-opacity-10 text-success border border-success-subtle';
                    } elseif ($statusCode === 'Pending') {
                        $badgeClass = 'bg-danger bg-opacity-10 text-danger border border-danger-subtle';
                    }
                    ?>
                    <span class="badge <?= Html::encode($badgeClass) ?> rounded-pill fw-medium px-2 py-1">
                        <i class="<?= Html::encode($iconClass) ?> me-1 fs-6"></i>
                        <?= Html::encode($label) ?> <?= number_format($count) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div>
    <div class="overflow-auto" style="max-height: 68vh;">
        <div class="row g-2">
            <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                <?php
                $vehicle = $item->vehicle;
                $requester = $vehicle?->userRequest() ?? [];
                $created = $vehicle?->viewCreated() ?? ['full' => '-'];

                $daysPassed = 0;
                try {
                    $createdAt = new \DateTime((string) ($vehicle?->created_at ?? ''));
                    $now = new \DateTime();
                    $daysPassed = (int) $createdAt->diff($now)->days;
                } catch (\Throwable $th) {
                    $daysPassed = 0;
                }

                $plate = trim((string) ($item->license_plate ?? ''));
                $hasAssignedDriver = !empty($item->driver_id) || ($plate !== '' && $plate !== ' ');
                $driverCount = !empty($item->driver_id) ? 1 : 0;
                $plateSummary = ($plate !== '' && $plate !== ' ') ? $plate : '-';

                $isOvernight = (string) ($vehicle?->go_type ?? '') === '2';
                $isWaitingStatus = in_array((string) $item->status, ['Pending', 'Pass', 'Approve'], true);

                $startDate = (string) ($vehicle?->date_start ?? $item->date_start ?? '');
                $startTime = (string) ($vehicle?->time_start ?? $item->time_start ?? '');
                $requestStartTs = strtotime(trim($startDate . ' ' . $startTime));
                $isStartPassed = $requestStartTs !== false ? $requestStartTs < time() : false;

                $isAllocationOverdue = $isOvernight && $isWaitingStatus && !$hasAssignedDriver && $isStartPassed;

                $queueNo = ($dataProvider->pagination->offset + 1) + $key;
                $statusView = $item->viewStatus()['view'] ?? '-';

                $locationTitle = $vehicle?->locationOrg?->title ?? '-';
                $reason = $vehicle?->reason ?? '-';
                $urgent = $vehicle?->viewUrgent() ?? '';

                $showDate = $vehicle?->showDateRange() ?? \app\components\ThaiDateHelper::formatThaiDateRange($item->date_start, $item->date_end);
                $showTimeFull = $vehicle?->viewTime()['full'] ?? $item->viewTime()['full'] ?? '-';

                $workUpdateUrl = [
                    '/booking/vehicle/work-update',
                    'id' => $item->id,
                    'title' => '<i class="fa-regular fa-pen-to-square"></i> บันทึกภาระกิจการใช้รถยนต์',
                ];
                $viewUrl = ['view', 'id' => $item->id];
                $cancelUrl = ['/booking/vehicle-detail/cancel', 'id' => $item->id];
                ?>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                <div class="fw-bold text-primary small">
                                    #<?= (int) $queueNo ?> · รหัสขอใช้รถ <?= Html::encode($vehicle?->code ?? '-') ?>
                                </div>
                                <div>
                                    <?php if (($vehicle?->is_shared ?? 0) == 1): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">
                                            <i class="fa-solid fa-user-group me-1"></i>จัดสรรร่วม
                                        </span>
                                    <?php else: ?>
                                        <?= $statusView ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row g-2 align-items-start">
                                <div class="col-12 col-lg-3">
                                    <div class="d-flex align-items-center">
                                        <?= Html::img('@web/img/loading.gif', [
                                            'class' => 'rounded-3 me-2 shadow-sm lazyload',
                                            'width' => '32',
                                            'height' => '32',
                                            'data' => [
                                                'expand' => '-20',
                                                'sizes' => 'auto',
                                                'src' => $requester['photo'] ?? '',
                                            ]
                                        ]); ?>
                                        <div>
                                            <div class="fw-bold mb-0 small"><?= Html::encode($requester['fullname'] ?? '-') ?></div>
                                            <small class="text-primary d-block"><?= Html::encode($requester['department'] ?? '-') ?></small>
                                        </div>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-calendar-check me-1"></i>จองเมื่อ <?= Html::encode($created['full'] ?? '-') ?>
                                    </div>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1 mt-1">
                                        ผ่านมาแล้ว <?= number_format($daysPassed) ?> วัน
                                    </span>
                                </div>

                                <div class="col-12 col-lg-3">
                                    <div class="small text-muted mb-1">รายละเอียด</div>
                                    <div class="small text-muted text-truncate"><?= Html::encode($reason) ?></div>
                                    <div class="small mt-1">ความเร่งด่วน <?= Html::encode((string) $urgent) ?></div>
                                </div>

                                <div class="col-12 col-lg-3">
                                    <div class="small text-muted mb-1">สถานที่ไป</div>
                                    <div class="fw-bold text-truncate small">
                                        <i class="bi bi-geo-alt text-danger me-1"></i><?= Html::encode($locationTitle) ?>
                                    </div>

                                    <div class="mt-2 d-flex flex-wrap gap-1">
                                        <?php if ($hasAssignedDriver): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">
                                                <i class="bi bi-check2-circle me-1"></i>จัดสรรแล้ว
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1">
                                                <i class="bi bi-hourglass-split me-1"></i>รอจัดสรร
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="small text-muted mt-2">รถยนต์</div>
                                    <div class="small fw-medium text-dark text-truncate">
                                        <i class="bi bi-car-front me-1 text-primary"></i><?= Html::encode($plateSummary) ?>
                                    </div>

                                    <div class="small text-muted mt-2">พขร</div>
                                    <div class="small text-muted mt-1">
                                        <?= $driverCount > 0 ? ($driverCount . ' คน') : '-' ?>
                                    </div>

                                    <div class="avatar-stack mt-1">
                                        <?php if (!empty($item->driver_id) && !empty($item->driver)): ?>
                                            <?= Html::a(
                                                Html::img('@web/img/loading.gif', [
                                                    'class' => 'avatar-sm rounded-circle shadow lazyload',
                                                    'data' => [
                                                        'expand' => '-20',
                                                        'sizes' => 'auto',
                                                        'src' => $item->driver->showAvatar(),
                                                    ]
                                                ]),
                                                $workUpdateUrl,
                                                ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]
                                            ) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-3">
                                    <div class="small text-muted mb-1"> วันเวลาที่ต้องการใช้รถ</div>
                                    <div class="fw-medium text-dark small">
                                        <?= Html::encode($showDate) ?> <?= Html::encode($showTimeFull) ?>
                                    </div>

                                    <?php if ($isAllocationOverdue): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1">
                                                เกินกำหนดจัดสรรรถ โปรดเร่งจัดรถ/คนขับ
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex flex-wrap gap-1 justify-content-end mt-1">
                                        <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>บันทึก', $workUpdateUrl, ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                        <?= Html::a('<i class="fa-solid fa-eye me-1"></i>แสดง', $viewUrl, ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                        <?= Html::a('<i class="fa-regular fa-circle-xmark me-1"></i>ยกเลิก', $cancelUrl, ['class' => 'btn btn-sm btn-outline-danger cancel-order', 'data' => ['size' => 'modal-lg']]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <div class="body-footer mt-2">
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

</div>

<?php endif; ?>
<?php $this->endCache(); endif; ?>

<?php Pjax::end(); ?>

<?php
$js = <<<JS

$(document).on('click', '.cancel-order', function(e) {
    e.preventDefault();
    let url = $(this).attr('href');
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: 'คุณต้องการยกเลิกคำขอนี้หรือไม่?',
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
                        location.reload();
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

