<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var \app\modules\inventory\models\StockMonthlyReport $model */

$fmt = static function ($v) {
    return number_format((float) ($v ?? 0), 2);
};

$calcClosingQty   = (float) $model->opening_qty + (float) $model->in_qty - (float) $model->total_out_qty;
$calcClosingValue = (float) $model->opening_value + (float) $model->in_value - (float) $model->total_out_value;
?>

<div class="card mb-3">
    <div class="card-header bg-secondary-subtle py-2">
        <strong><i class="fa-solid fa-circle-info"></i> ข้อมูลปัจจุบัน</strong>
    </div>
    <div class="card-body p-3">
        <dl class="row small mb-0">
            <dt class="col-6">เดือน</dt>
            <dd class="col-6 text-end"><?= Html::encode($model->getMonthLabel()) ?></dd>

            <dt class="col-6">คลัง</dt>
            <dd class="col-6 text-end"><?= Html::encode($model->warehouse->warehouse_name ?? '-') ?></dd>

            <dt class="col-6">รหัสพัสดุ</dt>
            <dd class="col-6 text-end"><?= Html::encode($model->item_code) ?></dd>

            <dt class="col-6">รายการ</dt>
            <dd class="col-6 text-end"><?= Html::encode($model->item->item_name ?? '-') ?></dd>

            <dt class="col-6">หน่วยนับ</dt>
            <dd class="col-6 text-end"><?= Html::encode($model->unit_name ?? '-') ?></dd>

            <dt class="col-6 text-muted">ยอดยกมา (qty / มูลค่า)</dt>
            <dd class="col-6 text-end"><?= $fmt($model->opening_qty) ?> / <?= $fmt($model->opening_value) ?></dd>

            <dt class="col-6 text-muted">รับเข้า (qty / มูลค่า)</dt>
            <dd class="col-6 text-end"><?= $fmt($model->in_qty) ?> / <?= $fmt($model->in_value) ?></dd>

            <dt class="col-6 text-muted">จ่ายออกรวม (qty / มูลค่า)</dt>
            <dd class="col-6 text-end"><?= $fmt($model->total_out_qty) ?> / <?= $fmt($model->total_out_value) ?></dd>

            <dt class="col-6 text-info">ระบบคำนวณ closing (qty)</dt>
            <dd class="col-6 text-end"><code><?= $fmt($calcClosingQty) ?></code></dd>

            <dt class="col-6 text-info">ระบบคำนวณ closing (มูลค่า)</dt>
            <dd class="col-6 text-end"><code><?= $fmt($calcClosingValue) ?></code></dd>

            <?php if ($model->isAdjusted()): ?>
            <dt class="col-6 text-warning">ปรับยอดล่าสุด</dt>
            <dd class="col-6 text-end text-warning"><?= date('d/m/Y H:i', $model->adjusted_at) ?></dd>

            <?php if ($model->original_closing_qty !== null): ?>
            <dt class="col-6 text-muted">ค่าเดิมก่อนปรับ</dt>
            <dd class="col-6 text-end text-muted">
                <?= $fmt($model->original_closing_qty) ?> / <?= $fmt($model->original_closing_value) ?>
            </dd>
            <?php endif; ?>
            <?php endif; ?>
        </dl>
    </div>
</div>

<div class="alert alert-warning small">
    <i class="fa-solid fa-triangle-exclamation"></i>
    แก้เฉพาะ <code>closing_qty</code> / <code>closing_value</code><br>
    ค่า opening ของเดือนถัดไปจะอ่านจาก closing ที่แก้นี้
</div>

<?php $form = ActiveForm::begin([
    'id' => 'form-adjust-' . $model->id,
    'action' => Url::to(['adjust', 'id' => $model->id, 'modal' => 1]),
    'options' => ['class' => 'form-adjust-ajax'],
]); ?>

<div class="row g-2">
    <div class="col-12">
        <?= $form->field($model, 'closing_qty')
            ->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control text-end'])
            ->label('คงเหลือใหม่ (จำนวน) <span class="text-danger">*</span>') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'closing_value')
            ->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control text-end'])
            ->label('คงเหลือใหม่ (มูลค่า) <span class="text-danger">*</span>') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'adjustment_note')
            ->textarea(['rows' => 3, 'placeholder' => 'เช่น: นับสต็อกจริงพบสินค้าหายไป 2 ชิ้น'])
            ->label('เหตุผลการปรับยอด <span class="text-danger">*</span>') ?>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <?= Html::submitButton('<i class="fa-solid fa-save"></i> บันทึก',
        ['class' => 'btn btn-warning flex-grow-1']) ?>
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
        ยกเลิก
    </button>
</div>

<?php if ($model->isAdjusted()): ?>
<hr>
<?= Html::beginForm(
    Url::to(['reset-adjust', 'id' => $model->id, 'modal' => 1]),
    'post',
    ['class' => 'form-reset-adjust-ajax']
) ?>
<?= Html::submitButton('<i class="fa-solid fa-rotate-left"></i> ยกเลิกการปรับยอด (คืนค่าเดิม)',
    ['class' => 'btn btn-outline-danger btn-sm w-100']) ?>
<?= Html::endForm() ?>
<?php endif; ?>

<?php ActiveForm::end(); ?>
