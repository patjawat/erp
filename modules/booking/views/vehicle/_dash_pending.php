<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var int $pendingTotal */

$requests = $searchModel->pendingRequests(6);
$today = date('Y-m-d');

$allUrl = Url::to([
    '/booking/vehicle/index',
    'VehicleSearch' => array_filter([
        'status' => 'Pending',
        'thai_year' => $searchModel->thai_year,
    ], static fn($v) => $v !== null && $v !== ''),
]);
?>

<section class="card border-0 shadow-sm vd-card" aria-labelledby="vd-pending-heading">
    <div class="card-header bg-primary-gradient text-white">
        <h4 id="vd-pending-heading" class="h6 text-white mb-0">
            <i class="bi bi-hourglass-split me-1" aria-hidden="true"></i>คำขอรอจัดสรร
            <span class="badge rounded-pill bg-white bg-opacity-25 text-white fw-medium ms-1 vd-num">
                <?= number_format((int) $pendingTotal) ?>
            </span>
        </h4>
        <p class="small text-white-50 mb-0">เรียงตามวันเดินทางที่ใกล้ที่สุด</p>
    </div>

    <div class="card-body">
        <?php if (empty($requests)): ?>
            <div class="text-center text-body-secondary py-4">
                <i class="bi bi-check2-circle fs-3 d-block mb-1 text-success" aria-hidden="true"></i>
                <div class="small">จัดสรรครบทุกคำขอแล้ว</div>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-1">
                <?php foreach ($requests as $item): ?>
                    <?php
                    $requester = $item->userRequest();
                    $isOverdue = $item->date_start !== null && $item->date_start < $today;
                    // "5 ส.ค. 2569" → บรรทัดบน "5" บรรทัดล่าง "ส.ค. 69"
                    $parts = $item->date_start
                        ? explode(' ', ThaiDateHelper::formatThaiDate($item->date_start, 'short'))
                        : [];
                    $day = $parts[0] ?? '-';
                    $month = trim(($parts[1] ?? '') . ' ' . (isset($parts[2]) ? mb_substr($parts[2], 2) : ''));
                    $url = Url::to([
                        '/booking/vehicle/view',
                        'id' => $item->id,
                        'title' => '<i class="fa-solid fa-car"></i> รายละเอียดการใช้ยานพาหนะ',
                    ]);
                    ?>
                    <a class="vd-task open-modal" href="<?= $url ?>" data-size="modal-lg"
                        aria-label="<?= Html::encode('เปิดคำขอ ' . $item->code . ' ของ ' . ($requester['fullname'] ?? '-')) ?>">
                        <span class="vd-task__date <?= $isOverdue ? 'text-danger' : 'text-body-secondary' ?>">
                            <span class="vd-task__day"><?= $day ?></span>
                            <span class="vd-task__month"><?= Html::encode($month) ?></span>
                        </span>
                        <span class="vd-task__body">
                            <span class="vd-task__title d-block">
                                <?= Html::encode($item->locationOrg?->title ?: ($item->location ?: '-')) ?>
                            </span>
                            <span class="vd-task__meta d-block text-body-secondary">
                                <?= Html::encode($requester['fullname'] ?? '-') ?>
                                · <?= Html::encode($item->viewTime()['full'] ?? '') ?>
                            </span>
                        </span>
                        <?php if ($isOverdue): ?>
                            <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis flex-shrink-0">เลยกำหนด</span>
                        <?php endif; ?>
                        <i class="bi bi-chevron-right text-body-tertiary flex-shrink-0" aria-hidden="true"></i>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($pendingTotal > count($requests)): ?>
                <div class="pt-2">
                    <?= Html::a(
                        'ดูคำขอรอจัดสรรทั้งหมด ' . number_format((int) $pendingTotal) . ' รายการ',
                        $allUrl,
                        ['class' => 'btn btn-sm btn-outline-primary w-100']
                    ) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
