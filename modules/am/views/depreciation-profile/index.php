<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\am\models\DepreciationProfile;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var bool $hasStd */

$this->title = 'เกณฑ์ค่าเสื่อมราคา';
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$totalCount = $dataProvider->getTotalCount();
$offset = $pagination === false ? 0 : $pagination->getOffset();

$renderStatus = static function (string $status): string {
    $config = DepreciationProfile::getStatusBadgeConfigFor($status);
    return Html::tag(
        'span',
        '<i data-lucide="' . Html::encode($config['icon']) . '" aria-hidden="true"></i>'
            . Html::encode($config['label']),
        ['class' => $config['class']]
    );
};
$renderUsefulLife = static function ($months, bool $compact = false): string {
    if (empty($months)) {
        return '<span class="dp-empty-value">—</span>';
    }

    $months = (int) $months;
    if ($months % 12 === 0) {
        $years = (int) ($months / 12);
        if ($compact) {
            return '<span class="dp-num">' . number_format($years) . ' ปี</span>';
        }
        return '<span class="dp-num dp-life__primary">' . number_format($years) . ' ปี</span>'
            . '<span class="dp-life__secondary">' . number_format($months) . ' เดือน</span>';
    }

    return '<span class="dp-num">' . number_format($months) . ' เดือน</span>';
};
$renderAnnualRate = static function ($rate): string {
    return $rate !== null
        ? '<span class="dp-num">' . number_format((float) $rate, 2) . '%</span>'
        : '<span class="dp-empty-value">—</span>';
};

$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h1 class="h4 fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="percent"></i></span>
    <?= Html::encode($this->title) ?>
</h1>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
กำหนดวิธีคำนวณ อายุการใช้งาน และอัตราค่าเสื่อมสำหรับทรัพย์สิน
<?php $this->endBlock(); ?>

<?php
$actionMenu = Html::tag('div', $this->render('@app/modules/am/menu', ['active' => 'depreciation']), [
    'class' => 'd-flex flex-wrap gap-2 justify-content-center justify-content-lg-end align-items-center',
]);
foreach (['action', 'page-action'] as $actionBlock) {
    $this->beginBlock($actionBlock);
    echo $actionMenu;
    $this->endBlock();
}
?>

