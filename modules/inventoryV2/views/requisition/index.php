<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use app\modules\hr\models\Employees;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\modules\inventoryV2\models\RequisitionSearch $searchModel */

$this->title = 'รายการใบขอเบิกวัสดุ';
$this->params['breadcrumbs'][] = $this->title;

$canCreateRequisition = (bool) ($canCreateRequisition ?? false);

$currentWarehouseId = Yii::$app->request->get('warehouse_id');
$currentWarehouseId = is_numeric($currentWarehouseId) ? (int) $currentWarehouseId : null;
if ($currentWarehouseId === null && !empty($searchModel->sub_warehouse_id)) {
    $currentWarehouseId = (int) $searchModel->sub_warehouse_id;
}

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$totalCount = $dataProvider->getTotalCount();

/* ---------- batch prefetch — ตัด N+1 ---------- */
$warehouseIds = [];
$empIds = [];
$userIds = [];
foreach ($models as $m) {
    if ($m->main_warehouse_id) $warehouseIds[] = (int) $m->main_warehouse_id;
    if ($m->sub_warehouse_id) $warehouseIds[] = (int) $m->sub_warehouse_id;
    foreach (['requester', 'approver'] as $role) {
        $eid = $m->getIssueSignatureEmpId($role);
        if ($eid) $empIds[] = (int) $eid;
    }
    if (!$m->getIssueSignatureEmpId('requester') && !empty($m->created_by)) {
        $userIds[] = (int) $m->created_by;
    }
}
$warehousesById = $warehouseIds
    ? Warehouse::find()->where(['id' => array_values(array_unique($warehouseIds))])->indexBy('id')->all()
    : [];
$empsById = $empIds
    ? Employees::find()->where(['id' => array_values(array_unique($empIds))])->indexBy('id')->all()
    : [];
$empsByUserId = $userIds
    ? Employees::find()->where(['user_id' => array_values(array_unique($userIds))])->indexBy('user_id')->all()
    : [];

/* ---------- status badge — ใช้ของจาก model ---------- */
$renderStatus = function ($status) {
    $s = StockOrder::getStatusBadgeConfigFor($status);
    $icon = !empty($s['icon'])
        ? '<i data-lucide="' . Html::encode($s['icon']) . '" class="me-1" style="width:14px;height:14px;vertical-align:-0.2em"></i>'
        : '';
    return '<span class="' . $s['class'] . '">' . $icon . Html::encode($s['label']) . '</span>';
};
$toListTimestamp = function ($value) {
    if (empty($value)) {
        return null;
    }
    if (is_numeric($value)) {
        return (int) $value;
    }
    $timestamp = strtotime((string) $value);
    return $timestamp === false ? null : $timestamp;
};
$formatListDate = function ($value, $timeValue = null, $withTime = true) use ($toListTimestamp) {
    $timestamp = $toListTimestamp($value);
    if ($timestamp === null) {
        return null;
    }

    $timeTimestamp = $toListTimestamp($timeValue);
    if ($withTime && $timeTimestamp !== null && date('H:i', $timeTimestamp) !== '00:00') {
        $timestamp = strtotime(date('Y-m-d', $timestamp) . ' ' . date('H:i:s', $timeTimestamp));
    }

    $thaiMonths = [
        1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม',
    ];
    $day = (int) date('j', $timestamp);
    $month = $thaiMonths[(int) date('n', $timestamp)] ?? '';
    $year = (int) date('Y', $timestamp) + 543;
    if (!$withTime) {
        return "{$day} {$month} {$year}";
    }

    $time = date('H:i', $timestamp);

    return "{$day} {$month} {$year} {$time} น.";
};
$renderDocumentDates = function (StockOrder $model) use ($formatListDate) {
    $requestDate = $formatListDate($model->order_date ?: $model->created_at, $model->created_at);

    return '<div class="req-doc-dates">'
        . '<span title="วันที่ขอเบิก">' . Html::encode($requestDate ?: '—') . '</span>'
        . '</div>';
};
$resolvePaidDate = function (StockOrder $model) use ($formatListDate) {
    if ($model->status !== StockOrder::STATUS_CONFIRMED) {
        return null;
    }
    return $formatListDate($model->disbursementDate, null, false);
};

