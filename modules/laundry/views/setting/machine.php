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
                <thead class="table-light"><tr><th class="ps-4">เครื่อง</th><th>เลขครุภัณฑ์</th><th style="width:220px">กำลัง (กก.) / สถานะ</th><th class="pe-4 text-end">จัดการ</th></tr></thead>
                <tbody>
                <?php foreach ($machines as $machine): ?>
                    <tr>
                        <td class="ps-4"><?= $machine['machine_type'] === 'WASH' ? '<i class="bi bi-droplet-half text-primary me-1"></i>เครื่องซัก' : '<i class="bi bi-wind text-warning me-1"></i>เครื่องอบ' ?></td>
                        <td><?= Html::encode(($machine['asset_code'] ?: '#' . $machine['asset_id']) . ' · ' . ($machine['asset_name'] ?: '')) ?><div class="small text-body-secondary"><?= Html::encode($machine['lifecycle_status'] ?: '') ?></div></td>
                        <td>
                            <?php if ($canManage): ?>
                                <?= Html::beginForm(['machine-save'], 'post', ['class' => 'd-flex align-items-center gap-1']) ?>
                                    <?= Html::hiddenInput('id', $machine['id']) ?>
                                    <?= Html::input('number', 'capacity_kg', (float) $machine['capacity_kg'], ['class' => 'form-control form-control-sm', 'style' => 'width:90px', 'step' => '0.001', 'min' => '0.001', 'required' => true]) ?>
                                    <div class="form-check form-switch mb-0 ms-1" title="เปิดใช้งาน">
                                        <?= Html::checkbox('is_active', (int) $machine['is_active'] === 1, ['class' => 'form-check-input']) ?>
                                    </div>
                                    <?= Html::submitButton('<i class="bi bi-save"></i>', ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'บันทึก']) ?>
                                <?= Html::endForm() ?>
                            <?php else: ?>
                                <?= number_format((float) $machine['capacity_kg'], 1) ?> กก.
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                <?= Html::a('<i class="bi bi-journal-text"></i>', ['/am/equip/view-asset', 'id' => $machine['asset_id']], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'ทะเบียน/ประวัติซ่อม']) ?>
                                <?= Html::a('<i class="bi bi-tools"></i>', ['/am/maintenance/index', 'code' => $machine['asset_code']], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'บำรุงรักษา']) ?>
                                <?php if ($canManage): ?>
                                    <?= Html::beginForm(['machine-delete'], 'post', ['class' => 'd-inline']) ?>
                                        <?= Html::hiddenInput('id', $machine['id']) ?>
                                        <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบเครื่อง', 'data-confirm' => 'ลบเครื่องนี้ออกจากทะเบียน?']) ?>
                                    <?= Html::endForm() ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$machines): ?><tr><td colspan="4" class="text-center text-body-secondary py-4"><i class="bi bi-inbox me-1"></i>ยังไม่เชื่อมเครื่องกับครุภัณฑ์</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