<div class="container-fluid py-3 dp-index">
    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $variant): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $variant ?> d-flex align-items-start gap-2" role="<?= $flash === 'error' ? 'alert' : 'status' ?>">
                <i data-lucide="<?= $flash === 'error' ? 'circle-alert' : 'circle-check' ?>" aria-hidden="true"></i>
                <span><?= Html::encode(Yii::$app->session->getFlash($flash)) ?></span>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php Pjax::begin([
        'id' => 'am-dp-container',
        'enablePushState' => false,
        'timeout' => false,
    ]); ?>
    <section class="card shadow-sm dp-list-card" aria-labelledby="dp-list-title">
        <header class="dp-list-head">
            <div class="dp-list-head__text">
                <h2 class="dp-list-head__title" id="dp-list-title">รายการเกณฑ์</h2>
                <span class="dp-list-head__caption"><?= number_format($totalCount) ?> รายการ</span>
            </div>
            <div class="dp-list-head__actions">
                <?php if ($hasStd): ?>
                    <?= Html::a('<i data-lucide="eraser" aria-hidden="true"></i> ล้างเกณฑ์มาตรฐาน', ['clear-standard'], [
                        'class' => 'btn btn-sm dp-btn-danger rounded-pill',
                        'data' => [
                            'method' => 'post',
                            'confirm' => 'ลบเกณฑ์มาตรฐานและถอนการผูกทั้งหมดหรือไม่?',
                            'pjax' => 0,
                        ],
                    ]) ?>
                <?php else: ?>
                    <?= Html::a('<i data-lucide="download" aria-hidden="true"></i> ติดตั้งเกณฑ์มาตรฐาน', ['seed-standard'], [
                        'class' => 'btn btn-sm btn-light rounded-pill',
                        'title' => 'สร้างเกณฑ์มาตรฐานกรมบัญชีกลางและผูกเข้ากับประเภททรัพย์สิน',
                        'data' => [
                            'method' => 'post',
                            'confirm' => 'สร้างเกณฑ์มาตรฐานครบทุกประเภทและผูกเข้ากับประเภททรัพย์สินหรือไม่?',
                            'pjax' => 0,
                        ],
                    ]) ?>
                <?php endif; ?>
                <?= Html::a('<i data-lucide="plus" aria-hidden="true"></i> เพิ่มเกณฑ์ใหม่', ['create', 'title' => 'เพิ่มเกณฑ์ค่าเสื่อม'], [
                    'class' => 'btn btn-sm btn-primary rounded-pill open-modal',
                    'data' => ['size' => 'modal-xl'],
                ]) ?>
            </div>
        </header>

        <?php if (empty($models)): ?>
            <div class="dp-empty-state">
                <h3 class="dp-empty-state__title">ยังไม่มีเกณฑ์ค่าเสื่อม</h3>
                <p class="dp-empty-state__caption">เพิ่มเกณฑ์เอง หรือติดตั้งเกณฑ์มาตรฐานเพื่อเริ่มผูกกับประเภททรัพย์สิน</p>
                <?= Html::a('<i data-lucide="plus" aria-hidden="true"></i> เพิ่มเกณฑ์แรก', ['create', 'title' => 'เพิ่มเกณฑ์ค่าเสื่อม'], [
                    'class' => 'btn btn-primary rounded-pill open-modal',
                    'data' => ['size' => 'modal-xl'],
                ]) ?>
            </div>
        <?php else: ?>
            <div class="d-none d-lg-block">
                <table class="dp-table">
                    <caption class="visually-hidden">รายการเกณฑ์ค่าเสื่อมราคา</caption>
                    <thead>
                        <tr>
                            <th class="dp-table__no" scope="col">#</th>
                            <th class="dp-table__code" scope="col">รหัส</th>
                            <th scope="col">ชื่อเกณฑ์</th>
                            <th class="dp-table__calculation" scope="col">การคำนวณ</th>
                            <th class="dp-table__number" scope="col">อายุใช้งาน</th>
                            <th class="dp-table__number" scope="col">อัตราต่อปี</th>
                            <th class="dp-table__status" scope="col">สถานะ</th>
                            <th class="dp-table__actions" scope="col"><span class="visually-hidden">จัดการ</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($models as $index => $model): /** @var DepreciationProfile $model */ ?>
                            <?php
                            $viewUrl = ['view', 'id' => $model->id];
                            $updateUrl = ['update', 'id' => $model->id, 'title' => 'แก้ไขเกณฑ์ค่าเสื่อม'];
                            $deleteUrl = ['delete', 'id' => $model->id];
                            ?>
                            <tr>
                                <td class="dp-table__no"><?= number_format($offset + $index + 1) ?></td>
                                <td class="dp-table__code">
                                    <?= Html::a(Html::encode($model->code), $viewUrl, [
                                        'class' => 'dp-code-link',
                                        'data-pjax' => 0,
                                    ]) ?>
                                </td>
                                <td>
                                    <div class="dp-name" title="<?= Html::encode($model->name) ?>"><?= Html::encode($model->name) ?></div>
                                </td>
                                <td class="dp-table__calculation">
                                    <span class="dp-calculation__method"><?= Html::encode(DepreciationProfile::methodOptions()[$model->method] ?? $model->method) ?></span>
                                    <span class="dp-calculation__basis"><?= Html::encode(DepreciationProfile::basisOptions()[$model->calculation_basis] ?? $model->calculation_basis) ?></span>
                                </td>
                                <td class="dp-table__number">
                                    <span class="dp-life"><?= $renderUsefulLife($model->useful_life_months) ?></span>
                                </td>
                                <td class="dp-table__number"><?= $renderAnnualRate($model->annual_rate) ?></td>
                                <td class="dp-table__status"><?= $renderStatus($model->status) ?></td>
                                <td class="dp-table__actions">
                                    <div class="dp-row-actions">
                                        <?= Html::a('<i data-lucide="eye" aria-hidden="true"></i>', $viewUrl, [
                                            'class' => 'btn btn-sm dp-row-action dp-row-action--view',
                                            'title' => 'ดูรายละเอียด ' . $model->name,
                                            'aria-label' => 'ดูรายละเอียด ' . $model->name,
                                            'data-pjax' => 0,
                                        ]) ?>
                                        <?= Html::a('<i data-lucide="pencil" aria-hidden="true"></i>', $updateUrl, [
                                            'class' => 'btn btn-sm dp-row-action dp-row-action--edit open-modal',
                                            'title' => 'แก้ไข ' . $model->name,
                                            'aria-label' => 'แก้ไข ' . $model->name,
                                            'data' => ['size' => 'modal-xl'],
                                        ]) ?>
                                        <?= Html::a('<i data-lucide="trash-2" aria-hidden="true"></i>', $deleteUrl, [
                                            'class' => 'btn btn-sm dp-row-action dp-row-action--delete',
                                            'title' => 'ลบ ' . $model->name,
                                            'aria-label' => 'ลบ ' . $model->name,
                                            'data' => [
                                                'method' => 'post',
                                                'confirm' => 'ลบเกณฑ์ “' . $model->name . '” หรือไม่?',
                                                'pjax' => 0,
                                            ],
                                        ]) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <ul class="dp-mobile-list d-lg-none" role="list" aria-label="รายการเกณฑ์ค่าเสื่อมราคา">
                <?php foreach ($models as $model): /** @var DepreciationProfile $model */ ?>
                    <?php
                    $viewUrl = ['view', 'id' => $model->id];
                    $updateUrl = ['update', 'id' => $model->id, 'title' => 'แก้ไขเกณฑ์ค่าเสื่อม'];
                    $deleteUrl = ['delete', 'id' => $model->id];
                    ?>
                    <li class="dp-mobile-item">
                        <div class="dp-mobile-item__head">
                            <?= Html::a(Html::encode($model->code), $viewUrl, [
                                'class' => 'dp-code-link',
                                'data-pjax' => 0,
                            ]) ?>
                            <?= $renderStatus($model->status) ?>
                        </div>
                        <h3 class="dp-mobile-item__name"><?= Html::encode($model->name) ?></h3>
                        <dl class="dp-mobile-meta">
                            <div>
                                <dt>วิธีคำนวณ</dt>
                                <dd><?= Html::encode(DepreciationProfile::methodOptions()[$model->method] ?? $model->method) ?></dd>
                            </div>
                            <div>
                                <dt>ฐานคำนวณ</dt>
                                <dd><?= Html::encode(DepreciationProfile::basisOptions()[$model->calculation_basis] ?? $model->calculation_basis) ?></dd>
                            </div>
                            <div>
                                <dt>อายุใช้งาน</dt>
                                <dd><?= $renderUsefulLife($model->useful_life_months, true) ?></dd>
                            </div>
                            <div>
                                <dt>อัตราต่อปี</dt>
                                <dd><?= $renderAnnualRate($model->annual_rate) ?></dd>
                            </div>
                        </dl>
                        <div class="dp-mobile-actions" aria-label="จัดการ <?= Html::encode($model->name) ?>">
                            <?= Html::a('<i data-lucide="eye" aria-hidden="true"></i> ดูรายละเอียด', $viewUrl, [
                                'class' => 'btn btn-sm btn-primary',
                                'data-pjax' => 0,
                            ]) ?>
                            <?= Html::a('<i data-lucide="pencil" aria-hidden="true"></i> แก้ไข', $updateUrl, [
                                'class' => 'btn btn-sm btn-light open-modal',
                                'data' => ['size' => 'modal-xl'],
                            ]) ?>
                            <?= Html::a('<i data-lucide="trash-2" aria-hidden="true"></i>', $deleteUrl, [
                                'class' => 'btn btn-sm dp-btn-danger dp-mobile-actions__delete',
                                'title' => 'ลบ ' . $model->name,
                                'aria-label' => 'ลบ ' . $model->name,
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => 'ลบเกณฑ์ “' . $model->name . '” หรือไม่?',
                                    'pjax' => 0,
                                ],
                            ]) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <footer class="card-footer dp-list-footer">
            <?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
        </footer>
    </section>
    <?php Pjax::end(); ?>
