<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\helpdesk2\helpers\HelpdeskSlaHelper;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\modules\hr\models\Employees;

/** @var app\modules\helpdesk2\models\Helpdesk $model */
/** @var string|null $returnUrl */

$returnUrl = $returnUrl ?? null;

$ticketId = $model->id ?? '-';
$titleText = 'หมายเลขงานซ่อม #' . $ticketId;

$dataJson = is_array($model->data_json ?? null) ? $model->data_json : [];
$statusCode = Helpdesk::normalizeRepairStatus($model->status ?? 'pending');
$urgencyCode = $dataJson['urgency'] ?? null;
$createdAt = $model->created_at ?? null;
$externalBillCount = (int) ($model->getExternalRepairBillsCount() ?? 0);

$badgeClass = static function (string $color): string {
    // ใช้ contract ของโปรเจกต์: bg-*-subtle + text-*-emphasis → ผ่าน WCAG AA และคุม dark mode อัตโนมัติ
    return 'badge bg-' . $color . '-subtle text-' . $color . '-emphasis border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1';
};

$statusMeta = Helpdesk::repairStatusMeta();

$statusInfo = $statusMeta[$statusCode] ?? ['label' => 'ไม่ทราบสถานะ', 'color' => 'secondary', 'icon' => 'fa-solid fa-circle'];
$statusBadge = Html::tag('span', '<i class="' . $statusInfo['icon'] . ' me-1" aria-hidden="true"></i>' . Html::encode($statusInfo['label']), [
    'class' => $badgeClass($statusInfo['color']),
]);

$priorityInfo = Helpdesk::repairUrgencyInfo(is_scalar($urgencyCode) ? $urgencyCode : '');
$priorityContent = Html::tag('i', '', [
    'class' => $priorityInfo['icon'] . ' me-1',
    'aria-hidden' => 'true',
]) . Html::encode('ความเร่งด่วน: ' . $priorityInfo['label']);
$priorityBadge = Html::tag('span', $priorityContent, [
    'class' => $badgeClass($priorityInfo['color']),
]);

$slaBadgeHtml = '';
try {
    $slaBadgeHtml = HelpdeskSlaHelper::renderBadge($model);
} catch (\Throwable $e) {
    $slaBadgeHtml = '';
}
if ($slaBadgeHtml === '') {
    $slaBadgeHtml = Html::tag('span', '<i class="fa-regular fa-clock me-1" aria-hidden="true"></i>ไม่มี SLA', [
        'class' => $badgeClass('secondary'),
    ]);
}

$descriptionSummary = Html::encode($model->title ?: '-');
$descriptionExtra = '';
if (!empty($dataJson['repair_note']) && is_string($dataJson['repair_note'])) {
    $descriptionExtra = Html::encode($dataJson['repair_note']);
} elseif (!empty($dataJson['description']) && is_string($dataJson['description'])) {
    $descriptionExtra = Html::encode($dataJson['description']);
}

$locationLabel = '-';
if (!empty($dataJson['location']) && is_string($dataJson['location'])) {
    $locationLabel = Html::encode($dataJson['location']);
}

$requester = [
    'fullname' => 'ยังไม่ระบุ',
    'department' => '-',
];
try {
    $requesterData = $model->getUserReq();
    if (is_array($requesterData) && $requesterData !== []) {
        $requester = array_merge($requester, $requesterData);
    }
} catch (\Throwable $e) {
    // คงค่า fallback เมื่อข้อมูลผู้แจ้งไม่พร้อมใช้งาน
}

$receiver = [
    'fullname' => 'ยังไม่มีผู้รับเรื่อง',
    'department' => '-',
    'avatar' => null,
    'received_at' => null,
];
try {
    $receiveLog = HelpdeskDetail::find()
        ->where([
            'helpdesk_id' => (int) $model->id,
            'name' => 'service_record',
            'status' => 'รับเรื่อง',
        ])
        ->orderBy(['id' => SORT_DESC])
        ->one();
    if ($receiveLog) {
        $receiver['received_at'] = $receiveLog->created_at ?? null;

        if (!empty($receiveLog->emp_id)) {
            $receiveEmp = Employees::findOne(['id' => (int) $receiveLog->emp_id]);
            if (!$receiveEmp) {
                $receiveEmp = Employees::findOne(['user_id' => (int) $receiveLog->emp_id]);
            }
            if ($receiveEmp) {
                $receiver['fullname'] = $receiveEmp->fullname ?? $receiver['fullname'];
                $receiver['department'] = method_exists($receiveEmp, 'departmentName')
                    ? ($receiveEmp->departmentName() ?? $receiver['department'])
                    : $receiver['department'];
                $receiver['avatar'] = method_exists($receiveEmp, 'ShowAvatar') ? $receiveEmp->ShowAvatar() : null;
            }
        }
    }
} catch (\Throwable $e) {
    // คงค่า fallback เมื่อข้อมูลผู้รับเรื่องไม่พร้อมใช้งาน
}

$receiverReceivedAtLabel = null;
if (!empty($receiver['received_at'])) {
    try {
        $receiverReceivedAtLabel = (string) \Yii::$app->formatter->asDatetime($receiver['received_at']);
    } catch (\Throwable $e) {
        $receiverReceivedAtLabel = (string) $receiver['received_at'];
    }
}

$assetCodeLabel = Html::encode($model->asset_number ?: ($dataJson['asset_code'] ?? '-'));
$requestRepairDateLabel = '-';
if (!empty($model->request_repair_date)) {
    try {
        $requestRepairDateLabel = Html::encode((string) \Yii::$app->thaiFormatter->asDate($model->request_repair_date, 'long'));
    } catch (\Throwable $e) {
        $requestRepairDateLabel = Html::encode((string) \Yii::$app->formatter->asDate($model->request_repair_date));
    }
}

$repairChannel = (string) ($dataJson['repair_channel'] ?? '');
$repairModeMap = [
    'internal' => ['label' => 'ซ่อมภายใน', 'color' => 'success', 'hint' => 'เบิกอะไหล่จากคลังและบันทึกงานซ่อมโดยทีมช่างภายใน'],
    'external' => ['label' => 'ซ่อมภายนอก', 'color' => 'warning', 'hint' => 'บันทึกค่าใช้จ่ายรวมและแนบบิลจากผู้รับซ่อมภายนอก'],
    'hybrid' => ['label' => 'ซ่อมผสม (ภายใน+ภายนอก)', 'color' => 'primary', 'hint' => 'บันทึกได้ทั้งเบิกอะไหล่ภายในและค่าใช้จ่ายซ่อมภายนอก'],
];
$repairModeInfo = $repairModeMap[$repairChannel] ?? ['label' => 'ยังไม่เลือกโหมดซ่อม', 'color' => 'secondary', 'hint' => 'แนะนำให้เลือกโหมดในฟอร์มสรุปงาน เพื่อให้การบันทึกต้นทุนครบถ้วน'];
$costLabor = is_numeric($dataJson['cost_labor'] ?? null) ? (float) $dataJson['cost_labor'] : 0.0;
$costParts = is_numeric($dataJson['cost_parts'] ?? null) ? (float) $dataJson['cost_parts'] : 0.0;
$costTotalForm = is_numeric($dataJson['cost_total'] ?? null) ? (float) $dataJson['cost_total'] : 0.0;

