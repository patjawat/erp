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
                            <span><strong id="hist-item-code">-</strong></span>
                            <span class="sep" aria-hidden="true">·</span>
                            <span><strong id="hist-item-name">-</strong></span>
                            <span class="sep" aria-hidden="true">·</span>
                            <span><strong id="hist-warehouse">-</strong></span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>

            <div class="modal-body">
                <?php
                // default: ย้อนหลัง 1 ปี → วันนี้ (รูปแบบไทย d/m/Y พ.ศ.)
                $defaultStart = date('d/m/', strtotime('-1 year')) . (date('Y', strtotime('-1 year')) + 543);
                $defaultEnd   = date('d/m/') . (date('Y') + 543);
                ?>
                <form id="historyFilterForm" class="bal-history-filter">
                    <label class="bal-history-filter__field">
                        <span class="bal-history-filter__label">เริ่ม</span>
                        <?= \app\widgets\datepicker\DatepickerThai::widget([
                            'name' => 'start_date',
                            'value' => $defaultStart,
                            'options' => [
                                'id' => 'hist-start-date',
                                'class' => 'form-control form-control-sm',
                                'placeholder' => 'เริ่มจากวันที่',
                            ],
                        ]) ?>
                    </label>
                    <label class="bal-history-filter__field">
                        <span class="bal-history-filter__label">ถึง</span>
                        <?= \app\widgets\datepicker\DatepickerThai::widget([
                            'name' => 'end_date',
                            'value' => $defaultEnd,
                            'options' => [
                                'id' => 'hist-end-date',
                                'class' => 'form-control form-control-sm',
                                'placeholder' => 'ถึงวันที่',
                            ],
                        ]) ?>
                    </label>
                    <button type="submit" class="bal-history-filter__btn">
                        <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>โหลดประวัติ
                    </button>
                </form>

                <ul class="bal-history-stats" aria-label="สรุปยอดเคลื่อนไหว" aria-live="polite">
                    <li class="bal-history-stat">
                        <span class="bal-history-stat__label">ยอดยกมา</span>
                        <span class="bal-history-stat__value" id="hist-bf-qty">0</span>
                        <span class="bal-history-stat__sub">มูลค่า <span id="hist-bf-value">0.00</span> ฿</span>
                    </li>
                    <li class="bal-history-stat">
                        <span class="bal-history-stat__label">เคลื่อนไหวในช่วงเวลา</span>
                        <span class="bal-history-stat__value bal-history-stat__value--inout">
                            <span class="dir-mark dir-mark--in" aria-hidden="true"><i class="bi bi-arrow-down-left"></i></span>
                            <span id="hist-total-in">0</span>
                            <span class="sep" aria-hidden="true">/</span>
                            <span class="dir-mark dir-mark--out" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span>
                            <span id="hist-total-out">0</span>
                        </span>
                        <span class="bal-history-stat__sub">
                            <span id="hist-tx-count">0</span> รายการ
                            · มูลค่า +<span id="hist-total-in-value">0.00</span> / -<span id="hist-total-out-value">0.00</span> ฿
                        </span>
                    </li>
                    <li class="bal-history-stat bal-history-stat--hero">
                        <span class="bal-history-stat__label">
                            ยอดคงเหลือปัจจุบัน
                            <span class="bal-history-stat__sticker" title="ค่าตรงจากตาราง stock_balance ของระบบ">ยอดจริงในระบบ</span>
                        </span>
                        <span class="bal-history-stat__value" id="hist-current-qty">0</span>
                        <span class="bal-history-stat__sub"><span id="hist-unit-name">หน่วย</span> · มูลค่า <span id="hist-current-value">0.00</span> ฿</span>
                    </li>
                </ul>

                <!-- variance / match strip (toggled by JS) -->
                <div id="hist-variance" class="bal-history-variance" role="alert" aria-live="assertive" hidden>
                    <div class="bal-history-variance__head">
                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                        <span class="bal-history-variance__title">ยอดสะสมไม่ตรงกับยอดคงเหลือในระบบ</span>
                    </div>
                    <div class="bal-history-variance__numbers">
                        <span class="bal-history-variance__delta">
                            ผลต่าง <strong id="hist-variance-delta">0</strong>
                        </span>
                        <span class="bal-history-variance__breakdown">
                            (สะสม <strong id="hist-variance-running">0</strong>
                            <span class="sep">·</span>
                            ระบบ <strong id="hist-variance-truth">0</strong>)
                        </span>
                    </div>
                    <!-- <ul class="bal-history-variance__causes">
                        <li>มีการย้ายข้อมูลจาก V1 ที่ snapshot ไม่ครบ → <a href="#" id="hist-link-negative" target="_blank" rel="noopener">ดูยอดติดลบทั้งระบบ</a></li>
                        <li>ยังไม่ตั้งยอดยกมาตั้งต้น (INITIAL) ก่อนช่วงนี้ → <a href="#" id="hist-link-initial" target="_blank" rel="noopener">ไปหน้ารับเข้า</a></li>
                        <li>ข้อมูลใน stock_detail กับ stock_balance ไม่ sync → <a href="#" id="hist-link-detail" target="_blank" rel="noopener">ตรวจรายละเอียดล็อต</a></li>
                    </ul> -->
                    <div class="bal-history-variance__actions">
                        <a href="#" id="hist-link-adjust" class="bal-history-variance__primary">
                            <i class="bi bi-sliders" aria-hidden="true"></i>ปรับยอดในหน้านี้
                        </a>
                        <div class="bal-history-variance__copyhint">
                            <span>ใช้เลขนี้ในช่อง "จำนวนที่ปรับ":</span>
                            <button type="button" class="bal-history-variance__copybtn" id="hist-copy-qty" title="คัดลอกตัวเลข">
                                <code id="hist-copy-val">0</code>
                                <i class="bi bi-clipboard" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="hist-match" class="bal-history-match" hidden>
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    ยอดสะสมตรงกับยอดคงเหลือในระบบ
                </div>

                <div id="hist-adjust-panel" class="bal-history-adjust" hidden>
                    <div class="bal-history-adjust__head">
                        <div>
                            <div class="bal-history-adjust__title">
                                <i class="bi bi-wrench-adjustable" aria-hidden="true"></i>
                                ปรับยอดในหน้านี้
                            </div>
                            <div class="bal-history-adjust__caption">ระบบจะสร้างเอกสาร ADJUST ใหม่และรีเฟรชยอดคงเหลือทันที</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="hist-adjust-close">
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="hist-adjust-alert" class="bal-history-adjust__alert" role="status" aria-live="polite" hidden></div>
                    <div class="bal-history-adjust__grid">
                        <div class="bal-history-adjust__field">
                            <label for="hist-adjust-current">ยอดระบบปัจจุบัน</label>
                            <input type="number" id="hist-adjust-current" class="form-control" readonly>
                        </div>
                        <div class="bal-history-adjust__field">
                            <label for="hist-adjust-target">ยอดตรวจนับจริง</label>
                            <input type="number" id="hist-adjust-target" class="form-control" step="any" inputmode="decimal">
                        </div>
                        <div class="bal-history-adjust__field">
                            <label>จำนวนที่จะปรับ</label>
                            <div id="hist-adjust-delta" class="bal-history-adjust__delta">0</div>
                        </div>
                        <div class="bal-history-adjust__field bal-history-adjust__field--wide">
                            <label for="hist-adjust-note">เหตุผล</label>
                            <input type="text" id="hist-adjust-note" class="form-control" value="ปรับยอดจากประวัติการเคลื่อนไหววัสดุ">
                        </div>
                    </div>
                    <div class="bal-history-adjust__actions">
                        <button type="button" class="btn btn-primary" id="hist-adjust-save">
                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                            บันทึกปรับยอด
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="hist-adjust-cancel">ยกเลิก</button>
                    </div>
                </div>

                <div id="hist-edit-panel" class="bal-history-adjust bal-history-edit" hidden>
                    <div class="bal-history-adjust__head">
                        <div>
                            <div class="bal-history-adjust__title">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                แก้จำนวนในใบเบิก
                            </div>
                            <div class="bal-history-adjust__caption">
                                <span id="hist-edit-doc">-</span>
                                · ระบบจะปรับผลต่างกับยอดคงเหลือและ FIFO ให้ทันที
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="hist-edit-close">
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="hist-edit-alert" class="bal-history-adjust__alert" role="status" aria-live="polite" hidden></div>
                    <input type="hidden" id="hist-edit-detail-id">
                    <div class="bal-history-adjust__grid">
                        <div class="bal-history-adjust__field">
                            <label for="hist-edit-current">จำนวนเดิมในใบเบิก</label>
                            <input type="number" id="hist-edit-current" class="form-control" readonly>
                        </div>
                        <div class="bal-history-adjust__field">
                            <label for="hist-edit-new">จำนวนที่ถูกต้อง</label>
                            <input type="number" id="hist-edit-new" class="form-control" step="any" min="0.000001" inputmode="decimal">
                        </div>
                        <div class="bal-history-adjust__field">
                            <label for="hist-edit-current-price">ราคา/หน่วยเดิม</label>
                            <input type="number" id="hist-edit-current-price" class="form-control" readonly>
                        </div>
                        <div class="bal-history-adjust__field">
                            <label for="hist-edit-new-price">ราคา/หน่วยที่ถูกต้อง</label>
                            <input type="number" id="hist-edit-new-price" class="form-control" step="any" min="0" inputmode="decimal">
                        </div>
                        <div class="bal-history-adjust__field">
                            <label>ผลต่อยอดคงเหลือ</label>
                            <div id="hist-edit-impact" class="bal-history-adjust__delta">0</div>
                        </div>
                        <div class="bal-history-adjust__field bal-history-adjust__field--wide">
                            <label for="hist-edit-note">เหตุผล</label>
                            <input type="text" id="hist-edit-note" class="form-control" value="แก้จำนวนใบเบิกจากประวัติการเคลื่อนไหววัสดุ">
                        </div>
                    </div>
                    <div class="bal-history-edit-preview" aria-live="polite">
                        <div class="bal-history-edit-preview__item">
                            <span>จำนวนใบเบิก</span>
                            <strong id="hist-edit-preview-issue">—</strong>
                        </div>
                        <div class="bal-history-edit-preview__item">
                            <span>ผลต่อยอดคงเหลือ</span>
                            <strong id="hist-edit-preview-stock">—</strong>
                        </div>
                        <div class="bal-history-edit-preview__item">
                            <span>ราคา/หน่วย</span>
                            <strong id="hist-edit-preview-price">—</strong>
                        </div>
                        <div class="bal-history-edit-preview__item">
                            <span>มูลค่ารายการ</span>
                            <strong id="hist-edit-preview-line-value">—</strong>
                        </div>
                        <div class="bal-history-edit-preview__item">
                            <span>ยอดคงเหลือหลังแก้</span>
                            <strong id="hist-edit-preview-balance">—</strong>
                        </div>
                        <div class="bal-history-edit-preview__item">
                            <span>ยอดสะสมหลังรายการนี้</span>
                            <strong id="hist-edit-preview-row-balance">—</strong>
                        </div>
                        <div class="bal-history-edit-preview__item">
                            <span>มูลค่าที่เปลี่ยน</span>
                            <strong id="hist-edit-preview-value">—</strong>
                        </div>
                    </div>
                    <div class="bal-history-adjust__actions">
                        <button type="button" class="btn btn-primary" id="hist-edit-save">
                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                            บันทึกจำนวนใหม่
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="hist-edit-cancel">ยกเลิก</button>
                    </div>
                </div>

                <div id="hist-action-alert" class="bal-history-action-alert" role="status" aria-live="polite" hidden></div>

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
                                <th class="text-end text-nowrap">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="hist-tbody">
                            <tr><td colspan="10" class="text-center bal-history-empty">เลือกช่วงเวลาแล้วกด "โหลดประวัติ"</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <span class="bal-history-modal__hint me-auto">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    นับเฉพาะเอกสารที่ยืนยันแล้ว (CONFIRMED)
                </span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="hist-adjust-btn" disabled>
                    <i class="bi bi-wrench-adjustable me-1"></i>ปรับยอดในหน้านี้
                </button>
                <button type="button" class="btn btn-sm bal-history-modal__export" id="hist-export-btn" disabled>
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
    margin-bottom: 0.3rem;
    color: var(--ink-1) !important;
    font-size: 1.05rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.bal-history-modal__title i { color: var(--ink-3); font-size: 0.9rem; }
