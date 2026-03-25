<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\helpdesk2\helpers\HelpdeskSlaHelper;
use app\modules\helpdesk2\models\HelpdeskDetail;

/** @var app\modules\helpdesk2\models\Helpdesk $model */

$ticketId = $model->id ?? '-';
$titleText = 'หมายเลขงานซ่อม #' . $ticketId;

$dataJson = is_array($model->data_json ?? null) ? $model->data_json : [];
$statusCode = (string) ($model->status ?? 'pending');
$urgencyCode = $dataJson['urgency'] ?? null;
$createdAt = $model->created_at ?? null;
$externalBillCount = (int) ($model->getExternalRepairBillsCount() ?? 0);

$badgeClass = static function (string $color): string {
    return 'badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1';
};

$statusMeta = [
    'pending' => ['label' => 'เปิดงาน', 'color' => 'warning', 'icon' => 'fa-solid fa-circle-info'],
    'receive' => ['label' => 'รับเรื่อง', 'color' => 'info', 'icon' => 'fa-solid fa-inbox'],
    'in_progress' => ['label' => 'กำลังดำเนินการ', 'color' => 'info', 'icon' => 'fa-solid fa-gears'],
    'success' => ['label' => 'เสร็จสิ้น', 'color' => 'success', 'icon' => 'fa-regular fa-circle-check'],
    'cancel' => ['label' => 'ยกเลิก', 'color' => 'danger', 'icon' => 'fa-solid fa-ban'],
];

$statusInfo = $statusMeta[$statusCode] ?? ['label' => 'ไม่ทราบสถานะ', 'color' => 'secondary', 'icon' => 'fa-solid fa-circle'];
$statusBadge = Html::tag('span', '<i class="' . $statusInfo['icon'] . ' me-1"></i>' . Html::encode($statusInfo['label']), [
    'class' => $badgeClass($statusInfo['color']),
]);

$priorityMap = [
    '1' => ['label' => 'ต่ำ', 'color' => 'primary'],
    '2' => ['label' => 'ปานกลาง', 'color' => 'info'],
    '3' => ['label' => 'สูง', 'color' => 'warning'],
    '4' => ['label' => 'วิกฤต', 'color' => 'danger'],
    'low' => ['label' => 'ต่ำ', 'color' => 'primary'],
    'medium' => ['label' => 'ปานกลาง', 'color' => 'info'],
    'high' => ['label' => 'สูง', 'color' => 'warning'],
    'critical' => ['label' => 'วิกฤต', 'color' => 'danger'],
];

$priorityInfo = is_scalar($urgencyCode) ? ($priorityMap[(string) $urgencyCode] ?? ['label' => 'ไม่ระบุ', 'color' => 'secondary']) : ['label' => 'ไม่ระบุ', 'color' => 'secondary'];
$priorityBadge = Html::tag('span', Html::encode('ความสำคัญ: ' . $priorityInfo['label']), [
    'class' => $badgeClass($priorityInfo['color']),
]);

$slaBadgeHtml = '';
try {
    $slaBadgeHtml = HelpdeskSlaHelper::renderBadge($model);
} catch (\Throwable $e) {
    $slaBadgeHtml = '';
}
if ($slaBadgeHtml === '') {
    $slaBadgeHtml = Html::tag('span', '<i class="fa-regular fa-clock me-1"></i>ไม่มี SLA', [
        'class' => $badgeClass('secondary'),
    ]);
}

$descriptionSummary = Html::encode($model->title ?: '—');
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

// Mock data (used when controller doesn't provide $logs).
$mockTimeline = [
    (object) ['created_at' => date('Y-m-d H:i:s', strtotime('-22 minutes')), 'message' => 'อัปเดตสถานะงานซ่อม'],
    (object) ['created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')), 'message' => 'รับเรื่องเรียบร้อยแล้ว รอช่างดำเนินการตรวจสอบ'],
    (object) ['created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')), 'message' => 'สร้างงานซ่อมใหม่'],
];

