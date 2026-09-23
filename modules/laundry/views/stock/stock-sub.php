<?php

use kartik\widgets\Select2;
use yii\helpers\Html;

/** @var array $departments id => name */
/** @var int $departmentId */
/** @var array $rows  item_id,item_name,target_qty,min_qty,counted */
/** @var array $availableItems id => item_name */
$this->title = 'คลังย่อย — ยอดผ้ารายหน่วยงาน';
$canManage = Yii::$app->user->can('laundry.manage');
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'stock']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-diagram-3 me-2"></i>คลังย่อยหน่วยงาน</h1>
        <div class="text-body-secondary small">กำหนดประเภทผ้า + ยอดตั้งต้น (กรอบผ้าที่หน่วยงานต้องมี) เทียบยอดนับล่าสุด → ส่วนขาดเพื่อจัดซื้อ</div>
    </div>

    <?php foreach (['error' => 'danger', 'success' => 'success'] as $k => $c): ?>
        <?php if (Yii::$app->session->hasFlash($k)): ?><div class="alert alert-<?= $c ?> d-flex align-items-center"><i class="bi bi-<?= $c === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($k)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body">
        <?= Html::beginForm(['sub'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <label class="form-label">หน่วยงาน</label>
                <?= Select2::widget([
                    'name' => 'department_id', 'value' => $departmentId ?: '', 'data' => $departments,
                    'options' => ['placeholder' => 'ค้นหา/เลือกหน่วยงาน', 'id' => 'sub-department'],
                    'pluginOptions' => ['allowClear' => false, 'width' => '100%'],
                    'pluginEvents' => ['change' => 'function(){ this.form.submit(); }'],
                ]) ?>
            </div>
        <?= Html::endForm() ?>
        <?php if (!$departments): ?>
            <div class="text-body-secondary small mt-2"><i class="bi bi-info-circle me-1"></i>ยังไม่มีหน่วยงาน — เพิ่มที่ <?= Html::a('ตั้งค่า → หน่วยงาน', ['/laundry/setting/unit'], ['class' => 'fw-semibold']) ?></div>
        <?php endif; ?>
    </div></div>

    <?php if ($departmentId): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
            <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-hospital me-2"></i><?= Html::encode($departments[$departmentId] ?? ('หน่วยงาน #' . $departmentId)) ?> — ประเภทผ้าและยอดตั้งต้น</h2></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th class="ps-4">ประเภทผ้า</th>
                        <th style="width:120px">ยอดตั้งต้น</th><th style="width:110px">ขั้นต่ำ</th>
                        <th class="text-end">นับล่าสุด</th><th class="text-end">ส่วนขาด</th><th class="pe-4 text-end" style="width:90px">จัดการ</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <?php
                        $target = (int) $r['target_qty'];
                        $counted = $r['counted'] === null ? null : (int) $r['counted'];
                        $gap = $counted === null ? null : max(0, $target - $counted);
                        ?>
                        <tr>
                            <td class="ps-4 fw-semibold"><?= Html::encode($r['item_name']) ?></td>
                            <?php if ($canManage): ?>
                                <td colspan="2">
                                    <?= Html::beginForm(['par-save'], 'post', ['class' => 'd-flex align-items-center gap-1']) ?>
                                        <?= Html::hiddenInput('department_id', $departmentId) ?>
                                        <?= Html::hiddenInput('item_id', $r['item_id']) ?>
                                        <?= Html::input('number', 'target_qty', $target, ['class' => 'form-control form-control-sm', 'style' => 'width:80px', 'min' => 0, 'title' => 'ยอดตั้งต้น']) ?>
                                        <span class="text-body-secondary small">/</span>
                                        <?= Html::input('number', 'min_qty', (int) $r['min_qty'], ['class' => 'form-control form-control-sm', 'style' => 'width:70px', 'min' => 0, 'title' => 'ขั้นต่ำ']) ?>
                                        <?= Html::submitButton('<i class="bi bi-save"></i>', ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'บันทึก']) ?>
                                    <?= Html::endForm() ?>
                                </td>
                            <?php else: ?>
                                <td><?= number_format($target) ?></td>
                                <td class="text-body-secondary"><?= number_format((int) $r['min_qty']) ?></td>
                            <?php endif; ?>
                            <td class="text-end"><?= $counted === null ? '<span class="text-body-secondary">ยังไม่นับ</span>' : number_format($counted) ?></td>
                            <td class="text-end">
                                <?php if ($gap === null): ?><span class="text-body-secondary">—</span>
                                <?php elseif ($gap > 0): ?><span class="badge text-bg-danger"><?= number_format($gap) ?></span>
                                <?php else: ?><span class="badge text-bg-success">ครบ</span><?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <?php if ($canManage): ?>
                                    <?= Html::beginForm(['par-delete'], 'post', ['class' => 'd-inline']) ?>
                                        <?= Html::hiddenInput('department_id', $departmentId) ?>
                                        <?= Html::hiddenInput('item_id', $r['item_id']) ?>
                                        <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบ', 'data-confirm' => 'ลบประเภทผ้านี้ออกจากคลังย่อย?']) ?>
                                    <?= Html::endForm() ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?>
                        <tr><td colspan="6" class="text-center text-body-secondary py-4"><i class="bi bi-inbox me-1"></i>ยังไม่ได้กำหนดประเภทผ้าให้หน่วยงานนี้ — เพิ่มด้านล่าง</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div></div>
            <div class="card-footer bg-body small text-body-secondary px-4 py-3">
                <i class="bi bi-lightbulb me-1"></i>ส่วนขาด = ยอดตั้งต้น − ยอดนับล่าสุด (จากตรวจนับผ้า) ใช้เป็นข้อมูลจัดซื้อเติมผ้า และตอนจ่ายผ้า
            </div>
        </div>

        <?php if ($canManage && $availableItems): ?>
            <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
                <h2 class="h6 fw-semibold mb-3"><i class="bi bi-plus-circle me-2"></i>เพิ่มประเภทผ้าให้หน่วยงานนี้</h2>
                <?= Html::beginForm(['par-save'], 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <?= Html::hiddenInput('department_id', $departmentId) ?>
                    <div class="col-12 col-md-5">
                        <label class="form-label">ประเภทผ้า</label>
                        <?= Html::dropDownList('item_id', null, $availableItems, ['prompt' => 'เลือกประเภทผ้า', 'class' => 'form-select', 'required' => true]) ?>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">ยอดตั้งต้น</label>
                        <?= Html::input('number', 'target_qty', '', ['class' => 'form-control', 'min' => 0, 'required' => true, 'placeholder' => 'เช่น 100']) ?>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">ขั้นต่ำ</label>
                        <?= Html::input('number', 'min_qty', 0, ['class' => 'form-control', 'min' => 0]) ?>
                    </div>
                    <div class="col-12 col-md-2">
                        <?= Html::submitButton('<i class="bi bi-plus-lg me-1"></i>เพิ่ม', ['class' => 'btn btn-primary w-100']) ?>
                    </div>
                <?= Html::endForm() ?>
            </div></div>
        <?php elseif ($canManage && !$availableItems && $rows): ?>
            <div class="text-body-secondary small"><i class="bi bi-check2-circle me-1"></i>กำหนดครบทุกประเภทผ้าแล้ว</div>
        <?php endif; ?>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-body-secondary py-5">
            <i class="bi bi-arrow-up-circle fs-2 d-block mb-2"></i>เลือกหน่วยงานเพื่อกำหนด/ดูยอดผ้า
        </div></div>
    <?php endif; ?>
</div>
