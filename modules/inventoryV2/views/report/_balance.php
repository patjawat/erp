<?php
/**
 * Shared body: รายงานสรุปยอดคงเหลือตามคลัง
 * รวม toolbar (filter) + summary strip + table + modal ประวัติเคลื่อนไหว
 *
 * @var \yii\web\View $this
 * @var string  $variant            'main' | 'sub' — บอกบทบาทของหน้านี้
 * @var string  $balanceUrl         URL ของหน้านี้ (form action / clear)
 * @var string  $exportUrl          URL ของ export action
 * @var string  $historyUrl         AJAX URL ของ item history (เหมือนเดิมทั้ง main/sub)
 * @var string  $exportHistoryUrl   URL ของ export item history
 *
 * @var int|null $warehouseId
 * @var array    $warehouses        id => name (รวม option 'ทุกคลัง')
 * @var array    $rows
 * @var array    $summary
 * @var array    $categories
 * @var mixed    $categoryId
 * @var mixed    $status
 * @var mixed    $search
 * @var int      $accessibleWarehouseCount
 */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\View;

$variant = $variant ?? 'sub';
$isMainVariant = $variant === 'main';
$hasActiveFilter = !empty($warehouseId) || !empty($categoryId) || !empty($status) || !empty($search);
$placeholderImg = Yii::getAlias('@web') . '/img/placeholder-img.jpg';

// quick filter links — เก็บ filter อื่นไว้ + toggle filter ที่ส่งมา
$buildFilterUrl = function (array $override) use ($balanceUrl) {
    $current = Yii::$app->request->getQueryParams();
    foreach ($override as $k => $v) {
        if ($v === null || $v === '') {
            unset($current[$k]);
        } else {
            $current[$k] = $v;
        }
    }
    return $balanceUrl . (empty($current) ? '' : '?' . http_build_query($current));
};
?>

