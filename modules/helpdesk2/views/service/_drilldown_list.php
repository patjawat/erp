<?php

use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array<int,array<string,mixed>> $tickets */
/** @var array<string,mixed> $meta */

$fmtDuration = static function (?int $s): string {
    if ($s === null) {
        return '-';
    }
    if ($s < 3600) {
        return round($s / 60) . ' นาที';
    }
    if ($s < 86400) {
        return round($s / 3600, 1) . ' ชม.';
    }
    return round($s / 86400, 1) . ' วัน';
};

$urgencyBadge = static function (string $u): string {
    $map = [
        'critical' => ['วิกฤต', 'danger'],
        'high' => ['สูง', 'warning'],
        'medium' => ['ปานกลาง', 'info'],
        'low' => ['ต่ำ', 'secondary'],
    ];
    [$label, $color] = $map[$u] ?? ['ปานกลาง', 'info'];
    return '<span class="badge bg-' . $color . ' bg-opacity-10 text-' . $color
        . ' border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1">' . $label . '</span>';
};

$slaBadge = static function (string $s): string {
    switch ($s) {
        case 'met':
            return '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2 py-1"><i class="fa-regular fa-circle-check me-1"></i>ตาม SLA</span>';
        case 'breached':
            return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-circle-exclamation me-1"></i>เกิน SLA</span>';
        case 'pending':
            return '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-2 py-1"><i class="fa-regular fa-clock me-1"></i>อยู่ระหว่างดำเนินการ</span>';
        default:
            return '<span class="text-muted small">-</span>';
    }
};

$scope = (string) ($meta['scope'] ?? '');
$showTime = in_array($scope, ['mtta', 'mttr'], true);
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h6 class="mb-0 fw-semibold"><?= Html::encode($meta['title'] ?? 'รายการงาน') ?></h6>
        <span class="small text-muted"><?= number_format((int) ($meta['count'] ?? 0)) ?> รายการ</span>
    </div>
</div>

<?php if (empty($tickets)): ?>
    <div class="text-center text-muted py-5">
        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
        ไม่พบรายการในกลุ่มนี้
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>รหัส</th>
                    <th>รายการ</th>
                    <th>ผู้แจ้ง/หน่วยงาน</th>
                    <th>ด่วน</th>
                    <th>สถานะ</th>
                    <?php if ($showTime): ?>
                        <th class="text-nowrap"><?= $scope === 'mtta' ? 'เวลารับเรื่อง' : 'เวลาซ่อมเสร็จ' ?></th>
                    <?php else: ?>
                        <th>SLA</th>
                    <?php endif; ?>
                    <th class="text-end">ดู</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($tickets as $t): ?>
                    <tr>
                        <td class="text-nowrap fw-medium small"><?= Html::encode($t['repair_number']) ?></td>
                        <td class="text-break small" style="max-width:220px;"><?= Html::encode($t['title']) ?>
                            <div class="text-muted"><?= Html::encode($t['device_type']) ?></div>
                        </td>
                        <td class="small">
                            <?= Html::encode($t['requester'] ?: '-') ?>
                            <div class="text-muted"><?= Html::encode($t['department']) ?></div>
                        </td>
                        <td><?= $urgencyBadge($t['urgency']) ?></td>
                        <td class="small text-nowrap"><?= Html::encode($t['status_label']) ?></td>
                        <?php if ($showTime): ?>
                            <td class="text-nowrap small fw-medium">
                                <?= $fmtDuration($scope === 'mtta' ? $t['ack_seconds'] : $t['resolve_seconds']) ?>
                            </td>
                        <?php else: ?>
                            <td><?= $slaBadge($t['sla_status']) ?></td>
                        <?php endif; ?>
                        <td class="text-end">
                            <?= Html::a('<i class="bi bi-box-arrow-up-right"></i>', Url::to(['/helpdesk/service/view-v2', 'id' => $t['id']]), [
                                'class' => 'btn btn-sm btn-outline-secondary',
                                'target' => '_blank',
                                'encode' => false,
                                'title' => 'เปิดหน้างาน',
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ((int) ($meta['count'] ?? 0) >= 500): ?>
        <p class="small text-muted mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>แสดงสูงสุด 500 รายการ — กรองช่วงเวลา/เงื่อนไขให้แคบลงเพื่อดูครบ</p>
    <?php endif; ?>
<?php endif; ?>