</div>

<?php
$this->registerCss(<<<'CSS'
.dp-index {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #5f6b7a;
    --ink-4: #667386;
    --surface: #fff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, .08);
    --line-strong: rgba(15, 23, 42, .14);
    --primary: #0d6efd;
    --primary-ink: #0a58ca;
    --primary-soft: rgba(13, 110, 253, .08);
    --success: #15803d;
    --success-ink: #166534;
    --success-soft: rgba(21, 128, 61, .1);
    --warning: #b45309;
    --warning-ink: #92400e;
    --warning-soft: rgba(180, 83, 9, .1);
    --danger: #b91c1c;
    --danger-soft: rgba(185, 28, 28, .1);
    --radius: 10px;
    --radius-sm: 8px;
    --radius-xs: 6px;
    --ease: cubic-bezier(.16, 1, .3, 1);
    --t-fast: 120ms;
}
[data-bs-theme="dark"] .dp-index {
    --ink-1: #f1f5f9;
    --ink-2: #e2e8f0;
    --ink-3: #cbd5e1;
    --ink-4: #cbd5e1;
    --surface: #212529;
    --surface-2: #2b3035;
    --surface-3: #343a40;
    --surface-hover: #30363d;
    --line: rgba(255, 255, 255, .12);
    --line-strong: rgba(255, 255, 255, .2);
    --primary-ink: #9ec5fe;
    --primary-soft: rgba(110, 168, 254, .2);
    --success-ink: #75b798;
    --success-soft: rgba(117, 183, 152, .16);
    --warning-ink: #ffda6a;
    --warning-soft: rgba(255, 218, 106, .14);
    --danger: #ea868f;
    --danger-soft: rgba(234, 134, 143, .16);
}
.dp-index .alert {
    border-radius: var(--radius-sm);
    font-size: .88rem;
}
.dp-index .alert svg {
    width: 1.1rem;
    height: 1.1rem;
    flex: 0 0 auto;
    margin-top: .1rem;
}
.dp-list-card {
    overflow: visible;
    border: 1px solid var(--line);
    border-radius: var(--radius);
    background: var(--surface);
}
.dp-list-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .85rem 1.1rem;
    border-bottom: 1px solid var(--line);
    background: var(--surface);
}
.dp-list-head__text {
    display: flex;
    align-items: baseline;
    gap: .6rem;
    min-width: 0;
}
.dp-list-head__title {
    margin: 0;
    color: var(--ink-1);
    font-size: .95rem;
    font-weight: 600;
    line-height: 1.2;
}
.dp-list-head__caption {
    color: var(--ink-3);
    font-size: .75rem;
    font-variant-numeric: tabular-nums;
}
.dp-list-head__actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: .5rem;
}
.dp-list-head__actions .btn,
.dp-row-actions .btn,
.dp-mobile-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    font-weight: 600;
    transition: background-color var(--t-fast) var(--ease),
        border-color var(--t-fast) var(--ease),
        color var(--t-fast) var(--ease),
        box-shadow var(--t-fast) var(--ease),
        transform 80ms var(--ease);
}
.dp-index .btn-primary {
    border-color: var(--primary);
    background: var(--primary);
    color: #fff;
}
.dp-index .btn-primary:hover:not(:disabled) {
    border-color: var(--primary-ink);
    background: var(--primary-ink);
    color: #fff;
}
.dp-index .btn-primary:active:not(:disabled) {
    transform: translateY(1px);
}
.dp-index .btn-primary:focus-visible {
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.dp-index .btn-light {
    border-color: var(--line-strong);
    background: var(--surface-2);
    color: var(--ink-2);
}
.dp-index .btn-light:hover:not(:disabled) {
    border-color: var(--line-strong);
    background: var(--surface-hover);
    color: var(--ink-1);
}
.dp-index .btn:disabled,
.dp-index .btn.disabled {
    opacity: .55;
    cursor: not-allowed;
}
.dp-btn-danger {
    border-color: transparent;
    background: var(--danger-soft);
    color: var(--danger);
}
.dp-btn-danger:hover:not(:disabled) {
    border-color: var(--danger);
    background: var(--danger);
    color: #fff;
}
.dp-btn-danger:focus-visible,
.dp-row-action--delete:focus-visible {
    box-shadow: 0 0 0 3px var(--danger-soft) !important;
}
.dp-row-action {
    border-color: transparent;
}
.dp-row-action--view {
    background: #0dcaf0;
    color: #052c35;
}
.dp-row-action--view:hover {
    background: #0bb5d8;
    color: #052c35;
}
.dp-row-action--edit {
    background: #ffc107;
    color: #332701;
}
.dp-row-action--edit:hover {
    background: #e5ad06;
    color: #332701;
}
.dp-row-action--delete {
    background: #dc3545;
    color: #fff;
}
.dp-row-action--delete:hover {
    background: #bb2d3b;
    color: #fff;
}
.dp-row-action:active {
    transform: translateY(1px);
}
.dp-list-head__actions .btn svg,
.dp-row-actions .btn svg,
.dp-mobile-actions .btn svg {
    width: .95rem;
    height: .95rem;
}
.dp-list-head__actions .btn:focus-visible,
.dp-row-actions .btn:focus-visible,
.dp-mobile-actions .btn:focus-visible,
.dp-code-link:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.dp-list-head__actions .dp-btn-danger:focus-visible,
.dp-row-actions .dp-row-action--delete:focus-visible,
.dp-mobile-actions .dp-btn-danger:focus-visible {
    box-shadow: 0 0 0 3px var(--danger-soft) !important;
}
.dp-row-actions .dp-row-action--view:focus-visible {
    box-shadow: 0 0 0 3px rgba(13, 202, 240, .24);
}
.dp-row-actions .dp-row-action--edit:focus-visible {
    box-shadow: 0 0 0 3px rgba(255, 193, 7, .24);
}
.dp-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
    table-layout: fixed;
}
.dp-table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    padding: .65rem .9rem;
    border-bottom: 1px solid var(--line-strong);
    background: var(--surface-2);
    color: var(--ink-2);
    font-size: .78rem;
    font-weight: 600;
    line-height: 1.3;
    text-align: left;
    white-space: nowrap;
}
.dp-table tbody td {
    padding: .62rem .9rem;
    border-bottom: 1px solid var(--line);
    color: var(--ink-1);
    font-size: .86rem;
    line-height: 1.35;
    vertical-align: middle;
}
.dp-table tbody tr:nth-child(even) {
    background: rgba(0, 0, 0, .012);
}
.dp-table tbody tr {
    transition: background-color var(--t-fast) var(--ease);
}
.dp-table tbody tr:hover {
    background: var(--surface-hover);
}
.dp-table tbody tr:last-child td {
    border-bottom: 0;
}
.dp-table__no {
    width: 3.2rem;
    color: var(--ink-3) !important;
    text-align: center !important;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}
