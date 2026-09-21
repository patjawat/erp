<?php

use app\modules\laundry\models\LaundryUnit;
use kartik\widgets\Select2;
use yii\helpers\Html;

/** @var LaundryUnit[] $units */
/** @var array $ouGroups  groupTitle => [org_unit_id => name] */
$this->title = 'ตั้งค่า — หน่วยงานซักฟอก';
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'setting']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-diagram-3 me-2"></i>หน่วยงานซักฟอก</h1>
        <div class="text-body-secondary small">กำหนดหน่วยงานที่จะแสดงเป็นการ์ดในหน้ารับผ้า / ตรวจรับ / จ่ายผ้า</div>
    </div>

    <?php foreach (['error' => 'danger', 'success' => 'success'] as $k => $c): ?>
        <?php if (Yii::$app->session->hasFlash($k)): ?><div class="alert alert-<?= $c ?> d-flex align-items-center"><i class="bi bi-<?= $c === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($k)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>เพิ่มหน่วยงาน</h2></div>
                <div class="card-body">
                        <?= Html::beginForm(['unit-save'], 'post', ['class' => 'row g-3']) ?>
                            <div class="col-12">
                                <label class="form-label">หน่วยงาน</label>
                                <?= Select2::widget([
                                    'name' => 'org_unit_id',
                                    'data' => $ouGroups,
                                    'options' => ['placeholder' => 'เลือกหน่วยงาน', 'id' => 'lnd-unit-select'],
                                    'pluginOptions' => ['allowClear' => false, 'width' => '100%'],
                                ]) ?>
                            </div>
                            <div class="col-6">
                                <label class="form-label">ชื่อย่อ</label>
                                <?= Html::textInput('abbr', '', ['class' => 'form-control', 'maxlength' => 20, 'placeholder' => 'เช่น OPD']) ?>
                            </div>
                            <div class="col-6">
                                <label class="form-label">ลำดับ</label>
                                <?= Html::input('number', 'sort_order', 0, ['class' => 'form-control', 'min' => 0]) ?>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <?= Html::checkbox('is_active', true, ['class' => 'form-check-input', 'id' => 'newActive']) ?>
                                    <label class="form-check-label" for="newActive">เปิดใช้งาน</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <?= Html::submitButton('<i class="bi bi-save me-1"></i>เพิ่ม', ['class' => 'btn btn-primary']) ?>
                            </div>
                        <?= Html::endForm() ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>หน่วยงานในทะเบียน (<?= count($units) ?>)</h2></div>
                <div class="card-body p-0"><div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">ชื่อย่อ</th><th>หน่วยงาน</th><th class="text-end">ลำดับ</th><th>สถานะ</th><th class="text-end pe-4">จัดการ</th></tr></thead>
                        <tbody>
                        <?php foreach ($units as $u): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?= Html::encode($u->abbr ?: '—') ?></td>
                                <td><?= Html::encode($u->name) ?></td>
                                <td class="text-end"><?= (int) $u->sort_order ?></td>
                                <td>
                                    <?php if ($u->is_active): ?><span class="badge text-bg-success">ใช้งาน</span><?php else: ?><span class="badge text-bg-secondary">ปิด</span><?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if (Yii::$app->user->can('laundry.manage')): ?>
                                        <?= Html::beginForm(['unit-delete'], 'post', ['class' => 'd-inline']) ?>
                                            <?= Html::hiddenInput('id', $u->id) ?>
                                            <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'ลบหน่วยงานนี้ออกจากทะเบียนซักฟอก?']) ?>
                                        <?= Html::endForm() ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$units): ?><tr><td colspan="5" class="text-center text-body-secondary py-4"><i class="bi bi-inbox me-1"></i>ยังไม่มีหน่วยงานในทะเบียน</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div></div>
            </div>
        </div>
    </div>
</div>