<div class="container-fluid px-3 px-md-4 bal-page">
    <form method="get"
          action="<?= Html::encode($balanceUrl) ?>"
          id="balance-filter-form"
          class="bal-toolbar"
          role="search"
          aria-label="ตัวกรองยอดคงเหลือ">
        <div class="bal-toolbar__fields">
            <label class="bal-toolbar__field bal-toolbar__field--search">
                <span class="visually-hidden">ค้นหา</span>
                <i class="bi bi-search bal-toolbar__icon" aria-hidden="true"></i>
                <?= Html::input('search', 'search', $search, [
                    'id' => 'balance-search',
                    'class' => 'bal-toolbar__input',
                    'placeholder' => 'ค้นหาชื่อ / รหัส / จำนวน / มูลค่า',
                    'aria-label' => 'ค้นหา',
                    'autocomplete' => 'off',
                ]) ?>
            </label>
            <label class="bal-toolbar__field">
                <span class="visually-hidden">คลัง</span>
                <i class="bi <?= $isMainVariant ? 'bi-building' : 'bi-shop' ?> bal-toolbar__icon" aria-hidden="true"></i>
                <?= Html::dropDownList('warehouse_id', $warehouseId, $warehouses, [
                    'id' => 'balance-warehouse',
                    'class' => 'bal-toolbar__select',
                    'aria-label' => $isMainVariant ? 'คลังหลัก' : 'คลังย่อย',
                ]) ?>
            </label>
            <label class="bal-toolbar__field">
                <span class="visually-hidden">ประเภทวัสดุ</span>
                <i class="bi bi-tag bal-toolbar__icon" aria-hidden="true"></i>
                <?= Html::dropDownList('category_id', $categoryId, $categories, [
                    'id' => 'balance-category',
                    'prompt' => 'ทุกประเภท',
                    'class' => 'bal-toolbar__select',
                    'aria-label' => 'ประเภทวัสดุ',
                ]) ?>
            </label>
            <label class="bal-toolbar__field">
                <span class="visually-hidden">สถานะ</span>
                <i class="bi bi-flag bal-toolbar__icon" aria-hidden="true"></i>
                <?= Html::dropDownList('status', $status, [
                    'below_min' => 'ต่ำกว่า Min',
                    'below_max' => 'ต่ำกว่า Max',
                    'normal' => 'ปกติ (พอดี)',
                ], [
                    'id' => 'balance-status',
                    'prompt' => 'ทุกสถานะ',
                    'class' => 'bal-toolbar__select',
                    'aria-label' => 'สถานะ',
                ]) ?>
            </label>
        </div>
        <div class="bal-toolbar__actions">
            <?php if ($hasActiveFilter): ?>
                <a href="<?= Html::encode($balanceUrl) ?>"
                   class="bal-toolbar__btn"
                   title="ล้างค่าตัวกรอง"
                   aria-label="ล้างค่าตัวกรอง">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                    <span class="d-none d-md-inline">ล้างค่า</span>
                </a>
            <?php endif; ?>
            <?= Html::a(
                '<i class="bi bi-file-earmark-excel" aria-hidden="true"></i><span class="d-none d-md-inline">Excel</span>',
                array_merge([$exportUrl], Yii::$app->request->getQueryParams()),
                [
                    'class' => 'bal-toolbar__btn bal-toolbar__btn--accent',
                    'title' => 'ดาวน์โหลด Excel',
                    'aria-label' => 'ดาวน์โหลด Excel',
                ]
            ) ?>
            <noscript>
                <button type="submit" class="bal-toolbar__btn bal-toolbar__btn--accent">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span>แสดงผล</span>
                </button>
            </noscript>
        </div>
    </form>

    <?php if ($accessibleWarehouseCount === 0): ?>
        <div class="bal-empty-block" role="status">
            <div class="bal-empty-block__icon" aria-hidden="true">
                <i class="bi bi-shield-lock"></i>
            </div>
            <div class="bal-empty-block__title">
                <?= $isMainVariant
                    ? 'คุณยังไม่ได้รับสิทธิ์เป็นเจ้าหน้าที่คลังหลัก'
                    : 'คุณยังไม่ได้รับสิทธิ์เป็นเจ้าหน้าที่คลังย่อย' ?>
            </div>
            <div class="bal-empty-block__caption">
                ติดต่อผู้ดูแลระบบเพื่อเพิ่มสิทธิ์ของคุณในหน้าตั้งค่าคลังสินค้า
            </div>
        </div>
    <?php else: ?>
        <?php
            $itemsLabel = number_format($summary['items_count']);
            $belowMinCount = (int) $summary['below_min_count'];
            $belowMaxCount = (int) $summary['below_max_count'];
            $statusMinUrl = $buildFilterUrl(['status' => $status === 'below_min' ? null : 'below_min']);
            $statusMaxUrl = $buildFilterUrl(['status' => $status === 'below_max' ? null : 'below_max']);
        ?>
        <ul class="bal-summary" aria-label="สรุปยอดคงเหลือ">
            <li class="bal-summary__item">
                <span class="bal-summary__label">มูลค่ารวม</span>
                <span class="bal-summary__value bal-summary__value--primary">
                    <?= number_format($summary['total_value'], 2) ?>
                    <span class="bal-summary__unit">฿</span>
                </span>
            </li>
            <li class="bal-summary__item">
                <span class="bal-summary__label">จำนวนรายการ</span>
                <span class="bal-summary__value"><?= $itemsLabel ?></span>
            </li>
            <li>
                <a href="<?= Html::encode($statusMinUrl) ?>"
                   class="bal-summary__item bal-summary__item--filter bal-summary__item--min <?= $status === 'below_min' ? 'is-active' : '' ?> <?= $belowMinCount > 0 ? 'has-count' : '' ?>"
                   title="กรองเฉพาะรายการต่ำกว่า Min">
                    <span class="bal-summary__label">
                        <i class="bi bi-exclamation-triangle-fill bal-summary__icon" aria-hidden="true"></i>
                        ต่ำกว่า Min
                    </span>
                    <span class="bal-summary__value"><?= number_format($belowMinCount) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= Html::encode($statusMaxUrl) ?>"
                   class="bal-summary__item bal-summary__item--filter bal-summary__item--max <?= $status === 'below_max' ? 'is-active' : '' ?> <?= $belowMaxCount > 0 ? 'has-count' : '' ?>"
                   title="กรองเฉพาะรายการต่ำกว่า Max">
                    <span class="bal-summary__label">
                        <i class="bi bi-exclamation-circle bal-summary__icon" aria-hidden="true"></i>
                        ต่ำกว่า Max
                    </span>
                    <span class="bal-summary__value"><?= number_format($belowMaxCount) ?></span>
                </a>
            </li>
        </ul>

        <?php if (empty($rows)): ?>
            <div class="bal-empty-block" role="status">
                <div class="bal-empty-block__icon" aria-hidden="true">
                    <i class="bi bi-inboxes"></i>
                </div>
                <div class="bal-empty-block__title">ไม่พบรายการที่ตรงตัวกรอง</div>
                <div class="bal-empty-block__caption">
                    <?php if ($hasActiveFilter): ?>
                        ลองปรับ <strong>คลัง / ประเภท / สถานะ / คำค้น</strong> หรือ
                        <a href="<?= Html::encode($balanceUrl) ?>">ล้างค่าตัวกรอง</a>
                    <?php else: ?>
                        ยังไม่มีรายการรับเข้าใน<?= $isMainVariant ? 'คลังหลัก' : 'คลังย่อย' ?>ที่คุณเข้าถึงได้
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm bal-table-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 bal-table">
                            <thead>
                                <tr>
                                    <th class="bal-th-num">#</th>
                                    <th>คลัง</th>
                                    <th>วัสดุ</th>
                                    <th class="text-center bal-th-cat">ประเภท</th>
                                    <th class="text-center bal-th-unit">หน่วย</th>
                                    <th class="text-end bal-th-qty">คงเหลือ</th>
                                    <th class="text-end bal-th-val">มูลค่า</th>
                                    <th class="text-center bal-th-mm">Min / Max</th>
                                    <th class="text-center bal-th-status">สถานะ</th>
                                    <th class="text-center bal-th-action">ประวัติ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $i => $r): ?>
                                    <?php
                                        $rowClass = '';
                                        if ($r['below_min']) {
                                            $rowClass = 'is-danger';
                                        } elseif ($r['below_max']) {
                                            $rowClass = 'is-warning';
                                        }
                                    ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td class="text-center bal-cell-num"><?= $i + 1 ?></td>
                                        <td class="bal-cell-warehouse">
                                            <span class="bal-warehouse-name" title="<?= Html::encode($r['warehouse_name']) ?>">
                                                <?= Html::encode($r['warehouse_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="bal-item">
                                                <img src="<?= Html::encode($r['image_url']) ?>"
                                                     alt=""
                                                     class="bal-item__thumb"
                                                     loading="lazy"
                                                     onerror="this.onerror=null;this.src='<?= $placeholderImg ?>';">
                                                <div class="bal-item__text">
                                                    <span class="bal-item__name"><?= Html::encode($r['item_name']) ?></span>
                                                    <span class="bal-item__code"><?= Html::encode($r['item_code']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center bal-cell-cat"><?= Html::encode($r['category_title']) ?></td>
                                        <td class="text-center bal-cell-unit"><?= Html::encode($r['unit_name']) ?></td>
                                        <td class="text-end bal-cell-qty"><?= number_format($r['balance_qty'], 2) ?></td>
                                        <td class="text-end bal-cell-val"><?= number_format($r['value'], 2) ?></td>
                                        <td class="text-center bal-cell-mm">
                                            <?= $r['min_qty'] !== null ? number_format($r['min_qty'], 0) : '<span class="bal-empty">—</span>' ?>
                                            <span class="bal-sep">/</span>
                                            <?= $r['max_qty'] !== null ? number_format($r['max_qty'], 0) : '<span class="bal-empty">—</span>' ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($r['below_min']): ?>
                                                <span class="bal-badge bal-badge--danger">ต่ำกว่า Min</span>
                                            <?php elseif ($r['below_max']): ?>
                                                <span class="bal-badge bal-badge--warning">ต่ำกว่า Max</span>
                                            <?php else: ?>
                                                <span class="bal-badge bal-badge--ok">พอดี</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="bal-history-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#itemHistoryModal"
                                                    data-item-code="<?= Html::encode($r['item_code']) ?>"
                                                    data-item-name="<?= Html::encode($r['item_name']) ?>"
                                                    data-unit-name="<?= Html::encode($r['unit_name']) ?>"
                                                    data-item-image="<?= Html::encode($r['image_url']) ?>"
                                                    data-warehouse-id="<?= (int) $r['warehouse_id'] ?>"
                                                    data-warehouse-name="<?= Html::encode($r['warehouse_name']) ?>"
                                                    aria-label="ดูประวัติเคลื่อนไหวของ <?= Html::encode($r['item_name']) ?> ที่ <?= Html::encode($r['warehouse_name']) ?>">
                                                <i class="bi bi-clock-history" aria-hidden="true"></i>
                                                <span>ดูประวัติ</span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal: ประวัติการเคลื่อนไหววัสดุ -->
<div class="modal fade" id="itemHistoryModal" tabindex="-1" aria-labelledby="itemHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow bal-history-modal">
            <div class="modal-header bal-history-modal__head align-items-start">
                <div class="flex-grow-1 pe-3 d-flex gap-3">
                    <img id="hist-thumb" src="<?= $placeholderImg ?>"
                         alt="" class="bal-history-modal__thumb"
                         onerror="this.onerror=null;this.src='<?= $placeholderImg ?>';">
                    <div class="flex-grow-1">
                        <h5 class="modal-title bal-history-modal__title" id="itemHistoryModalLabel">
                            <i class="bi bi-clock-history" aria-hidden="true"></i>
                            ประวัติการเคลื่อนไหววัสดุ
                        </h5>
                        <div class="bal-history-modal__meta">
                            <span><i class="bi bi-upc-scan" aria-hidden="true"></i><strong id="hist-item-code">-</strong></span>
                            <span><i class="bi bi-box" aria-hidden="true"></i><strong id="hist-item-name">-</strong></span>
                            <span><i class="bi bi-building" aria-hidden="true"></i><strong id="hist-warehouse">-</strong></span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>

            <div class="modal-body">
                <form id="historyFilterForm" class="bal-history-filter">
                    <label class="bal-history-filter__field">
                        <span class="bal-history-filter__label">เริ่ม</span>
                        <input type="date" id="hist-start-date" name="start_date" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
                    </label>
                    <label class="bal-history-filter__field">
                        <span class="bal-history-filter__label">ถึง</span>
                        <input type="date" id="hist-end-date" name="end_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                    </label>
                    <button type="submit" class="bal-history-filter__btn">
                        <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>โหลดประวัติ
                    </button>
                </form>

                <ul class="bal-history-stats" aria-label="สรุปยอดเคลื่อนไหว">
                    <li class="bal-history-stat">
                        <span class="bal-history-stat__label">ยอดยกมา</span>
                        <span class="bal-history-stat__value" id="hist-bf-qty">0</span>
                        <span class="bal-history-stat__sub">มูลค่า <span id="hist-bf-value">0.00</span> ฿</span>
                    </li>
                    <li class="bal-history-stat">
                        <span class="bal-history-stat__label">เคลื่อนไหวในช่วงเวลา</span>
                        <span class="bal-history-stat__value bal-history-stat__value--inout">
                            <span class="in" id="hist-total-in">+0</span>
                            <span class="sep">/</span>
                            <span class="out" id="hist-total-out">-0</span>
                        </span>
                        <span class="bal-history-stat__sub"><span id="hist-tx-count">0</span> รายการ</span>
                    </li>
                    <li class="bal-history-stat bal-history-stat--primary">
                        <span class="bal-history-stat__label">ยอดคงเหลือปัจจุบัน</span>
                        <span class="bal-history-stat__value" id="hist-current-qty">0</span>
                        <span class="bal-history-stat__sub"><span id="hist-unit-name">หน่วย</span></span>
                    </li>
                </ul>

                <div class="table-responsive bal-history-table-wrap">
                    <table class="table align-middle mb-0 bal-history-table">
                        <thead>
                            <tr>
                                <th class="text-nowrap">วัน/เวลา</th>
                                <th class="text-nowrap">เลขที่เอกสาร</th>
                                <th>รายการ</th>
                                <th class="text-center text-nowrap">ทิศทาง</th>
                                <th class="text-end text-nowrap">จำนวน</th>
                                <th class="text-end text-nowrap">ราคา/หน่วย</th>
                                <th class="text-end text-nowrap">ยอดสะสม</th>
                                <th class="text-end text-nowrap d-none d-md-table-cell">มูลค่าสะสม</th>
                                <th class="text-nowrap d-none d-md-table-cell">Lot</th>
                            </tr>
                        </thead>
                        <tbody id="hist-tbody">
                            <tr><td colspan="9" class="text-center bal-history-empty">เลือกช่วงเวลาแล้วกด "โหลดประวัติ"</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <span class="bal-history-modal__hint me-auto">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    นับเฉพาะเอกสารที่ยืนยันแล้ว (CONFIRMED)
                </span>
                <button type="button" class="btn btn-success btn-sm" id="hist-export-btn" disabled>
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                </button>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<style>
.bal-page {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #718096;
    --ink-4: #a0aec0;
    --surface: #ffffff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, 0.08);
    --line-strong: rgba(15, 23, 42, 0.14);
    --primary: #0d6efd;
    --primary-ink: #0a58ca;
    --primary-soft: rgba(13, 110, 253, 0.08);
    --primary-line: rgba(13, 110, 253, 0.22);
    --success: #15803d;
    --success-soft: rgba(21, 128, 61, 0.10);
    --warning: #b45309;
    --warning-soft: rgba(180, 83, 9, 0.10);
    --warning-line: rgba(180, 83, 9, 0.22);
    --danger: #b91c1c;
    --danger-soft: rgba(185, 28, 28, 0.10);
    --danger-line: rgba(185, 28, 28, 0.22);
    --radius: 10px;
    --radius-sm: 8px;
    --radius-xs: 6px;
    --shadow-1: 0 1px 2px rgba(15,23,42,0.04), 0 1px 1px rgba(15,23,42,0.03);
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    --t-fast: 120ms;
    --t-mid: 180ms;
    padding-top: 0.75rem;
    padding-bottom: 2rem;
}

/* === Toolbar === */
.bal-toolbar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    padding: 0.5rem 0;
    margin-bottom: 0.75rem;
    border-bottom: 1px solid var(--line);
}
.bal-toolbar__fields {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex: 1 1 auto;
    min-width: 0;
    flex-wrap: wrap;
}
.bal-toolbar__field {
    position: relative;
    display: inline-flex;
    align-items: center;
    min-width: 0;
    flex: 0 1 auto;
    margin: 0;
}
.bal-toolbar__icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--ink-3);
    font-size: 0.95rem;
    pointer-events: none;
}
.bal-toolbar__field--search { flex: 1 1 240px; min-width: 200px; }
.bal-toolbar__input,
.bal-toolbar__select {
    appearance: none;
    -webkit-appearance: none;
    width: 100%;
    min-height: 38px;
    padding: 0 0.85rem 0 2.2rem;
    background-color: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--ink-1);
    transition: border-color var(--t-fast) var(--ease), background-color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
}
.bal-toolbar__input::placeholder { color: var(--ink-3); font-weight: 400; }
.bal-toolbar__input:hover,
.bal-toolbar__select:hover { border-color: var(--ink-3); }
.bal-toolbar__select:hover { background-color: var(--surface-hover); }
.bal-toolbar__input:focus-visible,
.bal-toolbar__select:focus-visible {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.bal-toolbar__select {
    padding-right: 2.1rem;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%23718096' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='m4 6 4 4 4-4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.7rem center;
    background-size: 14px;
    cursor: pointer;
    max-width: 100%;
}
.bal-toolbar__actions {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    flex-shrink: 0;
}
.bal-toolbar__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0 0.85rem;
    min-height: 38px;
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    color: var(--ink-2);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease), color var(--t-fast) var(--ease);
}
.bal-toolbar__btn:hover {
    background: var(--surface-hover);
    color: var(--ink-1);
    border-color: var(--ink-3);
}
.bal-toolbar__btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--primary-soft);
    color: var(--ink-1);
}
.bal-toolbar__btn i { font-size: 0.95rem; line-height: 1; }
.bal-toolbar__btn--accent {
    color: var(--success);
    border-color: rgba(21, 128, 61, 0.22);
}
.bal-toolbar__btn--accent:hover {
    background: var(--success-soft);
    color: var(--success);
    border-color: rgba(21, 128, 61, 0.35);
}

