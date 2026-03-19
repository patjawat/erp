<?php
use yii\helpers\Html;

/** @var app\modules\helpdesk2\models\Helpdesk $model */
/** @var array $statusInfo */
/** @var array $priorityInfo */
/** @var string $statusBadge */
/** @var string $priorityBadge */
/** @var string $slaBadgeHtml */

$requesterMock = [
    'fullname' => 'ยังไม่ระบุ',
    'department' => '-',
];

$assigneeMock = [
    'fullname' => 'ยังไม่มอบหมาย',
    'department' => '-',
];

$req = null;
try {
    $req = $model->getUserReq();
} catch (\Throwable $e) {
    $req = null;
}

$requester = $req ?: $requesterMock;
$assignee = $assigneeMock;
try {
    if (!empty($model->emp)) {
        $assignee = [
            'fullname' => $model->emp->fullname ?? $assigneeMock['fullname'],
            'department' => method_exists($model->emp, 'departmentName') ? ($model->emp->departmentName() ?? $assigneeMock['department']) : $assigneeMock['department'],
        ];
    }
} catch (\Throwable $e) {
    $assignee = $assigneeMock;
}
?>

<div class="position-sticky top-0">
    <div class="card shadow-sm">
        <div class="card-header fw-bold">ปุ่มด่วน (Sticky)</div>
        <div class="card-body p-4">
            <div class="d-grid gap-2">
                <?= Html::a(
                    '<i class="fa-solid fa-circle-exclamation me-2"></i>รับงาน',
                    ['/helpdesk/service/receive', 'id' => $model->id],
                    ['class' => 'btn btn-success btn-lg receive-order']
                ) ?>

                <?= Html::a(
                    '<i class="fa-solid fa-user-plus me-2"></i>มอบหมายช่าง',
                    ['/helpdesk/team/create', 'helpdesk_id' => $model->id],
                    [
                        'class' => 'btn btn-outline-primary btn-lg btn-assign-team',
                        'data' => [
                            'title' => '<i class="fa-solid fa-user-plus me-2"></i>มอบหมายช่าง',
                            'size' => 'modal-md',
                        ],
                    ]
                ) ?>

                <?= Html::a(
                    '<i class="fa-solid fa-wrench me-2"></i>ส่งซ่อม',
                    ['send-repair', 'id' => $model->id],
                    ['class' => 'btn btn-warning btn-lg text-dark btn-send-repair']
                ) ?>

                <?= Html::a(
                    '<i class="fa-solid fa-xmark me-2"></i>ปิดงาน',
                    ['close', 'id' => $model->id],
                    ['class' => 'btn btn-danger btn-lg']
                ) ?>
            </div>

            <div class="text-muted small mt-3">
                ทำงานตามลำดับนี้: อนุมัติ → มอบหมายช่าง → ส่งซ่อม → ปิดงาน
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header fw-bold">ข้อมูลผู้แจ้ง</div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <?php if (!empty($requester['avatar'])): ?>
                    <div class="flex-shrink-0 rounded-3 border border-secondary-subtle overflow-hidden bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center lh-1">
                        <?= $requester['avatar'] ?>
                    </div>
                <?php else: ?>
                    <div class="rounded-3 border border-secondary-subtle bg-secondary bg-opacity-10 p-3 text-secondary flex-shrink-0">
                        <i class="bi bi-person-circle"></i>
                    </div>
                <?php endif; ?>
                <div>
                        <div class="fw-bold"><?= Html::encode($requester['fullname'] ?? '-') ?></div>
                        <div class="text-muted"><?= Html::encode($requester['department'] ?? '-') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header fw-bold">ผู้รับผิดชอบ</div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 border border-primary-subtle bg-primary bg-opacity-10 p-3 text-primary">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                        <div class="fw-bold"><?= Html::encode($assignee['fullname'] ?? '-') ?></div>
                        <div class="text-muted"><?= Html::encode($assignee['department'] ?? '-') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header fw-bold">Metadata</div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted">สถานะ</span>
                <span><?= $statusBadge ?? Html::encode($statusInfo['label'] ?? '-') ?></span>
            </div>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted">ความสำคัญ</span>
                <span><?= $priorityBadge ?? Html::encode($priorityInfo['label'] ?? '-') ?></span>
            </div>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted">SLA</span>
                <span><?= $slaBadgeHtml ?></span>
            </div>
        </div>
    </div>
</div>

