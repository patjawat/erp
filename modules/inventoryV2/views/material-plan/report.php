<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $filter */
/** @var app\modules\inventoryV2\models\MaterialPlan $plan */
/** @var array $rows */
/** @var array $off_plan */
/** @var array $summary */
/** @var array $quarter_labels */
/** @var array $warehouses */

$formatter = Yii::$app->formatter;

$this->title = 'รายงานแผน vs จัดซื้อจริง';
$this->params['breadcrumbs'][] = ['label' => 'คลังวัสดุ', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'จัดทำแผนวัสดุประจำปี', 'url' => ['/inventory-v2/material-plan/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();

$this->beginBlock('sub-title');
echo 'เทียบแผนที่บันทึกไว้กับยอดรับเข้าคลังจริง ปีงบประมาณ ' . Html::encode($plan->fiscal_year);
$this->endBlock();

$this->beginBlock('page-action');
echo $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'report-material-plan-variance']);
$this->endBlock();

$exportUrl = Url::to([
    '/inventory-v2/material-plan/report-export',
    'fiscal_year' => $filter['fiscal_year'],
    'warehouse_id' => $filter['warehouse_id'],
]);

/** สีของส่วนต่าง: จริงเกินแผน = เขียว, ต่ำกว่าแผน = แดง, เท่ากัน = เทา */
$varianceClass = static function ($value): string {
    if ($value > 0) {
        return 'text-success';
    }
    if ($value < 0) {
        return 'text-danger';
    }
    return 'text-body-secondary';
};