/* === Summary strip === */
.bal-summary {
    list-style: none;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.5rem;
    padding: 0;
    margin: 0 0 0.85rem;
}
.bal-summary > li { margin: 0; }
.bal-summary__item {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    padding: 0.65rem 0.9rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    text-decoration: none;
    transition: border-color var(--t-fast) var(--ease), background-color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
}
.bal-summary__item--filter {
    cursor: pointer;
    color: inherit;
}
.bal-summary__item--filter:hover {
    border-color: var(--line-strong);
    background: var(--surface-2);
}
.bal-summary__item--filter:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--primary-soft);
    border-color: var(--primary);
}
.bal-summary__item.is-active {
    border-color: var(--primary-line);
    background: var(--primary-soft);
}
.bal-summary__label {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-2);
    line-height: 1.2;
}
.bal-summary__icon {
    font-size: 0.85rem;
    color: var(--ink-3);
}
.bal-summary__value {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--ink-1);
    font-variant-numeric: tabular-nums;
    line-height: 1.15;
    display: inline-flex;
    align-items: baseline;
    gap: 0.25rem;
}
.bal-summary__value--primary { color: var(--ink-1); }
.bal-summary__unit {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--ink-3);
}
/* min/max items — default ink-3, semantic เมื่อมีจำนวน > 0 */
.bal-summary__item--min .bal-summary__value,
.bal-summary__item--max .bal-summary__value { color: var(--ink-3); }
.bal-summary__item--min.has-count .bal-summary__value,
.bal-summary__item--min.has-count .bal-summary__icon { color: var(--danger); }
.bal-summary__item--max.has-count .bal-summary__value,
.bal-summary__item--max.has-count .bal-summary__icon { color: var(--warning); }

