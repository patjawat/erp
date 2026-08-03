<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskDetail;

$badgeClass = static function (string $color): string {
    return 'badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1';
};

$dataJson = is_array($model->data_json ?? null) ? $model->data_json : [];
$statusCode = Helpdesk::normalizeRepairStatus($model->status ?? 'pending');
$statusMap = Helpdesk::repairStatusMeta();
$statusInfo = $statusMap[$statusCode] ?? [
    'label' => 'ไม่ทราบสถานะ',
    'color' => 'secondary',
    'icon' => 'fa-regular fa-circle-question',
];
$statusLabels = Helpdesk::repairStatusOptions();
$statusIcon = Html::tag('i', '', [
    'class' => $statusInfo['icon'] . ' me-1',
    'aria-hidden' => 'true',
]);

$detailRows = HelpdeskDetail::find()->where(['helpdesk_id' => $model->id])->orderBy(['id' => SORT_ASC])->all();
$repairTeamRows = array_values(array_filter($detailRows, static function ($d) {
    return strtolower((string) ($d->name ?? '')) === 'repair_team';
}));
$leadTechnician = '-';
$teamNames = [];
foreach ($repairTeamRows as $teamRow) {
    $fullName = trim((string) ($teamRow->emp->fullname ?? ''));
    if ($fullName !== '') {
        if ($leadTechnician === '-') {
            $leadTechnician = $fullName;
        }
        $teamNames[] = $fullName;
    }
}
$teamNames = array_values(array_unique($teamNames));
$teamText = !empty($teamNames) ? implode(', ', $teamNames) : '-';
$serviceRecordCount = 0;
$expenseCount = 0;
$partCount = 0;
$totalExpense = 0.0;
foreach ($detailRows as $d) {
    $nameCode = strtolower((string) ($d->name ?? ''));
    $dj = is_array($d->data_json ?? null) ? $d->data_json : [];
    if ($nameCode === 'service_record') {
        $serviceRecordCount++;
    }
    if (($nameCode === 'part_record') || ($nameCode === 'repair_part') || str_contains($nameCode, 'part')) {
        $partCount++;
    }
    if (($nameCode === 'expense_record') || ($nameCode !== 'service_record' && str_contains($nameCode, 'expense'))) {
        $expenseCount++;
        foreach (['total', 'amount', 'price', 'cost'] as $k) {
            if (isset($dj[$k]) && is_numeric($dj[$k])) {
                $totalExpense += (float) $dj[$k];
                break;
            }
        }
    }
}

$costLabor = is_numeric($dataJson['cost_labor'] ?? null) ? (float) $dataJson['cost_labor'] : 0.0;
$costParts = is_numeric($dataJson['cost_parts'] ?? null) ? (float) $dataJson['cost_parts'] : 0.0;
$costTotalForm = is_numeric($dataJson['cost_total'] ?? null) ? (float) $dataJson['cost_total'] : 0.0;
$totalCostDisplay = max($totalExpense, $costTotalForm, ($costLabor + $costParts));
$location = ($dataJson['location'] ?? '-') ?: '-';
$rootCause = (string) ($dataJson['root_cause'] ?? '-');
$diagnosis = (string) ($dataJson['diagnosis'] ?? '-');
$repairResult = (string) ($model->repair_result ?? '-');
$ratingValue = (int) ($model->rating ?? 0);
$commentText = (string) ($dataJson['comment'] ?? '');
$canRate = $statusCode === 'success';

$urlTimeline = Url::to(['/helpdesk2/service-record/timeline', 'helpdesk_id' => $model->id]);
$feedbackUrl = Url::to(['/me/repair-v2/feedback', 'id' => $model->id]);
?>