/* ---------- person block (ชื่อ + ตำแหน่ง + avatar) ---------- */
$renderPerson = function ($emp, $fallbackName, $fallbackPosition) {
    $name = $fallbackName ?: ($emp ? trim($emp->fname . ' ' . $emp->lname) : '');
    $position = $fallbackPosition;
    if (!$position && $emp && method_exists($emp, 'positionName')) {
        $position = (string) $emp->positionName();
    }
    if ($name === '' && $position === '') {
        return '<span class="req-empty">—</span>';
    }
    $img = '';
    if ($emp && method_exists($emp, 'showAvatar')) {
        $img = Html::img('@web/img/loading.gif', [
            'class' => 'req-person__avatar lazyload',
            'data' => ['src' => $emp->showAvatar(), 'expand' => '-20', 'sizes' => 'auto'],
            'alt' => '',
        ]);
    } else {
        $initial = $name !== '' ? mb_substr($name, 0, 1, 'UTF-8') : '?';
        $img = '<span class="req-person__avatar req-person__avatar--placeholder" aria-hidden="true">' . Html::encode($initial) . '</span>';
    }
    $out = '<div class="req-person">' . $img . '<div class="req-person__meta">';
    if ($name !== '') {
        $out .= '<div class="req-person__name" title="' . Html::encode($name) . '">' . Html::encode($name) . '</div>';
    }
    if ($position !== '') {
        $out .= '<div class="req-person__position" title="' . Html::encode($position) . '">' . Html::encode($position) . '</div>';
    }
    return $out . '</div></div>';
};

/* ---------- item summary ---------- */
$itemSummaryCache = [];
$resolveItemSummary = function (StockOrder $model) use (&$itemSummaryCache) {
    $cacheKey = $model->id ?: spl_object_hash($model);
    if (isset($itemSummaryCache[$cacheKey])) {
        return $itemSummaryCache[$cacheKey];
    }

    $details = $model->stockDetails ?: [];
    $amount = 0.0;
    $types = [];
    foreach ($details as $detail) {
        $amount += (float) ($detail->qty ?? 0) * (float) ($detail->unit_price ?? 0);

        $item = $detail->item;
        $category = $item ? $item->categoryType : null;
        $typeName = $category && trim((string) ($category->title ?? '')) !== ''
            ? trim((string) $category->title)
            : ($item && trim((string) ($item->category_id ?? '')) !== '' ? trim((string) $item->category_id) : '');

        if ($typeName !== '') {
            $types[$typeName] = $typeName;
        }
    }

    return $itemSummaryCache[$cacheKey] = [
        'lineCount' => count($details),
        'amount' => $amount,
        'types' => array_values($types),
    ];
};
$renderItemSummary = function (array $summary) {
    $out = '<div class="req-item-summary">';
    $out .= '<div class="req-item-summary__metrics">';
    $out .= '<span class="req-item-summary__metric"><span class="req-item-summary__label">จำนวน</span><strong>' . number_format((int) $summary['lineCount']) . '</strong><span>รายการ</span></span>';
    $out .= '</div>';
    $out .= '<div class="req-item-summary__badges">';
    if (!empty($summary['types'])) {
        foreach ($summary['types'] as $typeName) {
            $out .= '<span class="req-item-summary__badge">' . Html::encode($typeName) . '</span>';
        }
    } elseif ((int) $summary['lineCount'] > 0) {
        $out .= '<span class="req-item-summary__badge req-item-summary__badge--muted">ไม่ระบุประเภท</span>';
    } else {
        $out .= '<span class="req-empty">—</span>';
    }
    return $out . '</div></div>';
};
$renderAmountValue = function (array $summary) {
    return '<span class="req-amount-value">' . Html::encode(number_format((float) $summary['amount'], 4, '.', '')) . '</span>';
};
$renderPaidDateValue = function (StockOrder $model) use ($resolvePaidDate) {
    $paidDate = $resolvePaidDate($model);
    return $paidDate !== null
        ? '<span class="req-paid-date">' . Html::encode($paidDate) . '</span>'
        : '<span class="req-empty">—</span>';
};
$renderMobileSettlement = function (array $summary, StockOrder $model) use ($renderAmountValue, $renderPaidDateValue) {
    return '<div class="req-card-settlement">'
        . '<span class="req-card-settlement__item"><span class="req-card-settlement__label">มูลค่า</span>' . $renderAmountValue($summary) . '</span>'
        . '<span class="req-card-settlement__item"><span class="req-card-settlement__label">วันที่จ่าย</span>' . $renderPaidDateValue($model) . '</span>'
        . '</div>';
};

