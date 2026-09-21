<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\modules\pm\models\KpiGroup;
use app\modules\pm\models\KpiIndicator;

/** @var app\modules\pm\models\KpiGroup $model @var app\modules\pm\models\KpiGroup[] $groups */

$this->title = 'จัดการกลุ่มตัวชี้วัด';
$this->beginBlock('page-title'); ?>จัดการกลุ่มตัวชี้วัด<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'kpi']) ?><?php $this->endBlock();

$counts = [];
foreach (KpiIndicator::find()->select(['group_id', 'c' => 'COUNT(*)'])->groupBy('group_id')->asArray()->all() as $r) {
    $counts[(int) $r['group_id']] = (int) $r['c'];
}
?>

<?php foreach (['success' => 'success', 'error' => 'danger'] as $k => $cls): ?>
    <?php if (Yii::$app->session->hasFlash($k)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash($k)) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0"><?= $model->isNewRecord ? 'เพิ่มกลุ่มตัวชี้วัดใหม่' : 'แก้ไขกลุ่ม: ' . Html::encode($model->name) ?></h2>
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
</div>

<div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <?php $form = ActiveForm::begin(['action' => $model->isNewRecord ? ['groups'] : ['group-update', 'id' => $model->id]]); ?>
    <div class="row g-3">
        <div class="col-12 col-md-6"><?= $form->field($model, 'name')->textInput(['placeholder' => 'เช่น ตัวชี้วัดระดับโรงพยาบาล']) ?></div>
        <div class="col-12 col-md-6"><?= $form->field($model, 'name_en')->textInput(['placeholder' => 'e.g. Hospital-Level KPIs']) ?></div>
        <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 2, 'placeholder' => 'คำอธิบายสั้น ๆ เกี่ยวกับกลุ่มนี้']) ?></div>
        <div class="col-6 col-md-3"><?= $form->field($model, 'icon')->textInput(['placeholder' => 'bi-graph-up'])->hint('Bootstrap Icons') ?></div>
        <div class="col-6 col-md-3"><?= $form->field($model, 'color')->input('color') ?></div>
        <div class="col-6 col-md-2"><?= $form->field($model, 'sort_order')->input('number') ?></div>
        <div class="col-6 col-md-2 d-flex align-items-end"><?= $form->field($model, 'is_active')->checkbox() ?></div>
        <div class="col-12 col-md-2 d-flex align-items-end">
            <?= Html::submitButton($model->isNewRecord ? 'เพิ่มกลุ่ม' : 'บันทึก', ['class' => 'btn btn-primary w-100']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div></div>

<div class="card border-0 shadow-sm overflow-hidden"><div class="card-body p-0">
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4">กลุ่มตัวชี้วัด</th><th>คำอธิบาย</th><th class="text-center">จำนวน KPI</th><th class="text-center">ลำดับ</th><th class="text-end pe-4">จัดการ</th></tr></thead>
        <tbody>
        <?php foreach ($groups as $g): ?>
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded" style="width:34px;height:34px;background:<?= Html::encode($g->color ?: '#6c757d') ?>1a;color:<?= Html::encode($g->color ?: '#6c757d') ?>"><i class="bi <?= Html::encode($g->icon ?: 'bi-graph-up') ?>"></i></span>
                        <div><div class="fw-semibold"><?= Html::encode($g->name) ?><?php if ($g->isStrategy()): ?> <span class="badge bg-secondary-subtle text-secondary-emphasis">ยุทธศาสตร์</span><?php endif; ?></div>
                            <?php if ($g->name_en): ?><div class="small text-muted"><?= Html::encode($g->name_en) ?></div><?php endif; ?></div>
                    </div>
                </td>
                <td class="small text-muted"><?= Html::encode($g->description ?: '-') ?></td>
                <td class="text-center"><span class="badge bg-primary-subtle text-primary-emphasis"><?= $counts[$g->id] ?? 0 ?></span></td>
                <td class="text-center"><?= (int) $g->sort_order ?></td>
                <td class="text-end pe-4">
                    <?= Html::a('แก้ไข', ['group-update', 'id' => $g->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?php if (!$g->isStrategy()): ?>
                        <?= Html::a('ลบ', ['group-delete', 'id' => $g->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบกลุ่มนี้?']) ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div></div>
