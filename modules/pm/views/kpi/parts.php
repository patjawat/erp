<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\modules\pm\models\KpiIndicator;

/** @var app\modules\pm\models\KpiHaPart $model @var app\modules\pm\models\KpiHaPart[] $parts */

$this->title = 'จัดการตอน HA (Part)';
$this->beginBlock('page-title'); ?>จัดการตอน HA (Part)<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'kpi']) ?><?php $this->endBlock();

$counts = [];
foreach (KpiIndicator::find()->select(['ha_part_id', 'c' => 'COUNT(*)'])->where(['not', ['ha_part_id' => null]])->groupBy('ha_part_id')->asArray()->all() as $r) {
    $counts[(int) $r['ha_part_id']] = (int) $r['c'];
}
?>

<?php foreach (['success' => 'success', 'error' => 'danger'] as $k => $cls): ?>
    <?php if (Yii::$app->session->hasFlash($k)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash($k)) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0"><?= $model->isNewRecord ? 'เพิ่มตอน HA' : 'แก้ไขตอน: ' . Html::encode($model->code) ?></h2>
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
</div>

<div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <?php $form = ActiveForm::begin(['action' => $model->isNewRecord ? ['parts'] : ['part-update', 'id' => $model->id]]); ?>
    <div class="row g-3 align-items-end">
        <div class="col-6 col-md-2"><?= $form->field($model, 'code')->textInput(['placeholder' => 'I, II, III, IV']) ?></div>
        <div class="col-12 col-md-6"><?= $form->field($model, 'name')->textInput(['placeholder' => 'เช่น ตอนที่ I: ภาพรวมของการบริหารองค์กร']) ?></div>
        <div class="col-6 col-md-1"><?= $form->field($model, 'sort_order')->input('number') ?></div>
        <div class="col-6 col-md-1 d-flex align-items-end pb-1"><?= $form->field($model, 'is_active')->checkbox() ?></div>
        <div class="col-6 col-md-2"><?= Html::submitButton($model->isNewRecord ? 'เพิ่มตอน' : 'บันทึก', ['class' => 'btn btn-primary w-100']) ?></div>
    </div>
    <?php ActiveForm::end(); ?>
</div></div>

<div class="card border-0 shadow-sm overflow-hidden"><div class="card-body p-0">
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4">รหัส</th><th>ชื่อตอน</th><th class="text-center">จำนวน KPI</th><th class="text-center">ลำดับ</th><th class="text-end pe-4">จัดการ</th></tr></thead>
        <tbody>
        <?php foreach ($parts as $p): ?>
            <tr>
                <td class="ps-4 fw-semibold"><?= Html::encode($p->code) ?></td>
                <td><?= Html::encode($p->name) ?><?php if (!$p->is_active): ?> <span class="badge bg-secondary-subtle text-secondary-emphasis">ปิด</span><?php endif; ?></td>
                <td class="text-center"><span class="badge bg-primary-subtle text-primary-emphasis"><?= $counts[$p->id] ?? 0 ?></span></td>
                <td class="text-center"><?= (int) $p->sort_order ?></td>
                <td class="text-end pe-4">
                    <?= Html::a('แก้ไข', ['part-update', 'id' => $p->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?= Html::a('ลบ', ['part-delete', 'id' => $p->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบตอนนี้?']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div></div>