/* === Table === */
.bal-table-card { border-radius: var(--radius); overflow: hidden; }
.bal-table {
    font-size: 0.88rem;
    color: var(--ink-1);
}
.bal-table thead th {
    position: sticky;
    top: 0;
    background: var(--surface-2);
    border-bottom: 1px solid var(--line-strong);
    color: var(--ink-2);
    font-weight: 600;
    font-size: 0.78rem;
    padding: 0.55rem 0.85rem;
    white-space: nowrap;
    z-index: 1;
}
.bal-table tbody td {
    padding: 0.55rem 0.85rem;
    vertical-align: middle;
    border-color: var(--line);
}
.bal-table tbody tr {
    transition: background-color var(--t-fast) var(--ease);
}
.bal-table tbody tr:hover { background-color: var(--surface-hover); }
.bal-table tbody tr.is-warning { background-color: rgba(180, 83, 9, 0.045); }
.bal-table tbody tr.is-warning:hover { background-color: rgba(180, 83, 9, 0.08); }
.bal-table tbody tr.is-danger { background-color: rgba(185, 28, 28, 0.045); }
.bal-table tbody tr.is-danger:hover { background-color: rgba(185, 28, 28, 0.08); }

.bal-th-num { width: 3rem; text-align: center; }
.bal-th-cat, .bal-th-unit { width: 6rem; }
.bal-th-qty, .bal-th-val { text-align: right; }
.bal-th-mm { width: 7rem; }
.bal-th-status { width: 7rem; }
.bal-th-action { width: 7rem; }

