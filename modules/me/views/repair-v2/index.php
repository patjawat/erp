<?php

use yii\web\View;
use yii\helpers\Html;
use yii\bootstrap5\LinkPager;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\RepairFormSetting;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ทะเบียนประวัติแจ้งซ่อม';
$this->params['breadcrumbs'][] = ['label' => 'บริการ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'แจ้งซ่อม', 'url' => ['/me/repair']];
$this->params['breadcrumbs'][] = $this->title;

$badgeClass = static function (string $color): string {
    return 'badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1';
};

$urgencyList = Helpdesk::listUrgency();
$statusMeta = [
    'pending' => ['label' => 'เปิดงาน', 'color' => 'warning'],
    'receive' => ['label' => 'รับเรื่อง', 'color' => 'info'],
    'in_progress' => ['label' => 'กำลังดำเนินการ', 'color' => 'info'],
    'success' => ['label' => 'เสร็จสิ้น', 'color' => 'success'],
    'cancel' => ['label' => 'ยกเลิก', 'color' => 'danger'],
];

$workflowSteps = [
    ['code' => 'pending', 'label' => 'เปิดงาน'],
    ['code' => 'receive', 'label' => 'รับเรื่อง'],
    ['code' => 'in_progress', 'label' => 'ดำเนินการ'],
    ['code' => 'success', 'label' => 'เสร็จสิ้น'],
];

?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="fa-solid fa-clipboard-check"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?= $this->render('@app/components/ui/btnReturn') ?>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid px-2 px-md-3 px-lg-4">
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0 py-1"><i class="fa-solid fa-magnifying-glass me-2"></i>การค้นหา</h6>
                </div>
                <div class="card-body">
                    <?= $this->render('_search', ['model' => $searchModel]) ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md">
                            <h6 class="mb-0 py-1">
                                <i class="fa-solid fa-list-check me-2"></i>ทะเบียนงานซ่อม
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1 ms-1">
                                    <?= number_format($dataProvider->getTotalCount(), 0) ?>
                                </span>
                            </h6>
                        </div>
                        <div class="col-12 col-md-auto">
                            <div class="d-grid d-md-block">
                                <?= Html::a(
                                    '<i class="fa-solid fa-circle-plus me-1"></i> แจ้งซ่อม',
                                    ['/me/repair-v2/create', 'title' => '<i class="fa-solid fa-screwdriver-wrench"></i> แจ้งซ่อม'],
                                    ['class' => 'btn btn-light btn-sm open-modal', 'data' => ['size' => 'modal-lg']]
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php $models = $dataProvider->getModels(); ?>
        <div class="col-12">
            <div class="overflow-auto" style="max-height: 68vh;">
                <div class="row g-3" role="list">
                    <?php foreach ($models as $key => $item): ?>
                    <?php
                    $urgencyCode = is_array($item->data_json ?? null) ? ($item->data_json['urgency'] ?? null) : null;
                    $urgencyLabel = 'ไม่ระบุ';
                    if ($urgencyCode !== null && $urgencyCode !== '') {
                        $urgencyLabel = $urgencyList[(string) $urgencyCode] ?? $urgencyList[$urgencyCode] ?? 'ไม่ระบุ';
                    }

                    $vu = $item->viewUrgent();
                    $urgencyColor = 'secondary';
                    if (!empty($vu['color']) && is_string($vu['color'])) {
                        $c = preg_replace('/[^a-z]/', '', strtolower($vu['color']));
                        $allowed = ['primary', 'secondary', 'success', 'danger', 'warning', 'info'];
                        $urgencyColor = in_array($c, $allowed, true) ? $c : 'secondary';
                    }

                    $statusCode = (string) ($item->status ?? 'pending');
                    $sInfo = $statusMeta[$statusCode] ?? ['label' => ($item->repairStatus?->title ?? 'ไม่ทราบสถานะ'), 'color' => 'secondary'];
                    $location = is_array($item->data_json ?? null) ? (($item->data_json['location'] ?? '') !== '' ? (string) $item->data_json['location'] : '-') : '-';
                    $createdLabel = $item->viewCreated()['full'] ?? $item->viewCreateDateTime();
                    $deviceLabel = $item->deviceType->title ?? '-';
                    $titleText = (string) ($item->title ?? '');
                    $summaryTitle = mb_strlen($titleText) > 84 ? mb_substr($titleText, 0, 84) . '…' : $titleText;
                    $ratingValue = (int) ($item->rating ?? 0);
                    $ratingValue = max(0, min(5, $ratingValue));
                    $ratingIcons = '';
                    for ($star = 1; $star <= 5; $star++) {
                        $ratingIcons .= $star <= $ratingValue
                            ? '<i class="fa-solid fa-star"></i>'
                            : '<i class="fa-regular fa-star text-muted"></i>';
                    }

                    $daysSinceReport = null;
                    if (!empty($item->created_at)) {
                        try {
                            $dtCreated = new \DateTimeImmutable((string) $item->created_at);
                            $d0 = $dtCreated->setTime(0, 0, 0);
                            $d1 = (new \DateTimeImmutable('today'))->setTime(0, 0, 0);
                            $daysSinceReport = $d0 > $d1 ? 0 : (int) $d0->diff($d1)->days;
                        } catch (\Throwable $e) {
                            $daysSinceReport = null;
                        }
                    }
                    $durationLabel = null;
                    if ($statusCode === 'success' && !empty($item->created_at) && !empty($item->updated_at)) {
                        try {
                            $startAt = new \DateTimeImmutable((string) $item->created_at);
                            $endAt = new \DateTimeImmutable((string) $item->updated_at);
                            if ($endAt < $startAt) {
                                $endAt = $startAt;
                            }
                            $duration = $startAt->diff($endAt);
                            if ((int) $duration->days > 0) {
                                $durationLabel = 'ระยะเวลาดำเนินการ ' . (int) $duration->days . ' วัน';
                            } elseif ((int) $duration->h > 0) {
                                $durationLabel = 'ระยะเวลาดำเนินการ ' . (int) $duration->h . ' ชม.';
                            } else {
                                $durationLabel = 'ระยะเวลาดำเนินการ ' . max(1, (int) $duration->i) . ' นาที';
                            }
                        } catch (\Throwable $e) {
                            $durationLabel = null;
                        }
                    }

                    $flowIndex = array_search($statusCode, array_column($workflowSteps, 'code'), true);
                    if ($flowIndex === false) {
                        $flowIndex = -1;
                    }
                    ?>
                    <div class="col-12" role="listitem">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3 align-items-stretch">
                                    <div class="col-12 col-xl h-100">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                            <span class="<?= $badgeClass('secondary') ?>">#<?= (($dataProvider->pagination->offset + 1) + $key) ?></span>
                                            <h6 class="font-monospace text-primary"><?= Html::encode($item->repair_number ?? '-') ?></h6>
                                            <h6 class="text-body"><?= Html::encode($deviceLabel) ?></h6>
                                            <?php if (!empty($item->asset_number)): ?>
                                                <span class="text-muted small font-monospace"><?= Html::encode($item->asset_number) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex flex-row gap-3">
                                            <div class="small text-muted">
                                                <i class="fa-regular fa-user me-1"></i><?= Html::encode($item->emp->fullname ?? '-') ?>
                                                <span class="mx-1">·</span>
                                                <i class="fa-solid fa-location-dot me-1"></i><?= Html::encode($location) ?>
                                                <div class="text-muted small mt-1">
                                                    <i class="fa-regular fa-clock me-1"></i>วันเวลาแจ้งซ่อม: <?= Html::encode($createdLabel) ?>
                                                </div>
                                            </div>
                                            <div class="mb-1">
                                                <div class="small text-muted mb-1">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>รายละเอียดปัญหา
                                                </div>
                                                <div class="text-body fw-medium">
                                                    <?= Html::encode($summaryTitle !== '' ? $summaryTitle : '-') ?>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-12 col-xl-auto d-flex flex-column h-100">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                            <?php if ($daysSinceReport !== null): ?>
                                                <?php $agingColor = $daysSinceReport <= 3 ? 'secondary' : ($daysSinceReport <= 7 ? 'warning' : 'danger'); ?>
                                                <span class="<?= $badgeClass($agingColor) ?>">ผ่านมาแล้ว <?= $daysSinceReport ?> วัน</span>
                                            <?php endif; ?>
                                            <?php if ($durationLabel !== null): ?>
                                                <span class="<?= $badgeClass('success') ?>"><i class="fa-regular fa-hourglass me-1"></i><?= Html::encode($durationLabel) ?></span>
                                            <?php endif; ?>
                                            <span class="<?= $badgeClass($urgencyColor) ?>"><?= Html::encode($urgencyLabel) ?></span>
                                            <span class="<?= $badgeClass($sInfo['color']) ?>"><?= Html::encode($sInfo['label']) ?></span>
                                            <?php /* move rating to action row */ ?>
                                        </div>

                                        <div class="mt-auto text-end">
                                            <div class="fw-medium text-truncate" title="<?= Html::encode($item->emp->fullname ?? '-') ?>">
                                                <i class="fa-solid fa-user-check me-1"></i>ผู้รับเรื่อง:
                                                <?= Html::encode($item->emp->fullname ?? '-') ?>
                                            </div>
                                            <div class="text-muted small text-truncate" title="<?= Html::encode((string) $createdLabel) ?>">
                                                <i class="fa-regular fa-clock me-1"></i>รับเรื่องเมื่อ: <?= Html::encode((string) $createdLabel) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2 mt-2 border-top">
                                    <?php
                                    $totalSteps = count($workflowSteps);
                                    $progressPercent = ($flowIndex >= 0 && $totalSteps > 0) ? (int) round((($flowIndex + 1) / $totalSteps) * 100) : 0;
                                    $progressColor = $statusCode === 'success' ? 'success' : ($statusCode === 'in_progress' ? 'info' : ($statusCode === 'cancel' ? 'danger' : 'primary'));

                                    $receiverFullname = $item->emp->fullname ?? '-';
                                    $receiverAvatarUrl = '';
                                    try {
                                        $receiverAvatarUrl = (!empty($item->emp) && method_exists($item->emp, 'ShowAvatar')) ? (string) $item->emp->ShowAvatar() : '';
                                    } catch (\Throwable $e) {
                                        $receiverAvatarUrl = '';
                                    }
                                    ?>

                                    <div class="row g-2 align-items-start">
                                        <div class="col-12 col-lg-12">
                                            <div class="small text-muted mb-1">ความคืบหน้า</div>
                                            <div class="progress my-3" role="progressbar" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100" style="height: 6px;">
                                                <div class="progress-bar bg-<?= Html::encode($progressColor) ?>" style="width: <?= $progressPercent ?>%"></div>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center gap-1 gap-sm-2 mb-2">
                                                <?php foreach ($workflowSteps as $si => $step): ?>
                                                    <?php
                                                    $isDone = $flowIndex >= 0 && $si < $flowIndex;
                                                    $isCurrent = $flowIndex >= 0 && $si === $flowIndex;
                                                    if ($isDone) {
                                                        $stepClass = $badgeClass('success');
                                                    } elseif ($isCurrent) {
                                                        $stepClass = 'badge bg-primary text-white border border-primary-subtle rounded-pill fw-medium px-2 py-1';
                                                    } else {
                                                        $stepClass = $badgeClass('secondary') . ' opacity-75';
                                                    }
                                                    $stepIcon = ($isDone || ($isCurrent && $statusCode === 'success')) ? 'fa-solid fa-circle-check' : 'fa-solid fa-hourglass-half';
                                                    ?>
                                                    <?php if ($si > 0): ?><span class="text-muted small"><i class="fa-solid fa-chevron-right"></i></span><?php endif; ?>
                                                    <span class="<?= $stepClass ?>"><i class="<?= $stepIcon ?> me-1"></i><?= Html::encode($step['label']) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <?php /* ผู้รับเรื่องถูกย้ายไปแสดงฝั่งขวาเหนือความคืบหน้าแล้ว */ ?>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
                                        <div class="d-flex flex-wrap align-items-center gap-1">
                                            <span class="text-warning"><?= $ratingIcons ?></span>
                                        </div>
                                        <div class="d-flex flex-wrap justify-content-end gap-1">
                                            <?php if (RepairFormSetting::isEnabled()): ?>
                                                <?= Html::a(
                                                    '<i class="fa-solid fa-print me-2"></i>พิมพ์ใบส่งซ่อม',
                                                    ['/helpdesk/service/print-send-repair-pdf', 'id' => $item->id],
                                                    ['class' => 'btn btn-outline-dark btn-sm', 'target' => '_blank', 'rel' => 'noopener', 'data-pjax' => 0]
                                                ) ?>
                                            <?php endif; ?>
                                            <?= Html::a('<i class="fa-regular fa-eye me-2"></i>ดูรายละเอียด', ['/me/repair-v2/view', 'id' => $item->id, 'title' => 'รายละเอียดงานซ่อม #' . $item->repair_number], ['class' => 'btn btn-outline-secondary btn-sm open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                            <?php if ($item->status === 'pending' || $item->status === 'receive'): ?>
                                                <?= Html::a('<i class="fa-regular fa-pen-to-square me-2"></i>แก้ไข', ['/me/repair-v2/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square me-2"></i>แก้ไข'], ['class' => 'btn btn-outline-primary btn-sm open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                                <?= Html::a('<i class="fa-solid fa-ban me-2"></i>ยกเลิก', ['/me/repair-v2/cancel', 'id' => $item->id], ['class' => 'btn btn-outline-danger btn-sm cancel-order']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="d-flex justify-content-center pt-1">
                <div class="text-muted small">
                    <?= LinkPager::widget([
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
    </div>