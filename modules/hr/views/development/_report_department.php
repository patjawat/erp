<?php

use yii\helpers\Html;

/**
 * Drill-down รายบุคคลในหน่วยงาน (เปิดเป็น modal จากตารางรายหน่วยงานในหน้ารายงาน)
 *
 * @var yii\web\View $this
 * @var array $people [{id,name,times}]
 * @var string $name ชื่อหน่วยงาน
 * @var int $year ปีงบประมาณ
 */
$total = count($people);
$developed = count(array_filter($people, static fn($p) => $p['times'] > 0));
$gap = $total - $developed;
$cov = $total > 0 ? round($developed / $total * 100, 1) : 0;
$covColor = $cov >= 60 ? 'success' : ($cov >= 30 ? 'warning' : 'danger');
?>

<div class="mb-3">
    <div class="d-flex flex-wrap gap-3 align-items-center mb-2">
        <span class="badge text-bg-<?= $covColor ?> fs-6">ครอบคลุม <?= $cov ?>%</span>
        <span class="text-body-secondary small"><i class="bi bi-people"></i> พัฒนาแล้ว <?= $developed ?> / <?= $total ?> คน · ปีงบ <?= $year ?></span>
        <?php if ($gap > 0): ?>
            <span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> ยังไม่ได้รับการพัฒนา <?= $gap ?> คน</span>
        <?php endif; ?>
    </div>
    <div class="progress" style="height:8px;"><div class="progress-bar bg-<?= $covColor ?>" style="width:<?= $cov ?>%"></div></div>
</div>

<?php $typeCols = \app\modules\hr\services\DevelopmentReport::TYPE_SHORT; ?>
<div class="table-responsive" style="max-height:60vh;overflow:auto;">
    <table class="table table-sm table-hover align-middle mb-0 text-nowrap">
        <thead class="table-light sticky-top">
            <tr>
                <th style="width:2.5rem;">#</th>
                <th>บุคลากร</th>
                <?php foreach ($typeCols as $lbl): ?>
                    <th class="text-center"><?= Html::encode($lbl) ?></th>
                <?php endforeach; ?>
                <th class="text-center">จำนวน</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($people)): ?>
            <tr><td colspan="<?= count($typeCols) + 3 ?>" class="text-center text-body-secondary py-3">ไม่มีบุคลากรปฏิบัติงานในหน่วยนี้</td></tr>
        <?php endif; ?>
        <?php foreach ($people as $i => $p): ?>
            <tr class="<?= $p['times'] === 0 ? 'table-danger-subtle' : '' ?>">
                <td class="text-body-secondary small"><?= $i + 1 ?></td>
                <td><?= Html::a(
                    Html::encode($p['name']),
                    ['/hr/development/report-person', 'emp_id' => $p['id'], 'thai_year' => $year],
                    ['class' => 'open-modal link-primary text-decoration-none', 'data' => ['size' => 'modal-lg']]
                ) ?></td>
                <?php foreach (array_keys($typeCols) as $code): $n = $p['by_code'][$code] ?? 0; ?>
                    <td class="text-center <?= $n === 0 ? 'text-body-tertiary' : 'fw-medium' ?>"><?= $n ?></td>
                <?php endforeach; ?>
                <td class="text-center fw-bold <?= $p['times'] === 0 ? 'text-danger' : 'text-primary' ?>"><?= $p['times'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