/* ---------- resolvers ---------- */
$resolveRequester = function (StockOrder $model) use ($empsById, $empsByUserId) {
    $eid = $model->getIssueSignatureEmpId('requester');
    if ($eid && isset($empsById[$eid])) return $empsById[$eid];
    if (!empty($model->created_by) && isset($empsByUserId[$model->created_by])) {
        return $empsByUserId[$model->created_by];
    }
    return null;
};
$resolveApprover = function (StockOrder $model) use ($empsById) {
    $eid = $model->getIssueSignatureEmpId('approver');
    return $eid && isset($empsById[$eid]) ? $empsById[$eid] : null;
};
$warehouseName = function ($id) use ($warehousesById) {
    return $id && isset($warehousesById[$id]) ? $warehousesById[$id]->warehouse_name : null;
};
$mainWarehouseLabel = $searchModel->getAttributeLabel('main_warehouse_id');
$recipientUnitLabel = $searchModel->getAttributeLabel('sub_warehouse_id');
$currentWarehouseName = $warehouseName($currentWarehouseId);
?>

<?php $this->beginBlock('page-title'); ?>
<?= $this->render('@app/modules/inventoryV2/views/sub-stock/_page_head', [
    'icon'  => 'bi-file-earmark-text',
    'title' => $this->title,
    'currentWarehouseId' => $currentWarehouseId,
    'currentWarehouseName' => $currentWarehouseName ?? null,
]) ?>
<?php $this->endBlock(); ?>

<?php
$subStockActionMenu = $this->render('@app/modules/inventoryV2/views/sub-stock/_menu_sub_stock', [
    'active' => 'requisition',
    'currentWarehouseId' => $currentWarehouseId,
]);
$createRequisitionButton = $canCreateRequisition
    ? Html::a('<i class="bi bi-plus-circle me-1"></i> สร้างใบขอเบิกใหม่', ['create'], ['class' => 'btn btn-success rounded-pill'])
    : '';
$subStockActions = Html::tag('div', $subStockActionMenu, [
    'class' => 'd-flex flex-wrap gap-2 justify-content-center justify-content-lg-end align-items-center',
]);
foreach (['action', 'page-action'] as $actionBlock) {
    $this->beginBlock($actionBlock);
    echo $subStockActions;
    $this->endBlock();
}
?>

