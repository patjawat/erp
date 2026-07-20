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
$loadingImg = Yii::getAlias('@web') . '/img/loading.gif'; // placeholder เบา ระหว่าง lazysizes โหลดรูปจริง

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
                    'data-pjax' => '0',
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
                                                <img src="<?= $loadingImg ?>"
                                                     data-src="<?= Html::encode($r['image_url']) ?>"
                                                     alt=""
                                                     class="bal-item__thumb lazyload"
                                                     onerror="this.onerror=null;this.src='<?= $placeholderImg ?>';this.classList.add('lazyloaded');">
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
                                            <button type="button"
                                                    class="bal-mm-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#minMaxModal"
                                                    data-item-code="<?= Html::encode($r['item_code']) ?>"
                                                    data-item-name="<?= Html::encode($r['item_name']) ?>"
                                                    data-warehouse-id="<?= (int) $r['warehouse_id'] ?>"
                                                    data-warehouse-name="<?= Html::encode($r['warehouse_name']) ?>"
                                                    data-unit-name="<?= Html::encode($r['unit_name']) ?>"
                                                    data-item-image="<?= Html::encode($r['image_url']) ?>"
                                                    data-balance-qty="<?= htmlspecialchars((string) (float) $r['balance_qty']) ?>"
                                                    data-min-qty="<?= $r['min_qty'] !== null ? htmlspecialchars((string) (float) $r['min_qty']) : '' ?>"
                                                    data-max-qty="<?= $r['max_qty'] !== null ? htmlspecialchars((string) (float) $r['max_qty']) : '' ?>"
                                                    title="คลิกเพื่อตั้งค่า Min/Max"
                                                    aria-label="ตั้งค่า Min/Max ของ <?= Html::encode($r['item_name']) ?> ที่ <?= Html::encode($r['warehouse_name']) ?>">
                                                <span class="bal-mm-val"><?= $r['min_qty'] !== null ? number_format($r['min_qty'], 0) : '<span class="bal-empty">—</span>' ?></span>
                                                <span class="bal-sep">/</span>
                                                <span class="bal-mm-val"><?= $r['max_qty'] !== null ? number_format($r['max_qty'], 0) : '<span class="bal-empty">—</span>' ?></span>
                                                <i class="bi bi-pencil-fill bal-mm-btn__icon" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                        <td class="text-center bal-cell-status">
                                            <?php if ($r['below_min']): ?>
                                                <span class="bal-badge bal-badge--danger">ต่ำกว่า Min</span>
                                            <?php elseif ($r['below_max']): ?>
                                                <span class="bal-badge bal-badge--warning">ต่ำกว่า Max</span>
                                            <?php else: ?>
                                                <span class="bal-badge bal-badge--ok">พอดี</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-flex flex-column flex-sm-row gap-1 justify-content-center">
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
                                                <a href="<?= Url::to(['/inventory-v2/stock-adjust/modal', 'warehouse_id' => (int) $r['warehouse_id'], 'item_code' => $r['item_code']]) ?>"
                                                   class="bal-history-btn open-modal"
                                                   data-size="modal-lg"
                                                   aria-label="ปรับยอดของ <?= Html::encode($r['item_name']) ?> ที่ <?= Html::encode($r['warehouse_name']) ?>">
                                                    <i class="bi bi-wrench-adjustable" aria-hidden="true"></i>
                                                    <span>ปรับยอด</span>
                                                </a>
                                            </div>
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

<?php // Modal: ประวัติการเคลื่อนไหววัสดุ — partial ร่วม (ใช้ในหน้าปิดเดือน material-summary ด้วย) ?>
<?= $this->render('_item_history_modal', [
    'historyUrl' => $historyUrl,
    'exportHistoryUrl' => $exportHistoryUrl,
]) ?>

