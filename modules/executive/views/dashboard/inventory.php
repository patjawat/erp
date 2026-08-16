<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $dashboard */

$this->title = 'มิติเจาะลึกมูลค่าคงคลัง';
$summary = $dashboard['summary'];
$risk = $dashboard['risk'];
$this->registerCss(<<<CSS
.executive-inventory .executive-card { border-radius: .75rem; transition: transform .16s ease, box-shadow .16s ease; }
.executive-inventory a.executive-card:hover { transform: translateY(-2px); }
.executive-inventory .metric-icon { width: 2.5rem; height: 2.5rem; }
.executive-inventory .metric-value { font-variant-numeric: tabular-nums; }
.executive-inventory .inventory-value-card { min-height: 132px; }
.executive-inventory .inventory-status-card { min-height: 108px; }
.executive-inventory .warehouse-row { cursor: pointer; }
@media (max-width: 767.98px) { .executive-inventory .desktop-only { display: none; } }
@media (min-width: 768px) { .executive-inventory .mobile-only { display: none; } }
CSS);

$mainCards = array_merge([[
    'label' => 'มูลค่าคงคลังรวม',
    'value' => $summary['value'],
    'icon' => 'bi-buildings',
    'color' => 'primary',
    'note' => 'รวมคลังหลัก MATER ที่มีข้อมูลในระบบ',
]], $summary['mainWarehouses']);
$statusCards = [
    ['label' => 'ต่ำกว่าจุดสั่งซื้อ', 'value' => $risk['critical'], 'icon' => 'bi-exclamation-triangle', 'color' => 'danger', 'note' => 'รายการที่ควรตรวจแผนจัดหา', 'url' => Url::to(['/executive/dashboard/inventory-alerts', 'type' => 'low-stock'])],
    ['label' => 'Lot ใกล้หมดอายุ', 'value' => $risk['expiring'], 'icon' => 'bi-clock', 'color' => 'warning', 'note' => 'ภายใน 90 วัน', 'url' => Url::to(['/executive/dashboard/inventory-alerts', 'type' => 'expiring'])],
    ['label' => 'Lot หมดอายุแล้ว', 'value' => $risk['expired'], 'icon' => 'bi-calendar-x', 'color' => 'danger', 'note' => 'ยังมียอดคงเหลือในระบบ', 'url' => Url::to(['/executive/dashboard/inventory-alerts', 'type' => 'expired'])],
    ['label' => 'อยู่เหนือขั้นต่ำ', 'value' => $risk['sufficient'], 'icon' => 'bi-check-circle', 'color' => 'success', 'note' => 'รายการที่ประเมินว่าเพียงพอ', 'url' => Url::to(['/executive/dashboard/inventory-alerts', 'type' => 'sufficient'])],
];
$warehouseGroups = [
    ['label' => 'คลังย่อยภายในโรงพยาบาล', 'shortLabel' => 'โรงพยาบาล', 'icon' => 'bi-hospital', 'rows' => array_values(array_filter($dashboard['warehouses'], static fn($row) => $row['type'] === 'SUB'))],
    ['label' => 'คลัง รพ.สต.', 'shortLabel' => 'รพ.สต.', 'icon' => 'bi-house-heart', 'rows' => array_values(array_filter($dashboard['warehouses'], static fn($row) => $row['type'] === 'BRANCH'))],
];
?>

