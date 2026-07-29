<?php

use app\modules\kpi\models\KpiCycle;
use app\modules\kpi\models\KpiEntry;
use app\modules\kpi\models\KpiItem;
use app\modules\kpi\services\KpiService;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $model */

$cycles = $model->kpiCycles; // ใหม่สุดก่อน
$years = array_map(static fn(KpiCycle $c): int => (int) $c->fiscal_year, $cycles);

$requestedYear = (int) Yii::$app->request->get('fiscal_year');
$selectedYear = $requestedYear ?: ($years[0] ?? KpiService::currentFiscalYear());

/** @var KpiCycle|null $cycle */
$cycle = null;
foreach ($cycles as $c) {
    if ((int) $c->fiscal_year === $selectedYear) {
        $cycle = $c;
        break;
    }
}

$statusTone = [
    KpiCycle::STATUS_DRAFT => 'warning',
    KpiCycle::STATUS_PENDING => 'info',
    KpiCycle::STATUS_ACTIVE => 'success',
    KpiCycle::STATUS_CLOSED => 'secondary',
];
$statusLabels = KpiCycle::statusLabels();
$currentFy = KpiService::currentFiscalYear();
$fmt = static fn($n): string => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
$this->registerCss('.tnum{font-variant-numeric:tabular-nums}');

$selectableYears = $years;
if (!in_array($currentFy, $selectableYears, true)) {
    array_unshift($selectableYears, $currentFy);
}
rsort($selectableYears);

$manageUrl = ['/kpi/manage/view', 'emp_id' => $model->id, 'fiscal_year' => $selectedYear];
$canManage = KpiService::isHrOrAdmin() || KpiService::isSupervisorOf((int) $model->id);
?>

<div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
    <div>
        <h5 class="mb-1 fw-semibold">ตัวชี้วัด KPI ประจำปี</h5>
        <div class="text-body-secondary small">ปีงบประมาณ ต.ค.–ก.ย. · คลิกชื่อตัวชี้วัดเพื่อบันทึกผลรายเดือน</div>
    </div>
    <?php if ($canManage): ?>
        <?= Html::a('<i class="bi bi-gear me-1"></i>จัดการ KPI (เพิ่ม/แก้ไข)', $manageUrl, ['class' => 'btn btn-sm btn-outline-primary']) ?>
    <?php endif; ?>
</div>

<!-- แท็บเลือกปีงบประมาณ (ย้อนหลัง) -->
<ul class="nav nav-pills gap-1 mb-3">
    <?php foreach ($selectableYears as $fy): ?>
        <li class="nav-item">
            <a class="nav-link <?= $fy === $selectedYear ? 'active' : '' ?> py-1 px-3"
               href="<?= Url::to(['/profile', 'name' => 'kpi', 'fiscal_year' => $fy]) ?>" data-pjax="1">
                ปี <?= $fy ?><?= $fy === $currentFy ? ' <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1">ปัจจุบัน</span>' : '' ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<?php if (!$cycle): ?>
    <div class="card bg-body border"><div class="card-body text-center py-5">
        <i class="bi bi-bullseye fs-1 text-body-secondary d-block mb-2"></i>
        <h6 class="fw-semibold">ยังไม่มีชุด KPI ของปีงบประมาณ <?= $selectedYear ?></h6>
        <p class="text-body-secondary mb-0">หัวหน้าหน่วยงานหรือ HR สร้างชุด KPI ได้ที่หน้าจัดการ</p>
    </div></div>