// ประวัติการดำเนินการมาจาก service_record จริงที่ controller ส่งมาเท่านั้น (ไม่มีข้อมูลจำลอง)
$timelineItems = (isset($logs) && is_array($logs)) ? $logs : [];
$commentsItems = [];
if (isset($comments) && is_array($comments)) {
    $commentsItems = $comments;
} else {
    $feedbackComment = trim((string) ($dataJson['comment'] ?? ''));
    $feedbackRating = (int) ($model->rating ?? 0);
    $feedbackDate = (string) ($dataJson['comment_date'] ?? '');
    if ($feedbackRating > 0 || $feedbackComment !== '') {
        $requesterName = (string) ($requester['fullname'] ?? '-');
        // ส่งคะแนนเป็นข้อมูลแยก (ไม่ยัด ★ ลงในข้อความ) เพื่อให้ _comments เรนเดอร์ดาวแบบมี aria-label
        $commentsItems[] = (object) [
            'is_staff' => false,
            'user' => (object) ['name' => $requesterName],
            'created_at' => $feedbackDate !== '' ? $feedbackDate : null,
            'rating' => max(0, min(5, $feedbackRating)),
            'message' => $feedbackComment,
        ];
    }
}

$detailRows = HelpdeskDetail::find()
    ->where(['helpdesk_id' => $model->id])
    ->orderBy(['id' => SORT_ASC])
    ->all();
$serviceRecordCount = 0;
$partCount = 0;
$expenseCount = 0;
$totalExpense = 0.0;
foreach ($detailRows as $d) {
    $nameCode = strtolower((string) ($d->name ?? ''));
    $dj = is_array($d->data_json ?? null) ? $d->data_json : [];
    if ($nameCode === 'service_record') {
        $serviceRecordCount++;
    }
    $isPartRecord = ($nameCode === 'part_record') || ($nameCode === 'repair_part') || str_contains($nameCode, 'part');
    if ($isPartRecord) {
        $partCount++;
    }

    // นับเฉพาะค่าใช้จ่ายจริง ลดการนับซ้ำจาก service_record ทั่วไป
    $isExpenseRecord = ($nameCode === 'expense_record')
        || ($nameCode !== 'service_record' && str_contains($nameCode, 'expense'));
    if ($isExpenseRecord) {
        $expenseCount++;
        $amount = 0.0;
        foreach (['total', 'amount', 'price', 'cost'] as $k) {
            if (isset($dj[$k]) && is_numeric($dj[$k])) {
                $amount = (float) $dj[$k];
                break;
            }
        }
        if ($amount <= 0 && is_numeric($d->code ?? null)) {
            $amount = (float) $d->code;
        }
        $totalExpense += $amount;
    }
}
$totalCostDisplay = max($totalExpense, $costTotalForm, ($costLabor + $costParts));
$isReceived = in_array($statusCode, ['receive', 'in_progress', 'success', 'cancel'], true);
$isStarted = in_array($statusCode, ['in_progress', 'success'], true);
$isClosed = in_array($statusCode, ['success', 'cancel'], true);
$isFinished = ($statusCode === 'success');
$hasExternal = $model->isExternalRepair();
$hasRootCauseData = trim((string) ($model->data_json['root_cause'] ?? '')) !== ''
    || trim((string) ($model->data_json['diagnosis'] ?? '')) !== '';

$receiveRoute = ['/helpdesk/service/receive', 'id' => $model->id];
$sendRepairRoute = ['/helpdesk/service/send-repair', 'id' => $model->id];
$refreshRoute = ['/helpdesk/service/view-v2', 'id' => $model->id];
if ($returnUrl !== null && $returnUrl !== '') {
    $receiveRoute['returnUrl'] = $returnUrl;
    $sendRepairRoute['returnUrl'] = $returnUrl;
    $refreshRoute['returnUrl'] = $returnUrl;
}
$receiveUrl = Url::to($receiveRoute);
$sendRepairUrl = Url::to($sendRepairRoute);
$refreshUrl = Url::to($refreshRoute);
$partsUrl = Url::to(['/helpdesk/repair-parts/create', 'helpdesk_id' => $model->id, 'title' => 'เบิกอะไหล่งานซ่อม #' . $model->repair_number]);
$expenseUrl = Url::to(['/helpdesk/expenses/create', 'helpdesk_id' => $model->id, 'title' => 'ลงค่าใช้จ่ายงานซ่อม #' . $model->repair_number]);
$billUploadUrl = Url::to(['/helpdesk/service/external-bill-form', 'id' => $model->id, 'title' => 'อัปโหลดบิลค่าใช้จ่าย #' . $model->repair_number]);
$closeJobUrl = Url::to(['/helpdesk/service/update-status', 'id' => $model->id, 'title' => 'ปิดงานซ่อม #' . $model->repair_number]);
$editTicketLiteUrl = Url::to(['/helpdesk/service/edit-ticket-form', 'id' => $model->id, 'title' => 'แก้ไขใบแจ้งซ่อม #' . $model->repair_number]);

$requiredDone = 0;
$requiredDone += $isReceived ? 1 : 0;
$requiredDone += $isStarted ? 1 : 0;
$requiredDone += $hasRootCauseData ? 1 : 0;
$requiredDone += $isFinished ? 1 : 0;
$requiredTotal = 4;
$requiredProgress = (int) round(($requiredDone / max(1, $requiredTotal)) * 100);

$hasStep4Activity = ($partCount > 0) || ($expenseCount > 0) || ($externalBillCount > 0);
$activeStep = null;
if (!$isReceived) {
    $activeStep = 1;
} elseif (!$isStarted) {
    $activeStep = 2;
} elseif (!$hasRootCauseData) {
    $activeStep = 3;
} elseif (!$isClosed && !$hasStep4Activity) {
    $activeStep = 4;
} elseif (!$isClosed) {
    $activeStep = 5;
}