<div class="requisition-index">
    <?= $this->render('_search', ['searchModel' => $searchModel]) ?>

    <div class="card shadow-sm">
        <div class="req-list-head">
            <div class="req-list-head__text">
                <h2 class="req-list-head__title">รายการใบขอเบิก</h2>
                <span class="req-list-head__caption"><?= number_format($totalCount) ?> รายการ</span>
            </div>
            <?= $createRequisitionButton ?>
        </div>
        <div class="card-body p-0">

            <?php if (empty($models)): ?>
                <?php
                $hasFilter = !empty($searchModel->order_no)
                    || !empty($searchModel->main_warehouse_id)
                    || !empty($searchModel->sub_warehouse_id)
                    || !empty($searchModel->status)
                    || !empty($searchModel->date_filter)
                    || !empty($searchModel->date_start)
                    || !empty($searchModel->date_end)
                    || !empty($searchModel->q_requester_emp_id)
                    || !empty($searchModel->q_approver_emp_id);
                ?>
                <div class="req-empty-state">
                    <?php if ($hasFilter): ?>
                        <h3 class="req-empty-state__title">ไม่พบใบขอเบิกตามเงื่อนไข</h3>
                        <p class="req-empty-state__caption">ลองเปลี่ยนช่วงวันที่ ผู้ขอเบิก หรือสถานะ หรือกดล้างตัวกรองเพื่อดูทั้งหมด</p>
                        <?= Html::a('<i class="bi bi-eraser me-1"></i> ล้างตัวกรอง', ['index'], ['class' => 'btn btn-outline-secondary rounded-pill']) ?>
                    <?php else: ?>
                        <h3 class="req-empty-state__title">ยังไม่มีใบขอเบิก</h3>
                        <p class="req-empty-state__caption">สร้างใบขอเบิกใบแรก ระบบจะส่งให้หัวหน้าหน่วยงานอนุมัติอัตโนมัติ</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>

                <!-- ============ Desktop (≥992px) ============ -->
                <div class="req-table-wrap d-none d-lg-block">
                    <table class="req-table">
                        <thead>
                            <tr>
                                <th class="req-table__no" scope="col">#</th>
                                <th class="req-table__doc" scope="col">เลขที่เอกสาร</th>
                                <th scope="col">ผู้ขอเบิก</th>
                                <th scope="col"><?= Html::encode($mainWarehouseLabel) ?></th>
                                <th scope="col"><?= Html::encode($recipientUnitLabel) ?></th>
                                <th class="req-table__items" scope="col">รายการเบิก</th>
                                <th class="req-table__amount" scope="col">มูลค่า</th>
                                <th class="req-table__paid-date" scope="col">วันที่จ่าย</th>
                                <th scope="col">หัวหน้าตรวจสอบ</th>
                                <th class="req-table__status" scope="col">สถานะ</th>
                                <th class="req-table__action" scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $offset = $pagination ? $pagination->getOffset() : 0; ?>
                            <?php foreach ($models as $idx => $model): /** @var StockOrder $model */ ?>
                                <?php
                                $requesterEmp = $resolveRequester($model);
                                $requesterSig = $model->getIssueSignature('requester');
                                $approverEmp = $resolveApprover($model);
                                $approverSig = $model->getIssueSignature('approver');
                                $mainWarehouseName = $warehouseName($model->main_warehouse_id) ?? '—';
                                $recipientUnitName = $warehouseName($model->sub_warehouse_id) ?? '—';
                                $itemSummary = $resolveItemSummary($model);
                                ?>
                                <tr>
                                    <td class="req-table__no"><?= $offset + $idx + 1 ?></td>
                                    <td class="req-table__doc">
                                        <?= Html::a(Html::encode($model->order_no), ['view', 'id' => $model->id], [
                                            'class' => 'req-doc-link open-modal',
                                            'data' => ['size' => 'modal-xl'],
                                        ]) ?>
                                        <?= $renderDocumentDates($model) ?>
                                    </td>
                                    <td>
                                        <?= $renderPerson($requesterEmp, $requesterSig['name'] ?? '', $requesterSig['position'] ?? '') ?>
                                    </td>
                                    <td class="req-table__warehouse" title="<?= Html::encode($mainWarehouseName) ?>">
                                        <?= Html::encode($mainWarehouseName) ?>
                                    </td>
                                    <td class="req-table__warehouse" title="<?= Html::encode($recipientUnitName) ?>">
                                        <?= Html::encode($recipientUnitName) ?>
                                    </td>
                                    <td class="req-table__items">
                                        <?= $renderItemSummary($itemSummary) ?>
                                    </td>
                                    <td class="req-table__amount">
                                        <?= $renderAmountValue($itemSummary) ?>
                                    </td>
                                    <td class="req-table__paid-date">
                                        <?= $renderPaidDateValue($model) ?>
                                    </td>
                                    <td>
                                        <?= $renderPerson($approverEmp, $approverSig['name'] ?? '', $approverSig['position'] ?? '') ?>
                                    </td>
                                    <td class="req-table__status">
                                        <?= $renderStatus($model->status) ?>
                                    </td>
                                    <td class="req-table__action">
                                        <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-outline-primary open-modal',
                                            'title' => 'ดูรายละเอียด',
                                            'aria-label' => 'ดูรายละเอียด',
                                            'data' => ['size' => 'modal-xl'],
                                        ]) ?>
                                        <?php if ($model->canEdit()): ?>
                                            <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $model->id], [
                                                'class' => 'btn btn-sm btn-outline-secondary',
                                                'title' => 'แก้ไข',
                                            ]) ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ============ Mobile (<992px) ============ -->
                <ul class="req-cards d-lg-none" role="list">
                    <?php foreach ($models as $model): /** @var StockOrder $model */ ?>
                        <?php
                        $requesterEmp = $resolveRequester($model);
                        $requesterSig = $model->getIssueSignature('requester');
                        $approverEmp = $resolveApprover($model);
                        $approverSig = $model->getIssueSignature('approver');
                        $mainWarehouseName = $warehouseName($model->main_warehouse_id) ?? '—';
                        $recipientUnitName = $warehouseName($model->sub_warehouse_id) ?? '—';
                        $itemSummary = $resolveItemSummary($model);
                        ?>
                        <li class="req-card">
                            <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="req-card__main open-modal" data-size="modal-xl">
                                <div class="req-card__head">
                                    <span class="req-card__doc">
                                        <?= Html::encode($model->order_no) ?>
                                        <?php if ($model->isMigratedFromV1()): ?>
                                            <span class="badge bg-secondary ms-1" title="ย้ายมาจาก Inventory V1">V1</span>
                                        <?php endif; ?>
                                    </span>
                                    <?= $renderStatus($model->status) ?>
                                </div>
                                <?= $renderDocumentDates($model) ?>
                                <?= $renderItemSummary($itemSummary) ?>
                                <?= $renderMobileSettlement($itemSummary, $model) ?>
                                <div class="req-card__meta">
                                    <span class="req-card__route">
                                        <span class="req-card__route-item">
                                            <span class="req-card__route-label"><?= Html::encode($mainWarehouseLabel) ?></span>
                                            <span class="req-card__route-value"><?= Html::encode($mainWarehouseName) ?></span>
                                        </span>
                                        <span class="req-card__route-arrow" aria-hidden="true">→</span>
                                        <span class="req-card__route-item">
                                            <span class="req-card__route-label"><?= Html::encode($recipientUnitLabel) ?></span>
                                            <span class="req-card__route-value"><?= Html::encode($recipientUnitName) ?></span>
                                        </span>
                                    </span>
                                </div>
                                <div class="req-card__people">
                                    <div class="req-card__person">
                                        <span class="req-card__person-label">ผู้ขอเบิก</span>
                                        <?= $renderPerson($requesterEmp, $requesterSig['name'] ?? '', $requesterSig['position'] ?? '') ?>
                                    </div>
                                    <div class="req-card__person">
                                        <span class="req-card__person-label">หัวหน้าตรวจสอบ</span>
                                        <?= $renderPerson($approverEmp, $approverSig['name'] ?? '', $approverSig['position'] ?? '') ?>
                                    </div>
                                </div>
                            </a>
                            <?php if ($model->canEdit()): ?>
                                <div class="req-card__actions">
                                    <?= Html::a('<i class="bi bi-pencil me-1"></i>แก้ไข', ['update', 'id' => $model->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                    ]) ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($pagination && $pagination->getPageCount() > 1): ?>
                    <div class="req-pager">
                        <div class="req-pager__info">
                            หน้า <?= $pagination->getPage() + 1 ?> จาก <?= $pagination->getPageCount() ?>
                        </div>
                        <?= LinkPager::widget([
                            'pagination' => $pagination,
                            'options' => ['class' => 'pagination pagination-sm mb-0'],
                            'maxButtonCount' => 5,
                        ]) ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.requisition-index {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #718096;
    --ink-4: #a0aec0;
    --surface: #fff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, 0.08);
    --primary-ink: #0a58ca;
    --primary-soft: rgba(13, 110, 253, 0.08);
    --radius-xs: 6px;
    color: var(--ink-1);
}