$timelineItems = isset($logs) ? $logs : $mockTimeline;
$commentsItems = [];
if (isset($comments) && is_array($comments)) {
    $commentsItems = $comments;
} else {
    $feedbackComment = trim((string) ($dataJson['comment'] ?? ''));
    $feedbackRating = (int) ($model->rating ?? 0);
    $feedbackDate = (string) ($dataJson['comment_date'] ?? '');
    if ($feedbackRating > 0 || $feedbackComment !== '') {
        $requesterName = '-';
        try {
            $reqInfo = $model->getUserReq();
            $requesterName = (string) ($reqInfo['fullname'] ?? '-');
        } catch (\Throwable $e) {
            $requesterName = '-';
        }
        $ratingStars = str_repeat('★', max(0, min(5, $feedbackRating)));
        $ratingText = $feedbackRating > 0 ? ('คะแนนความพึงพอใจ: ' . $feedbackRating . '/5 ' . $ratingStars) : '';
        $message = trim($ratingText . ($feedbackComment !== '' ? ("\n" . $feedbackComment) : ''));
        $commentsItems[] = (object) [
            'is_staff' => false,
            'user' => (object) ['name' => $requesterName],
            'created_at' => $feedbackDate !== '' ? $feedbackDate : null,
            'message' => $message !== '' ? $message : '-',
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
    $titleTextRaw = strtolower((string) ($d->title ?? ''));
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

$receiveUrl = Url::to(['/helpdesk/service/receive', 'id' => $model->id]);
$sendRepairUrl = Url::to(['/helpdesk/service/send-repair', 'id' => $model->id]);
$recordMethodUrl = Url::to(['/helpdesk/service-record/create', 'helpdesk_id' => $model->id, 'title' => 'บันทึกวิธีดำเนินการซ่อม #' . $model->repair_number]);
$partsUrl = Url::to(['/helpdesk/repair-parts/create', 'helpdesk_id' => $model->id, 'title' => 'เบิกอะไหล่งานซ่อม #' . $model->repair_number]);
$expenseUrl = Url::to(['/helpdesk/expenses/create', 'helpdesk_id' => $model->id, 'title' => 'ลงค่าใช้จ่ายงานซ่อม #' . $model->repair_number]);
$billUploadUrl = Url::to(['/helpdesk/service/external-bill-form', 'id' => $model->id, 'title' => 'อัปโหลดบิลค่าใช้จ่าย #' . $model->repair_number]);
$closeJobUrl = Url::to(['/helpdesk/service/update-status', 'id' => $model->id, 'title' => 'ปิดงานซ่อม #' . $model->repair_number]);
$printSendRepairUrl = Url::to(['/helpdesk/service/print-send-repair-pdf', 'id' => $model->id]);
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
$isWorkflowFinished = $isClosed;

$stepCardClass = static function (int $stepNumber) use ($activeStep): string {
    if ($activeStep === $stepNumber) {
        return 'border border-primary-subtle bg-primary bg-opacity-10 rounded-3 p-3';
    }
    return 'border border-secondary border-opacity-25 rounded-3 p-3';
};

?>

<div class="container-fluid py-3">
    <div class="row g-3">
        <!-- RIGHT -->
        <div class="col-lg-4">
            <?= $this->render('_sidebar', [
                'model' => $model,
                'statusInfo' => $statusInfo,
                'priorityInfo' => $priorityInfo,
                'statusBadge' => $statusBadge,
                'priorityBadge' => $priorityBadge,
                'slaBadgeHtml' => $slaBadgeHtml,
            ]); ?>
        </div>
        <!-- LEFT -->
        <div class="col-lg-8">
            <?= $this->render('_header', [
                'model' => $model,
                'titleText' => $titleText,
                'statusBadge' => $statusBadge,
                'priorityBadge' => $priorityBadge,
                'slaBadgeHtml' => $slaBadgeHtml,
            ]); ?>

            <div class="card shadow-sm mt-3">
                <div class="card-header fw-bold d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span><i class="bi bi-signpost-split me-1"></i> ขั้นตอนทำงานสำหรับช่าง (ทำตามลำดับ)</span>
                    <?= Html::tag('span', 'คืบหน้า ' . $requiredProgress . '%', ['class' => $badgeClass($requiredProgress >= 100 ? 'success' : 'info')]) ?>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-primary mb-3" role="alert">
                        เริ่มจากรับเรื่อง → ส่งซ่อม/เริ่มงาน → บันทึกวิธีดำเนินการ → ปิดงานให้เสร็จสมบูรณ์
                    </div>

                    <div class="progress mb-3" role="progressbar" aria-label="workflow-progress" aria-valuenow="<?= $requiredProgress ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar <?= $isClosed ? 'bg-success' : '' ?>" style="width: <?= $requiredProgress ?>%"></div>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <div class="<?= $stepCardClass(1) ?>">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="fw-medium">1) รับเรื่อง</div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($activeStep === 1): ?>
                                        <?= Html::tag('span', 'กำลังทำ', ['class' => $badgeClass('primary')]) ?>
                                    <?php endif; ?>
                                    <?= Html::tag('span', $isReceived ? 'เสร็จแล้ว' : 'รอดำเนินการ', ['class' => $badgeClass($isReceived ? 'success' : 'secondary')]) ?>
                                    <?php if (!$isReceived): ?>
                                        <?= Html::a('<i class="fa-solid fa-circle-exclamation me-1"></i> รับเรื่อง', $receiveUrl, ['class' => 'btn btn-sm btn-outline-primary receive-order']) ?>
                                    <?php endif; ?>
                                    <?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i> แก้ไขใบแจ้งซ่อม', $editTicketLiteUrl, ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                </div>
                            </div>
                        </div>

                        <div class="<?= $stepCardClass(2) ?>">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="fw-medium">2) ส่งซ่อม / เริ่มดำเนินการ</div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($activeStep === 2): ?>
                                        <?= Html::tag('span', 'กำลังทำ', ['class' => $badgeClass('primary')]) ?>
                                    <?php endif; ?>
                                    <?= Html::tag('span', $isStarted ? 'เสร็จแล้ว' : 'รอดำเนินการ', ['class' => $badgeClass($isStarted ? 'success' : 'secondary')]) ?>
                                    <?php if (!$isStarted): ?>
                                        <?= Html::a('<i class="fa-solid fa-truck-fast me-1"></i> ส่งซ่อม/เริ่มงาน', $sendRepairUrl, ['class' => 'btn btn-sm btn-outline-info btn-send-repair']) ?>
                                    <?php endif; ?>
                                    <?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i> บันทึกวิธีดำเนินการ', $recordMethodUrl, ['class' => 'btn btn-sm btn-outline-dark btn-open-repair-method']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="<?= $stepCardClass(3) ?>">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="fw-medium">3) บันทึกสาเหตุของปัญหา</div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($activeStep === 3): ?>
                                        <?= Html::tag('span', 'กำลังทำ', ['class' => $badgeClass('primary')]) ?>
                                    <?php endif; ?>
                                    <?= Html::tag('span', $hasRootCauseData ? 'เสร็จแล้ว' : 'ยังไม่บันทึก', ['class' => $badgeClass($hasRootCauseData ? 'success' : 'secondary')]) ?>
                                    <?= Html::a(
                                        '<i class="fa-solid fa-pen-to-square me-1"></i> ลงข้อมูล',
                                        ['/helpdesk/service/root-cause-form', 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-sm btn-outline-primary open-modal',
                                            'data' => ['size' => 'modal-lg'],
                                        ]
                                    ) ?>
                                </div>
                            </div>
                        </div>

                        <div class="<?= $stepCardClass(4) ?>">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="fw-medium">4) อะไหล่ / ค่าใช้จ่าย (ถ้ามี)</div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($activeStep === 4): ?>
                                        <?= Html::tag('span', 'กำลังทำ', ['class' => $badgeClass('primary')]) ?>
                                    <?php endif; ?>
                                    <?= Html::tag('span', 'อะไหล่ ' . number_format($partCount) . ' รายการ', ['class' => $badgeClass($partCount > 0 ? 'success' : 'secondary')]) ?>
                                    <?= Html::tag('span', 'ค่าใช้จ่าย ' . number_format($expenseCount) . ' รายการ', ['class' => $badgeClass($expenseCount > 0 ? 'warning' : 'secondary')]) ?>
                                    <?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบส่งซ่อม', $printSendRepairUrl, ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']) ?>
                                    <?= Html::a('<i class="fa-regular fa-file-lines me-1"></i> เบิกอะไหล่', $partsUrl, ['class' => 'btn btn-sm btn-outline-secondary btn-open-part-pos']) ?>
                                    <?= Html::a('<i class="fa-solid fa-money-bill-wave me-1"></i> ลงค่าใช้จ่าย', $expenseUrl, ['class' => 'btn btn-sm btn-outline-warning btn-open-expense-pos']) ?>
                                    <?= Html::a('<i class="fa-solid fa-file-arrow-up me-1"></i> อัปโหลดบิลค่าใช้จ่าย (' . number_format($externalBillCount) . ')', $billUploadUrl, ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                </div>
                            </div>
                        </div>

                        <div class="<?= $stepCardClass(5) ?>">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="fw-medium">5) ปิดงาน</div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($activeStep === 5): ?>
                                        <?= Html::tag('span', 'กำลังทำ', ['class' => $badgeClass('primary')]) ?>
                                    <?php elseif ($isWorkflowFinished): ?>
                                        <?= Html::tag('span', 'สิ้นสุดกระบวนการ', ['class' => $badgeClass($isFinished ? 'success' : 'danger')]) ?>
                                    <?php endif; ?>
                                    <?= Html::tag('span', $isFinished ? 'ปิดงานแล้ว' : ($isClosed ? 'จบงาน (ยกเลิก)' : 'ยังไม่ปิดงาน'), ['class' => $badgeClass($isFinished ? 'success' : ($isClosed ? 'danger' : 'secondary'))]) ?>
                                    <?php if (!$isClosed): ?>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-flag-checkered me-1"></i> ปิดงาน',
                                            $closeJobUrl,
                                            [
                                                'class' => 'btn btn-sm btn-primary open-modal',
                                                'data' => ['size' => 'modal-md'],
                                            ]
                                        ) ?>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-ban me-1"></i> ยกเลิกงานซ่อม',
                                            ['/helpdesk/service/cancel', 'id' => $model->id],
                                            [
                                                'class' => 'btn btn-sm btn-outline-danger btn-cancel-repair',
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <div class="card shadow-sm mt-3">
                <div class="card-header fw-bold d-flex flex-wrap align-items-center gap-2">
                    <span class="flex-grow-1 min-w-0"><i class="bi bi-journal-check me-1"></i> มาตรฐานการบันทึกงานซ่อม</span>
                    <div class="d-flex flex-wrap gap-2 ms-auto">
                       
                        
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?= Html::tag('span', ($isReceived ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'รับเรื่อง', ['class' => $badgeClass($isReceived ? 'success' : 'secondary')]) ?>
                        <?= Html::tag('span', ($isStarted ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'เริ่มดำเนินการ', ['class' => $badgeClass($isStarted ? 'success' : 'secondary')]) ?>
                        <?= Html::tag('span', ($hasRootCauseData ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'บันทึกสาเหตุปัญหา', ['class' => $badgeClass($hasRootCauseData ? 'success' : 'secondary')]) ?>
                        <?= Html::tag('span', ($partCount > 0 ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'บันทึกอะไหล่', ['class' => $badgeClass($partCount > 0 ? 'success' : 'secondary')]) ?>
                        <?= Html::tag('span', ($expenseCount > 0 ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'บันทึกค่าใช้จ่าย', ['class' => $badgeClass($expenseCount > 0 ? 'success' : 'secondary')]) ?>
                        <?= Html::tag('span', ($isClosed ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'ปิดงาน', ['class' => $badgeClass($isClosed ? 'success' : 'secondary')]) ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border border-secondary border-opacity-25 rounded-3 p-3 h-100">
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
                        <div class="col-md-6">
                            <div class="border border-secondary border-opacity-25 rounded-3 p-3 h-100">
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
                                    <span class="fw-bold text-danger"><?= number_format($totalCostDisplay, 2) ?> บาท</span>
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
                                <div class="mt-3 pt-3 border-top border-secondary border-opacity-25">
                                    <div class="small text-muted">ลงบันทึกตามรูปแบบการซ่อมได้จากส่วน “ขั้นตอนทำงานสำหรับช่าง (ทำตามลำดับ)” ด้านบน</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="card shadow-sm mt-3">
                <div class="card-header fw-bold">รายละเอียดงานซ่อม</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="d-flex align-items-start gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-3 p-2">
                                    <i class="bi bi-clipboard2-check"></i>
                                </div>
                                <div>
                                    <div class="fw-bold mb-1"><?= $descriptionSummary ?></div>
                                    <?php if ($descriptionExtra !== ''): ?>
                                        <div class="text-muted"><?= nl2br($descriptionExtra) ?></div>
                                    <?php else: ?>
                                        <div class="text-muted">ไม่มีรายละเอียดเพิ่มเติม</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">รหัสครุภัณฑ์</span>
                                <span class="fw-medium"><?= $assetCodeLabel ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">สถานที่</span>
                                <span class="fw-medium"><?= $locationLabel ?></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">วันที่ต้องการให้ซ่อม</span>
                                <span class="fw-medium"><?= $requestRepairDateLabel ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">อัปเดต</span>
                                <span class="fw-medium"><?= Html::encode($createdAt ? \Yii::$app->formatter->asDatetime($createdAt) : '-') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <?= $this->render('_timeline', ['items' => $timelineItems]); ?>

            <!-- Comments -->
            <?= $this->render('_comments', ['comments' => $commentsItems]); ?>

            <!-- Attachments -->
            <div class="card shadow-sm mt-3">
                <div class="card-header fw-bold d-flex align-items-center justify-content-between gap-2">
                    <span>ไฟล์แนบ</span>
                    <span class="text-muted small">รูปภาพที่ผู้แจ้งแนบมา</span>
                </div>
                <div class="card-body p-4">
                    <?= $model->imageRequest ?>
                </div>
            </div>
        </div>


    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="repair-method-offcanvas" aria-labelledby="repair-method-offcanvas-label">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="repair-method-offcanvas-label">บันทึกวิธีดำเนินการซ่อม</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="repair-method-offcanvas-content" class="text-muted small">กำลังโหลดฟอร์ม...</div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="expense-pos-offcanvas" aria-labelledby="expense-pos-offcanvas-label">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="expense-pos-offcanvas-label">บันทึกค่าใช้จ่าย (POS)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="expense-pos-offcanvas-content" class="text-muted small">กำลังโหลดเมนูบันทึกค่าใช้จ่าย...</div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="part-pos-offcanvas" aria-labelledby="part-pos-offcanvas-label">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="part-pos-offcanvas-label">เบิกอะไหล่จากคลัง</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="part-pos-offcanvas-content" class="text-muted small">กำลังโหลดเมนูเบิกอะไหล่...</div>
    </div>
</div>

<?php
$js = <<<JS
// Support legacy callbacks from team form (view.php)
window.loadFormTeam = function () { window.location.reload(); };
window.loadListTeam = function () { window.location.reload(); };
window.loadFormServiceRecord = function () { window.location.reload(); };
window.loadTimeline = function () { window.location.reload(); };

$('body').off('click.repairMethodClose').on('click.repairMethodClose', '#repair-method-offcanvas [data-bs-dismiss="offcanvas"]', function () {
  // รีเซ็ตข้อความโหลด และเรียก hide ซ้ำเพื่อกันกรณี bootstrap ไม่ซ่อนจริง
  try {
    var offcanvasEl = document.getElementById('repair-method-offcanvas');
    if (offcanvasEl && offcanvasEl.__repairOffcanvasInstance) {
      offcanvasEl.__repairOffcanvasInstance.hide();
    }
  } catch (e) {}

  $('#repair-method-offcanvas-content').html('<div class="text-muted small">กำลังโหลดฟอร์ม...</div>');
});

$('body').on('click', 'a.btn-open-repair-method', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var offcanvasEl = document.getElementById('repair-method-offcanvas');
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
  offcanvasEl.__repairOffcanvasInstance = offcanvas;
  $('#repair-method-offcanvas-content').html('<div class="text-muted small">กำลังโหลดฟอร์ม...</div>');
  offcanvas.show();

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      $('#repair-method-offcanvas-label').html(response.title || 'บันทึกวิธีดำเนินการซ่อม');
      $('#repair-method-offcanvas-content').html(response.content || '<div class="text-danger small">ไม่พบฟอร์ม</div>');
    },
    error: function () {
      $('#repair-method-offcanvas-content').html('<div class="text-danger small">ไม่สามารถโหลดฟอร์มได้</div>');
      Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเปิดฟอร์มบันทึกวิธีดำเนินการซ่อมได้', icon: 'error' });
    }
  });
});

$('body').off('click.expensePosOpen').on('click.expensePosOpen', 'a.btn-open-expense-pos', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var offcanvasEl = document.getElementById('expense-pos-offcanvas');
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
  $('#expense-pos-offcanvas-content').html('<div class="text-muted small">กำลังโหลดเมนูบันทึกค่าใช้จ่าย...</div>');
  offcanvas.show();

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      $('#expense-pos-offcanvas-label').html(response.title || 'บันทึกค่าใช้จ่าย (POS)');
      $('#expense-pos-offcanvas-content').html(response.content || '<div class="text-danger small">ไม่พบฟอร์มค่าใช้จ่าย</div>');
    },
    error: function () {
      $('#expense-pos-offcanvas-content').html('<div class="text-danger small">ไม่สามารถโหลดเมนูบันทึกค่าใช้จ่ายได้</div>');
      Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเปิดเมนูบันทึกค่าใช้จ่ายได้', icon: 'error' });
    }
  });
});