// สถานะรายขั้น: เสร็จแล้ว (ขั้นที่ 4 เป็น optional จึงถือว่าเสร็จเมื่อมีกิจกรรมหรือปิดงานแล้ว)
$stepDone = [
    1 => $isReceived,
    2 => $isStarted,
    3 => $hasRootCauseData,
    4 => $hasStep4Activity || $isClosed,
    5 => $isClosed,
];
// map ขั้น → สถานะ marker: current | done | cancel | todo (ขั้นสุดท้ายที่ปิดแบบยกเลิก = cancel)
$stepState = static function (int $n) use ($activeStep, $stepDone, $isFinished): string {
    if ($activeStep === $n) {
        return 'current';
    }
    if (!empty($stepDone[$n])) {
        return ($n === 5 && !$isFinished) ? 'cancel' : 'done';
    }
    return 'todo';
};

?>

<div
    id="helpdesk-service-view-v2"
    data-refresh-url="<?= Html::encode($refreshUrl) ?>"
    aria-busy="false"
>
            <?= $this->render('_header', [
                'model' => $model,
                'titleText' => $titleText,
                'statusBadge' => $statusBadge,
                'priorityBadge' => $priorityBadge,
                'slaBadgeHtml' => $slaBadgeHtml,
                'returnUrl' => $returnUrl,
                'requester' => $requester,
                'descriptionSummary' => $descriptionSummary,
                'descriptionExtra' => $descriptionExtra,
                'locationLabel' => $locationLabel,
            ]); ?>

            <div class="row g-4 mt-0 align-items-start">
                <aside class="col-12 col-lg-4 order-2 order-lg-1" aria-labelledby="repair-activity-heading">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h2 id="repair-activity-heading" class="h6 fw-bold mb-0">ความเคลื่อนไหวของงาน</h2>
                            <div class="text-body-secondary small mt-1">ประวัติการดำเนินงานและความคิดเห็นของผู้แจ้ง</div>
                        </div>
                        <div class="card-body p-3">
                            <?= $this->render('_timeline', ['items' => $timelineItems]); ?>
                            <?= $this->render('_comments', ['comments' => $commentsItems]); ?>
                        </div>
                    </div>
                </aside>

                <div class="col-12 col-lg-8 order-1 order-lg-2">
            <div class="card shadow-sm">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h2 class="h6 fw-bold mb-0"><i class="fa-solid fa-list-check me-1" aria-hidden="true"></i> ขั้นตอนงานซ่อม</h2>
                    <?= Html::tag('span', 'คืบหน้า ' . $requiredProgress . '%', ['class' => $badgeClass($requiredProgress >= 100 ? 'success' : 'info')]) ?>
                </div>
                <div class="card-body p-4">
                    <div class="progress mb-4" role="progressbar" aria-label="ความคืบหน้างานซ่อม" aria-valuenow="<?= $requiredProgress ?>" aria-valuemin="0" aria-valuemax="100" style="height: .5rem;">
                        <div class="progress-bar <?= $isClosed ? 'bg-success' : '' ?>" style="width: <?= $requiredProgress ?>%"></div>
                    </div>

                    <?php // ยกเลิกช่องทางเบิกอะไหล่จากคลังเดิม (inventory v1) — ให้เบิกผ่าน inventoryV2 (POS) ที่เดียว
                          // รายการเก่า (part_record_legacy) ยังนับ/แสดงในไทม์ไลน์เหมือนเดิม ?>
                    <ol class="repair-stepper list-unstyled mb-0">
                        <?php $st = $stepState(1); ?>
                        <li class="repair-step is-<?= $st ?>"<?= $st === 'current' ? ' aria-current="step"' : '' ?>>
                            <div class="repair-step__rail">
                                <span class="repair-step__marker"><?php if ($st === 'done'): ?><i class="fa-solid fa-check" aria-hidden="true"></i><?php else: ?>1<?php endif; ?></span>
                            </div>
                            <div class="repair-step__body">
                                <div class="repair-step__head">
                                    <span class="repair-step__title">รับเรื่อง</span>
                                    <?= Html::tag('span', $isReceived ? 'เสร็จแล้ว' : 'รอดำเนินการ', ['class' => $badgeClass($isReceived ? 'success' : 'secondary')]) ?>
                                </div>
                                <?php if ($isReceived): ?>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <?php if (!empty($receiver['avatar'])): ?>
                                            <?= Html::img($receiver['avatar'], [
                                                'class' => 'rounded-circle border border-secondary-subtle object-fit-cover flex-shrink-0',
                                                'alt' => '',
                                                'loading' => 'lazy',
                                                'width' => 32,
                                                'height' => 32,
                                            ]) ?>
                                        <?php else: ?>
                                            <span class="rounded-circle border border-secondary-subtle bg-secondary-subtle text-secondary-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0 p-2 lh-1" aria-hidden="true">
                                                <i class="fa-solid fa-user-check"></i>
                                            </span>
                                        <?php endif; ?>
                                        <div class="overflow-hidden">
                                            <div class="small text-body-secondary">ผู้รับเรื่องซ่อม</div>
                                            <div class="d-flex flex-wrap align-items-baseline gap-1">
                                                <span class="fw-semibold text-break"><?= Html::encode($receiver['fullname'] ?? '-') ?></span>
                                                <?php if (!empty($receiver['department']) && $receiver['department'] !== '-'): ?>
                                                    <span class="small text-body-secondary text-break">(<?= Html::encode($receiver['department']) ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($receiverReceivedAtLabel !== null): ?>
                                                <div class="small text-body-secondary">
                                                    <i class="fa-regular fa-clock me-1" aria-hidden="true"></i>รับเรื่องเมื่อ: <?= Html::encode($receiverReceivedAtLabel) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="repair-step__actions">
                                    <?php if (!$isReceived): ?>
                                <?= Html::button('<i class="fa-solid fa-inbox me-1" aria-hidden="true"></i> รับเรื่อง', [
                                    'type' => 'button',
                                    'class' => 'btn btn-sm btn-outline-primary receive-order',
                                    'data' => ['url' => $receiveUrl],
                                ]) ?>
                                    <?php endif; ?>
                                    <?= Html::a('<i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i> แก้ไขใบแจ้งซ่อม', $editTicketLiteUrl, ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                </div>
                            </div>
                        </li>

                        <?php $st = $stepState(2); ?>
                        <li class="repair-step is-<?= $st ?>"<?= $st === 'current' ? ' aria-current="step"' : '' ?>>
                            <div class="repair-step__rail">
                                <span class="repair-step__marker"><?php if ($st === 'done'): ?><i class="fa-solid fa-check" aria-hidden="true"></i><?php else: ?>2<?php endif; ?></span>
                            </div>
                            <div class="repair-step__body">
                                <div class="repair-step__head">
                                    <span class="repair-step__title">ส่งซ่อม / เริ่มดำเนินการ</span>
                                    <?= Html::tag('span', $isStarted ? 'เสร็จแล้ว' : 'รอดำเนินการ', ['class' => $badgeClass($isStarted ? 'success' : 'secondary')]) ?>
                                </div>
                                <?php if (!$isStarted): ?>
                                    <div class="repair-step__actions">
                                        <?= Html::button('<i class="fa-solid fa-truck-fast me-1" aria-hidden="true"></i> ส่งซ่อม / เริ่มงาน', [
                                            'type' => 'button',
                                            'class' => 'btn btn-sm btn-outline-info btn-send-repair',
                                            'data' => ['url' => $sendRepairUrl],
                                        ]) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>

                        <?php $st = $stepState(3); ?>
                        <li class="repair-step is-<?= $st ?>"<?= $st === 'current' ? ' aria-current="step"' : '' ?>>
                            <div class="repair-step__rail">
                                <span class="repair-step__marker"><?php if ($st === 'done'): ?><i class="fa-solid fa-check" aria-hidden="true"></i><?php else: ?>3<?php endif; ?></span>
                            </div>
                            <div class="repair-step__body">
                                <div class="repair-step__head">
                                    <span class="repair-step__title">บันทึกสาเหตุของปัญหา</span>
                                    <?= Html::tag('span', $hasRootCauseData ? 'เสร็จแล้ว' : 'ยังไม่บันทึก', ['class' => $badgeClass($hasRootCauseData ? 'success' : 'secondary')]) ?>
                                </div>
                                <div class="repair-step__actions">
                                    <?= Html::a(
                                        '<i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i> ลงข้อมูลวินิจฉัย',
                                        ['/helpdesk/service/root-cause-form', 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-sm btn-outline-primary open-modal',
                                            'data' => ['size' => 'modal-lg'],
                                        ]
                                    ) ?>
                                </div>
                            </div>
                        </li>

                        <?php $st = $stepState(4); ?>
                        <li class="repair-step is-<?= $st ?>"<?= $st === 'current' ? ' aria-current="step"' : '' ?>>
                            <div class="repair-step__rail">
                                <span class="repair-step__marker"><?php if ($st === 'done'): ?><i class="fa-solid fa-check" aria-hidden="true"></i><?php else: ?>4<?php endif; ?></span>
                            </div>
                            <div class="repair-step__body">
                                <div class="repair-step__head">
                                    <span class="repair-step__title">อะไหล่ / ค่าใช้จ่าย <span class="text-body-secondary fw-normal">(ถ้ามี)</span></span>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?= Html::tag('span', 'อะไหล่ ' . number_format($partCount) . ' รายการ', ['class' => $badgeClass($partCount > 0 ? 'success' : 'secondary')]) ?>
                                        <?= Html::tag('span', 'ค่าใช้จ่าย ' . number_format($expenseCount) . ' รายการ', ['class' => $badgeClass($expenseCount > 0 ? 'warning' : 'secondary')]) ?>
                                    </div>
                                </div>
                                <div class="repair-step__actions">
                                    <?= Html::a('<i class="fa-regular fa-file-lines me-1" aria-hidden="true"></i> เบิกอะไหล่ (POS)', $partsUrl, ['class' => 'btn btn-sm btn-outline-secondary btn-open-part-pos']) ?>
                                    <?= Html::a('<i class="fa-solid fa-money-bill-wave me-1" aria-hidden="true"></i> ลงค่าใช้จ่าย (POS)', $expenseUrl, ['class' => 'btn btn-sm btn-outline-secondary btn-open-expense-pos']) ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-solid fa-ellipsis me-1" aria-hidden="true"></i> เพิ่มเติม<?php if ($externalBillCount > 0): ?> <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1"><?= number_format($externalBillCount) ?></span><?php endif; ?>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><?= Html::a('<i class="fa-solid fa-file-arrow-up me-2" aria-hidden="true"></i> อัปโหลดบิลค่าใช้จ่าย (' . number_format($externalBillCount) . ')', $billUploadUrl, ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <?php $st = $stepState(5); ?>
                        <li class="repair-step is-<?= $st ?>"<?= $st === 'current' ? ' aria-current="step"' : '' ?>>
                            <div class="repair-step__rail">
                                <span class="repair-step__marker"><?php if ($st === 'done'): ?><i class="fa-solid fa-check" aria-hidden="true"></i><?php elseif ($st === 'cancel'): ?><i class="fa-solid fa-xmark" aria-hidden="true"></i><?php else: ?>5<?php endif; ?></span>
                            </div>
                            <div class="repair-step__body">
                                <div class="repair-step__head">
                                    <span class="repair-step__title">ปิดงาน</span>
                                    <?= Html::tag('span', $isFinished ? 'ปิดงานแล้ว' : ($isClosed ? 'จบงาน (ยกเลิก)' : 'ยังไม่ปิดงาน'), ['class' => $badgeClass($isFinished ? 'success' : ($isClosed ? 'danger' : 'secondary'))]) ?>
                                </div>
                                <?php if (!$isClosed): ?>
                                    <div class="repair-step__actions">
                                        <?= Html::a(
                                            '<i class="fa-solid fa-flag-checkered me-1" aria-hidden="true"></i> ปิดงาน',
                                            $closeJobUrl,
                                            [
                                                'class' => 'btn btn-sm btn-primary open-modal',
                                                'data' => ['size' => 'modal-md'],
                                            ]
                                        ) ?>
                                        <?= Html::button(
                                            '<i class="fa-solid fa-ban me-1" aria-hidden="true"></i> ยกเลิกงานซ่อม',
                                            [
                                                'type' => 'button',
                                                'class' => 'btn btn-sm btn-outline-danger btn-cancel-repair',
                                                'data' => [
                                                    'url' => Url::to(['/helpdesk/service/cancel', 'id' => $model->id]),
                                                ],
                                            ]
                                        ) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ol>

                    <section class="border-top mt-4 pt-4" aria-labelledby="repair-responsibility-heading">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <h3 id="repair-responsibility-heading" class="h6 fw-bold mb-0">
                                <i class="fa-solid fa-users-gear me-1" aria-hidden="true"></i> ผู้รับผิดชอบและข้อมูลอ้างอิง
                            </h3>
                            <?= Html::a(
                                '<i class="fa-solid fa-user-plus me-1" aria-hidden="true"></i> เพิ่มช่าง',
                                ['/helpdesk/team/create', 'helpdesk_id' => $model->id],
                                [
                                    'class' => 'btn btn-sm btn-outline-primary btn-assign-team',
                                    'aria-controls' => 'assign-team-offcanvas',
                                ]
                            ) ?>
                        </div>

                        <?= $this->render('_participants', [
                            'model' => $model,
                        ]); ?>

                        <div class="border-top mt-4 pt-3">
                            <div class="d-flex justify-content-between gap-3 py-1">
                                <span class="text-muted">รหัสครุภัณฑ์</span>
                                <span class="fw-medium text-end text-break"><?= $assetCodeLabel ?></span>
                            </div>
                            <div class="d-flex justify-content-between gap-3 py-1">
                                <span class="text-muted">วันที่ต้องการให้ซ่อม</span>
                                <span class="fw-medium text-end text-break"><?= $requestRepairDateLabel ?></span>
                            </div>
                            <div class="d-flex justify-content-between gap-3 py-1">
                                <span class="text-muted">อัปเดต</span>
                                <span class="fw-medium text-end text-break"><?= Html::encode($createdAt ? \Yii::$app->formatter->asDatetime($createdAt) : '-') ?></span>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

                <div class="card shadow-sm mt-4">
                <div class="card-header d-flex flex-wrap align-items-center gap-2">
                    <h2 class="h6 fw-bold mb-0 flex-grow-1 overflow-hidden"><i class="fa-solid fa-clipboard-list me-1" aria-hidden="true"></i> สรุปผลการดำเนินงาน</h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column gap-4">
                        <div>
                            <div class="bg-body-tertiary rounded-3 p-3 h-100">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <div class="small text-muted mb-0">สาเหตุของปัญหา</div>
                                </div>
                                <div class="fw-medium mb-3">
                                    <?= nl2br(Html::encode((string) ($model->data_json['root_cause'] ?? '-'))) ?>
                                </div>

                                <div class="small text-muted mb-1">รายละเอียดการวินิจฉัย</div>
                                <div class="fw-medium">
                                    <?= nl2br(Html::encode((string) ($model->data_json['diagnosis'] ?? '-'))) ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="bg-body-tertiary rounded-3 p-3 h-100">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                    <div class="small text-muted mb-0">สรุปอะไหล่และค่าใช้จ่าย</div>
                                    <?= Html::tag('span', Html::encode($repairModeInfo['label']), ['class' => $badgeClass($repairModeInfo['color'])]) ?>
                                </div>
                                <div class="small text-muted mb-3"><?= Html::encode($repairModeInfo['hint']) ?></div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">รายการบันทึกซ่อม</span>
                                    <span class="fw-medium"><?= number_format($serviceRecordCount) ?> รายการ</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">รายการอะไหล่</span>
                                    <span class="fw-medium"><?= number_format($partCount) ?> รายการ</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">รายการค่าใช้จ่าย</span>
                                    <span class="fw-medium"><?= number_format($expenseCount) ?> รายการ</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">ค่าใช้จ่ายรวม</span>
                                    <span class="fw-bold text-body-emphasis"><?= number_format($totalCostDisplay, 2) ?> บาท</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">ค่าแรง (ฟอร์มสรุป)</span>
                                    <span class="fw-medium"><?= number_format($costLabor, 2) ?> บาท</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">ค่าอะไหล่ (ฟอร์มสรุป)</span>
                                    <span class="fw-medium"><?= number_format($costParts, 2) ?> บาท</span>
                                </div>
                                <?php if ($hasExternal): ?>
                                    <div class="mt-2 pt-2 border-top border-secondary border-opacity-25">
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                            <div class="small text-muted">บิล/หลักฐานส่งซ่อมภายนอก</div>
                                            <?= Html::tag('span', 'แนบแล้ว ' . number_format($externalBillCount) . ' ไฟล์', ['class' => $badgeClass($externalBillCount > 0 ? 'success' : 'secondary')]) ?>
                                        </div>
                                        <?= $model->getExternalRepairBillsHtml() ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                </div>
            </div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="repair-method-offcanvas" aria-labelledby="repair-method-offcanvas-label">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title h5" id="repair-method-offcanvas-label">บันทึกวิธีดำเนินการซ่อม</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="repair-method-offcanvas-content" class="text-muted small" role="status" aria-live="polite" aria-atomic="true" aria-busy="false">กำลังโหลดฟอร์ม...</div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="expense-pos-offcanvas" aria-labelledby="expense-pos-offcanvas-label">
    <div class="offcanvas-header border-bottom">
        <h2 class="offcanvas-title h5" id="expense-pos-offcanvas-label">บันทึกค่าใช้จ่าย (POS)</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="expense-pos-offcanvas-content" class="text-muted small" role="status" aria-live="polite" aria-atomic="true" aria-busy="false">กำลังโหลดเมนูบันทึกค่าใช้จ่าย...</div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="part-legacy-offcanvas" aria-labelledby="part-legacy-offcanvas-label">
    <div class="offcanvas-header border-bottom">
        <h2 class="offcanvas-title h5" id="part-legacy-offcanvas-label">เบิกอะไหล่จากคลัง</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="part-legacy-offcanvas-content" class="text-muted small" role="status" aria-live="polite" aria-atomic="true" aria-busy="false">กำลังโหลดเมนูเบิกอะไหล่...</div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="part-pos-offcanvas" aria-labelledby="part-pos-offcanvas-label">
    <div class="offcanvas-header border-bottom">
        <h2 class="offcanvas-title h5" id="part-pos-offcanvas-label">เบิกอะไหล่จากคลัง</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="part-pos-offcanvas-content" class="text-muted small" role="status" aria-live="polite" aria-atomic="true" aria-busy="false">กำลังโหลดเมนูเบิกอะไหล่...</div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="assign-team-offcanvas" aria-labelledby="assign-team-offcanvas-label">
    <div class="offcanvas-header border-bottom">
        <h2 class="offcanvas-title h5" id="assign-team-offcanvas-label">เพิ่มช่างผู้รับผิดชอบ</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="ปิด"></button>
    </div>
    <div class="offcanvas-body">
        <div id="assign-team-offcanvas-content" role="status" aria-live="polite" aria-atomic="false" aria-busy="false">
            <div class="text-body-secondary small">กำลังโหลดรายชื่อช่าง...</div>
        </div>
    </div>
</div>
</div>

<?php
// Bootstrap ไม่มี responsive width utility สำหรับ offcanvas จึง override เฉพาะแผงที่ต้องใช้พื้นที่เพิ่มผ่าน CSS variable
// Stepper แนวตั้ง: ผูกสีกับ Bootstrap CSS vars ทั้งหมด → theme-aware light/dark เอง, ไม่ต้อง build global
$css = <<<CSS
#part-pos-offcanvas { --bs-offcanvas-width: 100vw; }
#assign-team-offcanvas {
    --bs-offcanvas-width: min(100vw, 32rem);
    /* เว้นพื้นที่ให้ header-fixed ของระบบ เพื่อให้หัว Offcanvas และปุ่มปิดไม่ถูกบัง */
    top: 72px;
    height: calc(100% - 72px);
}
@media (min-width: 576px) {
    #part-pos-offcanvas { --bs-offcanvas-width: min(90vw, 56rem); }
}
.repair-stepper { --rp-marker: 2rem; }
.repair-step { display: flex; gap: .875rem; padding-bottom: 1.25rem; }
.repair-step:last-child { padding-bottom: 0; }
.repair-step__rail { position: relative; flex: 0 0 var(--rp-marker); display: flex; justify-content: center; }
.repair-step:not(:last-child) .repair-step__rail::after {
    content: ""; position: absolute; top: var(--rp-marker); bottom: -1.25rem; left: 50%;
    width: 2px; transform: translateX(-50%); background: var(--bs-border-color);
}
.repair-step.is-done .repair-step__rail::after { background: var(--bs-success); }
.repair-step__marker {
    position: relative; z-index: 1; width: var(--rp-marker); height: var(--rp-marker);
    border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: .8125rem; line-height: 1;
    border: 2px solid var(--bs-border-color); background: var(--bs-body-bg); color: var(--bs-secondary-color);
}
.repair-step.is-done .repair-step__marker { background: var(--bs-success); border-color: var(--bs-success); color: var(--bs-white); }
.repair-step.is-cancel .repair-step__marker { background: var(--bs-danger); border-color: var(--bs-danger); color: var(--bs-white); }
.repair-step.is-current .repair-step__marker {
    border-color: var(--bs-primary); color: var(--bs-primary); background: var(--bs-primary-bg-subtle);
    box-shadow: 0 0 0 4px var(--bs-primary-bg-subtle);
}
.repair-step__body { flex: 1 1 auto; min-width: 0; padding: .25rem 0; }
.repair-step.is-current .repair-step__body {
    background: var(--bs-primary-bg-subtle); border: 1px solid var(--bs-primary-border-subtle);
    border-radius: .5rem; padding: .625rem .875rem; margin-top: -.125rem;
}
.repair-step__head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem; }
.repair-step__title { font-weight: 600; }
.repair-step__actions { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .625rem; }
.repair-step__actions:empty { display: none; }
@media (pointer: coarse), (max-width: 767.98px) {
    #helpdesk-service-view-v2 .btn,
    #helpdesk-service-view-v2 .dropdown-item,
    #helpdesk-service-view-v2 .btn-close {
        min-height: 2.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    #helpdesk-service-view-v2 .btn-close {
        min-width: 2.75rem;
    }
}
CSS;
$this->registerCss($css);