.req-list-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.75rem 0.9rem;
    background: var(--surface);
    border-bottom: 1px solid var(--line);
}
.req-list-head__text {
    min-width: 0;
}
.req-list-head__title {
    margin: 0;
    color: var(--ink-1);
    font-size: 0.98rem;
    font-weight: 600;
    line-height: 1.25;
}
.req-list-head__caption {
    display: block;
    margin-top: 0.1rem;
    color: var(--ink-3);
    font-size: 0.78rem;
    line-height: 1.3;
}

/* desktop table */
.req-table-wrap { width: 100%; }
.req-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin: 0;
    font-size: 0.88rem;
}
.req-table thead th {
    position: sticky; top: 0; z-index: 1;
    background: var(--surface-2);
    color: var(--ink-2);
    font-weight: 600;
    font-size: 0.78rem;
    text-align: left;
    padding: 0.6rem 0.9rem;
    border-bottom: 1px solid var(--line);
    white-space: nowrap;
}
.req-table tbody td {
    padding: 0.65rem 0.9rem;
    border-bottom: 1px solid var(--line);
    vertical-align: middle;
    color: var(--ink-1);
}
.req-table tbody tr:hover td { background: var(--surface-hover); }
.req-table tbody tr:last-child td { border-bottom: none; }

.req-table__no {
    width: 42px;
    text-align: center;
    color: var(--ink-3);
    font-variant-numeric: tabular-nums;
    font-size: 0.82rem;
}
.req-table__doc { width: 1%; white-space: nowrap; }
.req-table__warehouse { color: var(--ink-2); max-width: 14rem; }
.req-table__items {
    min-width: 15rem;
    max-width: 20rem;
}
.req-table__amount {
    width: 1%;
    text-align: right;
    white-space: nowrap;
}
.req-table th.req-table__amount {
    text-align: right;
}
.req-table__paid-date {
    width: 1%;
    color: var(--ink-2);
    white-space: nowrap;
}
.req-table__status { width: 1%; white-space: nowrap; }
.req-table__action { width: 1%; white-space: nowrap; text-align: right; }
.req-table__action .btn + .btn { margin-left: 0.25rem; }

