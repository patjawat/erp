<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\roster\models\Period;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\roster\models\PeriodSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $units */
/** @var bool $canCreate */
/** @var int $pendingCount */

$this->title = 'ทะเบียนรอบเวร';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-calendar3"></i> <?= Html::encode($this->title) ?>
    </h4>
    <div class="text-body-secondary small">ตารางเวรรายเดือนของแต่ละหน่วยงาน</div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/roster/menu', ['active' => 'period', 'pendingCount' => $pendingCount]) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'roster-period']); ?>

<div class="card border shadow-sm mb-3">
    <div class="card-header bg-body-tertiary">
        <h6 class="mb-0"><i class="bi bi-search"></i> ค้นหา</h6>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(['method' => 'get', 'options' => ['data-pjax' => true]]); ?>
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <?= $form->field($searchModel, 'unit_id')->dropDownList($units, [
                    'prompt' => 'ทุกหน่วยงาน', 'class' => 'form-select',
                ])->label('หน่วยงาน') ?>
            </div>
            <div class="col-6 col-md-2">
                <?= $form->field($searchModel, 'month')->dropDownList(Period::monthNames(), [
                    'prompt' => 'ทุกเดือน', 'class' => 'form-select',
                ])->label('เดือน') ?>
            </div>
            <div class="col-6 col-md-2">
                <?= $form->field($searchModel, 'thai_year')->input('number', [
                    'placeholder' => (int) date('Y') + 543, 'class' => 'form-control',
                ])->label('ปี (พ.ศ.)') ?>
            </div>
            <div class="col-6 col-md-2">
                <?= $form->field($searchModel, 'status')->dropDownList(Period::statusLabels(), [
                    'prompt' => 'ทุกสถานะ', 'class' => 'form-select',
                ])->label('สถานะ') ?>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2 pb-3">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i> ค้นหา</button>
                <?= Html::a('<i class="bi bi-arrow-counterclockwise"></i>', ['index'], [
                    'class' => 'btn btn-outline-secondary', 'title' => 'ล้างตัวกรอง',
                ]) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
        <h6 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-list-ul"></i> รอบเวร
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">
                <?= number_format($dataProvider->getTotalCount()) ?>
            </span>
        </h6>
        <?php if (!empty($canCreate)): ?>
            <?= Html::a('<i class="bi bi-plus-lg"></i> สร้างรอบเวร', ['create'], [
                'class' => 'btn btn-sm btn-primary open-modal',
                'data' => ['size' => 'modal-md'],
            ]) ?>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <?php if ($dataProvider->getTotalCount() === 0): ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x fs-1 text-body-secondary"></i>
                <h6 class="mt-3 mb-1">ยังไม่มีรอบเวร</h6>
                <p class="text-body-secondary small mb-0">กด “สร้างรอบเวร” เพื่อเริ่มจัดเวรของเดือนถัดไป</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th>หน่วยงาน / แผ่น</th>
                            <th style="width:180px">เดือน</th>
                            <th class="text-center" style="width:130px">ช่องเวร</th>
                            <th class="text-center" style="width:140px">สถานะ</th>
                            <th style="width:170px">อัปเดตล่าสุด</th>
                            <th class="text-end" style="width:150px"></th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($dataProvider->getModels() as $period): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= Html::encode($period->title) ?></div>
                                    <div class="text-body-secondary small"><?= Html::encode($period->unitName()) ?></div>
                                </td>
                                <td><?= Html::encode($period->monthLabel()) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                        <?= number_format($period->getItems()->count()) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $period->getStatusColor() ?>-subtle text-<?= $period->getStatusColor() ?>-emphasis">
                                        <?= Html::encode($period->getStatusLabel()) ?>
                                    </span>
                                </td>
                                <td class="text-body-secondary small"><?= Html::encode((string) $period->updated_at) ?></td>
                                <td class="text-end">
                                    <?= Html::a('<i class="bi bi-grid-3x3"></i> จัดเวร', ['grid', 'id' => $period->id], [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'data-pjax' => 0,
                                    ]) ?>
                                    <?php if ($period->status === Period::STATUS_DRAFT): ?>
                                        <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $period->id], [
                                            'class' => 'btn btn-sm btn-outline-danger period-delete',
                                            'title' => 'ลบรอบร่าง',
                                            'data-pjax' => 0,
                                        ]) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-body-tertiary">
        <?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
    </div>
</div>

<?php Pjax::end(); ?>

<?php
$js = <<<'JS'
$('body').off('click.periodDelete').on('click.periodDelete', '.period-delete', function (e) {
    e.preventDefault();
    if (!window.confirm('ลบรอบเวรร่างนี้? เวรที่จัดไว้จะหายทั้งหมด')) { return; }
    $.get($(this).attr('href'), function (res) {
        if (res.status === 'success') {
            if (typeof success === 'function') { success('ลบแล้ว'); }
            if (typeof erpReloadPjax === 'function') { erpReloadPjax(res.container); } else { location.reload(); }
        } else if (typeof warning === 'function') { warning(res.message); }
    });
});
JS;
$this->registerJs($js);
?>