.bal-cell-num { color: var(--ink-3); font-variant-numeric: tabular-nums; }
.bal-cell-cat, .bal-cell-unit { color: var(--ink-3); font-size: 0.82rem; }
.bal-cell-qty, .bal-cell-val { font-weight: 600; font-variant-numeric: tabular-nums; }
.bal-cell-mm { color: var(--ink-3); font-size: 0.82rem; font-variant-numeric: tabular-nums; line-height: 1.2; }
.bal-sep { color: var(--ink-4); padding: 0 0.25rem; }
.bal-empty { color: var(--ink-4); }

.bal-cell-warehouse {
    max-width: 12rem;
}
.bal-warehouse-name {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--ink-2);
    font-size: 0.84rem;
}

.bal-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    min-width: 14rem;
}
.bal-item__thumb {
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: var(--radius-sm);
    object-fit: cover;
    background: var(--surface-3);
    border: 1px solid var(--line);
    transition: transform var(--t-mid) var(--ease);
}
.bal-table tbody tr:hover .bal-item__thumb { transform: scale(1.03); }
.bal-item__text { line-height: 1.2; }
.bal-item__name {
    display: block;
    color: var(--ink-1);
    font-weight: 600;
    word-break: break-word;
}
.bal-item__code {
    display: block;
    margin-top: 0.1rem;
    color: var(--ink-3);
    font-size: 0.72rem;
    font-variant-numeric: tabular-nums;
}