$js = <<<JS
var repairViewRefreshRequest = null;

function repairCsrfData() {
  var data = {};
  if (typeof yii !== 'undefined' && typeof yii.getCsrfParam === 'function') {
    data[yii.getCsrfParam()] = yii.getCsrfToken();
  }
  return data;
}

function setRepairActionPending(actionElement, pending, pendingText) {
  var action = $(actionElement);

  if (pending) {
    if (action.data('request-pending')) {
      return false;
    }

    action.data('request-pending', true);
    action.data('original-html', action.html());
    action
      .prop('disabled', true)
      .addClass('disabled')
      .attr({
        'aria-disabled': 'true',
        'aria-busy': 'true',
        'tabindex': '-1'
      })
      .html(
        '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>' +
        '<span>' + pendingText + '</span>'
      );

    return true;
  }

  var originalHtml = action.data('original-html');
  if (typeof originalHtml === 'string') {
    action.html(originalHtml);
  }
  action
    .prop('disabled', false)
    .removeData('request-pending')
    .removeData('original-html')
    .removeClass('disabled')
    .removeAttr('aria-disabled aria-busy tabindex');

  return true;
}

function findRepairViewInResponse(response) {
  var html = response;

  if (typeof response === 'string') {
    try {
      var parsedResponse = JSON.parse(response);
      if (parsedResponse && typeof parsedResponse.content === 'string') {
        html = parsedResponse.content;
      }
    } catch (error) {
      html = response;
    }
  } else if (response && typeof response.content === 'string') {
    html = response.content;
  }

  if (typeof html !== 'string') {
    return $();
  }

  var parsed = $.parseHTML(html, document, true);
  return $('<div>').append(parsed).find('#helpdesk-service-view-v2').first();
}

