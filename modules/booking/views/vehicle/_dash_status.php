<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * การ์ด KPI สถานะคำขอ — รูปแบบเดียวกับ kpi_summary ของโมดูลอื่น
 * (การ์ดแยกใบ · ตัวเลขใหญ่ · ป้ายชื่อสีตามสถานะ · ไอคอนในวงกลม tinted)
 *
 * @var yii\web\View $this
 * @var app\modules\booking\models\VehicleSearch $searchModel
 * @var array $summary
 */

$total = (int) $summary['total'];
$year = $searchModel->thai_year;

$link = static function (?string $status) use ($year): string {
    $filter = array_filter([
        'status' => $status,
        'thai_year' => $year,
    ], static fn($v) => $v !== null && $v !== '');

    return Url::to($filter === []
        ? ['/booking/vehicle/index']
        : ['/booking/vehicle/index', 'VehicleSearch' => $filter]);
};

$cards = [
    [
        'label' => 'รอจัดสรร (คำขอ)',
        'value' => (int) $summary['pending'],
        'tone' => 'warning',
        'icon' => 'bi-hourglass-split',
        'url' => $link('Pending'),
    ],
    [
        'label' => 'จัดสรรแล้ว (คำขอ)',
        'value' => (int) $summary['allocated'],
        'tone' => 'primary',
        'icon' => 'bi-check2-circle',
        'url' => $link('Pass'),
    ],
    [
        'label' => 'เสร็จสิ้นภารกิจ (คำขอ)',
        'value' => (int) $summary['success'],
        'tone' => 'success',
        'icon' => 'bi-flag',
        'url' => $link('Success'),
    ],
    [
        'label' => 'ยกเลิก (คำขอ)',
        'value' => (int) $summary['cancelled'],
        'tone' => 'secondary',
        'icon' => 'bi-x-circle',
        'url' => $link('Cancel'),
    ],
];
?>

<h3 class="h6 text-body-secondary mb-2">
    ภาพรวมคำขอ ปีงบประมาณ <span class="vd-num"><?= Html::encode((string) $year) ?></span>
    · ทั้งหมด <span class="vd-num fw-semibold text-body"><?= number_format($total) ?></span> คำขอ
</h3>

<div class="row g-3 mb-4">
    <?php foreach ($cards as $card): ?>
        <?php $percent = $total > 0 ? (int) round(($card['value'] / $total) * 100) : 0; ?>
        <div class="col-6 col-xl-3">
            <a href="<?= $card['url'] ?>" class="card h-100 border shadow-sm vd-kpi text-decoration-none text-body"
                aria-label="<?= Html::encode($card['label'] . ' ' . number_format($card['value']) . ' — เปิดทะเบียนการจอง') ?>">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="d-flex flex-column gap-3">
                            <span class="fw-bold fs-3 vd-num"><?= number_format($card['value']) ?></span>
                            <span class="text-<?= $card['tone'] ?>-emphasis">
                                <?= Html::encode($card['label']) ?>
                                <span class="d-block small text-body-tertiary vd-num"><?= $percent ?>% ของทั้งหมด</span>
                            </span>
                        </div>
                        <div class="bg-<?= $card['tone'] ?>-subtle text-<?= $card['tone'] ?>-emphasis p-3 rounded-circle vd-kpi__icon"
                            aria-hidden="true">
                            <i class="bi <?= $card['icon'] ?>"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