.bal-history-modal__meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    column-gap: 0.5rem;
    row-gap: 0.15rem;
    font-size: 0.8rem;
    color: var(--ink-2);
}
.bal-history-modal__meta .sep { color: var(--ink-4); }
.bal-history-modal__meta strong { color: var(--ink-1); font-weight: 600; }
.bal-history-modal__export {
    background: var(--primary);
    border: 1px solid var(--primary);
    color: #fff;
    font-weight: 600;
    transition: background-color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
}
.bal-history-modal__export:hover:not(:disabled) {
    background: var(--primary-ink);
    border-color: var(--primary-ink);
    color: #fff;
}
.bal-history-modal__export:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.bal-history-modal__export:disabled {
    background: var(--surface-3);
    border-color: var(--line-strong);
    color: var(--ink-3);
    cursor: not-allowed;
}
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
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    color: var(--ink-1);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease), color var(--t-fast) var(--ease);
}
.bal-history-filter__btn:hover {
    background: var(--surface-hover);
    border-color: var(--ink-3);
}
.bal-history-filter__btn:focus-visible {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}

/* === History stats === */
.bal-history-stats {
    list-style: none;
    padding: 0;
    margin: 0 0 0.85rem;
    display: grid;
    grid-template-columns: 2fr 2.2fr 2.6fr;
    gap: 0.5rem;
}
.bal-history-stat {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.75rem 0.95rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
}
.bal-history-stat--hero { padding: 0.8rem 1rem; }
.bal-history-stat__label {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-2);
}
.bal-history-stat__sticker {
    font-size: 0.66rem;
    font-weight: 600;
    color: var(--ink-3);
    background: var(--surface-3);
    padding: 0.05rem 0.4rem;
    border-radius: 999px;
    cursor: help;
}
.bal-history-stat__value {
    font-size: 1.45rem;
    font-weight: 700;
    color: var(--ink-1);
    line-height: 1.05;
    font-variant-numeric: tabular-nums;
}
.bal-history-stat--hero .bal-history-stat__value { font-size: 1.6rem; }
.bal-history-stat__value--inout {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    align-items: baseline;
    font-size: 1.25rem;
    color: var(--ink-1);
}
.bal-history-stat__value--inout .sep {
    color: var(--ink-4);
    font-weight: 400;
    font-size: 0.95rem;
    align-self: center;
}
.bal-history-stat__sub {
    font-size: 0.78rem;
    color: var(--ink-3);
    font-variant-numeric: tabular-nums;
}

/* Direction marks (neutral — color reserved for negative balance + variance) */
.dir-mark {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.1rem;
    height: 1.1rem;
    border-radius: var(--radius-xs);
    background: var(--surface-3);
    color: var(--ink-2);
}
.dir-mark i { font-size: 0.78rem; line-height: 1; }
.dir-mark--in i { transform: translate(0, 0); }
.dir-mark--out i { transform: translate(0, 0); }