.req-doc-link {
    color: var(--primary-ink);
    font-weight: 600;
    text-decoration: none;
    font-variant-numeric: tabular-nums;
}
.req-doc-link:hover { text-decoration: underline; }
.req-doc-dates {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.25rem 0.4rem;
    margin-top: 0.18rem;
    color: var(--ink-3);
    font-size: 0.76rem;
    font-weight: 500;
    line-height: 1.35;
    white-space: normal;
}
.req-amount-value {
    display: inline-block;
    min-width: 5.8rem;
    color: var(--ink-1);
    font-variant-numeric: tabular-nums;
    font-weight: 800;
    text-align: right;
}
.req-paid-date {
    color: var(--ink-2);
    font-size: 0.82rem;
    font-weight: 500;
    white-space: nowrap;
}

/* person */
.req-person {
    display: flex; align-items: center; gap: 0.55rem;
    min-width: 0;
}
.req-person__avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--surface-3);
    flex-shrink: 0;
    border: 1px solid var(--line);
}
.req-person__avatar--placeholder {
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--ink-2);
    font-weight: 700;
    font-size: 0.82rem;
}
.req-person__meta { min-width: 0; line-height: 1.25; }
.req-person__name {
    color: var(--ink-1);
    font-weight: 600;
    font-size: 0.86rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 14rem;
}
.req-person__position {
    color: var(--ink-3);
    font-size: 0.74rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 14rem;
    margin-top: 1px;
}
.req-empty { color: var(--ink-4); }
.req-item-summary {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    min-width: 0;
}
.req-item-summary__metrics {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem 0.7rem;
}
.req-item-summary__metric {
    display: inline-flex;
    align-items: baseline;
    gap: 0.25rem;
    color: var(--ink-2);
    font-size: 0.78rem;
    line-height: 1.25;
    white-space: nowrap;
}
.req-item-summary__metric strong {
    color: var(--ink-1);
    font-size: 0.92rem;
    font-weight: 800;
}
.req-item-summary__label {
    color: var(--ink-3);
    font-weight: 700;
}
.req-item-summary__badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}
.req-item-summary__badge {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    padding: 0.2rem 0.55rem;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 0.72rem;
    font-weight: 800;
    line-height: 1.2;
    overflow-wrap: anywhere;
}
.req-item-summary__badge--muted {
    border-color: #e2e8f0;
    background: #f8fafc;
    color: var(--ink-3);
}

