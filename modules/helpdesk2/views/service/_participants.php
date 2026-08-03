<?php
use yii\helpers\Html;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\modules\hr\models\Employees;

/** @var app\modules\helpdesk2\models\Helpdesk $model */
$assigneeMock = [
    'fullname' => 'ยังไม่มอบหมาย',
    'department' => '-',
    'avatar' => null,
];

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

<div>
    <div class="d-flex flex-column gap-3">
        <?php foreach ($technicians as $idx => $assignee): ?>
            <div class="d-flex align-items-center gap-3<?= $idx > 0 ? ' pt-2 border-top border-secondary-subtle' : '' ?>">
                <?php if (!empty($assignee['avatar'])): ?>
                    <?= Html::img($assignee['avatar'], [
                        'class' => 'rounded-circle border border-secondary-subtle shadow-sm object-fit-cover flex-shrink-0',
                        'alt' => '',
                        'loading' => 'lazy',
                        'width' => 48,
                        'height' => 48,
                    ]) ?>
                <?php else: ?>
                    <div class="rounded-3 border border-secondary-subtle bg-secondary-subtle text-secondary-emphasis p-3 flex-shrink-0 lh-1">
                        <i class="fa-solid fa-circle-user fs-3" aria-hidden="true"></i>
                    </div>
                <?php endif; ?>
                <div class="overflow-hidden flex-grow-1">
                    <div class="fw-bold text-break"><?= Html::encode($assignee['fullname'] ?? '-') ?></div>
                    <div class="text-body-secondary small text-break"><?= Html::encode($assignee['department'] ?? '-') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
