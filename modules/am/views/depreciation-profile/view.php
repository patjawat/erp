<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use app\modules\am\models\DepreciationProfile;
use app\modules\am\models\DepreciationProfileRate;

/** @var yii\web\View $this */
/** @var app\modules\am\models\DepreciationProfile $model */

$this->title = 'เกณฑ์: ' . $model->name;
$newRate = new DepreciationProfileRate(['depreciation_profile_id' => $model->id]);
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = ['label' => 'เกณฑ์ค่าเสื่อม', 'url' => ['/am/depreciation-profile/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h1 class="h4 fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="percent"></i></span>
    <?= Html::encode($this->title) ?>
</h1>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= Html::a('<i data-lucide="pencil"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary dp-profile-action']) ?>
<?= Html::a('<i data-lucide="arrow-left"></i> กลับ', ['index'], ['class' => 'btn btn-light dp-profile-action']) ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3 dp-profile-view">

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $cls): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $cls ?>"><?= Yii::$app->session->getFlash($flash) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card"><div class="card-body">
                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table table-sm'],
                    'attributes' => [
                        'code',
                        'name',
                        ['attribute' => 'method', 'value' => fn($m) => DepreciationProfile::methodOptions()[$m->method] ?? $m->method],
                        'useful_life_months',
                        ['attribute' => 'annual_rate', 'value' => fn($m) => $m->annual_rate !== null ? $m->annual_rate . '%' : '-'],
                        ['attribute' => 'salvage_value_type', 'value' => fn($m) => DepreciationProfile::salvageTypeOptions()[$m->salvage_value_type] ?? $m->salvage_value_type],
                        'salvage_value',
                        ['attribute' => 'calculation_basis', 'value' => fn($m) => DepreciationProfile::basisOptions()[$m->calculation_basis] ?? $m->calculation_basis],
                        ['attribute' => 'start_rule', 'value' => fn($m) => DepreciationProfile::startRuleOptions()[$m->start_rule] ?? $m->start_rule],
                        'rounding_scale',
                        'effective_from',
                        'effective_to',
                        ['attribute' => 'status', 'value' => fn($m) => DepreciationProfile::statusOptions()[$m->status] ?? $m->status],
                    ],
                ]) ?>
            </div></div>
        </div>

        <div class="col-lg-6">
            <div class="card"><div class="card-body">
                <h6 class="mb-3"><i data-lucide="layers"></i> อัตราค่าเสื่อมหลายช่วง</h6>
                <div class="table-responsive dp-rate-table-wrap">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr><th>ลำดับ</th><th>เดือนเริ่ม</th><th>เดือนสิ้นสุด</th><th class="text-end">อัตรา (%)</th><th><span class="visually-hidden">การทำงาน</span></th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($model->rates)): ?>
                                <tr><td colspan="5" class="text-muted text-center">ยังไม่มีช่วงอัตรา (ใช้อัตรา/อายุจากเกณฑ์หลัก)</td></tr>
                            <?php else: foreach ($model->rates as $r): ?>
                                <tr>
                                    <td><?= $r->sequence ?></td>
                                    <td><?= $r->start_month ?></td>
                                    <td><?= $r->end_month ?? '∞' ?></td>
                                    <td class="text-end"><?= number_format($r->rate_percent, 4) ?></td>
                                    <td class="text-end">
                                        <?= Html::a('<i data-lucide="trash-2" aria-hidden="true"></i>', ['rate-delete', 'id' => $r->id], [
                                            'class' => 'btn dp-action-danger dp-rate-delete',
                                            'aria-label' => 'ลบช่วงอัตราลำดับ ' . $r->sequence,
                                            'title' => 'ลบช่วงอัตราลำดับ ' . $r->sequence,
                                            'data' => ['method' => 'post', 'confirm' => 'ลบช่วงอัตรานี้?'],
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php $form = ActiveForm::begin(['action' => ['rate-create', 'id' => $model->id]]); ?>
                <div class="row g-2 align-items-end dp-rate-form">
                    <div class="col-12 col-sm-6 col-xl"><?= $form->field($newRate, 'start_month')->textInput(['type' => 'number', 'min' => 1])->label('เดือนเริ่ม') ?></div>
                    <div class="col-12 col-sm-6 col-xl"><?= $form->field($newRate, 'end_month')->textInput(['type' => 'number', 'min' => 1])->label('เดือนสิ้นสุด (ว่าง=∞)') ?></div>
                    <div class="col-12 col-sm-6 col-xl"><?= $form->field($newRate, 'rate_percent')->textInput(['type' => 'number', 'step' => '0.0001'])->label('อัตรา %') ?></div>
                    <div class="col-12 col-sm-6 col-xl"><?= $form->field($newRate, 'sequence')->textInput(['type' => 'number'])->label('ลำดับ') ?></div>
                    <div class="col-12 col-xl-auto"><?= Html::submitButton('<i data-lucide="plus"></i> เพิ่มช่วงอัตรา', ['class' => 'btn btn-primary mb-3']) ?></div>
                </div>
                <?php ActiveForm::end(); ?>
            </div></div>
        </div>
    </div>
</div>

<?php
$this->registerCss(<<<'CSS'
.dp-profile-view,
.dp-profile-action {
    --dp-primary: #0d6efd;
    --dp-primary-ink: #0a58ca;
    --dp-primary-soft: rgba(13, 110, 253, .08);
    --dp-surface-2: #f7f9fc;
    --dp-surface-hover: #f1f5f9;
    --dp-ink-1: #1a202c;
    --dp-ink-2: #4a5568;
    --dp-line-strong: rgba(15, 23, 42, .14);
    --dp-danger: #b91c1c;
    --dp-danger-soft: rgba(185, 28, 28, .1);
    --dp-ease: cubic-bezier(.16, 1, .3, 1);
}
[data-bs-theme="dark"] .dp-profile-view,
[data-bs-theme="dark"] .dp-profile-action {
    --dp-primary-soft: rgba(110, 168, 254, .2);
    --dp-surface-2: #2b3035;
    --dp-surface-hover: #343a40;
    --dp-ink-1: #f1f5f9;
    --dp-ink-2: #e2e8f0;
    --dp-line-strong: rgba(255, 255, 255, .2);
    --dp-danger: #ea868f;
    --dp-danger-soft: rgba(234, 134, 143, .16);
}
.dp-profile-action,
.dp-rate-delete,
.dp-rate-form .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    min-height: 44px;
    border-radius: 8px;
    font-weight: 600;
    transition: background-color 120ms var(--dp-ease),
        border-color 120ms var(--dp-ease),
        color 120ms var(--dp-ease),
        box-shadow 120ms var(--dp-ease),
        transform 80ms var(--dp-ease);
}
.dp-profile-action.btn-primary,
.dp-profile-view .btn-primary {
    border-color: var(--dp-primary);
    background: var(--dp-primary);
    color: #fff;
}
.dp-profile-action.btn-primary:hover:not(:disabled),
.dp-profile-view .btn-primary:hover:not(:disabled) {
    border-color: var(--dp-primary-ink);
    background: var(--dp-primary-ink);
    color: #fff;
}
.dp-profile-action.btn-primary:active:not(:disabled),
.dp-profile-view .btn-primary:active:not(:disabled) {
    transform: translateY(1px);
}
.dp-profile-action.btn-primary:focus-visible,
.dp-profile-view .btn-primary:focus-visible {
    box-shadow: 0 0 0 3px var(--dp-primary-soft);
}
.dp-profile-action.btn-light {
    border-color: var(--dp-line-strong);
    background: var(--dp-surface-2);
    color: var(--dp-ink-2);
}
.dp-profile-action.btn-light:hover {
    border-color: var(--dp-line-strong);
    background: var(--dp-surface-hover);
    color: var(--dp-ink-1);
}
.dp-profile-action.btn-light:focus-visible {
    box-shadow: 0 0 0 3px var(--dp-primary-soft);
}
.dp-action-danger {
    border-color: transparent;
    background: var(--dp-danger-soft);
    color: var(--dp-danger);
}
.dp-action-danger:hover {
    border-color: var(--dp-danger);
    background: var(--dp-danger);
    color: #fff;
}
.dp-action-danger:focus-visible {
    box-shadow: 0 0 0 3px var(--dp-danger-soft);
}
.dp-profile-view .btn:disabled,
.dp-profile-action.disabled {
    opacity: .55;
    cursor: not-allowed;
}
.dp-rate-table-wrap {
    max-width: 100%;
    margin-bottom: 1rem;
    contain: paint;
}
.dp-rate-table-wrap table {
    min-width: 36rem;
}
.dp-rate-table-wrap thead th {
    background: var(--bs-tertiary-bg);
    color: var(--bs-body-color);
    white-space: nowrap;
}
.dp-rate-delete {
    width: 44px;
    padding: 0;
}
.dp-rate-form .form-control {
    min-height: 44px;
}
@media (max-width: 1199.98px) {
    .dp-rate-form .btn {
        width: 100%;
    }
}
@media (prefers-reduced-motion: reduce) {
    .dp-profile-action,
    .dp-rate-delete,
    .dp-rate-form .btn {
        transition: none !important;
    }
}
CSS);
?>