function disposeRepairViewOverlays(currentView) {
  currentView.find('.offcanvas').each(function () {
    var instance = bootstrap.Offcanvas.getInstance(this);
    if (instance) {
      instance.dispose();
    }
  });

  $('.offcanvas-backdrop').remove();
  if (!$('.modal.show').length) {
    $('body').css({ overflow: '', paddingRight: '' });
  }
}

function refreshRepairView(options) {
  options = options || {};

  if (repairViewRefreshRequest) {
    return repairViewRefreshRequest;
  }

  var currentView = $('#helpdesk-service-view-v2').first();
  if (!currentView.length) {
    return $.Deferred().reject().promise();
  }

  var refreshUrl = options.url || currentView.data('refresh-url') || window.location.href;
  var scrollTop = window.scrollY;
  var activeElementId = document.activeElement && document.activeElement.id
    ? document.activeElement.id
    : '';

  currentView.attr('aria-busy', 'true');
  repairViewRefreshRequest = $.ajax({
    type: 'get',
    url: refreshUrl,
    dataType: 'text',
    cache: false
  });

  repairViewRefreshRequest
    .done(function (html) {
      var nextView = findRepairViewInResponse(html);
      if (!nextView.length) {
        Swal.fire({
          title: 'อัปเดตข้อมูลไม่สำเร็จ',
          text: 'รูปแบบข้อมูลที่ได้รับไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง',
          icon: 'error'
        });
        return;
      }

      disposeRepairViewOverlays(currentView);
      currentView.replaceWith(nextView);

      window.requestAnimationFrame(function () {
        window.scrollTo(0, scrollTop);

        var focusTarget = activeElementId
          ? document.getElementById(activeElementId)
          : null;

        if (!focusTarget) {
          focusTarget = nextView.find('h1').get(0);
          if (focusTarget) {
            focusTarget.setAttribute('tabindex', '-1');
          }
        }

        if (focusTarget) {
          focusTarget.focus({ preventScroll: true });
        }
      });
    })
    .fail(function () {
      Swal.fire({
        title: 'อัปเดตข้อมูลไม่สำเร็จ',
        text: 'ไม่สามารถโหลดข้อมูลงานซ่อมล่าสุดได้ กรุณาลองใหม่อีกครั้ง',
        icon: 'error'
      });
    })
    .always(function () {
      $('#helpdesk-service-view-v2').attr('aria-busy', 'false');
      repairViewRefreshRequest = null;
    });

  return repairViewRefreshRequest;
}