<div class="executive-inventory container-fluid py-3 py-lg-4">
    <header class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-end mb-4">
        <div>
            <?= Html::a('<i class="bi bi-arrow-left me-2"></i>แดชบอร์ดบริหาร', ['/executive/dashboard'], ['class' => 'btn btn-outline-secondary btn-sm mb-3']) ?>
            <div class="small text-primary fw-semibold mb-1">ระบบ ERP โรงพยาบาล</div>
            <h1 class="h3 mb-1">มิติเจาะลึกมูลค่าคงคลัง</h1>
            <div class="text-body-secondary">ภาพรวมสำหรับผู้บริหาร · <?= Html::encode($dashboard['snapshotLabel']) ?> · อัปเดต <?= Yii::$app->formatter->asDatetime($dashboard['asOf'], 'php:d/m/Y H:i') ?> น.</div>
        </div>
        <form method="get" class="card executive-card border shadow-sm p-2">
            <label class="d-flex align-items-center gap-2 mb-0">
                <span class="text-body-secondary text-nowrap">ปีงบประมาณ</span>
                <select class="form-select" name="year" onchange="this.form.submit()">
                    <?php foreach ($dashboard['availableYears'] as $year): ?>
                        <option value="<?= (int) $year ?>" <?= (int) $year === (int) $dashboard['selectedFiscalYear'] ? 'selected' : '' ?>><?= (int) $year ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>
    </header>

    <section class="mb-4" aria-labelledby="main-value-heading">
        <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
            <div><h2 id="main-value-heading" class="h5 mb-1">มูลค่าคงคลังหลัก</h2><div class="small text-body-secondary">ไม่รวมคลังย่อย เพื่อให้ยอดระดับบริหารไม่ซ้ำซ้อน</div></div>
            <span class="badge bg-primary-subtle text-primary-emphasis">เฉพาะวัสดุ MATER</span>
        </div>
        <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-xl-5">
            <?php foreach ($mainCards as $card): ?>
                <div class="col">
                    <article class="card executive-card inventory-value-card h-100 border shadow-sm border-<?= Html::encode($card['color']) ?>-subtle">
                        <div class="card-body p-3 d-flex flex-column justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="metric-icon rounded-3 bg-<?= Html::encode($card['color']) ?>-subtle text-<?= Html::encode($card['color']) ?>-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0"><i class="bi <?= Html::encode($card['icon']) ?>"></i></span>
                                <h3 class="h6 mb-0"><?= Html::encode($card['label']) ?></h3>
                            </div>
                            <div><div class="metric-value fs-5 fw-semibold mb-1"><?= $card['value'] === null ? 'N/A' : number_format((float) $card['value'], 2) ?></div><div class="small text-body-secondary text-truncate"><?= Html::encode($card['note'] ?? 'บาท') ?></div></div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($summary['mainWarehouses'][0]['value'] === null): ?>
            <div class="alert bg-warning-subtle border-warning-subtle text-warning-emphasis mt-3 mb-0 small"><i class="bi bi-info-circle me-2"></i>ยังไม่พบแหล่งข้อมูลคลังยาในโมดูลวัสดุ จึงแสดง N/A แทนการคาดเดาตัวเลข ส่วนยอดรวมคำนวณจากคลังหลัก MATER ที่มีข้อมูลจริง</div>
        <?php endif; ?>
    </section>

    <section class="mb-4" aria-labelledby="status-heading">
        <h2 id="status-heading" class="h5 mb-3">สถานะสำคัญของคลัง</h2>
        <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-xl-4">
            <?php foreach ($statusCards as $card): ?>
                <div class="col">
                    <a href="<?= Html::encode($card['url']) ?>" class="card executive-card inventory-status-card h-100 border shadow-sm text-decoration-none text-body">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <span class="metric-icon rounded-3 bg-<?= Html::encode($card['color']) ?>-subtle text-<?= Html::encode($card['color']) ?>-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0"><i class="bi <?= Html::encode($card['icon']) ?> fs-5"></i></span>
                            <div class="flex-grow-1"><div class="d-flex justify-content-between gap-2"><h3 class="h6 mb-1"><?= Html::encode($card['label']) ?></h3><strong class="fs-4 metric-value"><?= number_format((int) $card['value']) ?></strong></div><div class="small text-body-secondary"><?= Html::encode($card['note']) ?></div><div class="small text-primary mt-2">ดูรายละเอียด <i class="bi bi-arrow-right"></i></div></div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="card executive-card border shadow-sm" aria-labelledby="sub-warehouse-heading">
        <div class="card-header bg-body py-3 d-flex flex-column flex-md-row justify-content-between gap-2">
            <div><h2 id="sub-warehouse-heading" class="h5 mb-1">คลังย่อย: คงคลังและยอดเบิกทั้งปี</h2><div class="small text-body-secondary">มูลค่าใบขอเบิกเข้าคลังย่อยสะสมปีงบประมาณ <?= (int) $dashboard['selectedFiscalYear'] ?> นับเฉพาะรายการ REQUEST ที่ยืนยันแล้ว</div></div>
            <span class="small text-body-secondary align-self-md-center">คลิกชื่อคลังเพื่อดูรายการที่ขอเบิก</span>
        </div>
        <div class="table-responsive desktop-only">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>คลังย่อย</th><th>ประเภท</th><th class="text-end">จำนวนรายการคงเหลือ</th><th class="text-end">มูลค่าคงคลัง</th><th class="text-end">มูลค่าเบิกทั้งปี</th><th class="text-end"><span class="visually-hidden">ดูรายละเอียด</span></th></tr></thead>
                <?php foreach ($warehouseGroups as $group): ?>
                    <?php if (!$group['rows']) continue; ?>
                    <tbody>
                        <tr class="table-group-divider"><th colspan="6" class="bg-body-tertiary py-2"><i class="bi <?= Html::encode($group['icon']) ?> me-2 text-primary"></i><?= Html::encode($group['label']) ?><span class="badge bg-secondary-subtle text-secondary-emphasis ms-2"><?= number_format(count($group['rows'])) ?> คลัง</span></th></tr>
                        <?php $currentOrgGroup = null; ?>
                        <?php foreach ($group['rows'] as $warehouse): $url = Url::to(['/executive/dashboard/sub-warehouse', 'id' => $warehouse['id'], 'year' => $dashboard['selectedFiscalYear']]); ?>
                            <?php if ($warehouse['type'] === 'SUB' && $currentOrgGroup !== $warehouse['group_name']): $currentOrgGroup = $warehouse['group_name']; ?>
                                <tr><td colspan="6" class="small fw-semibold text-body-secondary bg-body-tertiary py-2 ps-4"><i class="bi bi-diagram-3 me-2"></i><?= Html::encode($currentOrgGroup) ?></td></tr>
                            <?php endif; ?>
                            <tr class="warehouse-row" onclick="window.location.href='<?= Html::encode($url) ?>'">
                                <td class="ps-4"><?= Html::a(Html::encode($warehouse['display_name']), $url, ['class' => 'fw-semibold text-decoration-none']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode($group['shortLabel']) ?></span></td>
                                <td class="text-end metric-value"><?= number_format((int) $warehouse['item_count']) ?></td>
                                <td class="text-end metric-value"><?= number_format((float) $warehouse['value'], 2) ?></td>
                                <td class="text-end fw-semibold metric-value"><?= number_format((float) $warehouse['usage_value'], 2) ?></td>
                                <td class="text-end"><i class="bi bi-chevron-right text-body-secondary"></i></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="mobile-only">
            <?php foreach ($warehouseGroups as $group): ?>
                <?php if (!$group['rows']) continue; ?>
                <div class="bg-body-tertiary border-top border-bottom px-3 py-2 fw-semibold"><i class="bi <?= Html::encode($group['icon']) ?> me-2 text-primary"></i><?= Html::encode($group['label']) ?><span class="badge bg-secondary-subtle text-secondary-emphasis ms-2"><?= number_format(count($group['rows'])) ?></span></div>
                <div class="list-group list-group-flush">
                    <?php $currentOrgGroup = null; ?>
                    <?php foreach ($group['rows'] as $warehouse): ?>
                        <?php if ($warehouse['type'] === 'SUB' && $currentOrgGroup !== $warehouse['group_name']): $currentOrgGroup = $warehouse['group_name']; ?>
                            <div class="small fw-semibold text-body-secondary bg-body-tertiary border-bottom px-3 py-2"><i class="bi bi-diagram-3 me-2"></i><?= Html::encode($currentOrgGroup) ?></div>
                        <?php endif; ?>
                        <a class="list-group-item list-group-item-action py-3" href="<?= Url::to(['/executive/dashboard/sub-warehouse', 'id' => $warehouse['id'], 'year' => $dashboard['selectedFiscalYear']]) ?>">
                            <div class="d-flex justify-content-between gap-2 mb-2"><strong><?= Html::encode($warehouse['display_name']) ?></strong><i class="bi bi-chevron-right"></i></div>
                            <div class="row g-2 small"><div class="col-6 text-body-secondary">คงคลัง<br><strong class="text-body metric-value"><?= number_format((float) $warehouse['value'], 2) ?> บาท</strong></div><div class="col-6 text-body-secondary">เบิกทั้งปี<br><strong class="text-body metric-value"><?= number_format((float) $warehouse['usage_value'], 2) ?> บาท</strong></div></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
