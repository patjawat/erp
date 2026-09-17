<?php

use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var array $machines */
$this->title = 'ตั้งค่า — เครื่องซัก–อบ';
$canManage = Yii::$app->user->can('laundry.manage');
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'setting']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-cpu me-2"></i>เครื่องซัก–อบ</h1>
        <div class="text-body-secondary small">เชื่อมเครื่องซัก/อบกับครุภัณฑ์ในระบบทรัพย์สิน แล้วนำไปใช้ในรอบซัก–อบ</div>
    </div>

    <?php foreach (['error' => 'danger', 'success' => 'success'] as $k => $c): ?>
        <?php if (Yii::$app->session->hasFlash($k)): ?><div class="alert alert-<?= $c ?> d-flex align-items-center"><i class="bi bi-<?= $c === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($k)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-link-45deg me-2"></i>เชื่อมเครื่องกับครุภัณฑ์</h2></div>
        <div class="card-body">
            <?php if ($canManage): ?>
                <?= Html::beginForm(['/laundry/processing/register-machine'], 'post', ['class' => 'row g-3 align-items-end mb-3']) ?>
                    <div class="col-12 col-md-5"><label class="form-label">ครุภัณฑ์ (จากระบบทรัพย์สิน)</label>
                        <?= Select2::widget([
                            'name' => 'asset_id',
                            'data' => [],
                            'options' => ['placeholder' => 'ค้นหาเลข/ชื่อครุภัณฑ์...', 'id' => 'lnd-asset-select'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 1,
                                'ajax' => [
                                    'url' => Url::to(['/laundry/processing/asset-search']),
                                    'dataType' => 'json',
                                    'delay' => 250,
                                    'data' => new JsExpression('function(params){ return {q: params.term}; }'),
                                    'cache' => true,
                                ],
                            ],
                        ]) ?>
                    </div>
                    <div class="col-6 col-md-3"><label class="form-label">ประเภท</label><?= Html::dropDownList('machine_type', 'WASH', ['WASH' => 'เครื่องซัก', 'DRY' => 'เครื่องอบ'], ['class' => 'form-select']) ?></div>
                    <div class="col-6 col-md-2"><label class="form-label">กำลังเครื่อง (กก.)</label><?= Html::input('number', 'capacity_kg', '', ['class' => 'form-control', 'step' => '0.001', 'min' => '0.001', 'required' => true]) ?></div>
                    <div class="col-12 col-md-2"><?= Html::submitButton('<i class="bi bi-link-45deg me-1"></i>เชื่อม', ['class' => 'btn btn-primary w-100']) ?></div>
                <?= Html::endForm() ?>
            <?php endif; ?>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-4">เครื่อง</th><th>เลขครุภัณฑ์</th><th class="text-end">กำลัง</th><th>สถานะครุภัณฑ์</th><th class="pe-4">รายละเอียด</th></tr></thead>
                <tbody>
                <?php foreach ($machines as $machine): ?>
                    <tr>
                        <td class="ps-4"><?= $machine['machine_type'] === 'WASH' ? '<i class="bi bi-droplet me-1"></i>เครื่องซัก' : '<i class="bi bi-wind me-1"></i>เครื่องอบ' ?></td>
                        <td><?= Html::encode(($machine['asset_code'] ?: '#' . $machine['asset_id']) . ' · ' . ($machine['asset_name'] ?: '')) ?></td>
                        <td class="text-end"><?= number_format((float) $machine['capacity_kg'], 3) ?> กก.</td>
                        <td><?= Html::encode($machine['lifecycle_status'] ?: 'ไม่ระบุ') ?></td>
                        <td class="pe-4 d-flex flex-wrap gap-1">
                            <?= Html::a('<i class="bi bi-journal-text me-1"></i>ทะเบียน/ประวัติซ่อม', ['/am/equip/view-asset', 'id' => $machine['asset_id']], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            <?= Html::a('<i class="bi bi-tools me-1"></i>บำรุงรักษา', ['/am/maintenance/index', 'code' => $machine['asset_code']], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$machines): ?><tr><td colspan="5" class="text-center text-body-secondary py-4"><i class="bi bi-inbox me-1"></i>ยังไม่เชื่อมเครื่องกับครุภัณฑ์</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