function submitRepairAction(actionElement, options) {
  if (!setRepairActionPending(actionElement, true, options.pendingText)) {
    return null;
  }

  Swal.fire({
    title: options.pendingTitle,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: function () {
      Swal.showLoading();
    }
  });

  return $.ajax({
    type: 'post',
    url: options.url,
    dataType: 'json',
    data: repairCsrfData()
  })
    .done(options.success)
    .fail(function () {
      Swal.fire({
        title: 'ไม่สำเร็จ',
        text: options.errorText,
        icon: 'error'
      });
    })
    .always(function () {
      setRepairActionPending(actionElement, false, '');
    });
}

window.refreshRepairView = refreshRepairView;

// Support legacy callbacks from team form (view.php)
window.loadFormTeam = refreshRepairView;
window.loadListTeam = refreshRepairView;
window.loadFormServiceRecord = refreshRepairView;
window.loadTimeline = refreshRepairView;

$('body').off('click.repairMethodClose').on('click.repairMethodClose', '#repair-method-offcanvas [data-bs-dismiss="offcanvas"]', function () {
  // รีเซ็ตข้อความโหลด และเรียก hide ซ้ำเพื่อกันกรณี bootstrap ไม่ซ่อนจริง
  try {
    var offcanvasEl = document.getElementById('repair-method-offcanvas');
    if (offcanvasEl && offcanvasEl.__repairOffcanvasInstance) {
      offcanvasEl.__repairOffcanvasInstance.hide();
    }
  } catch (e) {}

  $('#repair-method-offcanvas-content')
    .attr('aria-busy', 'false')
    .html('<div class="text-muted small">กำลังโหลดฟอร์ม...</div>');
});