<?php else: ?>
    <?php
    $items = KpiItem::find()
        ->where(['cycle_id' => $cycle->id, 'status' => KpiItem::STATUS_ACTIVE])
        ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
        ->all();
    $entries = [];
    if ($items) {
        foreach (KpiEntry::find()->where(['kpi_item_id' => array_column($items, 'id'), 'period_type' => KpiEntry::PERIOD_MONTH])->all() as $e) {
            $entries[$e->kpi_item_id][$e->period_index] = $e;
        }
    }
    $totalWeight = 0.0;
    foreach ($items as $it) {
        $totalWeight += (float) $it->weight;
    }
    $canRecordNow = KpiService::canRecord($cycle) && ($cycle->status === KpiCycle::STATUS_ACTIVE || KpiService::isHrOrAdmin());
    ?>
    <div class="card bg-body border mb-3"><div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
        <div>
            <?php $st = $statusTone[$cycle->status] ?? 'secondary'; ?>
            <span class="badge rounded-pill bg-<?= $st ?>-subtle text-<?= $st ?>-emphasis me-2"><?= Html::encode($statusLabels[$cycle->status] ?? $cycle->status) ?></span>
            <span class="text-body-secondary small"><?= Html::encode(KpiService::fiscalRangeLabel($cycle->fiscal_year)) ?></span>
        </div>
        <?php $wt = abs($totalWeight - 100) < 0.01 ? 'success' : 'warning'; ?>
        <span class="badge bg-<?= $wt ?>-subtle text-<?= $wt ?>-emphasis">น้ำหนักรวม <?= $fmt($totalWeight) ?>%</span>
    </div></div>

    <?php if (!$items): ?>
        <div class="card bg-body border"><div class="card-body text-center text-body-secondary py-4">ยังไม่มี KPI ในชุดนี้</div></div>
    <?php else: ?>
        <div class="card bg-body border"><div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead><tr class="text-body-secondary">
                    <th>ตัวชี้วัด</th>
                    <?php foreach (KpiService::FISCAL_MONTHS as $cm): ?><th class="text-center px-1"><?= KpiService::MONTH_LABELS_TH[$cm] ?></th><?php endforeach; ?>
                    <th class="text-center text-nowrap">สรุปผล</th>
                </tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td>
                            <?php if ($canRecordNow): ?>
                                <a href="<?= Url::to(['/kpi/manage/record-item', 'id' => $it->id]) ?>" class="open-modal fw-semibold text-decoration-none" data-size="modal-lg" data-pjax="0" title="คลิกเพื่อบันทึก/แก้ไขผลรายเดือน"><?= Html::encode($it->indicator) ?></a>
                            <?php else: ?>
                                <span class="fw-semibold"><?= Html::encode($it->indicator) ?></span>
                            <?php endif; ?>
                            <?php if ($it->source_type === KpiItem::SOURCE_JD): ?> <span class="badge bg-secondary-subtle text-secondary-emphasis fw-normal">JD</span><?php endif; ?>
                            <div class="text-body-secondary fs-13 mt-1">
                                <i class="bi bi-bullseye me-1"></i>เป้า <?= Html::encode($it->target_text ?: ($it->target_value !== null ? $fmt($it->target_value) . ($it->unit ? ' ' . $it->unit : '') : '—')) ?>
                                <span class="mx-1">·</span>น้ำหนัก <?= $it->weight > 0 ? $fmt($it->weight) . '%' : '—' ?>
                            </div>
                        </td>
                        <?php for ($fi = 1; $fi <= 12; $fi++): ?>
                            <?php $e = $entries[$it->id][$fi] ?? null; $val = $e ? ($e->value_num !== null ? $fmt($e->value_num) : ($e->value_text ? '•' : '')) : ''; ?>
                            <td class="text-center px-1 tnum" title="<?= $e && $e->value_text ? Html::encode($e->value_text) : '' ?>"><?= $val !== '' ? Html::encode($val) : '<span class="text-body-secondary">·</span>' ?></td>
                        <?php endfor; ?>
                        <?php
                        $sm = $it->summarize($entries[$it->id] ?? []);
                        $aggLabel = KpiItem::aggregationLabels()[$it->aggregation] ?? $it->aggregation;
                        $lvTone = $sm['level'] === null ? 'secondary' : ($sm['level'] >= 4 ? 'success' : ($sm['level'] >= 3 ? 'warning' : 'secondary'));
                        ?>
                        <td class="text-center text-nowrap">
                            <?php if ($it->value_type === KpiItem::TYPE_NUMERIC && $sm['value'] !== null): ?>
                                <span class="fw-semibold tnum" title="วิธี: <?= Html::encode($aggLabel) ?>"><?= $fmt($sm['value']) ?><?= $it->unit ? ' ' . Html::encode($it->unit) : '' ?></span>
                                <?php if ($sm['level'] !== null): ?>
                                    <div class="small mt-1"><span class="badge bg-<?= $lvTone ?>-subtle text-<?= $lvTone ?>-emphasis">ระดับ <?= $sm['level'] ?>/5</span></div>
                                <?php elseif ($sm['pct'] !== null): ?>
                                    <div class="small text-body-secondary tnum"><?= $fmt($sm['pct']) ?>%</div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-body-secondary">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div>
        <p class="text-body-secondary small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i><?= $canRecordNow ? 'คลิกที่ชื่อตัวชี้วัดเพื่อบันทึก/แก้ไขผลรายเดือน · การเพิ่ม/แก้ KPI ทำได้ที่หน้าจัดการ' : 'การบันทึกผลจะเปิดใช้เมื่อชุด KPI ได้รับการอนุมัติ' ?></p>
    <?php endif; ?>
<?php endif; ?>
