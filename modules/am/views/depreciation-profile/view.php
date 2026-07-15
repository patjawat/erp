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
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="percent"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= Html::a('<i data-lucide="pencil"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
<?= Html::a('<i data-lucide="arrow-left"></i> กลับ', ['index'], ['class' => 'btn btn-light']) ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">

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
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr><th>ลำดับ</th><th>เดือนเริ่ม</th><th>เดือนสิ้นสุด</th><th class="text-end">อัตรา (%)</th><th></th></tr>
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
                                    <?= Html::a('<i data-lucide="trash-2"></i>', ['rate-delete', 'id' => $r->id], [
                                        'class' => 'text-danger',
                                        'data' => ['method' => 'post', 'confirm' => 'ลบช่วงอัตรานี้?'],
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <?php $form = ActiveForm::begin(['action' => ['rate-create', 'id' => $model->id]]); ?>
                <div class="row g-2 align-items-end">
                    <div class="col"><?= $form->field($newRate, 'start_month')->textInput(['type' => 'number', 'min' => 1])->label('เดือนเริ่ม') ?></div>
                    <div class="col"><?= $form->field($newRate, 'end_month')->textInput(['type' => 'number', 'min' => 1])->label('เดือนสิ้นสุด (ว่าง=∞)') ?></div>
                    <div class="col"><?= $form->field($newRate, 'rate_percent')->textInput(['type' => 'number', 'step' => '0.0001'])->label('อัตรา %') ?></div>
                    <div class="col"><?= $form->field($newRate, 'sequence')->textInput(['type' => 'number'])->label('ลำดับ') ?></div>
                    <div class="col-auto"><?= Html::submitButton('<i data-lucide="plus"></i> เพิ่ม', ['class' => 'btn btn-success mb-3']) ?></div>
                </div>
                <?php ActiveForm::end(); ?>
            </div></div>
        </div>
    </div>
</div>