<!-- Modal: ตั้งค่า Min/Max วัสดุประจำคลัง -->
<div class="modal fade" id="minMaxModal" tabindex="-1" aria-labelledby="minMaxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow bal-mm-modal">
            <div class="modal-header">
                <h5 class="modal-title bal-mm-modal__title" id="minMaxModalLabel">
                    <i class="bi bi-sliders" aria-hidden="true"></i>
                    ตั้งค่า Min / Max
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <div class="bal-mm-modal__meta">
                    <img id="mm-thumb" src="<?= $placeholderImg ?>"
                         alt="" class="bal-mm-modal__thumb"
                         onerror="this.onerror=null;this.src='<?= $placeholderImg ?>';">
                    <div class="bal-mm-modal__meta-text">
                        <strong id="mm-item-name">-</strong>
                        <span class="bal-mm-modal__code" id="mm-item-code"></span>
                        <div class="bal-mm-modal__warehouse" id="mm-warehouse-name">-</div>
                    </div>
                </div>
                <div id="mm-alert" class="bal-mm-modal__alert" role="status" aria-live="polite" hidden></div>
                <div class="row g-3 mt-1">
                    <div class="col-6">
                        <label for="mm-min-qty" class="form-label">Min</label>
                        <input type="number" id="mm-min-qty" class="form-control" step="any" min="0" inputmode="decimal">
                    </div>
                    <div class="col-6">
                        <label for="mm-max-qty" class="form-label">Max</label>
                        <input type="number" id="mm-max-qty" class="form-control" step="any" min="0" inputmode="decimal">
                    </div>
                    <div class="col-12">
                        <label for="mm-note" class="form-label">หมายเหตุ (ถ้ามี)</label>
                        <input type="text" id="mm-note" class="form-control" placeholder="เช่น อ้างอิงจากการใช้เฉลี่ยรายเดือน">
                    </div>
                </div>
                <div class="bal-mm-modal__hint">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    ยอดคงเหลือปัจจุบัน: <strong id="mm-balance-qty">-</strong> <span id="mm-unit-name"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="mm-save-btn">
                    <i class="bi bi-check-lg" aria-hidden="true"></i> บันทึก
                </button>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal" id="mm-cancel-btn">ยกเลิก</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Design tokens — shared by the page และทั้งสอง modal
   (Bootstrap 5 ไม่ย้าย modal ไป body → modal เป็น sibling นอก .bal-page
    จึงต้องประกาศ token ให้ modal ตรงๆ ไม่งั้น var() undefined = สีตกทั้งหมด) */
.bal-page,
#itemHistoryModal,
#minMaxModal {
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
}
.bal-page {
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
.bal-cell-mm { font-size: 0.82rem; }
.bal-sep { color: var(--ink-4); padding: 0 0.25rem; }
.bal-empty { color: var(--ink-4); }

/* === Min/Max edit button === */
.bal-mm-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.5rem;
    background: transparent;
    border: 1px solid transparent;
    border-radius: var(--radius-xs);
    color: var(--ink-3);
    font-size: 0.82rem;
    font-variant-numeric: tabular-nums;
    line-height: 1.2;
    cursor: pointer;
    transition: background-color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease), color var(--t-fast) var(--ease);
}
.bal-mm-btn:hover,
.bal-mm-btn:focus-visible {
    background: var(--primary-soft);
    border-color: var(--primary-line);
    color: var(--primary-ink);
    outline: none;
}
.bal-mm-btn__icon { font-size: 0.68rem; opacity: 0; transition: opacity var(--t-fast) var(--ease); }
.bal-mm-btn:hover .bal-mm-btn__icon,
.bal-mm-btn:focus-visible .bal-mm-btn__icon { opacity: 0.7; }

