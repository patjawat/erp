<?php

use app\modules\complaint\models\ComplaintIndicator;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var ComplaintIndicator[] $items */
/** @var int[] $years */

$this->title = 'จำนวน visit รายปี (CC01)';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ข้อมูลพื้นฐานตัวชี้วัด — ตัวหารของ CC01<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/complaint/menu', ['active' => 'kpi']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $tone): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?>
            <div class="alert alert-<?= $tone ?> py-2 small"><?= Html::encode(Yii::$app->session->getFlash($key)) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-people me-1"></i> จำนวน visit ที่บันทึกไว้</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>ปีงบ</th><th class="text-end">จำนวน visit</th><th>หมายเหตุ</th></tr></thead>
                        <tbody>
                            <?php if (!$items): ?><tr><td colspan="3" class="text-center text-body-secondary py-3">— ยังไม่มีข้อมูล —</td></tr><?php endif; ?>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td class="fw-semibold"><?= (int) $it->fiscal_year ?></td>
                                    <td class="text-end"><?= number_format((int) $it->visit_count) ?></td>
                                    <td class="small text-body-secondary"><?= Html::encode($it->note ?: '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent fw-semibold">เพิ่ม/แก้ไขจำนวน visit</div>
                <div class="card-body">
                    <?= Html::beginForm(['save-indicator'], 'post') ?>
                        <div class="mb-2">
                            <label class="form-label small">ปีงบประมาณ</label>
                            <?= Html::dropDownList('fiscal_year', (int) \app\components\AppHelper::YearBudget(), array_combine($years, $years), ['class' => 'form-select form-select-sm']) ?>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">จำนวน visit ทั้งปี</label>
                            <?= Html::input('number', 'visit_count', '', ['class' => 'form-control form-control-sm', 'min' => 0, 'required' => true]) ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">หมายเหตุ</label>
                            <?= Html::textInput('note', '', ['class' => 'form-control form-control-sm']) ?>
                        </div>
                        <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึก', ['class' => 'btn btn-primary btn-sm']) ?>
                        <div class="form-text mt-2">บันทึกซ้ำปีเดิมจะเป็นการแก้ไขค่าเดิม</div>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
</div>
