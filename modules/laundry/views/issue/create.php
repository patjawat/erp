<?php

use app\components\AppHelper;
use app\widgets\datepicker\DatepickerThai;
use kartik\widgets\Select2;
use yii\helpers\Html;

/** @var int $countId */
/** @var string $countCode */
/** @var int $treeId */
/** @var string $unitName */
/** @var array $countOptions  id => label */
/** @var array $unitOptions   tree_id => name */
/** @var array $lines */
/** @var string $staffName */
/** @var string $issueDate Y-m-d */
$this->title = 'เพิ่มข้อมูลส่งผ้า';
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'issue']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-box-arrow-right me-2"></i>เพิ่มข้อมูลส่งผ้า</h1>
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับ', ['index', 'date' => AppHelper::convertToThai($issueDate)], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle me-2"></i><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
    <?php endif; ?>

    <!-- เลือกอ้างอิง / หน่วยงาน -->
    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body">
        <?= Html::beginForm(['create'], 'get', ['class' => 'row g-3 align-items-end']) ?>
            <?= Html::hiddenInput('date', AppHelper::convertToThai($issueDate)) ?>
            <div class="col-12 col-md-5">
                <label class="form-label">อ้างอิงผลตรวจนับ <span class="text-body-secondary small">(รู้ส่วนขาด)</span></label>
                <?= Select2::widget([
                    'name' => 'count_id', 'value' => $countId ?: '', 'data' => $countOptions,
                    'options' => ['placeholder' => '— ค้นหา/เลือกผลตรวจนับ —', 'id' => 'sel_count'],
                    'pluginOptions' => ['allowClear' => true, 'width' => '100%'],
                    'pluginEvents' => ['select2:select' => "function(){ $('#sel_tree').val(null); this.form.submit(); }"],
                ]) ?>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label">หรือเลือกหน่วยงาน <span class="text-body-secondary small">(กรอกเอง)</span></label>
                <?= Select2::widget([
                    'name' => 'tree_id', 'value' => $countId ? '' : ($treeId ?: ''), 'data' => $unitOptions,
                    'options' => ['placeholder' => '— ค้นหา/เลือกหน่วยงาน —', 'id' => 'sel_tree'],
                    'pluginOptions' => ['allowClear' => true, 'width' => '100%'],
                    'pluginEvents' => ['select2:select' => "function(){ $('#sel_count').val(null); this.form.submit(); }"],
                ]) ?>
            </div>
        <?= Html::endForm() ?>
    </div></div>

    <?php if ($treeId && !$lines): ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-body-secondary py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>หน่วยงานนี้ยังไม่ได้กำหนดประเภทผ้า/ยอดตั้งต้น — ตั้งที่ <?= Html::a('คลังผ้า → คลังย่อย', ['/laundry/stock/sub', 'department_id' => $treeId], ['class' => 'fw-semibold']) ?> ก่อน
        </div></div>
    <?php elseif ($treeId && $lines): ?>
        <?= Html::beginForm(['save'], 'post') ?>
            <?= Html::hiddenInput('tree_id', $treeId) ?>
            <?= Html::hiddenInput('count_id', $countId) ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-body px-4 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div><h2 class="h6 fw-semibold mb-0"><i class="bi bi-hospital me-2"></i><?= Html::encode($unitName) ?></h2>
                        <?php if ($countCode): ?><div class="small text-body-secondary">อ้างอิง <?= Html::encode($countCode) ?></div><?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2 small">
                        <span class="text-body-secondary">วันที่</span>
                        <?= DatepickerThai::widget(['name' => 'issued_date', 'value' => AppHelper::convertToThai($issueDate), 'options' => ['class' => 'form-control form-control-sm', 'style' => 'width:130px', 'autocomplete' => 'off']]) ?>
                        <span class="text-body-secondary">เวลา</span>
                        <?= Html::input('time', 'issued_time', date('H:i'), ['class' => 'form-control form-control-sm', 'style' => 'width:110px']) ?>
                        <span class="text-body-secondary">ผู้จ่าย <b class="text-body"><?= Html::encode($staffName) ?></b></span>
                    </div>
                </div>
                <div class="card-body p-0"><div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr>
                            <th class="ps-4">รายการผ้า</th>
                            <th class="text-end">ยอดตั้งต้น</th><th class="text-end">นับล่าสุด</th>
                            <th class="text-end">คลังหลัก</th><th class="text-end">จำนวนเบิก</th>
                            <th class="text-end pe-4" style="width:140px">จำนวนจ่าย</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($lines as $l): ?>
                            <?php
                            $target = (int) $l['target_qty'];
                            $counted = $l['counted'] === null ? null : (int) $l['counted'];
                            $gap = $counted === null ? $target : max(0, $target - $counted);
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= Html::encode($l['item_name']) ?></td>
                                <td class="text-end"><?= number_format($target) ?></td>
                                <td class="text-end"><?= $counted === null ? '<span class="text-body-secondary">—</span>' : number_format($counted) ?></td>
                                <td class="text-end"><?= number_format((int) $l['main_balance']) ?></td>
                                <td class="text-end">
                                    <?= $gap > 0 ? '<span class="badge text-bg-danger">' . number_format($gap) . '</span>' : '<span class="text-body-secondary">0</span>' ?>
                                    <?= Html::hiddenInput('need_qty[' . $l['item_id'] . ']', $gap) ?>
                                </td>
                                <td class="pe-4">
                                    <?= Html::input('number', 'issue_qty[' . $l['item_id'] . ']', $gap ?: '', ['class' => 'form-control form-control-sm text-end', 'min' => '0', 'step' => '1', 'inputmode' => 'numeric', 'placeholder' => '0']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div></div>
                <div class="card-footer bg-body d-flex justify-content-between align-items-center px-4 py-3">
                    <span class="small text-body-secondary"><i class="bi bi-info-circle me-1"></i>จำนวนจ่ายเริ่มต้น = ส่วนขาด (แก้ได้) · การจ่ายจะตัดยอดคลังหลัก</span>
                    <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกข้อมูล', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        <?= Html::endForm() ?>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-body-secondary py-5">
            <i class="bi bi-arrow-up-circle fs-2 d-block mb-2"></i>เลือกผลตรวจนับ หรือหน่วยงาน เพื่อจัดผ้าจ่าย
        </div></div>
    <?php endif; ?>
</div>
