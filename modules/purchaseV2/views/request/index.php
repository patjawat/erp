<?php

    use app\components\AppHelper;
    use app\components\widgets\DataSummaryWidget;
    use app\modules\purchaseV2\models\PurchaseRequest;
    use yii\helpers\Html;

    $this->title                   = 'คำขอซื้อจัดจ้าง';
    $this->params['breadcrumbs'][] = ['label' => 'จัดซื้อจัดจ้าง V2', 'url' => ['/purchase-v2/default/index']];
    $this->params['breadcrumbs'][] = $this->title;

    $canManage      = Yii::$app->user->can('admin') || Yii::$app->user->can('purchase');
    $totalCount     = $dataProvider->getTotalCount();
    $models         = $dataProvider->getModels();
    $searchKey      = $searchModel->formName();
    $currentFilters = Yii::$app->request->get($searchKey, []);
    $activeGroup    = $searchModel->status_group ?: 'all';

    $statusUi = [
    PurchaseRequest::STATUS_DRAFT            => ['label' => 'ร่าง', 'color' => 'secondary', 'icon' => 'file-pen-line'],
    PurchaseRequest::STATUS_PENDING_APPROVAL => ['label' => 'รออนุมัติ', 'color' => 'warning', 'icon' => 'hourglass'],
    PurchaseRequest::STATUS_APPROVED         => ['label' => 'อนุมัติแล้ว', 'color' => 'primary', 'icon' => 'badge-check'],
    PurchaseRequest::STATUS_ORDERED          => ['label' => 'กำลังจัดซื้อ', 'color' => 'info', 'icon' => 'refresh-cw'],
    PurchaseRequest::STATUS_RECEIVED         => ['label' => 'รอตรวจรับ', 'color' => 'success', 'icon' => 'package-check'],
    PurchaseRequest::STATUS_STOCKED          => ['label' => 'รอตรวจรับ', 'color' => 'success', 'icon' => 'package-search'],
    PurchaseRequest::STATUS_COMPLETED        => ['label' => 'เสร็จสิ้น', 'color' => 'success', 'icon' => 'circle-check-big'],
    PurchaseRequest::STATUS_CANCELLED        => ['label' => 'ยกเลิก', 'color' => 'danger', 'icon' => 'x-circle'],
    ];

    $summaryCards = [
    [
        'label'      => 'รออนุมัติ',
        'count'      => $statusCounts['pending'] ?? 0,
        'icon'       => 'hourglass',
        'color'      => 'warning',
        'delta'      => '+3',
        'deltaClass' => 'bg-success-subtle text-success',
    ],
    [
        'label'      => 'อนุมัติแล้ว',
        'count'      => $statusCounts['approved'] ?? 0,
        'icon'       => 'badge-check',
        'color'      => 'primary',
        'delta'      => '+5',
        'deltaClass' => 'bg-success-subtle text-success',
    ],
    [
        'label'      => 'กำลังจัดซื้อ',
        'count'      => $statusCounts['ordered'] ?? 0,
        'icon'       => 'refresh-cw',
        'color'      => 'info',
        'delta'      => '+2',
        'deltaClass' => 'bg-success-subtle text-success',
    ],
    [
        'label'      => 'รอตรวจรับ',
        'count'      => $statusCounts['received'] ?? 0,
        'icon'       => 'package',
        'color'      => 'success',
        'delta'      => '+4',
        'deltaClass' => 'bg-success-subtle text-success',
    ],
    [
        'label'      => 'เสร็จสิ้น',
        'count'      => $statusCounts['completed'] ?? 0,
        'icon'       => 'check-check',
        'color'      => 'success',
        'delta'      => '+8',
        'deltaClass' => 'bg-success-subtle text-success',
    ],
    [
        'label'      => 'ยกเลิก',
        'count'      => $statusCounts['cancelled'] ?? 0,
        'icon'       => 'x-circle',
        'color'      => 'danger',
        'delta'      => '-1',
        'deltaClass' => 'bg-danger-subtle text-danger',
    ],
    ];

    $tabItems = [
    ['key' => 'all', 'label' => 'ทั้งหมด', 'icon' => 'layout-grid', 'count' => $statusCounts['all'] ?? $totalCount],
    ['key' => 'draft', 'label' => 'ร่าง', 'icon' => 'file-pen-line', 'count' => $statusCounts['draft'] ?? 0],
    ['key' => 'pending', 'label' => 'รออนุมัติ', 'icon' => 'hourglass', 'count' => $statusCounts['pending'] ?? 0],
    ['key' => 'approved', 'label' => 'อนุมัติแล้ว', 'icon' => 'badge-check', 'count' => $statusCounts['approved'] ?? 0],
    ['key' => 'ordered', 'label' => 'กำลังจัดซื้อ', 'icon' => 'refresh-cw', 'count' => $statusCounts['ordered'] ?? 0],
    ['key' => 'received', 'label' => 'รอตรวจรับ', 'icon' => 'package-check', 'count' => $statusCounts['received'] ?? 0],
    ['key' => 'completed', 'label' => 'เสร็จสิ้น', 'icon' => 'circle-check-big', 'count' => $statusCounts['completed'] ?? 0],
    ['key' => 'cancelled', 'label' => 'ยกเลิก', 'icon' => 'x-circle', 'count' => $statusCounts['cancelled'] ?? 0],
    ];

    $formatShortDate = static function ($date, bool $fromThaiNumeric = false): string {
    if (empty($date)) {
        return '-';
    }

    if ($fromThaiNumeric) {
        $date = AppHelper::convertToGregorian($date) ?: $date;
    }

    $timestamp = strtotime((string) $date);
    if ($timestamp === false) {
        return '-';
    }

    $thaiMonths = [
        1  => 'ม.ค.',
        2  => 'ก.พ.',
        3  => 'มี.ค.',
        4  => 'เม.ย.',
        5  => 'พ.ค.',
        6  => 'มิ.ย.',
        7  => 'ก.ค.',
        8  => 'ส.ค.',
        9  => 'ก.ย.',
        10 => 'ต.ค.',
        11 => 'พ.ย.',
        12 => 'ธ.ค.',
    ];

    $day       = date('j', $timestamp);
    $month     = $thaiMonths[(int) date('n', $timestamp)] ?? '';
    $yearShort = substr((string) (date('Y', $timestamp) + 543), -2);

    return $day . ' ' . $month . ' ' . $yearShort;
    };

    $buildTabUrl = static function (string $group) use ($searchKey, $currentFilters): array {
    $params                 = $currentFilters;
    $params['status_group'] = $group;
    unset($params['page'], $params['status']);

    if ($group === 'all') {
        $params['status_group'] = 'all';
    }

    return ['/purchase-v2/request/index', $searchKey => $params];
    };
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3 mb-3">
    <div>
        <h4 class="fw-bold mb-1"><?php echo Html::encode($this->title) ?></h4>
        <div class="text-muted small">รายการคำขอตามสถานะ พร้อมตัวกรองและการอนุมัติ</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php echo Html::button('<i data-lucide="download" class="me-1"></i> Export', [
                'class' => 'btn btn-outline-secondary btn-sm rounded-3 fw-semibold d-inline-flex align-items-center',
                'type'  => 'button',
        ]) ?>
        <?php echo Html::a('<i data-lucide="plus" class="me-1"></i> สร้างคำขอใหม่', ['/purchase-v2/request/create'], [
                'class' => 'btn btn-primary btn-sm rounded-3 fw-semibold d-inline-flex align-items-center open-modal',
                'data'  => ['size' => 'modal-xl'],
        ]) ?>
    </div>
