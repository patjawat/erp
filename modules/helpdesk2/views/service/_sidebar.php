<?php
use yii\helpers\Html;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\modules\hr\models\Employees;

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
    'avatar' => null,
];
$receiverMock = [
    'fullname' => 'ยังไม่มีผู้รับเรื่อง',
    'department' => '-',
    'avatar' => null,
    'received_at' => null,
];

$req = null;
try {
    $req = $model->getUserReq();
} catch (\Throwable $e) {
    $req = null;
}

$requester = $req ?: $requesterMock;
$receiver = $receiverMock;
try {
    $receiveLog = HelpdeskDetail::find()
        ->where([
            'helpdesk_id' => (int) $model->id,
            'name' => 'service_record',
            'status' => 'รับเรื่อง',
        ])
        ->orderBy(['id' => SORT_DESC])
        ->one();
    if ($receiveLog && !empty($receiveLog->emp_id)) {
        $receiveEmp = Employees::findOne(['id' => (int) $receiveLog->emp_id]);
        if (!$receiveEmp) {
            $receiveEmp = Employees::findOne(['user_id' => (int) $receiveLog->emp_id]);
        }
        if ($receiveEmp) {
            $receiver = [
                'fullname' => $receiveEmp->fullname ?? $receiverMock['fullname'],
                'department' => method_exists($receiveEmp, 'departmentName') ? ($receiveEmp->departmentName() ?? $receiverMock['department']) : $receiverMock['department'],
                'avatar' => method_exists($receiveEmp, 'ShowAvatar') ? $receiveEmp->ShowAvatar() : null,
                'received_at' => $receiveLog->created_at ?? null,
            ];
        }
    }
} catch (\Throwable $e) {
    $receiver = $receiverMock;
}

$technicians = [];
try {
    $resolveRowToEmployee = static function (HelpdeskDetail $row): ?Employees {
        $teamEmp = $row->emp;
        if (!$teamEmp && !empty($row->emp_id)) {
            $teamEmp = Employees::findOne(['id' => (int) $row->emp_id]);
        }
        if (!$teamEmp && !empty($row->emp_id)) {
            $teamEmp = Employees::findOne(['user_id' => (int) $row->emp_id]);
        }
        return $teamEmp;
    };

    $seenEmpIds = [];

    $pushFromEmployee = static function (Employees $emp) use (&$technicians, &$seenEmpIds, $assigneeMock): void {
        $eid = (int) $emp->id;
        if (isset($seenEmpIds[$eid])) {
            return;
        }
        $seenEmpIds[$eid] = true;
        $technicians[] = [
            'fullname' => $emp->fullname ?? $assigneeMock['fullname'],
            'department' => method_exists($emp, 'departmentName') ? ($emp->departmentName() ?? $assigneeMock['department']) : $assigneeMock['department'],
            'avatar' => method_exists($emp, 'ShowAvatar') ? $emp->ShowAvatar() : null,
        ];
    };

    // eager-load ความสัมพันธ์ emp เพื่อเลี่ยง N+1 (ดึงช่างทั้งทีมด้วย query เดียว)
    $teamRows = HelpdeskDetail::find()
        ->where(['name' => 'repair_team', 'helpdesk_id' => (int) $model->id])
        ->with('emp')
        ->orderBy(['id' => SORT_ASC])
        ->all();

    foreach ($teamRows as $row) {
        $teamEmp = $resolveRowToEmployee($row);
        if ($teamEmp) {
            $pushFromEmployee($teamEmp);
        }
    }

    if (empty($technicians)) {
        $technicians[] = $assigneeMock;
    }
} catch (\Throwable $e) {
    $technicians = [$assigneeMock];
}
?>