/* === Variance banner === */
.bal-history-variance {
    margin: 0 0 1rem;
    padding: 0.85rem 1rem;
    background: var(--danger-soft);
    border: 1px solid var(--danger-line);
    border-radius: var(--radius);
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    transition: opacity var(--t-mid) var(--ease), transform var(--t-mid) var(--ease);
}
.bal-history-variance[hidden] { display: none !important; }
.bal-history-variance.is-entering {
    opacity: 0;
    transform: translateY(-4px);
}
.bal-history-variance__head {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--danger);
}
.bal-history-variance__head i { font-size: 1rem; }
.bal-history-variance__numbers {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.75rem;
    font-size: 0.85rem;
    color: var(--ink-2);
}
.bal-history-variance__delta strong {
    color: var(--danger);
    font-weight: 700;
    font-size: 1.05rem;
    font-variant-numeric: tabular-nums;
    margin-left: 0.3rem;
}
.bal-history-variance__breakdown {
    color: var(--ink-3);
    font-variant-numeric: tabular-nums;
}
.bal-history-variance__breakdown strong { color: var(--ink-1); font-weight: 600; }
.bal-history-variance__breakdown .sep { color: var(--ink-4); margin: 0 0.2rem; }
.bal-history-variance__causes {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    font-size: 0.82rem;
    color: var(--ink-2);
}
.bal-history-variance__causes li {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.4rem;
}
.bal-history-variance__causes li::before {
    content: '•';
    color: var(--ink-4);
}
.bal-history-variance__causes a {
    color: var(--primary-ink);
    text-decoration: none;
    font-weight: 600;
    border-bottom: 1px dashed var(--primary-line);
    padding-bottom: 1px;
}
.bal-history-variance__causes a:hover { color: var(--primary); border-bottom-color: var(--primary); }
.bal-history-variance__actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.6rem;
    margin-top: 0.15rem;
}
.bal-history-variance__primary {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.85rem;
    background: var(--danger);
    border: 1px solid var(--danger);
    border-radius: var(--radius-sm);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: background-color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
}
.bal-history-variance__primary:hover {
    background: #991616;
    border-color: #991616;
    color: #fff;
}
.bal-history-variance__primary:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--danger-soft);
}
.bal-history-variance__hint {
    font-size: 0.75rem;
    color: var(--ink-3);
}
.bal-history-variance__copyhint {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: var(--ink-2);
}
.bal-history-variance__copybtn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.2rem 0.55rem;
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-xs);
    cursor: pointer;
    font: inherit;
    color: var(--ink-1);
    transition: background-color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
}
.bal-history-variance__copybtn code {
    font-family: ui-monospace, SFMono-Regular, 'JetBrains Mono', Menlo, monospace;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--danger);
    font-variant-numeric: tabular-nums;
    background: transparent;
    padding: 0;
}
.bal-history-variance__copybtn i { color: var(--ink-3); font-size: 0.8rem; }
.bal-history-variance__copybtn:hover { background: var(--surface-hover); border-color: var(--ink-3); }
.bal-history-variance__copybtn:focus-visible {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.bal-history-variance__copybtn.is-copied {
    background: var(--success-soft);
    border-color: var(--success);
}
.bal-history-variance__copybtn.is-copied i::before {
    content: "\f26b"; /* bi-clipboard-check */
}
.bal-history-variance__copybtn.is-copied i { color: var(--success); }

/* Match strip */
.bal-history-match {
    margin: 0 0 0.85rem;
    padding: 0.45rem 0.85rem;
    background: var(--surface-2);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    color: var(--success);
    font-weight: 600;
}
.bal-history-match[hidden] { display: none !important; }
.bal-history-match i { font-size: 0.9rem; }

/* Inline adjustment panel */
.bal-history-adjust {
    margin: 0 0 0.9rem;
    padding: 0.9rem;
    border: 1px solid var(--line);
    border-radius: var(--radius);
    background: var(--surface);
    box-shadow: 0 10px 24px rgba(15,23,42,0.08);
}
.bal-history-adjust[hidden] { display: none !important; }
.bal-history-adjust__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.bal-history-adjust__title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-weight: 700;
    color: var(--ink-1);
}
.bal-history-adjust__caption {
    margin-top: 0.15rem;
    font-size: 0.82rem;
    color: var(--ink-3);
}
.bal-history-adjust__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(160px, 1fr));
    gap: 0.75rem;
}
.bal-history-adjust__field {
    min-width: 0;
}
.bal-history-adjust__field label {
    display: block;
    margin-bottom: 0.3rem;
    color: var(--ink-3);
    font-size: 0.78rem;
    font-weight: 700;
}
.bal-history-adjust__field--wide {
    grid-column: span 2;
}
.bal-history-adjust__delta {
    min-height: 38px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0.375rem 0.75rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--surface-2);
    font-weight: 800;
    color: var(--ink-1);
}
.bal-history-adjust__delta.is-in { color: var(--success); }
.bal-history-adjust__delta.is-out { color: var(--danger); }
.bal-history-action-alert,
.bal-history-adjust__alert {
    margin-bottom: 0.75rem;
    padding: 0.55rem 0.75rem;
    border-radius: var(--radius-sm);
    font-size: 0.84rem;
    border: 1px solid var(--line);
    background: var(--surface-2);
}
.bal-history-action-alert[hidden] { display: none !important; }
.bal-history-action-alert.is-success,
.bal-history-adjust__alert.is-success {
    color: var(--success);
    background: var(--success-soft);
    border-color: rgba(21,128,61,0.22);
}
.bal-history-action-alert.is-error,
.bal-history-adjust__alert.is-error {
    color: var(--danger);
    background: var(--danger-soft);
    border-color: rgba(220,53,69,0.22);
}
.bal-history-adjust__actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.85rem;
}
.bal-history-edit-preview {
    display: grid;
    grid-template-columns: repeat(5, minmax(130px, 1fr));
    gap: 0.55rem;
    margin-top: 0.8rem;
}
.bal-history-edit-preview__item {
    min-width: 0;
    padding: 0.55rem 0.65rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--surface-2);
}
.bal-history-edit-preview__item span {
    display: block;
    margin-bottom: 0.18rem;
    color: var(--ink-3);
    font-size: 0.72rem;
    font-weight: 700;
}
.bal-history-edit-preview__item strong {
    display: block;
    color: var(--ink-1);
    font-size: 0.92rem;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.bal-history-edit-preview__item strong.is-in { color: var(--success); }
.bal-history-edit-preview__item strong.is-out { color: var(--danger); }
.bal-cell-qty.is-updated {
    background: var(--success-soft);
    color: var(--success);
    transition: background-color 0.35s ease, color 0.35s ease;
}
@media (max-width: 991.98px) {
    .bal-history-adjust__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .bal-history-adjust__field--wide { grid-column: span 2; }
    .bal-history-edit-preview { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575.98px) {
    .bal-history-adjust__grid { grid-template-columns: 1fr; }
    .bal-history-adjust__field--wide { grid-column: span 1; }
    .bal-history-edit-preview { grid-template-columns: 1fr; }
}

/* === History table === */
.bal-history-table-wrap { border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; }
.bal-history-table { font-size: 0.875rem; line-height: 1.45; margin-bottom: 0; }
.bal-history-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    font-weight: 600;
    font-size: 0.78rem;
    color: var(--ink-3);
    background: var(--surface-2);
    border-bottom: 1px solid var(--line);
    padding: 0.55rem 0.75rem;
    letter-spacing: 0;
    text-transform: none;
}
.bal-history-table tbody td {
    font-variant-numeric: tabular-nums;
    padding: 0.5rem 0.75rem;
    border-color: var(--line);
    color: var(--ink-1);
    vertical-align: middle;
}
.bal-history-table tbody tr {
    transition: background-color var(--t-fast) var(--ease);
}
.bal-history-table tbody tr:hover { background: var(--surface-hover); }
.bal-history-table tbody tr.is-preview {
    background: var(--primary-soft);
}
.bal-history-table tbody tr.is-preview td {
    transition: color 0.18s ease, background-color 0.18s ease;
}
.bal-history-table td.is-preview-value {
    color: var(--primary);
    font-weight: 700;
}
.bal-history-table .muted { color: var(--ink-4); }
.bal-history-table .text-meta { color: var(--ink-3); }
.bal-history-table .direction-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    width: 1.4rem;
    height: 1.4rem;
    border-radius: var(--radius-xs);
    background: var(--surface-3);
    color: var(--ink-2);
    font-size: 0.78rem;
}
.bal-history-table .direction-pill i { line-height: 1; }
.bal-history-table .doc-chip {
    display: inline-block;
    padding: 0.1rem 0.5rem;
    background: var(--surface-3);
    color: var(--ink-2);
    border-radius: var(--radius-xs);
    font-size: 0.78rem;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
}
.bal-history-table .hist-reverse-btn,
.bal-history-table .hist-edit-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.8rem;
    height: 1.8rem;
    padding: 0;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-xs);
    color: var(--ink-2);
    background: var(--surface);
}
.bal-history-table .hist-reverse-btn:hover,
.bal-history-table .hist-edit-btn:hover {
    color: var(--danger);
    border-color: rgba(220,53,69,0.35);
    background: var(--danger-soft);
}
.bal-history-table .hist-edit-btn:hover {
    color: var(--primary);
    border-color: var(--primary-line);
    background: var(--primary-soft);
}
.bal-history-table .hist-reverse-btn:disabled,
.bal-history-table .hist-edit-btn:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}
.bal-history-table td.is-negative {
    color: var(--danger);
    font-weight: 700;
}
.bal-history-table tr.bf-row {
    background: var(--surface-2);
    font-weight: 600;
    color: var(--ink-1);
}
.bal-history-table tr.bf-row:hover { background: var(--surface-2); }
.bal-history-table tr.bf-row td { border-top: 0; }
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
    .bal-history-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .bal-history-stat--hero { grid-column: 1 / -1; }
}
@media (max-width: 767.98px) {
    .bal-toolbar__field { flex: 1 1 auto; min-width: 130px; }
    .bal-toolbar__actions { flex: 1 1 100%; justify-content: flex-end; }
    .bal-history-stats { grid-template-columns: 1fr; }
    .bal-history-stat--hero { grid-column: auto; }
    .bal-cell-warehouse { max-width: 9rem; }
    .bal-history-variance__numbers { gap: 0.4rem; }
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
    .bal-history-filter__btn,
    .bal-history-modal__export,
    .bal-history-variance,
    .bal-history-variance__primary,
    .bal-history-table tbody tr { transition: none !important; }
    .bal-history-variance.is-entering { opacity: 1; transform: none; }
    .bal-skeleton { animation: none; background: rgba(15,23,42,0.08); }
}
</style>