$('body').off('click.repairMethodOpen').on('click.repairMethodOpen', 'a.btn-open-repair-method', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var offcanvasEl = document.getElementById('repair-method-offcanvas');
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
  offcanvasEl.__repairOffcanvasInstance = offcanvas;
  $('#repair-method-offcanvas-content')
    .attr('aria-busy', 'true')
    .html('<div class="text-muted small">กำลังโหลดฟอร์ม...</div>');
  offcanvas.show();

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      $('#repair-method-offcanvas-label').html(response.title || 'บันทึกวิธีดำเนินการซ่อม');
      $('#repair-method-offcanvas-content')
        .attr('aria-busy', 'false')
        .html(response.content || '<div class="text-danger small">ไม่พบฟอร์ม</div>');
    },
    error: function () {
      $('#repair-method-offcanvas-content')
        .attr('aria-busy', 'false')
        .html('<div class="text-danger small">ไม่สามารถโหลดฟอร์มได้</div>');
      Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเปิดฟอร์มบันทึกวิธีดำเนินการซ่อมได้', icon: 'error' });
    }
  });
});

$('body').off('click.expensePosOpen').on('click.expensePosOpen', 'a.btn-open-expense-pos', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var offcanvasEl = document.getElementById('expense-pos-offcanvas');
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
  $('#expense-pos-offcanvas-content')
    .attr('aria-busy', 'true')
    .html('<div class="text-muted small">กำลังโหลดเมนูบันทึกค่าใช้จ่าย...</div>');
  offcanvas.show();

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      $('#expense-pos-offcanvas-label').html(response.title || 'บันทึกค่าใช้จ่าย (POS)');
      $('#expense-pos-offcanvas-content')
        .attr('aria-busy', 'false')
        .html(response.content || '<div class="text-danger small">ไม่พบฟอร์มค่าใช้จ่าย</div>');
    },
    error: function () {
      $('#expense-pos-offcanvas-content')
        .attr('aria-busy', 'false')
        .html('<div class="text-danger small">ไม่สามารถโหลดเมนูบันทึกค่าใช้จ่ายได้</div>');
      Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเปิดเมนูบันทึกค่าใช้จ่ายได้', icon: 'error' });
    }
  });
});

$('body').off('click.partPosOpen').on('click.partPosOpen', 'a.btn-open-part-pos', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var offcanvasEl = document.getElementById('part-pos-offcanvas');
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
  $('#part-pos-offcanvas-content')
    .attr('aria-busy', 'true')
    .html('<div class="text-muted small">กำลังโหลดเมนูเบิกอะไหล่...</div>');
  offcanvas.show();

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      $('#part-pos-offcanvas-label').html(response.title || 'เบิกอะไหล่จากคลัง');
      $('#part-pos-offcanvas-content')
        .attr('aria-busy', 'false')
        .html(response.content || '<div class="text-danger small">ไม่พบฟอร์มเบิกอะไหล่</div>');
    },
    error: function () {
      $('#part-pos-offcanvas-content')
        .attr('aria-busy', 'false')
        .html('<div class="text-danger small">ไม่สามารถโหลดเมนูเบิกอะไหล่ได้</div>');
      Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเปิดเมนูเบิกอะไหล่ได้', icon: 'error' });
    }
  });
});

// Open "assign team" form in its dedicated offcanvas
$('body').off('click.assignTeamOpen').on('click.assignTeamOpen', 'a.btn-assign-team', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var offcanvasElement = document.getElementById('assign-team-offcanvas');
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);
  var content = $('#assign-team-offcanvas-content');

  content
    .attr('aria-busy', 'true')
    .html('<div class="text-body-secondary small">กำลังโหลดรายชื่อช่าง...</div>');
  offcanvas.show();

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      $('#assign-team-offcanvas-label').text(response.title || 'เพิ่มช่างผู้รับผิดชอบ');
      content
        .attr('aria-busy', 'false')
        .html(response.content || '<div class="alert alert-secondary mb-0">ไม่พบรายชื่อช่างที่เลือกได้</div>');

      window.requestAnimationFrame(function () {
        var firstOption = content.find('input[type="radio"]').first().get(0);
        var emptyState = content.find('[data-assign-team-empty]').first().get(0);
        if (firstOption) {
          firstOption.focus({ preventScroll: true });
        } else if (emptyState) {
          emptyState.focus({ preventScroll: true });
        }
      });
    },
    error: function () {
      content
        .attr('aria-busy', 'false')
        .html('<div class="alert alert-danger mb-0">ไม่สามารถโหลดรายชื่อช่างได้ กรุณาลองใหม่อีกครั้ง</div>');
    }
  });
});

