<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $filter */
/** @var array $rows */
/** @var array $summary */
/** @var array $warehouses */
/** @var array $departments */
/** @var array $categories */
/** @var array $quarterLabels */
/** @var int $baseYear */
/** @var string $balanceSource */
/** @var array $coverage */
/** @var app\modules\inventoryV2\models\MaterialPlan|null $plan */
/** @var bool $canLock ผู้ใช้มีสิทธิ์เปิด-ปิดค่าแผนหรือไม่ */

$isLocked = $plan !== null && $plan->isLocked();

$this->title = 'จัดทำแผนวัสดุประจำปี';
$this->params['breadcrumbs'][] = ['label' => 'คลังวัสดุ', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'รายงาน', 'url' => ['/inventory-v2/report/material-summary']];
$this->params['breadcrumbs'][] = $this->title;

$formatter = Yii::$app->formatter;
$historyYears = $rows ? array_keys($rows[0]['history']) : [$baseYear - 2, $baseYear - 1, $baseYear];
$isPartialYear = (int) ($coverage['months'] ?? 12) < 12;

$priceSourceLabels = [
    'average' => 'ราคาเฉลี่ยถ่วงน้ำหนักจากการรับเข้าปี ' . $baseYear,
    'latest' => 'ราคารับเข้าครั้งล่าสุด (ปี ' . $baseYear . ' ไม่มีการรับเข้า)',
    'manual' => 'ผู้ใช้กรอกเอง',
    'none' => 'ไม่พบราคารับเข้าย้อนหลัง ต้องกรอกเอง',
];

$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();

$this->beginBlock('sub-title');
echo 'คาดการณ์จากยอดจ่ายจริงปีงบ ' . Html::encode($baseYear) . ' เสนอปริมาณจัดซื้อปีงบ ' . Html::encode($filter['fiscal_year']) . ' แบ่ง 4 ไตรมาส';
$this->endBlock();

$this->beginBlock('page-action');
echo $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'report-material-plan']);
$this->endBlock();

/** จำนวนคอลัมน์ทั้งหมด ใช้กับแถวหัวหมวดและแถวสรุปท้ายตาราง */
$totalColumns = 11 + count($historyYears) + (count($quarterLabels) * 2);
?>

