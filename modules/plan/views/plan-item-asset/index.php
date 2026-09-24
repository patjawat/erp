<?php

use yii\helpers\Html;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\models\Categorise[] $categories */
/** @var string $cat */
/** @var app\models\Categorise[] $items */
/** @var array $mapped plan_item code => asset_type codes */
/** @var array $countByCat */
/** @var array $typeOptions */

$this->title = 'แผนงาน ↔ ประเภทพัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body mb-0"><i class="bi bi-diagram-3 me-2"></i><?= $this->title ?></h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php if ($flash = Yii::$app->session->getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= Html::encode($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-info small">
    <i class="bi bi-info-circle me-1"></i>
    กำหนดว่าแผนงานแต่ละรายการ <b>ซื้อพัสดุประเภทใดได้บ้าง</b> — ใช้กับงานจัดซื้อปีที่ผูกแผน:
    เลือกแผนแล้ว หน้า "เลือกรายการ" จะแสดงเฉพาะประเภทที่กำหนด และถ้าใบขอซื้อมีรายการประเภทอื่น ระบบจะปรับเป็น <b>นอกแผน</b> ต้องรออนุมัติ.
    แผนงานที่<b>เว้นว่าง = ไม่จำกัดประเภท</b>
</div>

<div class="card">
    <div class="card-header bg-light d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex gap-2 align-items-center']) ?>
            <label class="small text-muted text-nowrap">หมวด</label>
            <select name="cat" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($categories as $c): ?>
                    <option value="<?= Html::encode($c->code) ?>" <?= $c->code === $cat ? 'selected' : '' ?>>
                        <?= Html::encode($c->title) ?><?= !empty($countByCat[$c->code]) ? ' — กำหนดแล้ว ' . $countByCat[$c->code] : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?= Html::endForm() ?>
        <?= Html::beginForm(['suggest', 'cat' => $cat], 'post', ['data' => ['confirm' => 'เติมค่าแนะนำให้แผนงานที่ยังไม่ได้กำหนด? (ไม่ทับค่าที่ตั้งไว้แล้ว)']]) ?>
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-magic me-1"></i> เติมค่าแนะนำ</button>
        <?= Html::endForm() ?>
    </div>

    <?= Html::beginForm(['save', 'cat' => $cat], 'post') ?>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:90px">รหัส</th>
                    <th style="min-width:220px">แผนงาน</th>
                    <th style="min-width:320px">ประเภทพัสดุที่ซื้อได้ <span class="text-muted fw-normal small">(ว่าง = ไม่จำกัด)</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="3" class="text-center text-muted py-3">หมวดนี้ไม่มีแผนงาน</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="text-muted small"><?= Html::encode($item->code) ?></td>
                        <td><?= Html::encode($item->title) ?></td>
                        <td>
                            <?= Html::hiddenInput('codes[]', $item->code) ?>
                            <?= Select2::widget([
                                'name' => 'types[' . $item->code . ']',
                                'value' => $mapped[$item->code] ?? [],
                                'data' => $typeOptions,
                                'options' => ['multiple' => true, 'placeholder' => 'ไม่จำกัดประเภท', 'id' => 'types-' . md5($item->code)],
                                'pluginOptions' => ['allowClear' => true],
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($items): ?>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="bi bi-floppy me-1"></i> บันทึกหมวดนี้</button>
        </div>
    <?php endif; ?>
    <?= Html::endForm() ?>
</div>