/* === Badge === */
.bal-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 600;
    line-height: 1.4;
}
.bal-badge--ok { background: var(--success-soft); color: var(--success); }
.bal-badge--warning { background: var(--warning-soft); color: var(--warning); }
.bal-badge--danger { background: var(--danger-soft); color: var(--danger); }

/* === History button === */
.bal-history-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.6rem;
    background: var(--primary-soft);
    border: 1px solid var(--primary-line);
    border-radius: var(--radius-sm);
    color: var(--primary-ink);
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.3;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color var(--t-fast) var(--ease), color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
}
.bal-history-btn:hover {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.bal-history-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.bal-history-btn i { font-size: 0.85rem; }

/* === Empty block === */
.bal-empty-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 3rem 1.5rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    text-align: center;
}
.bal-empty-block__icon {
    width: 56px;
    height: 56px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: var(--surface-3);
    color: var(--ink-3);
    font-size: 1.5rem;
}
.bal-empty-block__title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--ink-1);
}
.bal-empty-block__caption {
    font-size: 0.86rem;
    color: var(--ink-3);
    max-width: 32rem;
}
.bal-empty-block__caption a {
    color: var(--primary-ink);
    text-decoration: none;
    font-weight: 600;
}
.bal-empty-block__caption a:hover { text-decoration: underline; }

/* === Modal === */
.bal-history-modal { border-radius: var(--radius); overflow: hidden; }
#itemHistoryModal .modal-dialog {
    transition: transform var(--t-mid) var(--ease), opacity var(--t-fast) var(--ease);
}
#itemHistoryModal:not(.show) .modal-dialog {
    transform: translateY(8px) scale(0.985);
    opacity: 0;
}
#itemHistoryModal.show .modal-dialog {
    transform: translateY(0) scale(1);
    opacity: 1;
}
.bal-history-modal__head {
    background: var(--surface) !important;
    color: var(--ink-1) !important;
    border-bottom: 1px solid var(--line) !important;
    padding: 1rem 1.25rem;
}
.bal-history-modal__thumb {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-sm);
    object-fit: cover;
    background: var(--surface-3);
    border: 1px solid var(--line);
    flex-shrink: 0;
}
.bal-history-modal__title {
    margin-bottom: 0.25rem;
    color: var(--ink-1) !important;
    font-size: 1.05rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.bal-history-modal__title i { color: var(--primary); font-size: 1rem; }
.bal-history-modal__meta {
    display: flex;
    flex-wrap: wrap;
    column-gap: 1.25rem;
    row-gap: 0.15rem;
    font-size: 0.82rem;
    color: var(--ink-2);
}
.bal-history-modal__meta i {
    color: var(--primary);
    margin-right: 0.3rem;
}
.bal-history-modal__meta strong { color: var(--ink-1); font-weight: 600; }
.bal-history-modal__hint {
    font-size: 0.78rem;
    color: var(--ink-3);
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

/* === History filter === */
.bal-history-filter {
    display: flex;
    align-items: end;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}
.bal-history-filter__field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 9rem;
    flex: 1 1 9rem;
    margin: 0;
}
.bal-history-filter__label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-2);
}
.bal-history-filter__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.45rem 0.95rem;
    background: var(--primary);
    border: 1px solid var(--primary);
    border-radius: var(--radius-sm);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
}
.bal-history-filter__btn:hover {
    background: var(--primary-ink);
    border-color: var(--primary-ink);
}
.bal-history-filter__btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--primary-soft);
}

/* === History stats === */
.bal-history-stats {
    list-style: none;
    padding: 0;
    margin: 0 0 1rem;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.5rem;
}
.bal-history-stat {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    padding: 0.7rem 0.95rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
}
.bal-history-stat--primary {
    background: var(--primary-soft);
    border-color: var(--primary-line);
}
.bal-history-stat__label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-2);
}
.bal-history-stat__value {
    font-size: 1.45rem;
    font-weight: 700;
    color: var(--ink-1);
    line-height: 1.1;
    font-variant-numeric: tabular-nums;
}
.bal-history-stat--primary .bal-history-stat__value { color: var(--primary-ink); }
.bal-history-stat__value--inout {
    display: flex;
    gap: 0.35rem;
    align-items: baseline;
}
.bal-history-stat__value--inout .in { color: var(--success); }
.bal-history-stat__value--inout .out { color: var(--danger); }
.bal-history-stat__value--inout .sep { color: var(--ink-4); font-weight: 400; font-size: 1rem; }
.bal-history-stat__sub {
    font-size: 0.78rem;
    color: var(--ink-3);
    font-variant-numeric: tabular-nums;
}

/* === History table === */
.bal-history-table-wrap { border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; }
.bal-history-table { font-size: 0.86rem; }
.bal-history-table thead th {
    font-weight: 600;
    font-size: 0.76rem;
    color: var(--ink-2);
    background: var(--surface-2);
    border-bottom: 1px solid var(--line);
    padding: 0.5rem 0.75rem;
}
.bal-history-table tbody td {
    font-variant-numeric: tabular-nums;
    padding: 0.5rem 0.75rem;
    border-color: var(--line);
}
.bal-history-table .direction-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
}
.bal-history-table .direction-pill.in { background: var(--success-soft); color: var(--success); }
.bal-history-table .direction-pill.out { background: var(--danger-soft); color: var(--danger); }
.bal-history-table tr.bf-row { background: var(--surface-2); font-weight: 600; color: var(--ink-1); }
.bal-history-empty { color: var(--ink-3); padding: 1.25rem 0; }