.dp-table__code {
    width: 8.6rem;
}
.dp-table__calculation {
    width: 13.5rem;
}
.dp-table__number {
    width: 7.6rem;
    text-align: right !important;
}
.dp-table__status {
    width: 8.5rem;
}
.dp-table__actions {
    width: 9.5rem;
    text-align: center !important;
}
.dp-code-link {
    display: inline-flex;
    align-items: center;
    color: var(--primary-ink);
    font-weight: 600;
    text-decoration: none;
    overflow-wrap: anywhere;
}
.dp-code-link:hover {
    color: #084298;
    text-decoration: underline;
    text-underline-offset: .14em;
}
.dp-name {
    overflow: hidden;
    color: var(--ink-1);
    font-weight: 600;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.dp-calculation__method,
.dp-calculation__basis,
.dp-life__primary,
.dp-life__secondary {
    display: block;
}
.dp-calculation__method {
    color: var(--ink-1);
}
.dp-calculation__basis,
.dp-life__secondary {
    margin-top: .12rem;
    color: var(--ink-3);
    font-size: .74rem;
}
.dp-num {
    font-variant-numeric: tabular-nums;
}
.dp-empty-value {
    color: var(--ink-4);
}
.dp-status {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .28rem .55rem;
    border-radius: 999px;
    font-size: .74rem;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
}
.dp-status svg {
    width: .82rem;
    height: .82rem;
    stroke-width: 2.5;
}
.dp-status--active {
    color: var(--success-ink);
    background: var(--success-soft);
}
.dp-status--draft {
    color: var(--warning-ink);
    background: var(--warning-soft);
}
.dp-status--inactive {
    color: var(--ink-2);
    background: var(--surface-3);
}
.dp-row-actions {
    display: flex;
    justify-content: center;
    gap: .3rem;
}
.dp-row-actions .btn {
    flex: 0 0 32px;
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: var(--radius-sm);
}
.dp-mobile-list {
    margin: 0;
    padding: 0;
    list-style: none;
}
.dp-mobile-item {
    padding: 1rem;
    border-bottom: 1px solid var(--line);
}
.dp-mobile-item:last-child {
    border-bottom: 0;
}
.dp-mobile-item__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}
.dp-mobile-item__name {
    margin: .45rem 0 .8rem;
    color: var(--ink-1);
    font-size: .95rem;
    font-weight: 600;
    line-height: 1.4;
}
.dp-mobile-meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .7rem 1rem;
    margin: 0;
    padding: .75rem;
    border-radius: var(--radius-sm);
    background: var(--surface-2);
}
.dp-mobile-meta dt {
    margin: 0 0 .2rem;
    color: var(--ink-3);
    font-size: .72rem;
    font-weight: 500;
}
.dp-mobile-meta dd {
    margin: 0;
    color: var(--ink-1);
    font-size: .83rem;
    line-height: 1.35;
}
.dp-mobile-actions {
    display: flex;
    gap: .5rem;
    margin-top: .8rem;
}
.dp-mobile-actions .btn {
    min-height: 44px;
    border-radius: var(--radius-sm);
}
.dp-mobile-actions .btn:not(.dp-mobile-actions__delete) {
    flex: 1 1 auto;
}
.dp-mobile-actions__delete {
    flex: 0 0 44px;
    width: 44px;
    padding: 0;
}
.dp-list-footer {
    padding: .8rem 1.1rem;
    border-top: 1px solid var(--line);
    background: var(--surface);
    font-size: .8rem;
}
.dp-list-footer .pagination {
    margin: 0;
}
.dp-empty-state {
    padding: 3.5rem 1.5rem;
    text-align: center;
}
.dp-empty-state__title {
    margin: 0;
    color: var(--ink-1);
    font-size: 1.05rem;
    font-weight: 600;
}
.dp-empty-state__caption {
    max-width: 38rem;
    margin: .45rem auto 1rem;
    color: var(--ink-3);
    font-size: .88rem;
}
@media (max-width: 991.98px) {
    .dp-index {
        padding-top: .75rem !important;
    }
    .dp-list-head {
        align-items: flex-start;
    }
    .dp-list-head__actions {
        max-width: 70%;
    }
    .dp-list-head__actions .btn,
    .dp-code-link {
        min-height: 44px;
    }
}
@media (max-width: 575.98px) {
    .dp-list-head {
        flex-direction: column;
    }
    .dp-list-head__actions {
        width: 100%;
        max-width: none;
        justify-content: stretch;
    }
    .dp-list-head__actions .btn {
        flex: 1 1 auto;
        min-height: 44px;
    }
    .dp-list-footer > .d-flex {
        flex-direction: column;
        align-items: stretch !important;
        gap: .75rem;
    }
    .dp-list-footer .pagination {
        flex-wrap: wrap;
    }
}
@media (prefers-reduced-motion: reduce) {
    .dp-table tbody tr,
    .dp-index .btn,
    .dp-code-link {
        transition: none !important;
    }
}
CSS);
?>
