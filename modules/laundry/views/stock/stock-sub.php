<?php

use yii\helpers\Html;

/** @var array $departments id => name */
/** @var int $departmentId */
/** @var array $rows  item_id,item_name,target_qty,min_qty,counted */
$this->title = 'คลังย่อย — ยอดผ้ารายหน่วยงาน';
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'stock']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-diagram-3 me-2"></i>คลังย่อยหน่วยงาน</h1>
        <div class="text-body-secondary small">ยอดตั้งต้น (กรอบผ้าที่หน่วยงานต้องมี) เทียบยอดนับล่าสุด → ส่วนขาดเพื่อจัดซื้อ</div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body">
        <?= Html::beginForm(['sub'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <label class="form-label">หน่วยงาน</label>
                <?= Html::dropDownList('department_id', $departmentId ?: '', $departments, [
                    'prompt' => 'เลือกหน่วยงาน', 'class' => 'form-select', 'onchange' => 'this.form.submit()',
                ]) ?>
            </div>
        <?= Html::endForm() ?>
        <?php if (!$departments): ?>
            <div class="text-body-secondary small mt-2"><i class="bi bi-info-circle me-1"></i>ยังไม่มีหน่วยงานที่ตั้งยอดตั้งต้น — ตั้งได้ที่เมนูตั้งค่า</div>
        <?php endif; ?>
    </div></div>

    <?php if ($departmentId): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-hospital me-2"></i><?= Html::encode($departments[$departmentId] ?? ('หน่วยงาน #' . $departmentId)) ?></h2></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th class="ps-4">ประเภทผ้า</th>
                        <th class="text-end">ยอดตั้งต้น</th><th class="text-end">ขั้นต่ำ</th>
                        <th class="text-end">นับล่าสุด</th><th class="text-end pe-4">ส่วนขาด</th>
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
                            <td class="text-end"><?= number_format($target) ?></td>
                            <td class="text-end text-body-secondary"><?= number_format((int) $r['min_qty']) ?></td>
                            <td class="text-end"><?= $counted === null ? '<span class="text-body-secondary">ยังไม่นับ</span>' : number_format($counted) ?></td>
                            <td class="text-end pe-4">
                                <?php if ($gap === null): ?>
                                    <span class="text-body-secondary">—</span>
                                <?php elseif ($gap > 0): ?>
                                    <span class="badge text-bg-danger"><?= number_format($gap) ?></span>
                                <?php else: ?>
                                    <span class="badge text-bg-success">ครบ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?>
                        <tr><td colspan="5" class="text-center text-body-secondary py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>หน่วยงานนี้ยังไม่ได้ตั้งยอดตั้งต้น</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div></div>
            <div class="card-footer bg-body small text-body-secondary px-4 py-3">
                <i class="bi bi-lightbulb me-1"></i>ส่วนขาด = ยอดตั้งต้น − ยอดนับล่าสุด (จากการสอบยอดสิ้นปีที่อนุมัติแล้ว) ใช้เป็นข้อมูลจัดซื้อเติมผ้า
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-body-secondary py-5">
            <i class="bi bi-arrow-up-circle fs-2 d-block mb-2"></i>เลือกหน่วยงานเพื่อดูยอดผ้า
        </div></div>
    <?php endif; ?>
</div>