/** แถบ %สำเร็จ */
$pctBadge = static function ($pct): string {
    if ($pct === null) {
        return '<span class="text-body-secondary">-</span>';
    }
    $cls = $pct >= 90 ? 'bg-success-subtle text-success-emphasis'
        : ($pct >= 50 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-danger-subtle text-danger-emphasis');
    return '<span class="badge ' . $cls . ' fw-semibold">' . number_format((float) $pct, 1) . '%</span>';
};
?>

<div class="d-flex flex-column gap-3">
    <section class="card border shadow-sm">
        <div class="card-body">
            <?= Html::beginForm(['/inventory-v2/material-plan/report'], 'get', ['class' => 'row g-3 align-items-end']) ?>
                <label class="col-6 col-md-3">
                    <span class="mp-label">ปีงบประมาณ</span>
                    <?= Html::input('number', 'fiscal_year', $filter['fiscal_year'], [
                        'class' => 'form-control', 'min' => 2400, 'max' => 2800, 'step' => 1,
                    ]) ?>
                </label>
                <label class="col-6 col-md-4">
                    <span class="mp-label">คลังหลัก</span>
                    <?= Html::dropDownList('warehouse_id', $filter['warehouse_id'], $warehouses, ['class' => 'form-select']) ?>
                </label>
                <div class="col-12 col-md-5 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat me-1"></i>ดูรายงาน
                    </button>
                    <a href="<?= Url::to(['/inventory-v2/material-plan/index', 'fiscal_year' => $filter['fiscal_year'], 'warehouse_id' => $filter['warehouse_id']]) ?>"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-pencil-square me-1"></i>ไปที่แผน
                    </a>
                    <a href="<?= $exportUrl ?>" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </a>
                </div>
            <?= Html::endForm() ?>
        </div>
    </section>

    <div class="row g-3">
        <?php
        $cards = [
            ['label' => 'มูลค่าตามแผน', 'value' => $formatter->asDecimal($summary['plan_value'], 2), 'unit' => 'บาท'],
            ['label' => 'จัดซื้อจริง', 'value' => $formatter->asDecimal($summary['actual_value'], 2), 'unit' => 'บาท (รับเข้าคลัง)'],
            [
                'label' => 'ส่วนต่าง (จริง-แผน)',
                'value' => ($summary['variance_value'] >= 0 ? '+' : '') . $formatter->asDecimal($summary['variance_value'], 2),
                'unit' => 'บาท',
                'class' => $varianceClass($summary['variance_value']),
            ],
            [
                'label' => '%สำเร็จตามมูลค่า',
                'value' => $summary['achieve_pct'] === null ? '-' : number_format((float) $summary['achieve_pct'], 1) . '%',
                'unit' => $formatter->asInteger($summary['purchased_count']) . '/' . $formatter->asInteger($summary['item_count']) . ' รายการมีการซื้อ',
            ],
        ];
        ?>
        <?php foreach ($cards as $card): ?>
            <div class="col-6 col-xl-3">
                <div class="card border shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <p class="mp-label mb-1"><?= Html::encode($card['label']) ?></p>
                        <p class="mp-kpi mb-0 <?= $card['class'] ?? '' ?>"><?= $card['value'] ?></p>
                        <p class="mp-caption mb-0"><?= Html::encode($card['unit']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($rows)): ?>
        <section class="card border shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-inbox fs-3 text-body-secondary"></i>
                <div>
                    <h3 class="mp-card-title mb-1">แผนนี้ยังไม่มีรายการ</h3>
                    <p class="small text-body-secondary mb-0">บันทึกแผนที่หน้าจัดทำแผนก่อน แล้วกลับมาดูรายงานเทียบ</p>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="card border shadow-sm overflow-hidden">
            <div class="card-header bg-body-tertiary py-2">
                <h2 class="mp-card-title mb-0">รายการตามแผน · เทียบจัดซื้อจริง</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center small">
                            <th rowspan="2" class="text-start">รายการ</th>
                            <th rowspan="2">หน่วย</th>
                            <th colspan="2">แผนจัดซื้อ</th>
                            <th colspan="2">จัดซื้อจริง</th>
                            <th rowspan="2">ส่วนต่าง<br>มูลค่า</th>
                            <th rowspan="2">%สำเร็จ</th>
                        </tr>
                        <tr class="text-center small">
                            <th>จำนวน</th><th>มูลค่า</th>
                            <th>จำนวน</th><th>มูลค่า</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= Html::encode($row['item_name']) ?></div>
                                    <div class="small text-body-secondary"><?= Html::encode($row['item_code']) ?> · <?= Html::encode($row['category_title']) ?></div>
                                </td>
                                <td class="text-center small"><?= Html::encode($row['unit_name']) ?></td>
                                <td class="text-end"><?= $formatter->asInteger($row['plan_qty']) ?></td>
                                <td class="text-end"><?= $formatter->asDecimal($row['plan_value'], 2) ?></td>
                                <td class="text-end"><?= $formatter->asInteger($row['actual_qty']) ?></td>
                                <td class="text-end"><?= $formatter->asDecimal($row['actual_value'], 2) ?></td>
                                <td class="text-end <?= $varianceClass($row['variance_value']) ?>">
                                    <?= ($row['variance_value'] >= 0 ? '+' : '') . $formatter->asDecimal($row['variance_value'], 2) ?>
                                </td>
                                <td class="text-center"><?= $pctBadge($row['achieve_pct']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td class="text-end" colspan="3">รวม</td>
                            <td class="text-end"><?= $formatter->asDecimal($summary['plan_value'], 2) ?></td>
                            <td></td>
                            <td class="text-end"><?= $formatter->asDecimal($summary['actual_value'], 2) ?></td>
                            <td class="text-end <?= $varianceClass($summary['variance_value']) ?>">
                                <?= ($summary['variance_value'] >= 0 ? '+' : '') . $formatter->asDecimal($summary['variance_value'], 2) ?>
                            </td>
                            <td class="text-center"><?= $pctBadge($summary['achieve_pct']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <?php if (!empty($off_plan)): ?>
            <section class="card border shadow-sm overflow-hidden">
                <div class="card-header bg-warning-subtle py-2 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle text-warning-emphasis"></i>
                    <h2 class="mp-card-title mb-0 text-warning-emphasis">
                        รายการนอกแผน — จัดซื้อจริงแต่ไม่มีในแผน (<?= $formatter->asInteger($summary['off_plan_count']) ?> รายการ · <?= $formatter->asDecimal($summary['off_plan_value'], 2) ?> บาท)
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small">
                                <th class="text-start">รายการ</th>
                                <th class="text-end">จำนวนรับเข้า</th>
                                <th class="text-end">มูลค่ารับเข้า</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($off_plan as $row): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= Html::encode($row['item_name']) ?></div>
                                        <div class="small text-body-secondary"><?= Html::encode($row['item_code']) ?></div>
                                    </td>
                                    <td class="text-end"><?= $formatter->asInteger($row['actual_qty']) ?></td>
                                    <td class="text-end"><?= $formatter->asDecimal($row['actual_value'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</div>