/* mobile cards */
.req-cards {
    list-style: none;
    margin: 0;
    padding: 0.6rem;
    display: flex; flex-direction: column;
    gap: 0.5rem;
}
.req-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 8px;
    overflow: hidden;
}
.req-card__main {
    display: block;
    padding: 0.8rem 0.9rem;
    color: inherit;
    text-decoration: none;
}
.req-card__head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.4rem;
}
.req-card .req-item-summary {
    margin: 0.65rem 0 0.55rem;
    padding-top: 0.65rem;
    border-top: 1px solid #e2e8f0;
}
.req-card-settlement {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem 1rem;
    margin: 0.2rem 0 0.55rem;
}
.req-card-settlement__item {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 7rem;
}
.req-card-settlement__label {
    color: var(--ink-3);
    font-size: 0.72rem;
    font-weight: 700;
}
.req-card-settlement .req-amount-value {
    min-width: 0;
    text-align: left;
}
.req-card__doc {
    color: var(--primary-ink);
    font-weight: 700;
    font-size: 0.95rem;
    font-variant-numeric: tabular-nums;
}
.req-card__meta {
    color: var(--ink-2);
    font-size: 0.8rem;
    margin-bottom: 0.65rem;
}
.req-card__sep { color: var(--ink-4); margin: 0 0.4rem; }
.req-card__route {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    max-width: 100%;
    vertical-align: top;
}
.req-card__route-item {
    display: inline-flex;
    align-items: baseline;
    gap: 0.25rem;
    min-width: 0;
}
.req-card__route-label {
    color: var(--ink-3);
    font-weight: 600;
    white-space: nowrap;
}
.req-card__route-value {
    color: var(--ink-1);
    font-weight: 600;
    min-width: 0;
    max-width: 9rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.req-card__route-arrow {
    color: var(--ink-4);
    flex: 0 0 auto;
}
.req-card__people {
    padding-top: 0.65rem;
    border-top: 1px dashed var(--line);
    display: grid;
    gap: 0.55rem;
}
.req-card__person { display: flex; flex-direction: column; gap: 0.2rem; }
.req-card__person-label {
    font-size: 0.7rem;
    color: var(--ink-3);
    font-weight: 600;
}
.req-card__actions {
    padding: 0 0.9rem 0.85rem;
    display: flex; gap: 0.4rem;
}
@media (max-width: 575.98px) {
    .req-list-head {
        align-items: stretch;
        flex-direction: column;
    }
    .req-list-head .btn {
        width: 100%;
    }
    .req-card__route {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0.2rem;
        margin-top: 0.25rem;
    }
    .req-card__route-item {
        max-width: 100%;
    }
    .req-card__route-value {
        max-width: 100%;
    }
    .req-card__route-arrow {
        display: none;
    }
}

/* empty state */
.req-empty-state {
    padding: 3.5rem 1.5rem;
    text-align: center;
}
.req-empty-state__title {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--ink-1);
    margin: 0 0 0.4rem;
}
.req-empty-state__caption {
    color: var(--ink-3);
    font-size: 0.88rem;
    max-width: 26rem;
    margin: 0 auto 1.25rem;
    line-height: 1.55;
}

/* pager */
.req-pager {
    padding: 0.6rem 0.9rem;
    border-top: 1px solid var(--line);
    background: var(--surface-2);
    display: flex; align-items: center; justify-content: space-between;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.req-pager__info {
    color: var(--ink-3);
    font-size: 0.78rem;
    font-variant-numeric: tabular-nums;
}
.req-pager .pagination { margin: 0; }
</style>

<?php
$this->registerJs(<<<JS
(function () {
    function init() {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
JS, \yii\web\View::POS_END);
?>