$('body').off('click.receiveOrder').on('click.receiveOrder', '.receive-order', function (e) {
  e.preventDefault();
  if ($(this).data('request-pending')) return;
  var actionElement = this;
  var url = $(this).data('url');

  Swal.fire({
    title: 'ยืนยันการรับเรื่อง',
    text: 'ต้องการรับเรื่องซ่อมรายการนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ยืนยันรับเรื่อง',
    cancelButtonText: 'ยกเลิก',
    reverseButtons: false
  }).then(function (result) {
    if (!result.isConfirmed) {
      return;
    }
    submitRepairAction(actionElement, {
      url: url,
      pendingText: 'กำลังรับเรื่อง...',
      pendingTitle: 'กำลังรับเรื่อง',
      errorText: 'ไม่สามารถรับเรื่องได้ กรุณาลองใหม่อีกครั้ง',
      success: function (response) {
        Swal.fire({
          title: 'รับเรื่องแล้ว',
          icon: 'success',
          timer: 900,
          showConfirmButton: false
        }).then(function () {
          refreshRepairView({
            url: (response && response.url) ? response.url : null
          });
        });
      }
    });
  });
});

$('body').off('click.sendRepair').on('click.sendRepair', '.btn-send-repair', function (e) {
  e.preventDefault();
  if ($(this).data('request-pending')) return;
  var actionElement = this;
  var url = $(this).data('url');

  Swal.fire({
    title: 'ยืนยันการส่งซ่อม',
    text: 'ต้องการส่งซ่อม/เริ่มดำเนินการรายการนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ยืนยันส่งซ่อม',
    cancelButtonText: 'ยกเลิก',
    reverseButtons: false
  }).then(function (result) {
    if (!result.isConfirmed) {
      return;
    }
    submitRepairAction(actionElement, {
      url: url,
      pendingText: 'กำลังส่งซ่อม...',
      pendingTitle: 'กำลังส่งซ่อม',
      errorText: 'ไม่สามารถส่งซ่อมได้ กรุณาลองใหม่อีกครั้ง',
      success: function (response) {
        if (!response || response.status !== 'success') {
          Swal.fire({
            title: 'ไม่สำเร็จ',
            text: 'ระบบไม่สามารถเปลี่ยนสถานะเป็นส่งซ่อมได้',
            icon: 'error'
          });
          return;
        }

        Swal.fire({
          title: 'ส่งซ่อมแล้ว',
          icon: 'success',
          timer: 900,
          showConfirmButton: false
        }).then(function () {
          refreshRepairView();
        });
      }
    });
  });
});

$('body').off('click.cancelRepair').on('click.cancelRepair', '.btn-cancel-repair', function (e) {
  e.preventDefault();
  if ($(this).data('request-pending')) return;
  var actionElement = this;
  var url = $(this).data('url');
  Swal.fire({
    title: 'ยืนยันการยกเลิกงานซ่อม',
    text: 'ต้องการยกเลิกงานซ่อมรายการนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ยืนยันยกเลิก',
    cancelButtonText: 'กลับ',
    reverseButtons: false
  }).then(function (result) {
    if (!result.isConfirmed) return;
    submitRepairAction(actionElement, {
      url: url,
      pendingText: 'กำลังยกเลิก...',
      pendingTitle: 'กำลังยกเลิกงานซ่อม',
      errorText: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้',
      success: function (response) {
        if (response && response.status === 'success') {
          Swal.fire({
            title: 'ยกเลิกงานแล้ว',
            icon: 'success',
            timer: 900,
            showConfirmButton: false
          }).then(function () {
            refreshRepairView();
          });
        } else {
          Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถยกเลิกงานได้', icon: 'error' });
        }
      }
    });
  });
});

function setExpenseOffcanvasWidth() {
  var el = document.getElementById('expense-pos-offcanvas');
  if (!el) return;
  el.classList.remove('w-50', 'w-75', 'w-100');
  var w = window.innerWidth || document.documentElement.clientWidth || 0;
  if (w >= 1200) {
    el.classList.add('w-50');
  } else if (w >= 768) {
    el.classList.add('w-75');
  } else {
    el.classList.add('w-100');
  }
}

setExpenseOffcanvasWidth();
window.addEventListener('resize', setExpenseOffcanvasWidth);

function setPartOffcanvasWidth() {
  var el = document.getElementById('part-pos-offcanvas');
  if (!el) return;
  el.classList.remove('w-50', 'w-75', 'w-100');
  var w = window.innerWidth || document.documentElement.clientWidth || 0;
  if (w >= 1200) {
    el.classList.add('w-50');
  } else if (w >= 768) {
    el.classList.add('w-75');
  } else {
    el.classList.add('w-100');
  }
}

setPartOffcanvasWidth();
window.addEventListener('resize', setPartOffcanvasWidth);

function setPartLegacyOffcanvasWidth() {
  var el = document.getElementById('part-legacy-offcanvas');
  if (!el) return;
  el.classList.remove('w-50', 'w-75', 'w-100');
  var w = window.innerWidth || document.documentElement.clientWidth || 0;
  if (w >= 1200) {
    el.classList.add('w-50');
  } else if (w >= 768) {
    el.classList.add('w-75');
  } else {
    el.classList.add('w-100');
  }
}
setPartLegacyOffcanvasWidth();
window.addEventListener('resize', setPartLegacyOffcanvasWidth);

$('body').off('click.partLegacyOpen').on('click.partLegacyOpen', 'a.btn-open-part-legacy', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var offcanvasEl = document.getElementById('part-legacy-offcanvas');
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
  $('#part-legacy-offcanvas-content')
    .attr('aria-busy', 'true')
    .html('<div class="text-muted small">กำลังโหลดเมนูเบิกอะไหล่...</div>');
  offcanvas.show();

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      $('#part-legacy-offcanvas-label').html(response.title || 'เบิกอะไหล่จากคลัง');
      $('#part-legacy-offcanvas-content')
        .attr('aria-busy', 'false')
        .html(response.content || '<div class="text-danger small">ไม่พบฟอร์มเบิกอะไหล่</div>');
    },
    error: function () {
      $('#part-legacy-offcanvas-content')
        .attr('aria-busy', 'false')
        .html('<div class="text-danger small">ไม่สามารถโหลดเมนูเบิกอะไหล่ได้</div>');
      Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเปิดเมนูเบิกอะไหล่ได้', icon: 'error' });
    }
  });
});
JS;
$this->registerJs($js);
?>
