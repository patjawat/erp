<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $fyBE */
/** @var string $type */
/** @var int $periodNo */
/** @var array $rows */
/** @var array $totals */
/** @var app\modules\am\models\AccountingPeriod|null $selectedPeriod */
/** @var int[] $years */
/** @var app\modules\am\models\AccountingPeriod[] $months */
/** @var string $group */
/** @var array $grouped */

$this->title = 'รายงานค่าเสื่อมราคา';
$typeLabels = ['month' => 'รายเดือน', 'quarter' => 'รายไตรมาส', 'fiscal_year' => 'ปีงบประมาณ'];
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="file-bar-chart"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<button class="btn btn-outline-secondary" onclick="window.print()"><i data-lucide="printer"></i> พิมพ์</button>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">

    <div class="card mb-3">
        <div class="card-body">
            <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
                <div class="col-md-3">
                    <label class="form-label">ปีงบประมาณ (พ.ศ.)</label>
                    <select name="fiscal_year" class="form-select">
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= $y == $fyBE ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                        <?php if (!in_array($fyBE, array_map('intval', $years), true)): ?>
                            <option value="<?= $fyBE ?>" selected><?= $fyBE ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ประเภทรายงาน</label>
                    <select name="type" class="form-select" onchange="document.getElementById('rep-periodno').style.display = this.value==='fiscal_year'?'none':'block'">
                        <?php foreach ($typeLabels as $k => $label): ?>
                            <option value="<?= $k ?>" <?= $type === $k ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3" id="rep-periodno" style="<?= $type === 'fiscal_year' ? 'display:none' : '' ?>">
                    <label class="form-label"><?= $type === 'quarter' ? 'ไตรมาส (1-4)' : 'เดือน' ?></label>
                    <?php if ($type === 'quarter'): ?>
                        <select name="period_no" class="form-select">
                            <?php for ($q = 1; $q <= 4; $q++): ?>
                                <option value="<?= $q ?>" <?= $periodNo === $q ? 'selected' : '' ?>>ไตรมาส <?= $q ?></option>
                            <?php endfor; ?>
                        </select>
                    <?php else: ?>
                        <select name="period_no" class="form-select">
                            <?php foreach ($months as $mp): ?>
                                <option value="<?= $mp->period_no ?>" <?= $periodNo === (int)$mp->period_no ? 'selected' : '' ?>><?= Html::encode($mp->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">มุมมอง</label>
                    <select name="group" class="form-select" onchange="this.form.submit()">
                        <option value="flat" <?= $group === 'flat' ? 'selected' : '' ?>>รายทรัพย์สิน</option>
                        <option value="type_category" <?= $group === 'type_category' ? 'selected' : '' ?>>จัดกลุ่ม ประเภท → หมวด</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <?= Html::submitButton('<i data-lucide="search"></i> แสดงรายงาน', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span class="fw-semibold">
                รายงาน<?= $typeLabels[$type] ?> ปีงบ <?= $fyBE ?>
                <?= $selectedPeriod ? ' — ' . Html::encode($selectedPeriod->name) : '' ?>
            </span>
            <span class="text-muted small">รวม <?= $totals['count'] ?> รายการ</span>
        </div>
        <div class="card-body table-responsive">
        <?php if ($group === 'type_category'): ?>
            <?php if (empty($grouped)): ?>
                <div class="text-center text-muted py-3">ไม่มีข้อมูล (ยังไม่ได้คำนวณ/บันทึกงวดนี้)</div>
            <?php else: ?>
            <table class="table table-sm table-bordered align-middle dep-rep-grouped">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>รหัส</th><th>รายการ</th>
                        <th>วันได้มา</th><th>เริ่มคิด</th><th class="text-end">อายุ(ด)</th><th class="text-end">อัตรา%</th>
                        <th class="text-end">ราคาทุน</th><th class="text-end">ค่าเสื่อมงวด</th>
                        <th class="text-end">ค่าเสื่อมสะสม</th><th class="text-end">มูลค่าสุทธิ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 0; foreach ($grouped as $g): ?>
                        <tr class="dep-grp-type">
                            <td colspan="11"><i data-lucide="folder" style="width:1rem;height:1rem;"></i>
                                ประเภท: <?= Html::encode($g['type_name']) ?>
                                <span class="fw-normal text-muted small">(<?= $g['totals']['count'] ?> รายการ)</span></td>
                        </tr>
                        <?php foreach ($g['categories'] as $cat): ?>
                            <tr class="dep-grp-cat">
                                <td colspan="11" class="ps-4"><i data-lucide="corner-down-right" style="width:.85rem;height:.85rem;"></i> หมวด: <?= Html::encode($cat['category_name']) ?></td>
                            </tr>
                            <?php foreach ($cat['rows'] as $r): $n++; ?>
                                <tr>
                                    <td><?= $n ?></td>
                                    <td><?= Html::encode($r['code']) ?></td>
                                    <td><?= Html::encode($r['asset_name']) ?></td>
                                    <td class="small"><?= $r['receive_date'] ?></td>
                                    <td class="small"><?= $r['depreciation_start_date'] ?></td>
                                    <td class="text-end"><?= $r['useful_life_months'] ?: ((int) $r['useful_life'] * 12 ?: '-') ?></td>
                                    <td class="text-end"><?= $r['depreciation_rate'] !== null ? number_format($r['depreciation_rate'], 2) : '-' ?></td>
                                    <td class="text-end"><?= number_format($r['cost'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['depreciation_period'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['accumulated'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['nbv'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="dep-sub-cat">
                                <td colspan="7" class="text-end">รวมหมวด <?= Html::encode($cat['category_name']) ?> (<?= $cat['totals']['count'] ?>)</td>
                                <td class="text-end"><?= number_format($cat['totals']['cost'], 2) ?></td>
                                <td class="text-end"><?= number_format($cat['totals']['depreciation'], 2) ?></td>
                                <td class="text-end"><?= number_format($cat['totals']['accumulated'], 2) ?></td>
                                <td class="text-end"><?= number_format($cat['totals']['nbv'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="dep-sub-type">
                            <td colspan="7" class="text-end">รวมประเภท <?= Html::encode($g['type_name']) ?> (<?= $g['totals']['count'] ?>)</td>
                            <td class="text-end"><?= number_format($g['totals']['cost'], 2) ?></td>
                            <td class="text-end"><?= number_format($g['totals']['depreciation'], 2) ?></td>
                            <td class="text-end"><?= number_format($g['totals']['accumulated'], 2) ?></td>
                            <td class="text-end"><?= number_format($g['totals']['nbv'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="7" class="text-end">รวมทั้งสิ้น</td>
                        <td class="text-end"><?= number_format($totals['cost'], 2) ?></td>
                        <td class="text-end"><?= number_format($totals['depreciation'], 2) ?></td>
                        <td class="text-end"><?= number_format($totals['accumulated'], 2) ?></td>
                        <td class="text-end"><?= number_format($totals['nbv'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
            <?php endif; ?>
        <?php else: ?>
            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>รหัส</th><th>รายการ</th><th>ประเภท</th><th>หมวด</th>
                        <th>วันได้มา</th><th>เริ่มคิด</th><th class="text-end">อายุ(ด)</th><th class="text-end">อัตรา%</th>
                        <th class="text-end">ราคาทุน</th><th class="text-end">ค่าเสื่อมงวด</th>
                        <th class="text-end">ค่าเสื่อมสะสม</th><th class="text-end">มูลค่าสุทธิ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="13" class="text-center text-muted">ไม่มีข้อมูล (ยังไม่ได้คำนวณ/บันทึกงวดนี้)</td></tr>
                    <?php else: foreach ($rows as $i => $r): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= Html::encode($r['code']) ?></td>
                            <td><?= Html::encode($r['asset_name']) ?></td>
                            <td><?= Html::encode($r['type_name']) ?></td>
                            <td><?= Html::encode($r['category_name']) ?></td>
                            <td class="small"><?= $r['receive_date'] ?></td>
                            <td class="small"><?= $r['depreciation_start_date'] ?></td>
                            <td class="text-end"><?= $r['useful_life_months'] ?: ((int)$r['useful_life'] * 12 ?: '-') ?></td>
                            <td class="text-end"><?= $r['depreciation_rate'] !== null ? number_format($r['depreciation_rate'], 2) : '-' ?></td>
                            <td class="text-end"><?= number_format($r['cost'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['depreciation_period'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['accumulated'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['nbv'], 2) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
                <?php if (!empty($rows)): ?>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="9" class="text-end">รวมทั้งสิ้น</td>
                            <td class="text-end"><?= number_format($totals['cost'], 2) ?></td>
                            <td class="text-end"><?= number_format($totals['depreciation'], 2) ?></td>
                            <td class="text-end"><?= number_format($totals['accumulated'], 2) ?></td>
                            <td class="text-end"><?= number_format($totals['nbv'], 2) ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        <?php endif; ?>
        </div>
    </div>
</div>

<?php
$this->registerCss(<<<CSS
.dep-rep-grouped .dep-grp-type td { background:#e6f1fb; color:#0c447c; font-weight:600; }
.dep-rep-grouped .dep-grp-cat td { background:#f1f5f9; color:#4a5568; }
.dep-rep-grouped .dep-sub-cat td { background:#f7f9fc; font-weight:600; }
.dep-rep-grouped .dep-sub-type td { background:#eef2f7; font-weight:700; border-top:2px solid #cbd5e1; }
.dep-rep-grouped tfoot td { border-top:2px solid #94a3b8; }
CSS);
$this->registerJs("if (window.lucide) { lucide.createIcons(); }");
?>