/* === Min/Max modal === */
.bal-mm-modal__title { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 1.05rem; font-weight: 600; color: var(--ink-1); }
.bal-mm-modal__title i { color: var(--ink-3); font-size: 0.9rem; }
.bal-mm-modal__meta { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.bal-mm-modal__thumb {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: var(--radius-sm);
    object-fit: cover;
    background: var(--surface-3);
    border: 1px solid var(--line);
}
.bal-mm-modal__meta-text { min-width: 0; }
.bal-mm-modal__meta strong { color: var(--ink-1); font-size: 0.95rem; }
.bal-mm-modal__code { color: var(--ink-3); font-size: 0.8rem; margin-left: 0.4rem; }
.bal-mm-modal__warehouse { color: var(--ink-2); font-size: 0.8rem; margin-top: 0.1rem; }
.bal-mm-modal__hint {
    margin-top: 0.85rem;
    font-size: 0.8rem;
    color: var(--ink-3);
}
.bal-mm-modal__hint strong { color: var(--ink-1); }
.bal-mm-modal__alert {
    margin-bottom: 0.75rem;
    padding: 0.5rem 0.7rem;
    border-radius: var(--radius-sm);
    font-size: 0.82rem;
    border: 1px solid var(--line);
    background: var(--surface-2);
}
.bal-mm-modal__alert[hidden] { display: none !important; }
.bal-mm-modal__alert.is-success { color: var(--success); background: var(--success-soft); border-color: rgba(21,128,61,0.22); }
.bal-mm-modal__alert.is-error { color: var(--danger); background: var(--danger-soft); border-color: var(--danger-line); }

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

</style>

<?php
$jsSaveSettingUrl = Json::encode(Url::to(['/inventory-v2/warehouse/save-setting']));

$this->registerJs(<<<JS
(function () {
    var modalEl = document.getElementById('minMaxModal');
    if (!modalEl) return;
    var itemNameEl = document.getElementById('mm-item-name');
    var itemCodeEl = document.getElementById('mm-item-code');
    var warehouseNameEl = document.getElementById('mm-warehouse-name');
    var thumbEl = document.getElementById('mm-thumb');
    var minInput = document.getElementById('mm-min-qty');
    var maxInput = document.getElementById('mm-max-qty');
    var noteInput = document.getElementById('mm-note');
    var balanceEl = document.getElementById('mm-balance-qty');
    var unitEl = document.getElementById('mm-unit-name');
    var alertEl = document.getElementById('mm-alert');
    var saveBtn = document.getElementById('mm-save-btn');
    var cancelBtn = document.getElementById('mm-cancel-btn');

    var ctx = { itemCode: null, warehouseId: null, row: null, balanceQty: 0 };

    function fmtInt(n) {
        return Number(n).toLocaleString('th-TH', { maximumFractionDigits: 0 });
    }

    function showAlert(type, message) {
        if (!alertEl) return;
        alertEl.textContent = message;
        alertEl.className = 'bal-mm-modal__alert is-' + type;
        alertEl.hidden = false;
    }
    function hideAlert() {
        if (alertEl) alertEl.hidden = true;
    }

    modalEl.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        if (!btn) return;
        hideAlert();
        ctx.itemCode = btn.getAttribute('data-item-code');
        ctx.warehouseId = btn.getAttribute('data-warehouse-id');
        ctx.row = btn.closest('tr');
        ctx.balanceQty = parseFloat(btn.getAttribute('data-balance-qty')) || 0;

        ctx.itemName = btn.getAttribute('data-item-name') || '-';
        ctx.warehouseName = btn.getAttribute('data-warehouse-name') || '-';

        if (itemNameEl) itemNameEl.textContent = ctx.itemName;
        if (itemCodeEl) itemCodeEl.textContent = ctx.itemCode || '';
        if (warehouseNameEl) warehouseNameEl.textContent = ctx.warehouseName;
        if (minInput) minInput.value = btn.getAttribute('data-min-qty') || '';
        if (maxInput) maxInput.value = btn.getAttribute('data-max-qty') || '';
        if (noteInput) noteInput.value = '';
        if (balanceEl) balanceEl.textContent = fmtInt(ctx.balanceQty);
        if (unitEl) unitEl.textContent = btn.getAttribute('data-unit-name') || '';
        if (thumbEl) {
            var img = btn.getAttribute('data-item-image');
            thumbEl.src = img || thumbEl.src;
            thumbEl.alt = ctx.itemName;
        }
        if (saveBtn) { saveBtn.disabled = false; saveBtn.classList.remove('disabled'); }
    });

    function setSaving(isSaving) {
        if (saveBtn) { saveBtn.disabled = isSaving; saveBtn.classList.toggle('disabled', isSaving); }
        if (cancelBtn) cancelBtn.disabled = isSaving;
    }

    function updateRowAfterSave(minVal, maxVal) {
        if (!ctx.row) return;
        var btn = ctx.row.querySelector('.bal-mm-btn');
        if (btn) {
            var vals = btn.querySelectorAll('.bal-mm-val');
            if (vals[0]) vals[0].textContent = fmtInt(minVal);
            if (vals[1]) vals[1].textContent = fmtInt(maxVal);
            btn.setAttribute('data-min-qty', minVal);
            btn.setAttribute('data-max-qty', maxVal);
        }
        var belowMin = minVal > 0 && ctx.balanceQty < minVal;
        var belowMax = maxVal > 0 && ctx.balanceQty < maxVal;
        ctx.row.classList.remove('is-danger', 'is-warning');
        var badge = ctx.row.querySelector('.bal-cell-status .bal-badge');
        if (belowMin) {
            ctx.row.classList.add('is-danger');
            if (badge) { badge.className = 'bal-badge bal-badge--danger'; badge.textContent = 'ต่ำกว่า Min'; }
        } else if (belowMax) {
            ctx.row.classList.add('is-warning');
            if (badge) { badge.className = 'bal-badge bal-badge--warning'; badge.textContent = 'ต่ำกว่า Max'; }
        } else if (badge) {
            badge.className = 'bal-badge bal-badge--ok';
            badge.textContent = 'พอดี';
        }
    }

    function performSave(minVal, maxVal) {
        var body = new URLSearchParams({
            warehouse_id: ctx.warehouseId,
            item_code: ctx.itemCode,
            min_qty: minVal,
            max_qty: maxVal,
            note: noteInput ? noteInput.value : ''
        });
        var csrfParam = document.querySelector('meta[name="csrf-param"]');
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfParam && csrfToken) {
            body.append(csrfParam.getAttribute('content'), csrfToken.getAttribute('content'));
        }

        hideAlert();
        setSaving(true);
        fetch($jsSaveSettingUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            credentials: 'same-origin',
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || res.status !== 'success') {
                    throw new Error((res && res.message) ? res.message : 'บันทึกไม่สำเร็จ');
                }
                updateRowAfterSave(minVal, maxVal);
                setSaving(false);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'บันทึกสำเร็จ',
                        text: 'อัปเดต Min/Max ของ "' + (ctx.itemName || ctx.itemCode) + '" เรียบร้อยแล้ว',
                        icon: 'success',
                        timer: 1400,
                        showConfirmButton: false
                    });
                } else {
                    showAlert('success', 'บันทึกสำเร็จ');
                }
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            })
            .catch(function (err) {
                setSaving(false);
                var message = err && err.message ? err.message : 'บันทึกไม่สำเร็จ';
                if (typeof Swal !== 'undefined') {
                    Swal.fire('ผิดพลาด', message, 'error');
                } else {
                    showAlert('error', message);
                }
            });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            var minVal = parseFloat(minInput ? minInput.value : '');
            var maxVal = parseFloat(maxInput ? maxInput.value : '');
            if (!ctx.itemCode || !ctx.warehouseId) {
                showAlert('error', 'ไม่พบข้อมูลวัสดุ/คลัง');
                return;
            }
            if (isNaN(minVal) || isNaN(maxVal)) {
                showAlert('error', 'กรอก Min และ Max ให้ครบ');
                return;
            }
            if (minVal > maxVal) {
                showAlert('error', 'Min ต้องไม่มากกว่า Max');
                return;
            }

            var confirmText = 'Min: ' + fmtInt(minVal) + '  /  Max: ' + fmtInt(maxVal) + '\\n'
                + (ctx.warehouseName || '');
            if (typeof Swal === 'undefined') {
                if (confirm('ยืนยันบันทึก Min/Max ของ "' + (ctx.itemName || ctx.itemCode) + '"?\\n' + confirmText)) {
                    performSave(minVal, maxVal);
                }
                return;
            }
            Swal.fire({
                title: 'ยืนยันบันทึก Min/Max?',
                html: '<strong>' + (ctx.itemName || ctx.itemCode) + '</strong><br>' + confirmText.replace(/\\n/g, '<br>'),
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก'
            }).then(function (result) {
                if (result.isConfirmed) {
                    performSave(minVal, maxVal);
                }
            });
        });
    }
})();
JS, View::POS_END);

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