<?php
$jsHistoryUrl = Json::encode($historyUrl);
$jsExportHistoryUrl = Json::encode($exportHistoryUrl);
$jsAdjustSaveUrl = Json::encode(Url::to(['/inventory-v2/stock-adjust/save']));
$jsReqDetailUpdateUrl = Json::encode(Url::to(['/inventory-v2/stock-adjust/update-requisition-detail-qty']));
$js = <<<JS
(function () {
    var modalEl = document.getElementById('itemHistoryModal');
    if (!modalEl) return;
    var adjustBtn = document.getElementById('hist-adjust-btn');
    var lastHistoryPayload = null;

    var ctx = { item_code: null, warehouse_id: null, unit_name: '-' };
    var exportBtn = document.getElementById('hist-export-btn');
    var adjustPanel = document.getElementById('hist-adjust-panel');
    var adjustCurrent = document.getElementById('hist-adjust-current');
    var adjustTarget = document.getElementById('hist-adjust-target');
    var adjustDelta = document.getElementById('hist-adjust-delta');
    var adjustNote = document.getElementById('hist-adjust-note');
    var adjustAlert = document.getElementById('hist-adjust-alert');
    var actionAlert = document.getElementById('hist-action-alert');
    var adjustSaveBtn = document.getElementById('hist-adjust-save');
    var adjustCancelBtn = document.getElementById('hist-adjust-cancel');
    var adjustCloseBtn = document.getElementById('hist-adjust-close');
    var editPanel = document.getElementById('hist-edit-panel');
    var editDoc = document.getElementById('hist-edit-doc');
    var editAlert = document.getElementById('hist-edit-alert');
    var editDetailId = document.getElementById('hist-edit-detail-id');
    var editCurrent = document.getElementById('hist-edit-current');
    var editNew = document.getElementById('hist-edit-new');
    var editCurrentPrice = document.getElementById('hist-edit-current-price');
    var editNewPrice = document.getElementById('hist-edit-new-price');
    var editImpact = document.getElementById('hist-edit-impact');
    var editNote = document.getElementById('hist-edit-note');
    var editSaveBtn = document.getElementById('hist-edit-save');
    var editCancelBtn = document.getElementById('hist-edit-cancel');
    var editCloseBtn = document.getElementById('hist-edit-close');
    var editPreviewIssue = document.getElementById('hist-edit-preview-issue');
    var editPreviewStock = document.getElementById('hist-edit-preview-stock');
    var editPreviewPrice = document.getElementById('hist-edit-preview-price');
    var editPreviewLineValue = document.getElementById('hist-edit-preview-line-value');
    var editPreviewBalance = document.getElementById('hist-edit-preview-balance');
    var editPreviewRowBalance = document.getElementById('hist-edit-preview-row-balance');
    var editPreviewValue = document.getElementById('hist-edit-preview-value');
    var activeEditRow = null;
    var activeBalanceRow = null;
    var fmt = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var fmtInt = new Intl.NumberFormat('th-TH', { maximumFractionDigits: 0 });

    // หน่วยที่นับเป็นจำนวนเต็มเสมอ → 0 ทศนิยม; อื่น ๆ → 2 ทศนิยม
    var integerUnits = ['ชิ้น','แผ่น','ม้วน','ขวด','กล่อง','ชุด','ใบ','อัน','คู่','ซอง','ห่อ','ตัว','เครื่อง','ถัง','ลัง','แท่ง','แท็บเล็ต','ฉบับ','เล่ม'];
    function isIntegerUnit(unit) {
        if (!unit) return false;
        var u = String(unit).trim();
        for (var i = 0; i < integerUnits.length; i++) {
            if (u === integerUnits[i] || u.indexOf(integerUnits[i]) !== -1) return true;
        }
        return false;
    }
    function fmtUnit(qty, unit) {
        return isIntegerUnit(unit) ? fmtInt.format(qty) : fmt.format(qty);
    }
    function todayISO() {
        var d = new Date();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + m + '-' + day;
    }

    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function stripNumber(value) {
        var num = parseFloat(value);
        if (isNaN(num)) return '';
        return String(parseFloat(num.toFixed(6)));
    }

    function showAdjustMessage(type, message) {
        if (!adjustAlert) return;
        adjustAlert.hidden = false;
        adjustAlert.classList.toggle('is-success', type === 'success');
        adjustAlert.classList.toggle('is-error', type === 'error');
        adjustAlert.textContent = message;
    }

    function hideAdjustMessage() {
        if (!adjustAlert) return;
        adjustAlert.hidden = true;
        adjustAlert.classList.remove('is-success', 'is-error');
        adjustAlert.textContent = '';
    }

    function showEditMessage(type, message) {
        if (!editAlert) return;
        editAlert.hidden = false;
        editAlert.classList.toggle('is-success', type === 'success');
        editAlert.classList.toggle('is-error', type === 'error');
        editAlert.textContent = message;
    }

    function hideEditMessage() {
        if (!editAlert) return;
        editAlert.hidden = true;
        editAlert.classList.remove('is-success', 'is-error');
        editAlert.textContent = '';
    }

    function setSignedPreview(el, text, value) {
        if (!el) return;
        el.textContent = text;
        el.classList.toggle('is-in', Number(value) > 0);
        el.classList.toggle('is-out', Number(value) < 0);
    }

    function clearEditPreview() {
        [editPreviewIssue, editPreviewStock, editPreviewPrice, editPreviewLineValue, editPreviewBalance, editPreviewRowBalance, editPreviewValue].forEach(function (el) {
            if (!el) return;
            el.textContent = '—';
            el.classList.remove('is-in', 'is-out');
        });
    }

    function getHistoryTableRow(detailId) {
        var tbody = document.getElementById('hist-tbody');
        if (!tbody) return null;
        var rows = tbody.querySelectorAll('.hist-tx-row');
        for (var i = 0; i < rows.length; i++) {
            if (String(rows[i].getAttribute('data-detail-id')) === String(detailId)) {
                return rows[i];
            }
        }
        return null;
    }

    function setPreviewCell(row, selector, text, numericValue, isQuantity) {
        if (!row) return;
        var cell = row.querySelector(selector);
        if (!cell) return;
        cell.textContent = text;
        cell.classList.toggle('is-negative', Number(numericValue) < 0 && !!isQuantity);
        cell.classList.toggle('is-preview-value', true);
    }

    function resetHistoryRowPreview() {
        if (lastHistoryPayload) {
            render(lastHistoryPayload);
        }
    }

    function updateHistoryRowPreview(newQty, newPrice, stockImpact, valueImpact) {
        if (!activeEditRow || !lastHistoryPayload || !lastHistoryPayload.transactions) return;
        var transactions = lastHistoryPayload.transactions;
        var activeIndex = -1;
        for (var i = 0; i < transactions.length; i++) {
            if (String(transactions[i].detail_id) === String(activeEditRow.detail_id)) {
                activeIndex = i;
                break;
            }
        }
        if (activeIndex < 0) return;

        for (var j = 0; j < transactions.length; j++) {
            var t = transactions[j];
            var row = getHistoryTableRow(t.detail_id);
            if (!row) continue;
            var shouldPreview = j >= activeIndex;
            row.classList.toggle('is-preview', shouldPreview);
            if (!shouldPreview) continue;

            var balanceQty = Number(t.balance_qty || 0) + stockImpact;
            var balanceValue = Number(t.balance_value || 0) + valueImpact;
            var qty = j === activeIndex ? newQty : Number(t.qty || 0);
            var price = j === activeIndex ? newPrice : Number(t.unit_price || 0);
            var sign = t.direction === 'in' ? '+' : '-';

            if (j === activeIndex) {
                setPreviewCell(row, '.hist-cell-qty', sign + fmtUnit(qty, ctx.unit_name), qty, false);
                setPreviewCell(row, '.hist-cell-price', fmt.format(price), price, false);
            }
            setPreviewCell(row, '.hist-cell-balance', fmtUnit(balanceQty, ctx.unit_name), balanceQty, true);
            setPreviewCell(row, '.hist-cell-value', fmt.format(balanceValue), balanceValue, true);
        }
    }

    function showActionMessage(type, message) {
        if (!actionAlert) return;
        actionAlert.hidden = false;
        actionAlert.classList.toggle('is-success', type === 'success');
        actionAlert.classList.toggle('is-error', type === 'error');
        actionAlert.textContent = message;
    }

    function hideActionMessage() {
        if (!actionAlert) return;
        actionAlert.hidden = true;
        actionAlert.classList.remove('is-success', 'is-error');
        actionAlert.textContent = '';
    }

    function updateAdjustDelta() {
        if (!adjustCurrent || !adjustTarget || !adjustDelta) return 0;
        var current = parseFloat(adjustCurrent.value || 0);
        var target = parseFloat(adjustTarget.value);
        if (isNaN(current)) current = 0;
        if (isNaN(target)) {
            adjustDelta.textContent = '—';
            adjustDelta.classList.remove('is-in', 'is-out');
            if (adjustSaveBtn) adjustSaveBtn.disabled = true;
            return 0;
        }
        var delta = target - current;
        adjustDelta.textContent = (delta > 0 ? '+' : '') + fmtUnit(delta, ctx.unit_name);
        adjustDelta.classList.toggle('is-in', delta > 0);
        adjustDelta.classList.toggle('is-out', delta < 0);
        if (adjustSaveBtn) adjustSaveBtn.disabled = Math.abs(delta) < 0.000001;
        return delta;
    }

    function updateMainBalanceRow(qty) {
        if (!activeBalanceRow) return;
        var qtyCell = activeBalanceRow.querySelector('.bal-cell-qty');
        if (!qtyCell) return;
        qtyCell.textContent = fmtUnit(qty, ctx.unit_name);
        qtyCell.classList.add('is-updated');
        setTimeout(function () { qtyCell.classList.remove('is-updated'); }, 1400);
    }

    function syncAdjustPanelCurrent(res) {
        if (!adjustPanel || adjustPanel.hidden || !res || !res.summary) return;
        var currentQty = parseFloat(res.summary.current_qty || 0);
        if (isNaN(currentQty)) currentQty = 0;
        if (adjustCurrent) adjustCurrent.value = stripNumber(currentQty);
        updateAdjustDelta();
    }

    function openAdjustPanel() {
        if (!adjustPanel || !lastHistoryPayload || !lastHistoryPayload.meta || !lastHistoryPayload.summary) return;
        var currentQty = parseFloat(lastHistoryPayload.summary.current_qty || 0);
        if (isNaN(currentQty)) currentQty = 0;
        var targetQty = currentQty < 0 ? 0 : currentQty;
        closeEditPanel();
        adjustPanel.hidden = false;
        hideAdjustMessage();
        if (adjustCurrent) adjustCurrent.value = stripNumber(currentQty);
        if (adjustTarget) adjustTarget.value = stripNumber(targetQty);
        if (adjustNote && !adjustNote.value) adjustNote.value = 'ปรับยอดจากประวัติการเคลื่อนไหววัสดุ';
        updateAdjustDelta();
        setTimeout(function () {
            if (adjustTarget) {
                adjustTarget.focus();
                adjustTarget.select();
            }
        }, 30);
    }

    function closeAdjustPanel() {
        if (adjustPanel) adjustPanel.hidden = true;
        hideAdjustMessage();
    }

    function updateEditImpact() {
        if (!editCurrent || !editNew || !editImpact) return 0;
        var currentQty = parseFloat(editCurrent.value || 0);
        var newQty = parseFloat(editNew.value);
        var currentPrice = parseFloat(editCurrentPrice ? editCurrentPrice.value : (activeEditRow ? activeEditRow.unit_price : 0));
        var newPrice = parseFloat(editNewPrice ? editNewPrice.value : currentPrice);
        if (isNaN(currentQty)) currentQty = 0;
        if (isNaN(currentPrice)) currentPrice = 0;
        if (isNaN(newQty) || newQty <= 0 || isNaN(newPrice) || newPrice < 0) {
            editImpact.textContent = '—';
            editImpact.classList.remove('is-in', 'is-out');
            clearEditPreview();
            resetHistoryRowPreview();
            if (editSaveBtn) editSaveBtn.disabled = true;
            return 0;
        }
        var itemDelta = newQty - currentQty;
        var priceDelta = newPrice - currentPrice;
        var stockImpact = -itemDelta;
        var currentBalance = lastHistoryPayload && lastHistoryPayload.summary ? Number(lastHistoryPayload.summary.current_qty || 0) : 0;
        var projectedBalance = currentBalance + stockImpact;
        var rowBalance = activeEditRow ? Number(activeEditRow.balance_qty || 0) : currentBalance;
        var projectedRowBalance = rowBalance + stockImpact;
        var directionFactor = activeEditRow && activeEditRow.direction === 'in' ? 1 : -1;
        var oldLineValue = currentQty * currentPrice;
        var newLineValue = newQty * newPrice;
        var valueImpact = (newLineValue * directionFactor) - (oldLineValue * directionFactor);
        var rowValue = activeEditRow ? Number(activeEditRow.balance_value || 0) : 0;
        var projectedRowValue = rowValue + valueImpact;

        editImpact.textContent = (stockImpact > 0 ? '+' : '') + fmtUnit(stockImpact, ctx.unit_name);
        editImpact.classList.toggle('is-in', stockImpact > 0);
        editImpact.classList.toggle('is-out', stockImpact < 0);
        setSignedPreview(
            editPreviewIssue,
            fmtUnit(currentQty, ctx.unit_name) + ' → ' + fmtUnit(newQty, ctx.unit_name) + ' (' + (itemDelta > 0 ? '+' : '') + fmtUnit(itemDelta, ctx.unit_name) + ')',
            stockImpact
        );
        setSignedPreview(editPreviewStock, (stockImpact > 0 ? '+' : '') + fmtUnit(stockImpact, ctx.unit_name), stockImpact);
        setSignedPreview(
            editPreviewPrice,
            fmt.format(currentPrice) + ' → ' + fmt.format(newPrice) + ' (' + (priceDelta > 0 ? '+' : '') + fmt.format(priceDelta) + ')',
            valueImpact
        );
        setSignedPreview(
            editPreviewLineValue,
            fmt.format(oldLineValue) + ' → ' + fmt.format(newLineValue) + ' ฿',
            valueImpact
        );
        setSignedPreview(editPreviewBalance, fmtUnit(projectedBalance, ctx.unit_name), projectedBalance);
        setSignedPreview(editPreviewRowBalance, fmtUnit(projectedRowBalance, ctx.unit_name), projectedRowBalance);
        setSignedPreview(editPreviewValue, (valueImpact > 0 ? '+' : '') + fmt.format(valueImpact) + ' ฿ (สะสม ' + fmt.format(projectedRowValue) + ' ฿)', valueImpact);
        updateHistoryRowPreview(newQty, newPrice, stockImpact, valueImpact);
        var changeMagnitude = Math.max(Math.abs(itemDelta), Math.abs(priceDelta));
        if (editSaveBtn) editSaveBtn.disabled = changeMagnitude < 0.000001;
        return changeMagnitude;
    }

    function closeEditPanel() {
        if (editPanel) editPanel.hidden = true;
        hideEditMessage();
        if (activeEditRow) resetHistoryRowPreview();
        activeEditRow = null;
        clearEditPreview();
    }

    function openEditPanel(trigger) {
        if (!editPanel || !trigger || !lastHistoryPayload || !lastHistoryPayload.transactions) return;
        var detailId = trigger.getAttribute('data-detail-id');
        var row = null;
        for (var i = 0; i < lastHistoryPayload.transactions.length; i++) {
            if (String(lastHistoryPayload.transactions[i].detail_id) === String(detailId)) {
                row = lastHistoryPayload.transactions[i];
                break;
            }
        }
        if (!row) return;

        if (activeEditRow && String(activeEditRow.detail_id) !== String(row.detail_id)) {
            resetHistoryRowPreview();
        }
        activeEditRow = row;
        closeAdjustPanel();
        hideActionMessage();
        hideEditMessage();
        editPanel.hidden = false;
        if (editDoc) editDoc.textContent = (row.order_no || '-') + ' · ' + (row.source_label || 'ใบเบิก');
        if (editDetailId) editDetailId.value = row.detail_id || '';
        if (editCurrent) editCurrent.value = stripNumber(Number(row.qty || 0));
        if (editNew) editNew.value = stripNumber(Number(row.qty || 0));
        if (editCurrentPrice) editCurrentPrice.value = stripNumber(Number(row.unit_price || 0));
        if (editNewPrice) editNewPrice.value = stripNumber(Number(row.unit_price || 0));
        if (editNote && !editNote.value) editNote.value = 'แก้จำนวนใบเบิกจากประวัติการเคลื่อนไหววัสดุ';
        updateEditImpact();
        setTimeout(function () {
            if (editNew) {
                editNew.focus();
                editNew.select();
            }
        }, 30);
    }

    function setEditSaving(isSaving) {
        if (editSaveBtn) {
            var shouldDisable = true;
            if (!isSaving && lastHistoryPayload) {
                shouldDisable = Math.abs(updateEditImpact()) < 0.000001;
            }
            editSaveBtn.disabled = isSaving || shouldDisable;
            editSaveBtn.classList.toggle('disabled', isSaving);
        }
        if (editCancelBtn) editCancelBtn.disabled = isSaving;
        if (editCloseBtn) editCloseBtn.disabled = isSaving;
    }

    function saveRequisitionDetailQty() {
        if (!lastHistoryPayload || !lastHistoryPayload.meta || !editDetailId || !editNew) return;
        var newQty = parseFloat(editNew.value);
        var newPrice = parseFloat(editNewPrice ? editNewPrice.value : 0);
        if (isNaN(newQty) || newQty <= 0) {
            showEditMessage('error', 'กรุณาระบุจำนวนที่ถูกต้องให้มากกว่า 0');
            return;
        }
        if (isNaN(newPrice) || newPrice < 0) {
            showEditMessage('error', 'กรุณาระบุราคา/หน่วยให้ถูกต้อง');
            return;
        }
        var changeMagnitude = updateEditImpact();
        if (Math.abs(changeMagnitude) < 0.000001) {
            showEditMessage('error', 'จำนวนและราคาใหม่เท่ากับค่าเดิม ไม่ต้องบันทึก');
            return;
        }

        var meta = lastHistoryPayload.meta;
        var body = new URLSearchParams({
            detail_id: editDetailId.value,
            warehouse_id: meta.warehouse_id || ctx.warehouse_id,
            item_code: meta.item_code || ctx.item_code,
            qty: stripNumber(newQty),
            unit_price: stripNumber(newPrice),
            note: (editNote && editNote.value ? editNote.value : 'แก้จำนวนใบเบิกจากประวัติการเคลื่อนไหววัสดุ')
        });
        var csrfParam = document.querySelector('meta[name="csrf-param"]');
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfParam && csrfToken) {
            body.append(csrfParam.getAttribute('content'), csrfToken.getAttribute('content'));
        }

        setEditSaving(true);
        showEditMessage('info', 'กำลังบันทึกจำนวนใหม่และปรับยอดคงเหลือ...');
        fetch($jsReqDetailUpdateUrl, {
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
                if (!res || !res.success) {
                    throw new Error((res && res.message) ? res.message : 'บันทึกไม่สำเร็จ');
                }
                showEditMessage('success', 'บันทึกสำเร็จ: ' + (res.order_no || '-') + ' จำนวน ' + fmtUnit(Number(res.old_qty || 0), ctx.unit_name) + ' → ' + fmtUnit(Number(res.new_qty || 0), ctx.unit_name) + ' · ราคา ' + fmt.format(Number(res.old_unit_price || 0)) + ' → ' + fmt.format(Number(res.new_unit_price || 0)));
                if (editCurrent) editCurrent.value = stripNumber(Number(res.new_qty || newQty));
                if (editNew) editNew.value = stripNumber(Number(res.new_qty || newQty));
                if (editCurrentPrice) editCurrentPrice.value = stripNumber(Number(res.new_unit_price || newPrice));
                if (editNewPrice) editNewPrice.value = stripNumber(Number(res.new_unit_price || newPrice));
                updateEditImpact();
                if (typeof res.current_qty !== 'undefined') {
                    updateMainBalanceRow(Number(res.current_qty || 0));
                }
                loadHistory();
            })
            .catch(function (err) {
                showEditMessage('error', err && err.message ? err.message : 'บันทึกไม่สำเร็จ');
            })
            .finally(function () {
                setEditSaving(false);
            });
    }

    function setAdjustSaving(isSaving) {
        if (adjustSaveBtn) {
            adjustSaveBtn.disabled = isSaving || Math.abs(updateAdjustDelta()) < 0.000001;
            adjustSaveBtn.classList.toggle('disabled', isSaving);
        }
        if (adjustCancelBtn) adjustCancelBtn.disabled = isSaving;
        if (adjustCloseBtn) adjustCloseBtn.disabled = isSaving;
    }

    function submitAdjustment(payload) {
        if (!lastHistoryPayload || !lastHistoryPayload.meta || !lastHistoryPayload.summary) {
            return Promise.reject(new Error('ยังไม่มีข้อมูลประวัติให้ปรับยอด'));
        }
        var meta = lastHistoryPayload.meta;
        var delta = Number(payload.delta || 0);
        var current = Number(payload.current != null ? payload.current : lastHistoryPayload.summary.current_qty || 0);
        var target = Number(payload.target != null ? payload.target : current + delta);
        if (isNaN(current)) current = 0;
        if (isNaN(target) || isNaN(delta)) {
            return Promise.reject(new Error('ตัวเลขปรับยอดไม่ถูกต้อง'));
        }
        if (Math.abs(delta) < 0.000001) {
            return Promise.reject(new Error('จำนวนที่ปรับเป็น 0'));
        }

        var body = new URLSearchParams({
            warehouse_id: meta.warehouse_id || ctx.warehouse_id,
            item_code: meta.item_code || ctx.item_code,
            adjustment_qty: stripNumber(delta),
            current_qty: stripNumber(current),
            target_qty: stripNumber(target),
            lot_number: 'ADJUST',
            source: payload.source || 'item-history-inline',
            note: payload.note || 'ปรับยอดจากประวัติการเคลื่อนไหววัสดุ'
        });
        var csrfParam = document.querySelector('meta[name="csrf-param"]');
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfParam && csrfToken) {
            body.append(csrfParam.getAttribute('content'), csrfToken.getAttribute('content'));
        }
        if (payload.reverseDetailId) {
            body.append('reverse_detail_id', payload.reverseDetailId);
        }
        if (payload.reverseOrderNo) {
            body.append('reverse_order_no', payload.reverseOrderNo);
        }

        return fetch($jsAdjustSaveUrl, {
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
                if (!res || !res.success) {
                    throw new Error((res && res.message) ? res.message : 'บันทึกไม่สำเร็จ');
                }
                return { response: res, current: current, target: target, delta: delta };
            });
    }

    function saveAdjustInline() {
        if (!lastHistoryPayload || !lastHistoryPayload.meta || !lastHistoryPayload.summary) return;
        var current = parseFloat(adjustCurrent ? adjustCurrent.value : 0);
        var target = parseFloat(adjustTarget ? adjustTarget.value : NaN);
        if (isNaN(current)) current = 0;
        if (isNaN(target)) {
            showAdjustMessage('error', 'กรุณาระบุยอดตรวจนับจริง');
            return;
        }
        var delta = target - current;
        if (Math.abs(delta) < 0.000001) {
            showAdjustMessage('error', 'ยอดตรวจนับจริงเท่ากับยอดระบบ ไม่ต้องสร้างรายการปรับยอด');
            return;
        }

        setAdjustSaving(true);
        showAdjustMessage('info', 'กำลังบันทึกและรีเฟรชยอด...');
        submitAdjustment({
            delta: delta,
            current: current,
            target: target,
            source: 'item-history-inline',
            note: (adjustNote && adjustNote.value ? adjustNote.value : 'ปรับยอดจากประวัติการเคลื่อนไหววัสดุ')
        })
            .then(function (result) {
                var res = result.response;
                showAdjustMessage('success', 'บันทึกสำเร็จ เลขที่เอกสาร: ' + (res.order_no || '-'));
                if (adjustCurrent) adjustCurrent.value = stripNumber(target);
                updateAdjustDelta();
                updateMainBalanceRow(target);
                hideActionMessage();
                loadHistory();
            })
            .catch(function (err) {
                showAdjustMessage('error', err && err.message ? err.message : 'บันทึกไม่สำเร็จ');
            })
            .finally(function () {
                setAdjustSaving(false);
            });
    }

    function reverseHistoryMovement(trigger) {
        if (!trigger || trigger.disabled || !lastHistoryPayload || !lastHistoryPayload.summary) return;
        var delta = parseFloat(trigger.getAttribute('data-delta') || '0');
        var qtyText = trigger.getAttribute('data-qty-text') || '';
        var orderNo = trigger.getAttribute('data-order-no') || '-';
        var label = trigger.getAttribute('data-label') || 'รายการนี้';
        if (isNaN(delta) || Math.abs(delta) < 0.000001) return;

        var confirmText = 'ยืนยันยกเลิกผลของรายการนี้?' + String.fromCharCode(10) +
            'เอกสาร: ' + orderNo + String.fromCharCode(10) +
            'รายการ: ' + label + String.fromCharCode(10) +
            'จำนวนที่จะปรับกลับ: ' + qtyText;
        if (!confirm(confirmText)) return;

        var current = Number(lastHistoryPayload.summary.current_qty || 0);
        if (isNaN(current)) current = 0;
        var target = current + delta;
        trigger.disabled = true;
        hideAdjustMessage();
        showActionMessage('info', 'กำลังยกเลิกผลรายการและรีเฟรชยอด...');

        submitAdjustment({
            delta: delta,
            current: current,
            target: target,
            source: 'item-history-reverse',
            note: 'ยกเลิกผลรายการ ' + orderNo + ' จากประวัติการเคลื่อนไหววัสดุ',
            reverseDetailId: trigger.getAttribute('data-detail-id') || '',
            reverseOrderNo: orderNo
        })
            .then(function (result) {
                var res = result.response;
                showActionMessage('success', 'ยกเลิกผลรายการสำเร็จ เลขที่เอกสารปรับยอด: ' + (res.order_no || '-'));
                updateMainBalanceRow(target);
                loadHistory(true);
            })
            .catch(function (err) {
                showActionMessage('error', err && err.message ? err.message : 'ยกเลิกผลรายการไม่สำเร็จ');
                trigger.disabled = false;
            });
    }

    // Stat tick: ค่อย ๆ นับเลขจากเดิม → ใหม่ ภายใน 240ms; ผลต่างน้อย < 0.5 → set ทันที
    var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function tickValue(id, target, unit) {
        var el = document.getElementById(id);
        if (!el) return;
        var raw = parseFloat(String(el.dataset.raw || '0').replace(/[^0-9.\-]/g, ''));
        if (isNaN(raw)) raw = 0;
        var delta = target - raw;
        el.dataset.raw = String(target);
        if (prefersReducedMotion || Math.abs(delta) < 0.5) {
            el.textContent = fmtUnit(target, unit);
            return;
        }
        var start = performance.now();
        var dur = 240;
        function step(now) {
            var t = Math.min(1, (now - start) / dur);
            // ease-out-expo
            var eased = t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
            var v = raw + delta * eased;
            el.textContent = fmtUnit(v, unit);
            if (t < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
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
                '<td class="text-end"><div class="bal-skeleton ms-auto" style="width:28px"></div></td>' +
                '</tr>';
        }
        tbody.innerHTML = rows;
    }

    function escHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
        });
    }

    // 'dd/mm/yyyy' (พ.ศ.) → 'yyyy-mm-dd' (ค.ศ.) สำหรับส่ง backend
    function toGregorianISO(thaiDate) {
        if (!thaiDate) return '';
        var parts = String(thaiDate).split('/');
        if (parts.length !== 3) return thaiDate; // เผื่อ user paste ISO ตรงๆ
        var day = parts[0].padStart(2, '0');
        var month = parts[1].padStart(2, '0');
        var year = parseInt(parts[2], 10);
        if (year > 2500) year -= 543; // พ.ศ. → ค.ศ.
        return year + '-' + month + '-' + day;
    }

    function loadHistory(keepActionMessage) {
        if (!ctx.item_code || !ctx.warehouse_id) return;
        var sd = toGregorianISO(document.getElementById('hist-start-date').value);
        var ed = toGregorianISO(document.getElementById('hist-end-date').value);
        skeletonRows();
        if (exportBtn) exportBtn.disabled = true;
        if (adjustBtn) adjustBtn.disabled = true;
        if (!keepActionMessage) hideActionMessage();
        lastHistoryPayload = null;

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
                lastHistoryPayload = res;
                render(res);
                syncAdjustPanelCurrent(res);
                if (exportBtn) exportBtn.disabled = false;
                if (adjustBtn) adjustBtn.disabled = false;
            })
            .catch(function () {
                var tbody = document.getElementById('hist-tbody');
                tbody.innerHTML = '<tr><td colspan="10" class="text-center bal-history-empty">' +
                    '<i class="bi bi-exclamation-triangle me-1"></i>โหลดข้อมูลไม่สำเร็จ ลองอีกครั้ง</td></tr>';
            });
    }

    function exportExcel() {
        if (!ctx.item_code || !ctx.warehouse_id) return;
        var sd = toGregorianISO(document.getElementById('hist-start-date').value);
        var ed = toGregorianISO(document.getElementById('hist-end-date').value);
        var params = new URLSearchParams({
            item_code: ctx.item_code,
            warehouse_id: ctx.warehouse_id,
            start_date: sd,
            end_date: ed
        });
        window.location.href = $jsExportHistoryUrl + '?' + params.toString();
    }

    if (adjustBtn) adjustBtn.onclick = openAdjustPanel;
    if (adjustTarget) adjustTarget.addEventListener('input', updateAdjustDelta);
    if (adjustSaveBtn) adjustSaveBtn.onclick = saveAdjustInline;
    if (adjustCancelBtn) adjustCancelBtn.onclick = closeAdjustPanel;
    if (adjustCloseBtn) adjustCloseBtn.onclick = closeAdjustPanel;
    if (editNew) editNew.addEventListener('input', updateEditImpact);
    if (editNewPrice) editNewPrice.addEventListener('input', updateEditImpact);
    if (editSaveBtn) editSaveBtn.onclick = saveRequisitionDetailQty;
    if (editCancelBtn) editCancelBtn.onclick = closeEditPanel;
    if (editCloseBtn) editCloseBtn.onclick = closeEditPanel;
    modalEl.addEventListener('click', function (event) {
        var editTrigger = event.target.closest ? event.target.closest('.hist-edit-btn') : null;
        if (editTrigger) {
            event.preventDefault();
            openEditPanel(editTrigger);
            return;
        }
        var reverseTrigger = event.target.closest ? event.target.closest('.hist-reverse-btn') : null;
        if (reverseTrigger) {
            event.preventDefault();
            reverseHistoryMovement(reverseTrigger);
            return;
        }
        var trigger = event.target.closest ? event.target.closest('#hist-link-adjust') : null;
        if (!trigger) return;
        event.preventDefault();
        openAdjustPanel();
    });

    function buildQuickLinks(itemCode, warehouseId, variance) {
        var ic = encodeURIComponent(itemCode);
        var wh = encodeURIComponent(warehouseId);
        return {
            adjust: '#',
            // Dashboard รวมยอดติดลบทั้งระบบ
            negative: '/inventory-v2/stock-adjust/negative-balance',
            // หน้า receive (สำหรับ INITIAL ยอดตั้งต้น)
            initial: '/inventory-v2/receive/create',
            // ตรวจ stock-detail ของพัสดุนี้ (Yii SearchModel filter)
            detail: '/inventory-v2/stock-detail/index?StockDetailSearch%5Bitem_code%5D=' + ic
        };
    }

    function renderVariance(res, unit) {
        var variancePanel = document.getElementById('hist-variance');
        var matchPanel = document.getElementById('hist-match');
        if (!variancePanel || !matchPanel) return;

        var lastRunning = res.transactions.length
            ? Number(res.transactions[res.transactions.length - 1].balance_qty)
            : Number(res.summary.qty_bf);
        var truth = Number(res.summary.current_qty);
        var variance = truth - lastRunning;
        var isOutOfSync = Math.abs(variance) > 0.001;
        var endIsToday = res.meta.end_date === todayISO();

        // ถ้าผู้ใช้เลือก end_date ก่อนวันนี้ → variance ไม่มีความหมาย (truth = ปัจจุบัน)
        if (!endIsToday) {
            variancePanel.hidden = true;
            matchPanel.hidden = true;
            return;
        }

        if (isOutOfSync) {
            matchPanel.hidden = true;
            // populate ตัวเลข
            setText('hist-variance-delta', (variance > 0 ? '+' : '') + fmtUnit(variance, unit));
            setText('hist-variance-running', fmtUnit(lastRunning, unit));
            setText('hist-variance-truth', fmtUnit(truth, unit));
            // populate link
            var links = buildQuickLinks(ctx.item_code, ctx.warehouse_id, variance);
            var lAdjust = document.getElementById('hist-link-adjust');
            var lNegative = document.getElementById('hist-link-negative');
            var lInitial = document.getElementById('hist-link-initial');
            var lDetail = document.getElementById('hist-link-detail');
            if (lAdjust) lAdjust.href = links.adjust;
            if (lNegative) lNegative.href = links.negative;
            if (lInitial) lInitial.href = links.initial;
            if (lDetail) lDetail.href = links.detail;
            // copy-to-clipboard hint: ตัวเลขที่ user ต้องกรอกใน "จำนวนที่ปรับ" คือ variance
            var copyVal = document.getElementById('hist-copy-val');
            if (copyVal) copyVal.textContent = (variance > 0 ? '+' : '') + fmtUnit(variance, unit);
            // reveal กับ subtle enter
            variancePanel.hidden = false;
            variancePanel.classList.add('is-entering');
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    variancePanel.classList.remove('is-entering');
                });
            });
        } else {
            variancePanel.hidden = true;
            matchPanel.hidden = false;
        }
    }

    function render(res) {
        var unit = res.meta.unit_name || ctx.unit_name || '-';

        // Stat values พร้อม tick animation
        tickValue('hist-bf-qty', Number(res.summary.qty_bf), unit);
        setText('hist-bf-value', fmt.format(res.summary.value_bf));
        tickValue('hist-total-in', Number(res.summary.total_in), unit);
        tickValue('hist-total-out', Number(res.summary.total_out), unit);
        tickValue('hist-current-qty', Number(res.summary.current_qty), unit);
        setText('hist-tx-count', fmtInt.format(res.summary.tx_count));
        setText('hist-total-in-value', fmt.format(Number(res.summary.total_in_value || 0)));
        setText('hist-total-out-value', fmt.format(Number(res.summary.total_out_value || 0)));
        setText('hist-current-value', fmt.format(Number(res.summary.current_value || 0)));

        setText('hist-unit-name', 'หน่วย: ' + unit);

        if (res.meta.image_url) {
            var thumb = document.getElementById('hist-thumb');
            if (thumb && thumb.src !== res.meta.image_url) {
                thumb.src = res.meta.image_url;
            }
        }

        var tbody = document.getElementById('hist-tbody');
        var html = '';

        // ยอดยกมา (bf-row) — ถ้าติดลบ → แดง
        var bfNegCls = res.summary.qty_bf < 0 ? ' is-negative' : '';
        var bfValNegCls = res.summary.value_bf < 0 ? ' is-negative' : '';
        var bfSrText = res.summary.qty_bf < 0 ? '<span class="visually-hidden">ยอดติดลบ </span>' : '';
        html += '<tr class="bf-row">' +
            '<td colspan="3" class="text-end">' +
                '<i class="bi bi-arrow-bar-right me-1"></i>ยอดยกมา ณ ' + escHtml(res.meta.start_date) +
            '</td>' +
            '<td class="text-center"><span class="muted">—</span></td>' +
            '<td class="text-end"><span class="muted">—</span></td>' +
            '<td class="text-end"><span class="muted">—</span></td>' +
            '<td class="text-end' + bfNegCls + '">' + bfSrText + fmtUnit(res.summary.qty_bf, unit) + '</td>' +
            '<td class="text-end d-none d-md-table-cell' + bfValNegCls + '">' + fmt.format(res.summary.value_bf) + '</td>' +
            '<td class="d-none d-md-table-cell"><span class="muted">—</span></td>' +
            '<td class="text-end"><span class="muted">—</span></td>' +
        '</tr>';

        if (!res.transactions.length) {
            html += '<tr><td colspan="10" class="text-center bal-history-empty">' +
                '<i class="bi bi-inbox me-1"></i>ไม่พบการเคลื่อนไหวในช่วงเวลานี้</td></tr>';
        } else {
            res.transactions.forEach(function (t) {
                var pillCls = t.direction === 'in' ? 'in' : 'out';
                var pillLabel = t.direction === 'in' ? 'รับเข้า' : 'จ่ายออก';
                var pillIcon = t.direction === 'in' ? 'arrow-down-left' : 'arrow-up-right';
                var sign = t.direction === 'in' ? '+' : '-';
                var balCls = t.balance_qty < 0 ? ' is-negative' : '';
                var balValCls = t.balance_value < 0 ? ' is-negative' : '';
                var balSr = t.balance_qty < 0 ? '<span class="visually-hidden">ยอดติดลบ </span>' : '';
                var balValSr = t.balance_value < 0 ? '<span class="visually-hidden">ยอดติดลบ </span>' : '';
                var actionHtml = t.can_edit_qty
                    ? '<button type="button" class="hist-edit-btn" title="แก้จำนวนในใบเบิก" aria-label="แก้จำนวนในใบเบิก" ' +
                        'data-detail-id="' + escHtml(t.detail_id) + '" ' +
                        'data-order-no="' + escHtml(t.order_no) + '" ' +
                        'data-label="' + escHtml(t.source_label) + '">' +
                        '<i class="bi bi-pencil-square" aria-hidden="true"></i>' +
                    '</button>'
                    : '<span class="muted" title="แก้จำนวนได้เฉพาะรายการใบเบิก">—</span>';

                html += '<tr class="hist-tx-row" data-detail-id="' + escHtml(t.detail_id) + '">' +
                    '<td class="text-nowrap">' + escHtml(t.date) + ' <span class="text-meta">' + escHtml(t.time) + '</span></td>' +
                    '<td class="text-nowrap"><span class="doc-chip">' + escHtml(t.order_no) + '</span></td>' +
                    '<td>' + escHtml(t.source_label) + '</td>' +
                    '<td class="text-center">' +
                        '<span class="direction-pill" title="' + pillLabel + '" aria-label="' + pillLabel + '">' +
                            '<i class="bi bi-' + pillIcon + '" aria-hidden="true"></i>' +
                        '</span>' +
                    '</td>' +
                    '<td class="text-end fw-semibold hist-cell-qty">' + sign + fmtUnit(t.qty, unit) + '</td>' +
                    '<td class="text-end text-meta hist-cell-price">' + fmt.format(t.unit_price) + '</td>' +
                    '<td class="text-end fw-semibold hist-cell-balance' + balCls + '">' + balSr + fmtUnit(t.balance_qty, unit) + '</td>' +
                    '<td class="text-end d-none d-md-table-cell hist-cell-value' + balValCls + '">' + balValSr + fmt.format(t.balance_value) + '</td>' +
                    '<td class="d-none d-md-table-cell text-meta">' + escHtml(t.lot) + '</td>' +
                    '<td class="text-end">' + actionHtml + '</td>' +
                '</tr>';
            });
        }

        tbody.innerHTML = html;
        renderVariance(res, unit);
        updateMainBalanceRow(Number(res.summary.current_qty || 0));
    }

    modalEl.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        if (!btn) return;
        ctx.item_code = btn.getAttribute('data-item-code');
        ctx.warehouse_id = btn.getAttribute('data-warehouse-id');
        ctx.unit_name = btn.getAttribute('data-unit-name') || '-';
        activeBalanceRow = btn.closest('tr');
        closeAdjustPanel();
        closeEditPanel();
        hideActionMessage();

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

        ['hist-bf-qty','hist-bf-value','hist-current-qty','hist-current-value','hist-total-in-value','hist-total-out-value'].forEach(function (id) {
            setText(id, '0.00');
            var el = document.getElementById(id);
            if (el) el.dataset.raw = '0';
        });
        setText('hist-total-in', '0');
        setText('hist-total-out', '0');
        ['hist-total-in','hist-total-out'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.dataset.raw = '0';
        });
        setText('hist-tx-count', '0');

        // ซ่อน variance/match จาก modal เปิดครั้งก่อน
        var vp = document.getElementById('hist-variance');
        var mp = document.getElementById('hist-match');
        if (vp) vp.hidden = true;
        if (mp) mp.hidden = true;
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

    // Copy ตัวเลข variance ไปวางในฟอร์มปรับยอด
    var copyBtn = document.getElementById('hist-copy-qty');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var val = document.getElementById('hist-copy-val');
            if (!val) return;
            var text = val.textContent.trim();
            // strip leading + (input รับเลขปกติ ไม่ต้องมี +)
            if (text.charAt(0) === '+') text = text.substring(1);
            // strip thousand separator + เครื่องหมายภาษาไทย → จำนวนเต็ม/ทศนิยม
            text = text.replace(/[^0-9.\-]/g, '');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    copyBtn.classList.add('is-copied');
                    setTimeout(function () { copyBtn.classList.remove('is-copied'); }, 1400);
                });
            } else {
                // fallback
                var ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); } catch (e) {}
                document.body.removeChild(ta);
                copyBtn.classList.add('is-copied');
                setTimeout(function () { copyBtn.classList.remove('is-copied'); }, 1400);
            }
        });
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