<div class="position-sticky top-0">
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
            <h2 class="h6 fw-bold mb-0">ข้อมูลผู้แจ้ง</h2>
        </div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <?php if (!empty($requester['avatar'])): ?>
                    <div class="flex-shrink-0 rounded-3 border border-secondary-subtle overflow-hidden bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center lh-1">
                        <?= $requester['avatar'] ?>
                    </div>
                <?php else: ?>
                    <div class="rounded-3 border border-secondary-subtle bg-secondary bg-opacity-10 p-3 text-secondary flex-shrink-0">
                        <i class="fa-solid fa-circle-user fs-3" aria-hidden="true"></i>
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
        <div class="card-header d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-check" aria-hidden="true"></i>
            <h2 class="h6 fw-bold mb-0">ผู้รับเรื่องซ่อม</h2>
        </div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <?php if (!empty($receiver['avatar'])): ?>
                    <div class="flex-shrink-0 rounded-3 border border-secondary-subtle overflow-hidden bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center lh-1">
                        <?= Html::img($receiver['avatar'], ['class' => 'avatar rounded-circle shadow', 'alt' => '', 'loading' => 'lazy']) ?>
                    </div>
                <?php else: ?>
                    <div class="rounded-3 border border-secondary-subtle bg-secondary bg-opacity-10 p-3 text-secondary flex-shrink-0">
                        <i class="fa-solid fa-circle-user fs-3" aria-hidden="true"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="fw-bold"><?= Html::encode($receiver['fullname'] ?? '-') ?></div>
                    <div class="text-muted"><?= Html::encode($receiver['department'] ?? '-') ?></div>
                    <?php if (!empty($receiver['received_at'])): ?>
                        <div class="small text-muted">รับเรื่องเมื่อ: <?= Html::encode((string) $receiver['received_at']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header d-flex align-items-center justify-content-between gap-2">
            <h2 class="h6 fw-bold mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-user-gear" aria-hidden="true"></i>
                <span>ช่างผู้รับผิดชอบงานซ่อม</span>
            </h2>
            <?= Html::a(
                '<i class="fa-solid fa-user-plus me-1" aria-hidden="true"></i> เพิ่มช่าง',
                ['/helpdesk/team/create', 'helpdesk_id' => $model->id],
                [
                    'class' => 'btn btn-sm btn-outline-primary btn-assign-team',
                    'data' => [
                        'title' => '<i class="fa-solid fa-user-plus me-1" aria-hidden="true"></i> เพิ่มช่างผู้รับผิดชอบ',
                        'size' => 'modal-md',
                    ],
                ]
            ) ?>
        </div>
        <div class="card-body p-4">
            <div class="d-flex flex-column gap-3">
                <?php foreach ($technicians as $idx => $assignee): ?>
                    <div class="d-flex align-items-center gap-3<?= $idx > 0 ? ' pt-2 border-top border-secondary-subtle' : '' ?>">
                        <?php if (!empty($assignee['avatar'])): ?>
                            <div class="flex-shrink-0 rounded-3 border border-secondary-subtle overflow-hidden bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center lh-1">
                                <?= Html::img($assignee['avatar'], ['class' => 'avatar rounded-circle shadow', 'alt' => '', 'loading' => 'lazy']) ?>
                            </div>
                        <?php else: ?>
                            <div class="rounded-3 border border-secondary-subtle bg-secondary bg-opacity-10 p-3 text-secondary flex-shrink-0">
                                <i class="fa-solid fa-circle-user fs-3" aria-hidden="true"></i>
                            </div>
                        <?php endif; ?>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-break"><?= Html::encode($assignee['fullname'] ?? '-') ?></div>
                            <div class="text-muted small text-break"><?= Html::encode($assignee['department'] ?? '-') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="text-muted small mb-0 mt-3">หนึ่งงานซ่อมมีช่างร่วมได้หลายคน กด «เพิ่มช่าง» เพื่อเพิ่มทีละคน</p>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="fa-solid fa-database" aria-hidden="true"></i>
            <h2 class="h6 fw-bold mb-0">ข้อมูลประกอบงานซ่อม</h2>
        </div>
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