<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-12">
            <?=$model->viewServiceRecordInfo()?>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-clipboard-list me-1"></i>รายละเอียดงานซ่อม</h6></div>
                <div class="card-body">
                    <?php $receiverEmpInDl = !empty($model->receive_date) ? ($model->emp ?? null) : null; ?>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">ผู้รับเรื่อง:</dt>
                        <dd class="col-sm-8">
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($receiverEmpInDl && !empty($receiverEmpInDl->ShowAvatar())): ?>
                                    <?= Html::img($receiverEmpInDl->ShowAvatar(), ['class' => 'avatar rounded-circle shadow', 'alt' => '']) ?>
                                <?php else: ?>
                                    <span class="d-inline-flex rounded-circle border border-secondary-subtle bg-secondary bg-opacity-10 p-2 text-secondary align-items-center justify-content-center flex-shrink-0">
                                        <i class="bi bi-person-circle"></i>
                                    </span>
                                <?php endif; ?>
                                <div class="fw-bold">
                                    <?= Html::encode(!empty($receiverEmpInDl) && !empty($receiverEmpInDl->fullname) ? $receiverEmpInDl->fullname : '-') ?>
                                </div>
                            </div>
                        </dd>
                        <dt class="col-sm-4">รหัสงาน:</dt><dd class="col-sm-8"><?=$model->repair_number ?: '-'?></dd>
                        <dt class="col-sm-4">ประเภทอุปกรณ์:</dt><dd class="col-sm-8"><?=$model->deviceType->title ?? '-'?></dd>
                        <dt class="col-sm-4">รหัสอุปกรณ์:</dt><dd class="col-sm-8"><?=$model->asset_number ?: '-'?></dd>
                        <dt class="col-sm-4">ปัญหา:</dt><dd class="col-sm-8"><?=$model->title ?: '-'?></dd>
                        <dt class="col-sm-4">สถานที่:</dt><dd class="col-sm-8"><?=Html::encode($location)?></dd>
                        <dt class="col-sm-4">วันที่รับเรื่อง:</dt><dd class="col-sm-8"><?= Html::encode($model->viewCreateDateTime()) ?></dd>
                        <dt class="col-sm-4">สถานะ:</dt>
                        <dd class="col-sm-8"><span class="<?=$badgeClass($statusInfo['color'])?>"><?=$statusIcon?><?=Html::encode($statusInfo['label'])?></span></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <?php
            // UI: ผู้รับเรื่อง/ความคืบหน้าสำหรับฝั่งขวา (ไม่เปลี่ยน business logic)
            $receiverEmp = !empty($model->receive_date) ? ($model->emp ?? null) : null;
            $receivedAtDisplay = '-';
            try {
                if (!empty($model->receive_date)) {
                    $receivedAtDisplay = Yii::$app->thaiFormatter->asDateTime($model->receive_date, 'medium');
                }
            } catch (\Throwable $e) {
                $receivedAtDisplay = !empty($model->receive_date) ? (string) $model->receive_date : '-';
            }

            $progressPercent = match ($statusCode) {
                'pending' => 25,
                'receive' => 50,
                'in_progress' => 75,
                'success' => 100,
                default => 0,
            };
            $stepIndex = array_search($statusCode, ['pending', 'receive', 'in_progress', 'success'], true);
            if ($stepIndex === false) {
                $stepIndex = 0;
            }
            ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="small text-muted mb-1">ผู้รับเรื่อง / ช่างที่มอบหมาย</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <?php if ($receiverEmp && !empty($receiverEmp->ShowAvatar())): ?>
                                <?= Html::img($receiverEmp->ShowAvatar(), ['class' => 'avatar rounded-circle shadow', 'alt' => '']) ?>
                            <?php else: ?>
                                <span class="d-inline-flex rounded-circle border border-secondary-subtle bg-secondary bg-opacity-10 p-2 text-secondary align-items-center justify-content-center flex-shrink-0">
                                    <i class="bi bi-person-circle"></i>
                                </span>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <div class="small fw-bold text-truncate">
                                    <?= Html::encode(!empty($receiverEmp) && !empty($receiverEmp->fullname) ? $receiverEmp->fullname : '-') ?>
                                </div>
                                <div class="text-muted small text-truncate">
                                    รับเรื่องเมื่อ: <?= Html::encode($receivedAtDisplay) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted mb-1">ช่างผู้รับผิดชอบงานซ่อม</div>
                        <div class="fw-bold text-truncate">
                            <?= Html::encode(!empty($leadTechnician) ? $leadTechnician : '-') ?>
                        </div>

                        <?php if (!empty($repairTeamRows)): ?>
                            <div class="mt-2">
                                <div class="small text-muted mb-1">ทีมช่างผู้รับผิดชอบงานซ่อม</div>
                                <div class="mb-1"><?=$model->StackTeam()?></div>
                                <div class="small text-muted"><?= Html::encode($teamText) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <div class="small text-muted mb-0">
                                ความคืบหน้า:
                                <span class="text-primary fw-bold"><?= (int) $progressPercent ?>%</span>
                            </div>
                            <span class="small fw-semibold text-body">
                                <?=$statusIcon?><?= Html::encode($statusInfo['label']) ?>
                            </span>
                        </div>

                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: <?= (int) $progressPercent ?>%"></div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <?php
                            $stepMeta = [
                                ['idx' => 0, 'label' => $statusLabels['pending'], 'done' => $stepIndex > 0],
                                ['idx' => 1, 'label' => $statusLabels['receive'], 'done' => $stepIndex > 1],
                                ['idx' => 2, 'label' => $statusLabels['in_progress'], 'done' => $stepIndex > 2],
                                ['idx' => 3, 'label' => $statusLabels['success'], 'done' => $stepIndex > 3],
                            ];
                            foreach ($stepMeta as $st) {
                                $isActive = ($stepIndex === $st['idx']);
                                $cls = $isActive
                                    ? $badgeClass('primary')
                                    : ($st['done'] ? $badgeClass('success') : $badgeClass('secondary') . ' opacity-75');
                                $icon = $st['done'] ? 'fa-solid fa-circle-check' : ($isActive ? 'fa-solid fa-hourglass-half' : 'fa-solid fa-hourglass-split');
                                echo '<span class="' . $cls . '"><i class="' . $icon . ' me-1"></i>' . Html::encode($st['label']) . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm h-100">
                <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-screwdriver-wrench me-1"></i>สรุปสาเหตุ ผลการซ่อม และค่าใช้จ่าย</h6></div>
                <div class="card-body">
                    <div class="small text-muted mb-1">สาเหตุของปัญหา</div>
                    <div class="mb-3"><?=nl2br(Html::encode($rootCause !== '' ? $rootCause : '-'))?></div>
                    <div class="small text-muted mb-1">รายละเอียดการวินิจฉัย</div>
                    <div class="mb-3"><?=nl2br(Html::encode($diagnosis !== '' ? $diagnosis : '-'))?></div>
                    <div class="small text-muted mb-1">สรุปผลการซ่อม</div>
                    <div class="mb-3"><?=nl2br(Html::encode($repairResult !== '' ? $repairResult : '-'))?></div>

                    <div class="border-top pt-2">
                        <div class="d-flex justify-content-between small py-1"><span class="text-muted">บันทึกงานซ่อม</span><span><?=number_format($serviceRecordCount)?> รายการ</span></div>
                        <div class="d-flex justify-content-between small py-1"><span class="text-muted">บันทึกอะไหล่</span><span><?=number_format($partCount)?> รายการ</span></div>
                        <div class="d-flex justify-content-between small py-1"><span class="text-muted">บันทึกค่าใช้จ่าย</span><span><?=number_format($expenseCount)?> รายการ</span></div>
                        <div class="d-flex justify-content-between py-1"><span class="text-muted">ค่าใช้จ่ายรวม</span><span class="fw-semibold text-danger"><?=number_format($totalCostDisplay, 2)?> บาท</span></div>
                    </div>

                    <div class="border-top mt-2 pt-2">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="small text-muted">ไฟล์บิลค่าใช้จ่าย</div>
                            <span class="<?=$badgeClass($model->getExternalRepairBillsCount() > 0 ? 'success' : 'secondary')?>">แนบแล้ว <?=$model->getExternalRepairBillsCount()?> ไฟล์</span>
                        </div>
                        <?=$model->getExternalRepairBillsHtml()?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-timeline me-1"></i>ไทม์ไลน์การบันทึกข้อมูลของช่าง</h6></div>
                <div class="card-body"><div id="showTimeline"></div></div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-star me-1"></i>ให้คะแนนและคอมเมนต์งานซ่อม</h6></div>
                <div class="card-body">
                    <?php if (!$canRate): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fa-solid fa-lock me-1"></i>สามารถให้คะแนนและคอมเมนต์ได้เมื่อปิดงานซ่อมแล้วเท่านั้น
                        </div>
                    <?php else: ?>
                    <form id="repair-feedback-form">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="feedback-rating" class="form-label">คะแนนความพึงพอใจ</label>
                                <select id="feedback-rating" class="form-select" name="rating">
                                    <option value="">เลือกคะแนน</option>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?=$i?>" <?=$ratingValue === $i ? 'selected' : ''?>><?=$i?> ดาว</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="feedback-comment" class="form-label">ความคิดเห็นงานซ่อม</label>
                                <textarea id="feedback-comment" class="form-control" name="comment" rows="3" placeholder="พิมพ์ความคิดเห็นของผู้แจ้ง..."><?=Html::encode($commentText)?></textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-regular fa-paper-plane me-1"></i>บันทึกคะแนนและคอมเมนต์</button>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
loadTimeline();
function loadTimeline() {
    $.ajax({
        type: "get",
        url: "$urlTimeline",
        dataType: "json",
        success: function (response) {
            $('#showTimeline').html(response.content);
        }
    });
}

$('body').off('submit.repairFeedback').on('submit.repairFeedback', '#repair-feedback-form', function (e) {
    e.preventDefault();
    const form = $(this);
    $.ajax({
        type: 'post',
        url: "$feedbackUrl",
        data: form.serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                Swal.fire({icon: 'success', title: 'บันทึกแล้ว', timer: 1200, showConfirmButton: false});
            } else {
                Swal.fire({icon: 'warning', title: 'ไม่สำเร็จ', text: response.message || 'ไม่สามารถบันทึกข้อมูลได้'});
            }
        },
        error: function () {
            Swal.fire({icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'});
        }
    });
    return false;
});
JS;
$this->registerJs($js);
?>