<div class="d-flex flex-column gap-3">
    <section class="card border shadow-sm overflow-hidden">
        <div class="card-header bg-body-tertiary py-2">
            <h2 class="mp-card-title mb-0">เงื่อนไขการจัดทำแผน</h2>
        </div>
        <div class="card-body">
            <?= Html::beginForm(['/inventory-v2/material-plan/index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
                <label class="col-6 col-md-4 col-xl-2">
                    <span class="mp-label">ปีงบประมาณที่จะจัดซื้อ</span>
                    <?= Html::input('number', 'fiscal_year', $filter['fiscal_year'], [
                        'class' => 'form-control',
                        'min' => 2400,
                        'max' => 2800,
                        'step' => 1,
                    ]) ?>
                </label>

                <label class="col-6 col-md-4 col-xl-2">
                    <span class="mp-label">อัตราปรับเพิ่ม/ลด</span>
                    <span class="input-group">
                        <?= Html::input('number', 'growth_pct', $filter['growth_pct'], [
                            'class' => 'form-control',
                            'min' => -100,
                            'max' => 500,
                            'step' => '0.5',
                        ]) ?>
                        <span class="input-group-text">%</span>
                    </span>
                </label>

                <label class="col-12 col-md-4 col-xl-2">
                    <span class="mp-label">หน่วยงานผู้เบิก</span>
                    <?= Html::dropDownList('dept_warehouse_id', $filter['dept_warehouse_id'], $departments, ['class' => 'form-select']) ?>
                </label>

                <label class="col-6 col-md-4 col-xl-2">
                    <span class="mp-label">คลังหลักที่จ่าย</span>
                    <?= Html::dropDownList('warehouse_id', $filter['warehouse_id'], $warehouses, ['class' => 'form-select']) ?>
                </label>

                <label class="col-6 col-md-4 col-xl-2">
                    <span class="mp-label">หมวดวัสดุ</span>
                    <?= Html::dropDownList('category_id', $filter['category_id'], $categories, ['class' => 'form-select']) ?>
                </label>

                <label class="col-12 col-md-4 col-xl-2">
                    <span class="mp-label">ค้นหารายการ</span>
                    <span class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <?= Html::textInput('q', $filter['q'], [
                            'class' => 'form-control',
                            'placeholder' => 'ชื่อหรือรหัสวัสดุ',
                        ]) ?>
                    </span>
                </label>

                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-calculator me-1"></i>คำนวณแผน
                    </button>
                    <a href="<?= Url::to(['/inventory-v2/material-plan/index']) ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>ล้างเงื่อนไข
                    </a>
                </div>
            <?= Html::endForm() ?>
        </div>
    </section>

    <?php if (empty($rows)): ?>
        <section class="card border shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-inbox fs-3 text-body-secondary"></i>
                <div>
                    <h3 class="mp-card-title mb-1">ไม่พบวัสดุที่มีความเคลื่อนไหวในปีงบ <?= Html::encode($baseYear) ?></h3>
                    <p class="small text-body-secondary mb-0">แผนคำนวณจากรายการที่มีการจ่ายออกจริง ลองเปลี่ยนปีงบประมาณ หน่วยงาน หรือหมวดวัสดุ</p>
                </div>
            </div>
        </section>
    <?php else: ?>
        <div class="row g-3" data-plan-summary>
            <?php
            $cards = [
                ['label' => 'วัสดุที่พยากรณ์', 'value' => $formatter->asInteger($summary['item_count']), 'unit' => 'รายการ', 'hook' => 'data-summary-items'],
                ['label' => 'ต้องจัดซื้อ', 'value' => $formatter->asInteger($summary['purchase_count']), 'unit' => 'รายการ', 'hook' => 'data-summary-purchase'],
                ['label' => 'ไม่พบราคาอ้างอิง', 'value' => $formatter->asInteger($summary['no_price_count']), 'unit' => 'ต้องกรอกราคาเอง', 'hook' => 'data-summary-noprice'],
                ['label' => 'มูลค่าจัดซื้อรวม', 'value' => $formatter->asDecimal($summary['plan_value'], 2), 'unit' => 'บาท', 'hook' => 'data-summary-value'],
            ];
            ?>
            <?php foreach ($cards as $card): ?>
                <div class="col-6 col-xl-3">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body py-2 px-3">
                            <p class="mp-label mb-1"><?= Html::encode($card['label']) ?></p>
                            <p class="mp-kpi mb-0" <?= $card['hook'] ?>><?= $card['value'] ?></p>
                            <p class="mp-caption mb-0"><?= Html::encode($card['unit']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?= Html::beginForm(['/inventory-v2/material-plan/export'], 'post', ['data-plan-form' => true]) ?>
            <?php foreach (['fiscal_year', 'growth_pct', 'warehouse_id', 'dept_warehouse_id', 'category_id', 'q'] as $key): ?>
                <?= Html::hiddenInput($key, $filter[$key]) ?>
            <?php endforeach; ?>
            <?= Html::hiddenInput('overrides', '', ['data-plan-overrides' => true]) ?>
            <?= Html::hiddenInput('added_items', '', ['data-plan-added' => true]) ?>

            <section class="card border shadow-sm overflow-hidden">
                <div class="card-header bg-body-tertiary d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                    <div>
                    <h2 class="mp-card-title mb-0">
                        แผนการจัดวัสดุ ปีงบประมาณ <?= Html::encode($filter['fiscal_year']) ?>
                        <?php if ($isPartialYear): ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold ms-1"
                                  title="ปีฐานมีข้อมูลจริง <?= Html::encode($coverage['months']) ?> เดือน ระบบขยายเป็นอัตราเต็มปีให้แล้ว">
                                ข้อมูล <?= Html::encode($coverage['months']) ?> เดือน ปรับเต็มปี ×<?= Html::encode($coverage['factor']) ?>
                            </span>
                        <?php endif; ?>
                    </h2>
                    <?php if ($plan !== null): ?>
                        <p class="mp-caption mb-0">
                            <?= $isLocked
                                ? 'ปิดค่าเมื่อ ' . Html::encode($formatter->asDatetime($plan->locked_at, 'medium'))
                                : 'บันทึกล่าสุด ' . Html::encode($formatter->asDatetime($plan->updated_at, 'medium')) ?>
                            · อัตราเผื่อ <?= Html::encode($plan->growth_pct) ?>%
                            · <?= $formatter->asInteger($plan->item_count) ?> รายการ
                        </p>
                    <?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <?php if ($plan !== null): ?>
                            <span class="badge <?= $plan->isLocked() ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?> fw-semibold">
                                <i class="bi <?= $plan->isLocked() ? 'bi-lock-fill' : 'bi-pencil' ?> me-1"></i><?= Html::encode($plan->statusLabel()) ?>
                            </span>
                        <?php endif; ?>
                        <div class="btn-group btn-group-sm" role="group" aria-label="คอลัมน์ที่แสดง">
                            <input type="checkbox" class="btn-check" id="mp-toggle-history" autocomplete="off" data-plan-toggle="history">
                            <label class="btn btn-outline-secondary" for="mp-toggle-history">ย้อนหลัง 3 ปี</label>
                            <input type="checkbox" class="btn-check" id="mp-toggle-qvalue" autocomplete="off" data-plan-toggle="qvalue">
                            <label class="btn btn-outline-secondary" for="mp-toggle-qvalue">มูลค่ารายไตรมาส</label>
                        </div>
                        <?php if (!$isLocked): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-plan-add-open>
                                <i class="bi bi-plus-lg me-1"></i>เพิ่มวัสดุ
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-plan-reset>
                                <i class="bi bi-arrow-counterclockwise me-1"></i>คืนค่า
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary" formaction="<?= Url::to(['/inventory-v2/material-plan/save']) ?>">
                                <i class="bi bi-save me-1"></i>บันทึกแผน
                            </button>
                        <?php endif; ?>
                        <?php if ($plan !== null && $canLock): ?>
                            <button type="submit" class="btn btn-sm <?= $isLocked ? 'btn-outline-warning' : 'btn-outline-dark' ?>"
                                    formaction="<?= Url::to(['/inventory-v2/material-plan/' . ($isLocked ? 'unlock' : 'lock')]) ?>"
                                    data-plan-confirm="<?= $isLocked
                                        ? 'ปลดล็อกแล้วตัวเลขจะแก้ไขได้อีกครั้ง และจะไม่ตรงกับฉบับที่ส่งไปแล้ว ยืนยันหรือไม่'
                                        : 'ปิดค่าแล้วตัวเลขจะหยุดนิ่ง ใช้เป็นฉบับส่ง สสจ. และเป็นอัตราเผื่อกลางของทั้งระบบ ยืนยันหรือไม่' ?>">
                                <i class="bi <?= $isLocked ? 'bi-unlock' : 'bi-lock' ?> me-1"></i><?= $isLocked ? 'ปลดล็อก' : 'ปิดค่า' ?>
                            </button>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                    </div>
                </div>

                <?php if (!$isLocked): ?>
                <div class="card-body border-bottom py-2 d-none" data-plan-add-panel>
                    <div class="row g-2 align-items-start">
                        <div class="col-12 col-md-6 col-xl-4">
                            <span class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" data-plan-add-input
                                       placeholder="พิมพ์ชื่อหรือรหัสวัสดุที่ต้องการเพิ่มเข้าแผน" autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" data-plan-add-close>ปิด</button>
                            </span>
                            <div class="list-group list-group-flush border rounded mt-1 d-none mp-add-results" data-plan-add-results></div>
                        </div>
                        <div class="col-12 col-xl-8">
                            <p class="mp-caption mb-0">
                                ใช้เพิ่มวัสดุที่ไม่มีการเบิกในปีฐาน จึงไม่ถูกดึงเข้าแผนอัตโนมัติ รายการที่เพิ่มจะเริ่มที่ 0 ให้กรอกปริมาณเอง
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mp-table-wrap">
                    <table class="table table-sm align-middle mb-0 mp-table is-hide-history is-hide-qvalue" data-plan-table>
                        <caption class="visually-hidden">แผนการจัดวัสดุ ปีงบประมาณ <?= Html::encode($filter['fiscal_year']) ?></caption>
                        <thead>
                            <tr>
                                <th rowspan="2" class="mp-sticky mp-col-seq">ลำดับ</th>
                                <th rowspan="2" class="mp-sticky mp-col-code">รหัส</th>
                                <th rowspan="2" class="mp-sticky mp-col-name">รายการสินค้า</th>
                                <th rowspan="2" class="mp-col-unit">หน่วย</th>
                                <th colspan="2" class="mp-num">ใช้จริงปีงบ <?= Html::encode($baseYear) ?></th>
                                <th colspan="<?= count($historyYears) ?>" class="mp-num mp-c-history">ใช้ย้อนหลัง <?= count($historyYears) ?> ปี</th>
                                <th rowspan="2" class="mp-num">ประมาณ<br>การใช้</th>
                                <th rowspan="2" class="mp-num">ยอด<br>คงคลัง</th>
                                <th rowspan="2" class="mp-num">ประมาณ<br>การจัดซื้อ</th>
                                <th rowspan="2" class="mp-num">ราคา<br>ต่อหน่วย</th>
                                <th rowspan="2" class="mp-num">ประมาณ<br>มูลค่า</th>
                                <?php foreach ($quarterLabels as $label): ?>
                                    <?php // colspan ต้องยุบเหลือ 1 เมื่อซ่อนคอลัมน์มูลค่า ไม่งั้นหัวตารางเหลื่อมกับข้อมูล ?>
                                    <th colspan="1" class="mp-num" data-quarter-head><?= Html::encode($label) ?></th>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <th class="mp-num"><?= $isPartialYear ? Html::encode($coverage['months']) . ' เดือน' : 'ทั้งปี' ?></th>
                                <th class="mp-num">เต็มปี</th>
                                <?php foreach ($historyYears as $year): ?>
                                    <th class="mp-num mp-c-history"><?= Html::encode($year) ?></th>
                                <?php endforeach; ?>
                                <?php foreach ($quarterLabels as $index => $label): ?>
                                    <th class="mp-num">จำนวน</th>
                                    <th class="mp-num mp-c-qvalue">มูลค่า</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody data-plan-body>
                            <?php $currentCategory = null; ?>
                            <?php foreach ($rows as $row): ?>
                                <?php if ($row['category_title'] !== $currentCategory): ?>
                                    <?php $currentCategory = $row['category_title']; ?>
                                    <tr class="mp-group">
                                        <th colspan="<?= $totalColumns ?>">
                                            <i class="bi bi-folder2-open me-1"></i>หมวด: <?= Html::encode($currentCategory) ?>
                                        </th>
                                    </tr>
                                <?php endif; ?>
                                <tr data-plan-row
                                    data-item-code="<?= Html::encode($row['item_code']) ?>"
                                    data-opening-qty="<?= Html::encode($row['opening_qty']) ?>">
                                    <td class="mp-sticky mp-col-seq"><?= $formatter->asInteger($row['seq']) ?></td>
                                    <td class="mp-sticky mp-col-code font-monospace" title="<?= Html::encode($row['item_code']) ?>"><?= Html::encode($row['item_code']) ?></td>
                                    <td class="mp-sticky mp-col-name"><?= Html::encode($row['item_name']) ?></td>
                                    <td class="mp-col-unit"><?= Html::encode($row['unit_name']) ?></td>
                                    <td class="mp-num text-body-secondary"><?= $formatter->asDecimal($row['actual_usage'], 2) ?></td>
                                    <td class="mp-num"><?= $formatter->asInteger($row['annual_usage']) ?></td>
                                    <?php foreach ($historyYears as $year): ?>
                                        <td class="mp-num mp-c-history text-body-secondary"><?= $formatter->asInteger($row['history'][$year] ?? 0) ?></td>
                                    <?php endforeach; ?>
                                    <td class="mp-cell-input">
                                        <?= Html::input('number', null, $row['forecast_qty'], [
                                            'class' => 'form-control form-control-sm mp-input',
                                            'step' => '1',
                                            'min' => 0,
                                            'data-plan-input' => 'forecast_qty',
                                            'readonly' => $isLocked,
                                            'aria-label' => 'ประมาณการใช้ ' . $row['item_name'],
                                        ]) ?>
                                    </td>
                                    <td class="mp-num text-body-secondary"><?= $formatter->asDecimal($row['opening_qty'], 2) ?></td>
                                    <td class="mp-cell-input">
                                        <?= Html::input('number', null, $row['plan_qty'], [
                                            'class' => 'form-control form-control-sm mp-input',
                                            'step' => '1',
                                            'min' => 0,
                                            'data-plan-input' => 'plan_qty',
                                            'readonly' => $isLocked,
                                            'aria-label' => 'ประมาณการจัดซื้อ ' . $row['item_name'],
                                        ]) ?>
                                    </td>
                                    <td class="mp-cell-input">
                                        <?= Html::input('number', null, $row['unit_price'], [
                                            'class' => 'form-control form-control-sm mp-input' . ($row['unit_price'] <= 0 ? ' is-invalid' : ''),
                                            'step' => '0.01',
                                            'min' => 0,
                                            'data-plan-input' => 'unit_price',
                                            'readonly' => $isLocked,
                                            'title' => $priceSourceLabels[$row['price_source']] ?? '',
                                            'aria-label' => 'ราคาต่อหน่วย ' . $row['item_name'],
                                        ]) ?>
                                    </td>
                                    <td class="mp-num fw-semibold" data-plan-output="plan_value"><?= $formatter->asDecimal($row['plan_value'], 2) ?></td>
                                    <?php foreach ($quarterLabels as $index => $label): ?>
                                        <td class="mp-cell-input">
                                            <?= Html::input('number', null, $row['quarters'][$index], [
                                                'class' => 'form-control form-control-sm mp-input',
                                                'step' => '1',
                                                'min' => 0,
                                                'data-plan-input' => 'q' . ($index + 1),
                                                'readonly' => $isLocked,
                                                'aria-label' => $label . ' ' . $row['item_name'],
                                            ]) ?>
                                        </td>
                                        <td class="mp-num mp-c-qvalue" data-plan-output="q<?= $index + 1 ?>_value"><?= $formatter->asDecimal($row['quarter_values'][$index], 2) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <?php // แถวสรุปต้องมีเซลล์ครบทุกคอลัมน์ที่ซ่อนได้ ไม่งั้นยอดจะเลื่อนไปคนละคอลัมน์เมื่อสลับการแสดงผล ?>
                            <tr class="mp-group">
                                <td colspan="6" class="text-end fw-semibold">รวมมูลค่าประมาณการ</td>
                                <?php foreach ($historyYears as $year): ?>
                                    <td class="mp-num mp-c-history">&nbsp;</td>
                                <?php endforeach; ?>
                                <td colspan="4" class="mp-num">&nbsp;</td>
                                <td class="mp-num fw-semibold" data-total="plan_value"><?= $formatter->asDecimal($summary['plan_value'], 2) ?></td>
                                <?php foreach ($quarterLabels as $index => $label): ?>
                                    <td class="mp-num">&nbsp;</td>
                                    <td class="mp-num mp-c-qvalue fw-semibold" data-total="q<?= $index + 1 ?>_value"><?= $formatter->asDecimal($summary['quarter_values'][$index], 2) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <?php if (!$isLocked): ?>
            <?php ob_start(); ?>
            <tr data-plan-row data-item-code="{code}" data-opening-qty="{opening}" data-plan-manual>
                <td class="mp-sticky mp-col-seq">+</td>
                <td class="mp-sticky mp-col-code font-monospace" title="{code}">{code}</td>
                <td class="mp-sticky mp-col-name">{name}</td>
                <td class="mp-col-unit">{unit}</td>
                <td class="mp-num text-body-secondary">0.00</td>
                <td class="mp-num">0</td>
                <?php foreach ($historyYears as $year): ?>
                    <td class="mp-num mp-c-history text-body-secondary">0</td>
                <?php endforeach; ?>
                <td class="mp-cell-input"><input type="number" class="form-control form-control-sm mp-input" step="1" min="0" value="0" data-plan-input="forecast_qty" aria-label="ประมาณการใช้ {name}"></td>
                <td class="mp-num text-body-secondary">{openingText}</td>
                <td class="mp-cell-input"><input type="number" class="form-control form-control-sm mp-input" step="1" min="0" value="0" data-plan-input="plan_qty" aria-label="ประมาณการจัดซื้อ {name}"></td>
                <td class="mp-cell-input"><input type="number" class="form-control form-control-sm mp-input" step="0.01" min="0" value="{price}" data-plan-input="unit_price" aria-label="ราคาต่อหน่วย {name}"></td>
                <td class="mp-num fw-semibold" data-plan-output="plan_value">0.00</td>
                <?php foreach ($quarterLabels as $index => $label): ?>
                    <td class="mp-cell-input"><input type="number" class="form-control form-control-sm mp-input" step="1" min="0" value="0" data-plan-input="q<?= $index + 1 ?>" aria-label="<?= Html::encode($label) ?> {name}"></td>
                    <td class="mp-num mp-c-qvalue" data-plan-output="q<?= $index + 1 ?>_value">0.00</td>
                <?php endforeach; ?>
            </tr>
            <?php $rowTemplate = trim(ob_get_clean()); ?>
            <template data-plan-row-template><?= $rowTemplate ?></template>
            <template data-plan-group-template>
                <tr class="mp-group" data-plan-manual-group>
                    <th colspan="<?= $totalColumns ?>"><i class="bi bi-plus-square me-1"></i>รายการที่เพิ่มเอง</th>
                </tr>
            </template>
            <?php endif; ?>
        <?= Html::endForm() ?>
    <?php endif; ?>
</div>

<?php
$this->registerCss(<<<'CSS'
.mp-card-title { font-size: .95rem; font-weight: 600; line-height: 1.3; }
.mp-label { display: block; font-size: .78rem; font-weight: 600; line-height: 1.3; color: var(--bs-secondary-color); margin-bottom: .25rem; }
.mp-kpi { font-size: 1.15rem; font-weight: 600; line-height: 1.25; font-variant-numeric: tabular-nums; }
.mp-caption { font-size: .75rem; line-height: 1.3; color: var(--bs-secondary-color); }

.mp-table-wrap { overflow-x: auto; }
.mp-table { border-collapse: separate; border-spacing: 0; width: auto; min-width: 100%; }
.mp-table > :not(caption) > * > * {
    font-size: .875rem;
    line-height: 1.35;
    padding: .3rem .45rem;
    border-bottom: 1px solid var(--bs-border-color);
    border-right: 1px solid var(--bs-border-color);
    box-shadow: none;
}
.mp-table > :not(caption) > * > *:first-child { border-left: 1px solid var(--bs-border-color); }
.mp-table thead > tr:first-child > * { border-top: 1px solid var(--bs-border-color); }
.mp-table thead th {
    background-color: var(--bs-tertiary-bg);
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}
.mp-table tbody td { background-color: var(--bs-body-bg); }
.mp-table tbody tr:hover td { background-color: var(--bs-tertiary-bg); }
.mp-table .mp-group > * { background-color: var(--bs-secondary-bg); font-weight: 600; }

.mp-num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
.mp-col-seq { width: 2.5rem; min-width: 2.5rem; text-align: center; }
/* รหัสวัสดุยาว 8 ตัวอักษรเกือบทั้งหมด กว้างเท่าที่พอดีจริง ที่เหลือเผื่อให้ชื่อสินค้า
   ส่วนไม่กี่รายการที่รหัสในทะเบียนเป็นข้อความยาวให้ตัดท้ายแล้วดูเต็มได้จาก tooltip */
.mp-col-code {
    width: 5.25rem;
    min-width: 5.25rem;
    max-width: 5.25rem;
    font-size: .8125rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.mp-col-name { min-width: 19rem; }
.mp-col-unit { width: 3.5rem; min-width: 3.5rem; text-align: center; white-space: nowrap; }
.mp-cell-input { padding: .15rem .2rem !important; width: 6rem; min-width: 6rem; }
.mp-input { text-align: right; font-variant-numeric: tabular-nums; padding: .15rem .35rem; font-size: .875rem; }

/* ตรึงคอลัมน์ระบุตัวสินค้าไว้ตอนเลื่อนดูคอลัมน์ขวา — เฉพาะจอกว้างพอที่ยังเหลือที่ให้ข้อมูล */
@media (min-width: 992px) {
    .mp-table .mp-sticky { position: sticky; z-index: 2; }
    .mp-table thead .mp-sticky { z-index: 3; }
    /* ระยะ left มาจากการวัดคอลัมน์จริงตอนโหลด (ดู syncSticky) ค่าใน fallback ใช้เฉพาะก่อน JS ทำงาน */
    .mp-table .mp-col-seq { left: 0; }
    .mp-table .mp-col-code { left: var(--mp-left-code, 2.5rem); }
    .mp-table .mp-col-name { left: var(--mp-left-name, 7.75rem); box-shadow: 2px 0 0 var(--bs-border-color); }
    .mp-table .mp-group > * { position: sticky; left: 0; }
}

.mp-table.is-hide-history .mp-c-history { display: none; }
.mp-table.is-hide-qvalue .mp-c-qvalue { display: none; }
.mp-add-results { max-height: 15rem; overflow-y: auto; }
.mp-add-results .list-group-item { cursor: pointer; font-size: .8125rem; padding: .35rem .6rem; }
CSS);

$quarterCount = count($quarterLabels);
$searchUrl = Url::to(['/inventory-v2/material-plan/search-item']);
$this->registerJs(<<<JS
(function () {
    var form = document.querySelector('[data-plan-form]');
    if (!form) { return; }

    var quarterCount = {$quarterCount};
    var searchUrl = '{$searchUrl}';
    var table = form.querySelector('[data-plan-table]');
    var body = form.querySelector('[data-plan-body]');
    var overridesField = form.querySelector('[data-plan-overrides]');
    var addedField = form.querySelector('[data-plan-added]');
    var overrides = {};
    var added = [];

    var money = function (v) { return v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    var round2 = function (v) { return Math.round(v * 100) / 100; };
    var readNumber = function (input) { var v = parseFloat(input.value); return isNaN(v) || v < 0 ? 0 : v; };
    // จำนวนทุกช่องเป็นจำนวนเต็มหน่วยนับ ปัดขึ้นเพราะสั่งซื้อเศษหน่วยไม่ได้
    var readQty = function (input) { return Math.ceil(readNumber(input)); };

    // แบ่ง 4 ไตรมาสเป็นจำนวนเต็ม เศษลงไตรมาสต้น ผลรวมเท่ากับยอดเดิมพอดี
    var splitQuarters = function (total) {
        var base = Math.floor(total / 4), rest = total % 4, out = [];
        for (var i = 0; i < 4; i++) { out.push(base + (i < rest ? 1 : 0)); }
        return out;
    };

    var recordOverride = function (row, key, value) {
        var code = row.dataset.itemCode;
        if (!overrides[code]) { overrides[code] = {}; }
        overrides[code][key] = value;
        overridesField.value = JSON.stringify(overrides);
    };

    var repaintRow = function (row) {
        var price = readNumber(row.querySelector('[data-plan-input="unit_price"]'));
        var planQty = readQty(row.querySelector('[data-plan-input="plan_qty"]'));
        row.querySelector('[data-plan-output="plan_value"]').textContent = money(round2(planQty * price));
        for (var i = 1; i <= quarterCount; i++) {
            var qty = readQty(row.querySelector('[data-plan-input="q' + i + '"]'));
            row.querySelector('[data-plan-output="q' + i + '_value"]').textContent = money(round2(qty * price));
        }
    };

    var repaintTotals = function () {
        var totals = { value: 0, items: 0, purchase: 0, noPrice: 0, q: [] };
        for (var i = 0; i < quarterCount; i++) { totals.q.push(0); }

        form.querySelectorAll('[data-plan-row]').forEach(function (row) {
            var price = readNumber(row.querySelector('[data-plan-input="unit_price"]'));
            var planQty = readQty(row.querySelector('[data-plan-input="plan_qty"]'));
            totals.items += 1;
            totals.value += planQty * price;
            if (planQty > 0) { totals.purchase += 1; }
            if (price <= 0) { totals.noPrice += 1; }
            for (var i = 1; i <= quarterCount; i++) {
                totals.q[i - 1] += readQty(row.querySelector('[data-plan-input="q' + i + '"]')) * price;
            }
        });

        form.querySelector('[data-total="plan_value"]').textContent = money(round2(totals.value));
        for (var j = 1; j <= quarterCount; j++) {
            form.querySelector('[data-total="q' + j + '_value"]').textContent = money(round2(totals.q[j - 1]));
        }

        var sum = document.querySelector('[data-plan-summary]');
        if (!sum) { return; }
        sum.querySelector('[data-summary-items]').textContent = totals.items.toLocaleString('en-US');
        sum.querySelector('[data-summary-purchase]').textContent = totals.purchase.toLocaleString('en-US');
        sum.querySelector('[data-summary-noprice]').textContent = totals.noPrice.toLocaleString('en-US');
        sum.querySelector('[data-summary-value]').textContent = money(round2(totals.value));
    };

    form.addEventListener('input', function (event) {
        var input = event.target.closest('[data-plan-input]');
        if (!input) { return; }

        var row = input.closest('[data-plan-row]');
        var key = input.dataset.planInput;
        var value = readNumber(input);

        // แก้ประมาณการใช้แล้วให้ประมาณการจัดซื้อกับไตรมาสไหลตามสูตรเดิม
        if (key === 'forecast_qty' || key === 'plan_qty') {
            if (key === 'forecast_qty') {
                var planInput = row.querySelector('[data-plan-input="plan_qty"]');
                var opening = parseFloat(row.dataset.openingQty || '0') || 0;
                var derived = Math.max(Math.ceil(value - opening), 0);
                planInput.value = derived;
                recordOverride(row, 'plan_qty', derived);
            }
            var quarters = splitQuarters(readQty(row.querySelector('[data-plan-input="plan_qty"]')));
            for (var i = 1; i <= quarterCount; i++) {
                var qInput = row.querySelector('[data-plan-input="q' + i + '"]');
                qInput.value = quarters[i - 1];
                recordOverride(row, 'q' + i, quarters[i - 1]);
            }
        }

        if (key === 'unit_price') { input.classList.toggle('is-invalid', value <= 0); }

        recordOverride(row, key, value);
        repaintRow(row);
        repaintTotals();
    });

    // ระยะตรึงคอลัมน์ต้องมาจากความกว้างจริงที่เบราว์เซอร์คำนวณ ไม่ใช่ค่าที่กะไว้ใน CSS
    // เพราะความกว้างขึ้นกับฟอนต์ไทยและข้อความหัวตาราง ถ้าไม่ตรงคอลัมน์จะซ้อนกันตอนเลื่อน
    var syncSticky = function () {
        var head = table.querySelector('thead tr');
        if (!head) { return; }
        var seqWidth = head.children[0].getBoundingClientRect().width;
        var codeWidth = head.children[1].getBoundingClientRect().width;
        table.style.setProperty('--mp-left-code', seqWidth + 'px');
        table.style.setProperty('--mp-left-name', (seqWidth + codeWidth) + 'px');
    };
    syncSticky();
    window.addEventListener('resize', syncSticky);

    form.querySelectorAll('[data-plan-toggle]').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var group = toggle.dataset.planToggle;
            table.classList.toggle('is-hide-' + group, !toggle.checked);

            // หัวไตรมาสคร่อม 2 คอลัมน์ (จำนวน+มูลค่า) ต้องยุบเหลือ 1 เมื่อซ่อนคอลัมน์มูลค่า
            if (group === 'qvalue') {
                table.querySelectorAll('[data-quarter-head]').forEach(function (head) {
                    head.colSpan = toggle.checked ? 2 : 1;
                });
            }
        });
    });

    // ปิดค่า/ปลดล็อกเปลี่ยนสถานะเอกสารที่ส่งออกไปแล้ว ต้องยืนยันก่อน
    form.querySelectorAll('[data-plan-confirm]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            if (button.dataset.confirmed === 'yes') { return; }
            event.preventDefault();
            if (!window.Swal) {
                if (window.confirm(button.dataset.planConfirm)) {
                    button.dataset.confirmed = 'yes';
                    button.click();
                }
                return;
            }
            Swal.fire({
                icon: 'question',
                title: button.textContent.trim(),
                text: button.dataset.planConfirm,
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: false
            }).then(function (result) {
                if (!result.isConfirmed) { return; }
                button.dataset.confirmed = 'yes';
                button.click();
            });
        });
    });

    var resetButton = form.querySelector('[data-plan-reset]');
    if (resetButton) { // ไม่มีปุ่มนี้เมื่อแผนปิดค่าแล้ว
        resetButton.addEventListener('click', function () {
            overrides = {};
            added = [];
            overridesField.value = '';
            addedField.value = '';
            window.location.reload();
        });
    }

    // ---- เพิ่มวัสดุที่ไม่มีความเคลื่อนไหวในปีฐาน ----
    var openAdd = form.querySelector('[data-plan-add-open]');
    var panel = form.querySelector('[data-plan-add-panel]');
    var addInput = form.querySelector('[data-plan-add-input]');
    var results = form.querySelector('[data-plan-add-results]');
    var rowTemplate = form.querySelector('[data-plan-row-template]');
    var groupTemplate = form.querySelector('[data-plan-group-template]');
    var searchTimer = null;

    // แผนที่ปิดค่าแล้วไม่มีปุ่มเพิ่มวัสดุ ข้ามส่วนนี้ไป
    if (!openAdd || !panel || !rowTemplate) { return; }

    var existingCodes = function () {
        var set = {};
        form.querySelectorAll('[data-plan-row]').forEach(function (row) { set[row.dataset.itemCode] = true; });
        return set;
    };

    var closeResults = function () {
        results.classList.add('d-none');
        results.innerHTML = '';
    };

    openAdd.addEventListener('click', function () {
        panel.classList.remove('d-none');
        addInput.focus();
    });
    form.querySelector('[data-plan-add-close]').addEventListener('click', function () {
        panel.classList.add('d-none');
        addInput.value = '';
        closeResults();
    });

    addInput.addEventListener('input', function () {
        window.clearTimeout(searchTimer);
        var term = addInput.value.trim();
        if (term.length < 2) { closeResults(); return; }

        searchTimer = window.setTimeout(function () {
            fetch(searchUrl + '?q=' + encodeURIComponent(term), {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (res) { return res.json(); })
                .then(function (items) {
                    var taken = existingCodes();
                    var available = items.filter(function (item) { return !taken[item.item_code]; });
                    if (!available.length) {
                        results.innerHTML = '<div class="list-group-item text-body-secondary">ไม่พบวัสดุที่ยังไม่อยู่ในแผน</div>';
                        results.classList.remove('d-none');
                        return;
                    }
                    results.innerHTML = available.map(function (item) {
                        return '<button type="button" class="list-group-item list-group-item-action" data-code="' + item.item_code + '">'
                            + '<span class="font-monospace me-2">' + item.item_code + '</span>' + item.item_name
                            + '<span class="mp-caption d-block">' + item.category_title + ' · ' + (item.unit_name || '-')
                            + ' · ราคาอ้างอิง ' + money(item.unit_price) + '</span></button>';
                    }).join('');
                    results.dataset.payload = JSON.stringify(available);
                    results.classList.remove('d-none');
                })
                .catch(function () { closeResults(); });
        }, 250);
    });

    results.addEventListener('click', function (event) {
        var button = event.target.closest('[data-code]');
        if (!button) { return; }

        var items = JSON.parse(results.dataset.payload || '[]');
        var item = items.filter(function (i) { return i.item_code === button.dataset.code; })[0];
        if (!item) { return; }

        if (!body.querySelector('[data-plan-manual-group]')) {
            body.appendChild(groupTemplate.content.cloneNode(true));
        }

        var markup = rowTemplate.innerHTML
            .split('{code}').join(item.item_code)
            .split('{name}').join(item.item_name)
            .split('{unit}').join(item.unit_name || '-')
            .split('{price}').join(item.unit_price)
            .split('{opening}').join('0')
            .split('{openingText}').join('0.00');

        var host = document.createElement('tbody');
        host.innerHTML = markup;
        body.appendChild(host.firstElementChild);

        added.push(item.item_code);
        addedField.value = JSON.stringify(added);
        addInput.value = '';
        closeResults();
        repaintTotals();
    });
})();
JS);
?>