$('body').off('click.partPosOpen').on('click.partPosOpen', 'a.btn-open-part-pos', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var offcanvasEl = document.getElementById('part-pos-offcanvas');
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
  $('#part-pos-offcanvas-content').html('<div class="text-muted small">กำลังโหลดเมนูเบิกอะไหล่...</div>');
  offcanvas.show();

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      $('#part-pos-offcanvas-label').html(response.title || 'เบิกอะไหล่จากคลัง');
      $('#part-pos-offcanvas-content').html(response.content || '<div class="text-danger small">ไม่พบฟอร์มเบิกอะไหล่</div>');
    },
    error: function () {
      $('#part-pos-offcanvas-content').html('<div class="text-danger small">ไม่สามารถโหลดเมนูเบิกอะไหล่ได้</div>');
      Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเปิดเมนูเบิกอะไหล่ได้', icon: 'error' });
    }
  });
});

// Open "assign team" form in main modal
$('body').on('click', 'a.btn-assign-team', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var title = $(this).data('title') || 'มอบหมายช่าง';
  var size = $(this).data('size') || 'modal-md';

  if (typeof beforLoadModal === 'function') { beforLoadModal(); }

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      var modal = $('#main-modal');
      modal.find('#main-modal-label').html(response.title || title);
      modal.find('.modal-body').html(response.content || '');
      modal.find('.modal-footer').html(response.footer || '');
      modal.find('.modal-dialog')
        .removeClass('modal-sm modal-md modal-lg modal-xl modal-xxl')
        .addClass(size);
      modal.modal('show');
    },
    error: function () {
      Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเปิดฟอร์มมอบหมายช่างได้', icon: 'error' });
    }
  });
});