</div>
<?php $this->endBlock(); ?>




<div class="d-flex flex-column gap-3 gap-lg-4">
    <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-xl-6">
        <?php foreach ($summaryCards as $card): ?>
        <div class="col">
            <div
                class="card border-0 shadow-sm rounded-4 h-100 border-top border-3 border-<?php echo Html::encode($card['color']) ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="d-flex flex-column gap-2">
                            <div
                                class="bg-<?php echo Html::encode($card['color']) ?> bg-opacity-10 text-<?php echo Html::encode($card['color']) ?> rounded-3 p-2 d-inline-flex align-items-center justify-content-center">
                                <i data-lucide="<?php echo Html::encode($card['icon']) ?>"></i>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold lh-1 mb-1"><?php echo number_format((int) $card['count'], 0) ?>
                                </div>
                                <div class="small text-muted"><?php echo Html::encode($card['label']) ?></div>
                            </div>
                        </div>
                        <span class="badge rounded-pill px-2 py-1 <?php echo Html::encode($card['deltaClass']) ?>">
                            <?php echo Html::encode($card['delta']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="d-flex flex-nowrap gap-2 overflow-auto">
                <?php foreach ($tabItems as $tab): ?>
                <?php
                        $isActive  = $activeGroup === $tab['key'];
                        $pillClass = $isActive
                            ? 'bg-primary text-white shadow-sm'
                            : 'text-body-secondary';
                        $iconClass  = $isActive ? 'text-white' : 'text-body-secondary';
                        $countClass = $isActive
                            ? 'bg-white text-primary'
                            : 'bg-light text-body-secondary border';
                    ?>
                <?php echo Html::a(
                            '<span class="d-inline-flex align-items-center gap-2 text-nowrap">'
                            . '<i data-lucide="' . Html::encode($tab['icon']) . '" class="' . $iconClass . '"></i>'
                            . '<span>' . Html::encode($tab['label']) . '</span>'
                            . '<span class="badge rounded-pill ' . $countClass . ' px-2 py-1">' . number_format((int) $tab['count'], 0) . '</span>'
                            . '</span>',
                            $buildTabUrl($tab['key']),
                            [
                                'class'     => 'd-inline-flex align-items-center gap-2 rounded-pill px-3 py-2 text-decoration-none fw-semibold text-nowrap ' . $pillClass,
                                'data-pjax' => 0,
                            ]
                    ) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <?php echo $this->render('_search', ['model' => $searchModel]) ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted">
                            <th class="text-nowrap">เลขที่ / วันที่</th>
                            <th>รายการ / รายละเอียด</th>
                            <th>ผู้ขอ / หน่วยงาน</th>
                            <th class="text-nowrap">ประเภทงบ</th>
                            <th class="text-end text-nowrap">จำนวนเงิน (บาท)</th>
                            <th class="text-nowrap">สถานะ</th>
                            <th class="text-nowrap">วันที่จัดการ</th>
                            <th class="text-end text-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($models as $model): ?>
                            <?php
                                $requester = $model->requesterSummary();
                                $department = $model->departmentSummary();
                                $itemCount = count($model->items);
                                $documentDate = $formatShortDate($model->request_date, true);
                                $managedDate = $formatShortDate($model->approved_at ?: $model->updated_at ?: $model->submitted_at ?: $model->request_date, false);
                                $statusMeta = $statusUi[(int) $model->status] ?? $statusUi[PurchaseRequest::STATUS_DRAFT];

                                $requestTypeColor = $model->request_type === PurchaseRequest::TYPE_PLANNED ? 'success' : 'danger';
                                $requestTypeLabel = $model->requestTypeLabel();
                                $budgetTypeLabel = trim((string) $model->budgetTypeLabel());
                                $budgetYearLabel = $model->budget_year ?: '-';
                                $vendorLabel = trim((string) $model->vendorLabel());

                                $detailLine = number_format($itemCount, 0) . ' รายการ';
                                if ($vendorLabel !== '' && $vendorLabel !== '-') {
                                    $detailLine .= ' · ' . $vendorLabel;
                                } elseif (!empty($model->summary)) {
                                    $detailLine .= ' · ' . mb_substr(trim(strip_tags((string) $model->summary)), 0, 30) . (mb_strlen((string) $model->summary) > 30 ? '…' : '');
                                } else {
                                    $detailLine .= ' · ' . $model->requestTypeLabel();
                                }
                            ?>

                            <tr>
                                <td class="align-top">
                                    <div class="text-dark fw-bold"><?= Html::encode($model->getDisplayReference()) ?></div>
                                    <div class="text-muted small"><?= Html::encode($documentDate) ?></div>
                                </td>
                                <td class="align-top">
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-bold"><?= Html::encode($model->request_title) ?></span>
                                        <span class="text-muted small"><?= Html::encode($detailLine) ?></span>
                                    </div>
                                </td>
                                <td class="align-top">
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-bold"><?= Html::encode($requester['fullname'] ?: '-') ?></span>
                                        <span class="text-muted small"><?= Html::encode($department['name'] ?: '-') ?></span>
                                    </div>
                                </td>
                                <td class="align-top">
                                    <div class="d-inline-flex align-items-center gap-2 bg-<?= Html::encode($requestTypeColor) ?>-subtle border border-<?= Html::encode($requestTypeColor) ?>-subtle rounded-pill px-3 py-1 shadow-sm">
                                        <span class="bg-<?= Html::encode($requestTypeColor) ?> rounded-circle d-inline-block" style="width:8px;height:8px;"></span>
                                        <span class="small fw-semibold text-<?= Html::encode($requestTypeColor) ?>-emphasis">
                                            <?= Html::encode($requestTypeLabel) ?>
                                        </span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <?= Html::encode($budgetTypeLabel . ' ' . $budgetYearLabel) ?>
                                    </div>
                                </td>
                                <td class="align-top text-end">
                                    <span class="fw-bold text-dark"><?= number_format((float) $model->grand_total, 0) ?></span>
                                </td>
                                <td class="align-top">
                                    <div class="d-inline-flex align-items-center gap-2 bg-<?= Html::encode($statusMeta['color']) ?>-subtle border border-<?= Html::encode($statusMeta['color']) ?>-subtle rounded-pill px-3 py-1 shadow-sm">
                                        <span class="bg-<?= Html::encode($statusMeta['color']) ?> rounded-circle d-inline-block" style="width:8px;height:8px;"></span>
                                        <span class="small fw-semibold text-<?= Html::encode($statusMeta['color']) ?>-emphasis">
                                            <?= Html::encode($statusMeta['label']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="align-top text-nowrap">
                                    <span class="text-dark fw-bold"><?= Html::encode($managedDate) ?></span>
                                </td>
                                <td class="align-top text-end text-nowrap">
                                    <div class="d-flex gap-2 justify-content-end act-btns">
                                        <?= Html::a('<i class="bi bi-eye-fill"></i>', ['/purchase-v2/request/view', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'title' => 'ดูรายละเอียด',
                                            'aria-label' => 'ดูรายละเอียด',
                                            'data' => ['size' => 'modal-xl'],
                                        ]) ?>

                                        <?php if ($model->canEdit() || Yii::$app->user->can('admin')): ?>
                                            <?= Html::a('<i class="bi bi-pencil-fill"></i>', ['/purchase-v2/request/update', 'id' => $model->id], [
                                                'class' => 'btn btn-sm btn-outline-warning open-modal',
                                                'title' => 'แก้ไข',
                                                'aria-label' => 'แก้ไข',
                                                'data' => ['size' => 'modal-xl'],
                                            ]) ?>
                                        <?php endif; ?>

                                        <?php if ($model->canCancel() && $canManage): ?>
                                            <?= Html::a('<i class="fa-solid fa-trash-can"></i>', ['/purchase-v2/request/cancel', 'id' => $model->id], [
                                                'class' => 'btn btn-sm btn-outline-danger',
                                                'title' => 'ยกเลิก',
                                                'aria-label' => 'ยกเลิก',
                                                'data' => ['method' => 'post', 'confirm' => 'ยืนยันการยกเลิกรายการนี้?'],
                                            ]) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($models)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <div class="fw-semibold mb-1">ไม่พบข้อมูล</div>
                                    <div class="text-muted small">ลองปรับคำค้นหา หรือล้างตัวกรองเพื่อดูรายการทั้งหมด</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top py-3 px-4">
            <?= DataSummaryWidget::widget([
                'dataProvider' => $dataProvider,
                'pagerOptions' => [],
            ]) ?>
        </div>
    </div>
</div>
