<?php

use yii\helpers\Html;
use app\modules\plan\components\PlanHelper;

/** @var yii\web\View $this */
/** @var app\models\Categorise[] $periods */
/** @var int $current */

$this->title = 'ตั้งค่ารอบทำแผน';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$phases = [
    PlanHelper::PHASE_OPEN   => PlanHelper::phaseLabel(PlanHelper::PHASE_OPEN),
    PlanHelper::PHASE_LOCK   => PlanHelper::phaseLabel(PlanHelper::PHASE_LOCK),
    PlanHelper::PHASE_ADJUST => PlanHelper::phaseLabel(PlanHelper::PHASE_ADJUST),
    PlanHelper::PHASE_CLOSED => PlanHelper::phaseLabel(PlanHelper::PHASE_CLOSED),
];
$nextYear = \app\components\AppHelper::YearBudget() + 1;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body mb-0"><i class="fa-solid fa-calendar-check me-2"></i><?= $this->title ?></h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php if ($flash = Yii::$app->session->getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= Html::encode($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-info d-flex align-items-center gap-2">
    <i class="fa-solid fa-circle-info"></i>
    ปีที่เปิดทำแผนปัจจุบัน = <strong class="mx-1"><?= $current ?></strong> — สถานะ
    <span class="badge <?= PlanHelper::phaseClass(PlanHelper::phase($current)) ?>"><?= PlanHelper::phaseLabel(PlanHelper::phase($current)) ?></span>
</div>

<div class="card mb-3">
    <div class="card-header fw-semibold bg-light"><i class="fa-solid fa-plus me-1"></i> เปิดรอบทำแผน (ปีใหม่ / เปิดล่วงหน้า)</div>
    <div class="card-body">
        <?= Html::beginForm(['save'], 'post', ['class' => 'row g-2 align-items-end']) ?>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">ปีงบประมาณ</label>
                <input type="number" name="thai_year" class="form-control" value="<?= $nextYear ?>" min="2500" max="2600" required>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">สถานะเริ่มต้น</label>
                <select name="phase" class="form-select">
                    <?php foreach ($phases as $k => $lb): ?>
                        <option value="<?= $k ?>" <?= $k === PlanHelper::PHASE_OPEN ? 'selected' : '' ?>><?= $lb ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-auto">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="current" value="1" id="mkcur" checked>
                    <label class="form-check-label small" for="mkcur">ตั้งเป็นปีที่เปิดทำแผนปัจจุบัน</label>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> บันทึก</button>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold bg-light"><i class="fa-solid fa-list me-1"></i> รอบทำแผนทั้งหมด</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ปีงบประมาณ</th>
                    <th style="min-width:220px">สถานะรอบ</th>
                    <th class="text-center">ปีปัจจุบัน</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($periods)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">ยังไม่มีรอบทำแผน</td></tr>
                <?php endif; ?>
                <?php foreach ($periods as $p): ?>
                    <?php
                    $dj = is_array($p->data_json) ? $p->data_json : (json_decode((string) $p->data_json, true) ?: []);
                    $ph = $dj['phase'] ?? PlanHelper::PHASE_CLOSED;
                    $isCur = !empty($dj['current']);
                    ?>
                    <tr>
                        <td class="fw-semibold fs-5"><?= Html::encode($p->code) ?></td>
                        <td>
                            <?= Html::beginForm(['set-phase'], 'post', ['class' => 'd-flex align-items-center gap-2']) ?>
                                <input type="hidden" name="thai_year" value="<?= Html::encode($p->code) ?>">
                                <select name="phase" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                    <?php foreach ($phases as $k => $lb): ?>
                                        <option value="<?= $k ?>" <?= $k === $ph ? 'selected' : '' ?>><?= $lb ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="badge <?= PlanHelper::phaseClass($ph) ?>"><?= PlanHelper::phaseLabel($ph) ?></span>
                            <?= Html::endForm() ?>
                        </td>
                        <td class="text-center">
                            <?php if ($isCur): ?>
                                <span class="badge bg-primary"><i class="fa-solid fa-check me-1"></i>ปัจจุบัน</span>
                            <?php else: ?>
                                <?= Html::beginForm(['set-current'], 'post') ?>
                                    <input type="hidden" name="thai_year" value="<?= Html::encode($p->code) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">ตั้งเป็นปัจจุบัน</button>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if (!$isCur): ?>
                                <?= Html::beginForm(['delete'], 'post', ['data' => ['confirm' => 'ลบรอบปี ' . $p->code . '?']]) ?>
                                    <input type="hidden" name="thai_year" value="<?= Html::encode($p->code) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
