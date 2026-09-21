<?php

use app\modules\ha12\models\Ha12Indicator;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12Indicator $model */
/** @var bool $canManage */

$this->title = 'HA12-PCT · ค่ารายเดือน';
$months = Ha12Indicator::monthLabels();
$valueMap = $model->valueMap();
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ค่ารายเดือน · ปีงบ <?= $model->fiscal_year ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'indicator']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $tone): ?>
        <?php if ($msg = Yii::$app->session->getFlash($flash)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 fw-semibold mb-0"><i class="bi bi-graph-up me-1"></i> <?= Html::encode($model->name) ?></h1>
            <div class="text-body-secondary small">
                <?= Html::encode($model->ownerUnit->name ?? '—') ?>
                · เป้าหมาย <?= Html::encode(($model->target ?? '—') . ($model->unit_label ? ' ' . $model->unit_label : '')) ?>
                <?= $model->level ? ' · ระดับ ' . Html::encode($model->level) : '' ?>
            </div>
        </div>
        <?= Html::a('<i class="bi bi-arrow-left"></i> กลับ', ['index', 'fy' => $model->fiscal_year], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
    </div>

    <?php if ($model->risk || $model->fix): ?>
        <div class="card border shadow-sm mb-3"><div class="card-body">
            <?php if ($model->risk): ?><div class="mb-2"><span class="small fw-semibold text-body-secondary">ความเสี่ยง:</span> <?= nl2br(Html::encode($model->risk)) ?></div><?php endif; ?>
            <?php if ($model->fix): ?><div><span class="small fw-semibold text-body-secondary">การแก้ไข:</span> <?= nl2br(Html::encode($model->fix)) ?></div><?php endif; ?>
        </div></div>
    <?php endif; ?>

    <?php $form = Html::beginForm(['save-values', 'id' => $model->id], 'post'); ?>
    <div class="card border shadow-sm">
        <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-calendar3 me-1"></i> ค่ารายเดือน (ปีงบประมาณ)</div>
        <div class="card-body">
            <div class="row g-2">
                <?php foreach ($months as $no => $label): ?>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small fw-semibold mb-1"><?= $label ?></label>
                        <?= Html::input('number', "value[$no]", $valueMap[$no] ?? '', ['class' => 'form-control', 'step' => 'any', 'readonly' => !$canManage]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ($canManage && !$model->deleted): ?>
            <div class="card-footer bg-body d-grid d-sm-flex justify-content-sm-end">
                <?= Html::submitButton('<i class="bi bi-save"></i> บันทึกค่ารายเดือน', ['class' => 'btn btn-primary']) ?>
            </div>
        <?php endif; ?>
    </div>
    <?= Html::endForm(); ?>
</div>