$('body').on('click', 'a.receive-order', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');

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
    $.ajax({
      type: 'get',
      url: url,
      dataType: 'json',
      success: function (response) {
        Swal.fire({
          title: 'รับเรื่องแล้ว',
          icon: 'success',
          timer: 900,
          showConfirmButton: false
        }).then(function () {
          window.location.href = (response && response.url) ? response.url : window.location.href;
        });
      },
      error: function () {
        Swal.fire({
          title: 'ไม่สำเร็จ',
          text: 'ไม่สามารถรับเรื่องได้ กรุณาลองใหม่อีกครั้ง',
          icon: 'error'
        });
      }
    });
  });
});

$('body').on('click', 'a.btn-send-repair', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');

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
    $.ajax({
      type: 'get',
      url: url,
      dataType: 'json',
      success: function () {
        Swal.fire({
          title: 'ส่งซ่อมแล้ว',
          icon: 'success',
          timer: 900,
          showConfirmButton: false
        }).then(function () {
          window.location.reload();
        });
      },
      error: function () {
        Swal.fire({
          title: 'ไม่สำเร็จ',
          text: 'ไม่สามารถส่งซ่อมได้ กรุณาลองใหม่อีกครั้ง',
          icon: 'error'
        });
      }
    });
  });
});

$('body').on('click', 'a.btn-cancel-repair', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
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
    $.ajax({
      type: 'get',
      url: url,
      dataType: 'json',
      success: function (response) {
        if (response && response.status === 'success') {
          Swal.fire({
            title: 'ยกเลิกงานแล้ว',
            icon: 'success',
            timer: 900,
            showConfirmButton: false
          }).then(function () {
            window.location.reload();
          });
        } else {
          Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถยกเลิกงานได้', icon: 'error' });
        }
      },
      error: function () {
        Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', icon: 'error' });
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
JS;
$this->registerJs($js);
?>