@keyframes bal-skel {
    0%   { background-position: 0% 50%; }
    100% { background-position: 100% 50%; }
}
.bal-skeleton {
    height: 12px;
    border-radius: var(--radius-xs);
    background: linear-gradient(90deg, rgba(15,23,42,0.05) 0%, rgba(15,23,42,0.1) 40%, rgba(15,23,42,0.05) 80%);
    background-size: 200% 100%;
    animation: bal-skel 1.1s ease-in-out infinite;
}

/* === Responsive === */
@media (max-width: 991.98px) {
    .bal-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767.98px) {
    .bal-toolbar__field { flex: 1 1 auto; min-width: 130px; }
    .bal-toolbar__actions { flex: 1 1 100%; justify-content: flex-end; }
    .bal-history-stats { grid-template-columns: 1fr; }
    .bal-cell-warehouse { max-width: 9rem; }
}

@media (prefers-reduced-motion: reduce) {
    .bal-toolbar__input,
    .bal-toolbar__select,
    .bal-toolbar__btn,
    .bal-summary__item,
    .bal-table tbody tr,
    .bal-item__thumb,
    .bal-history-btn,
    #itemHistoryModal .modal-dialog,
    .bal-history-filter__btn { transition: none !important; }
    .bal-skeleton { animation: none; background: rgba(15,23,42,0.08); }
}
</style>

