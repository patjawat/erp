<?php

use app\modules\ha12\models\Ha12Round;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12Round[] $rounds */
/** @var int $fiscalYear */
/** @var int[] $years */

$this->title = 'HA12-PCT · รายงาน';
$roundOptions = [];
foreach ($rounds as $r) {
    $roundOptions[$r->id] = $r->displayTitle();
}
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>รายงานผลสรุปและประเมินโดย PCT · ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'report']) ?></div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-upload"></i> นำเข้าข้อมูล', ['/ha12/import/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            <?= Html::a('<i class="bi bi-shield-lock"></i> ร่องรอยการกำกับดูแล', ['audit'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
        </div>
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-2']) ?>
            <label class="small text-body-secondary mb-0">ปีงบ</label>
            <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()']) ?>
        <?= Html::endForm() ?>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-printer me-1"></i> รายงานรอบ (พิมพ์ / Excel)</div>
                <div class="card-body">
                    <?php if (!$rounds): ?>
                        <p class="text-body-secondary small mb-0">ยังไม่มีรอบประเมินในปีงบนี้</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($rounds as $r): ?>
                                <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                                    <div>
                                        <div class="fw-semibold"><?= Html::encode($r->displayTitle()) ?></div>
                                        <div class="small text-body-secondary"><?= Html::encode($r->scopeUnit->name ?? 'ทั้งโรงพยาบาล') ?> · <?= $r->isClosed() ? 'ปิดรอบ' : 'เปิดอยู่' ?></div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <?= Html::a('<i class="bi bi-file-earmark-text"></i> รายงาน', ['round', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                        <?= Html::a('<i class="bi bi-file-earmark-excel"></i> Excel', ['export', 'id' => $r->id], ['class' => 'btn btn-sm btn-success']) ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-arrow-left-right me-1"></i> เทียบสองรอบ</div>
                <div class="card-body">
                    <?= Html::beginForm(['compare'], 'get') ?>
                        <?= Html::hiddenInput('fy', $fiscalYear) ?>
                        <label class="form-label small fw-semibold">รอบที่ 1</label>
                        <?= Html::dropDownList('a', null, $roundOptions, ['class' => 'form-select mb-2', 'prompt' => '— เลือกรอบ —']) ?>
                        <label class="form-label small fw-semibold">รอบที่ 2</label>
                        <?= Html::dropDownList('b', null, $roundOptions, ['class' => 'form-select mb-3', 'prompt' => '— เลือกรอบ —']) ?>
                        <?= Html::submitButton('<i class="bi bi-bar-chart-steps"></i> เทียบผล', ['class' => 'btn btn-primary w-100']) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
</div>