<?php
$jsHistoryUrl = Json::encode($historyUrl);
$jsExportHistoryUrl = Json::encode($exportHistoryUrl);
$js = <<<JS
(function () {
    var modalEl = document.getElementById('itemHistoryModal');
    if (!modalEl) return;

    var ctx = { item_code: null, warehouse_id: null, unit_name: '-' };
    var exportBtn = document.getElementById('hist-export-btn');
    var fmt = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var fmtInt = new Intl.NumberFormat('th-TH', { maximumFractionDigits: 0 });

    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function skeletonRows() {
        var tbody = document.getElementById('hist-tbody');
        if (!tbody) return;
        var rows = '';
        for (var i = 0; i < 5; i++) {
            rows += '<tr>' +
                '<td><div class="bal-skeleton" style="width:80%"></div></td>' +
                '<td><div class="bal-skeleton" style="width:60%"></div></td>' +
                '<td><div class="bal-skeleton" style="width:90%"></div></td>' +
                '<td class="text-center"><div class="bal-skeleton mx-auto" style="width:50%"></div></td>' +
                '<td class="text-end"><div class="bal-skeleton ms-auto" style="width:50%"></div></td>' +
                '<td class="text-end"><div class="bal-skeleton ms-auto" style="width:55%"></div></td>' +
                '<td class="text-end"><div class="bal-skeleton ms-auto" style="width:55%"></div></td>' +
                '<td class="text-end d-none d-md-table-cell"><div class="bal-skeleton ms-auto" style="width:60%"></div></td>' +
                '<td class="d-none d-md-table-cell"><div class="bal-skeleton" style="width:40%"></div></td>' +
                '</tr>';
        }
        tbody.innerHTML = rows;
    }

    function escHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
        });
    }

    function loadHistory() {
        if (!ctx.item_code || !ctx.warehouse_id) return;
        var sd = document.getElementById('hist-start-date').value;
        var ed = document.getElementById('hist-end-date').value;
        skeletonRows();
        if (exportBtn) exportBtn.disabled = true;

        var params = new URLSearchParams({
            item_code: ctx.item_code,
            warehouse_id: ctx.warehouse_id,
            start_date: sd,
            end_date: ed
        });

        fetch($jsHistoryUrl + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (res) {
                render(res);
                if (exportBtn) exportBtn.disabled = false;
            })
            .catch(function () {
                var tbody = document.getElementById('hist-tbody');
                tbody.innerHTML = '<tr><td colspan="9" class="text-center bal-history-empty">' +
                    '<i class="bi bi-exclamation-triangle me-1"></i>โหลดข้อมูลไม่สำเร็จ ลองอีกครั้ง</td></tr>';
            });
    }

    function exportExcel() {
        if (!ctx.item_code || !ctx.warehouse_id) return;
        var sd = document.getElementById('hist-start-date').value;
        var ed = document.getElementById('hist-end-date').value;
        var params = new URLSearchParams({
            item_code: ctx.item_code,
            warehouse_id: ctx.warehouse_id,
            start_date: sd,
            end_date: ed
        });
        window.location.href = $jsExportHistoryUrl + '?' + params.toString();
    }

    function render(res) {
        setText('hist-bf-qty', fmt.format(res.summary.qty_bf));
        setText('hist-bf-value', fmt.format(res.summary.value_bf));
        setText('hist-total-in', '+' + fmt.format(res.summary.total_in));
        setText('hist-total-out', '-' + fmt.format(res.summary.total_out));
        setText('hist-current-qty', fmt.format(res.summary.current_qty));
        setText('hist-tx-count', fmtInt.format(res.summary.tx_count));

        var unit = res.meta.unit_name || ctx.unit_name || '-';
        setText('hist-unit-name', 'หน่วย: ' + unit);

        if (res.meta.image_url) {
            var thumb = document.getElementById('hist-thumb');
            if (thumb && thumb.src !== res.meta.image_url) {
                thumb.src = res.meta.image_url;
            }
        }

        var tbody = document.getElementById('hist-tbody');
        var html = '';

        html += '<tr class="bf-row">' +
            '<td colspan="3" class="text-end">' +
                '<i class="bi bi-arrow-bar-right me-1"></i>ยอดยกมา ณ ' + escHtml(res.meta.start_date) +
            '</td>' +
            '<td class="text-center">—</td>' +
            '<td class="text-end">—</td>' +
            '<td class="text-end">—</td>' +
            '<td class="text-end">' + fmt.format(res.summary.qty_bf) + '</td>' +
            '<td class="text-end d-none d-md-table-cell">' + fmt.format(res.summary.value_bf) + '</td>' +
            '<td class="d-none d-md-table-cell">—</td>' +
        '</tr>';

        if (!res.transactions.length) {
            html += '<tr><td colspan="9" class="text-center bal-history-empty">' +
                '<i class="bi bi-inbox me-1"></i>ไม่พบการเคลื่อนไหวในช่วงเวลานี้</td></tr>';
        } else {
            res.transactions.forEach(function (t) {
                var pillCls = t.direction === 'in' ? 'in' : 'out';
                var pillLabel = t.direction === 'in' ? 'รับเข้า' : 'จ่ายออก';
                var pillIcon = t.direction === 'in' ? 'arrow-down-left' : 'arrow-up-right';
                html += '<tr>' +
                    '<td class="text-nowrap">' + escHtml(t.date) + ' <span class="text-muted">' + escHtml(t.time) + '</span></td>' +
                    '<td class="text-nowrap"><span class="bal-badge" style="background:var(--surface-3);color:var(--ink-2)">' + escHtml(t.order_no) + '</span></td>' +
                    '<td>' + escHtml(t.source_label) + '</td>' +
                    '<td class="text-center"><span class="direction-pill ' + pillCls + '">' +
                        '<i class="bi bi-' + pillIcon + '"></i>' + pillLabel + '</span></td>' +
                    '<td class="text-end fw-semibold" style="color:' + (t.direction === 'in' ? 'var(--success)' : 'var(--danger)') + '">' +
                        (t.direction === 'in' ? '+' : '-') + fmt.format(t.qty) + '</td>' +
                    '<td class="text-end" style="color:var(--ink-3)">' + fmt.format(t.unit_price) + '</td>' +
                    '<td class="text-end fw-semibold">' + fmt.format(t.balance_qty) + '</td>' +
                    '<td class="text-end d-none d-md-table-cell" style="color:var(--primary-ink)">' + fmt.format(t.balance_value) + '</td>' +
                    '<td class="d-none d-md-table-cell" style="color:var(--ink-3)">' + escHtml(t.lot) + '</td>' +
                '</tr>';
            });
        }

        tbody.innerHTML = html;
    }

    modalEl.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        if (!btn) return;
        ctx.item_code = btn.getAttribute('data-item-code');
        ctx.warehouse_id = btn.getAttribute('data-warehouse-id');
        ctx.unit_name = btn.getAttribute('data-unit-name') || '-';

        setText('hist-item-code', ctx.item_code);
        setText('hist-item-name', btn.getAttribute('data-item-name') || '-');
        setText('hist-warehouse', btn.getAttribute('data-warehouse-name') || '-');
        setText('hist-unit-name', 'หน่วย: ' + ctx.unit_name);

        var thumb = document.getElementById('hist-thumb');
        var img = btn.getAttribute('data-item-image');
        if (thumb && img) {
            thumb.src = img;
            thumb.alt = btn.getAttribute('data-item-name') || '';
        }

        ['hist-bf-qty','hist-bf-value','hist-current-qty'].forEach(function (id) { setText(id, '0.00'); });
        setText('hist-total-in', '+0.00');
        setText('hist-total-out', '-0.00');
        setText('hist-tx-count', '0');
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        loadHistory();
    });

    document.getElementById('historyFilterForm').addEventListener('submit', function (e) {
        e.preventDefault();
        loadHistory();
    });

    if (exportBtn) {
        exportBtn.addEventListener('click', exportExcel);
    }
})();
JS;
$this->registerJs($js, View::POS_END);

$this->registerJs(<<<JS
(function () {
    var form = document.getElementById('balance-filter-form');
    if (!form) return;
    form.addEventListener('change', function (e) {
        if (e.target && e.target.tagName === 'SELECT') {
            form.submit();
        }
    });

    var searchInput = document.getElementById('balance-search');
    if (searchInput) {
        var initial = searchInput.value;
        var timer = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                if (searchInput.value !== initial) form.submit();
            }, 450);
        });
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(timer);
                form.submit();
            }
        });
    }
})();
JS, View::POS_READY);
?>
