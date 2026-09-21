<?php

use yii\helpers\Html;

$this->title = 'บัญชีผ้าหมุนเวียน';
$canManage = Yii::$app->user->can('laundry.manage');
$canApprove = Yii::$app->user->can('laundry.approve');
$itemOptions = [];
foreach ($items as $item) {
    $itemOptions[$item['id']] = $item['item_code'] . ' · ' . $item['item_name'];
}
$reworkOptions = $repairOptions = $disposalOptions = [];
foreach ($items as $item) {
    $label = $item['item_code'] . ' · ' . $item['item_name'];
    if ($item['rework_qty'] > 0) {
        $reworkOptions[$item['id']] = $label . ' (' . number_format($item['rework_qty']) . ' ชิ้น)';
    }
    if ($item['repair_qty'] > 0) {
        $repairOptions[$item['id']] = $label . ' (' . number_format($item['repair_qty']) . ' ชิ้น)';
    }
    if ($item['disposal_pending_qty'] > 0) {
        $disposalOptions[$item['id']] = $label . ' (' . number_format($item['disposal_pending_qty']) . ' ชิ้น)';
    }
}
$itemField = static function () use ($itemOptions) {
    return Html::dropDownList('item_id', null, $itemOptions, ['prompt' => 'เลือกชนิดผ้า', 'class' => 'form-select', 'required' => true]);
};
$departmentField = static function () use ($departments) {
    return Html::dropDownList('department_id', null, $departments, ['prompt' => 'เลือกหน่วยงาน', 'class' => 'form-select', 'required' => true]);
};
$qtyField = static function () {
    return Html::input('number', 'qty', '', ['class' => 'form-control', 'min' => 1, 'step' => 1, 'required' => true]);
};
?>
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
        <div><h4 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h4><div class="text-muted">บัญชีผ้าหมุนเวียนเป็นชิ้น แยกจากยอดพัสดุในคลังย่อยและน้ำหนักงานซัก</div></div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('สอบยอดสิ้นปี', ['/laundry/annual-count/index'], ['class' => 'btn btn-outline-primary rounded-3']) ?>
            <?= Html::a('วางแผนจัดหาผ้า', ['/laundry/procurement/index'], ['class' => 'btn btn-outline-primary rounded-3']) ?>
            <?= Html::a('ตรวจสอบยอด', ['/laundry/audit/index'], ['class' => 'btn btn-outline-primary rounded-3']) ?>
            <?= Html::a('รอบซัก–อบ', ['/laundry/processing/index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
        </div>
    </div>
    <?php foreach (['error' => 'danger', 'success' => 'success'] as $key => $class): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?><div class="alert alert-<?= $class ?>"><?= Html::encode(Yii::$app->session->getFlash($key)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div><div class="fw-semibold">คลังย่อยเจ้าของผ้า</div><div class="text-muted small"><?= Html::encode($receivingWarehouseId && isset($warehouseOptions[$receivingWarehouseId]) ? $warehouseOptions[$receivingWarehouseId] : 'ยังไม่กำหนด — ไม่สามารถลงทะเบียนผ้าใหม่เข้าบัญชีหมุนเวียนได้') ?> · การหมุนเวียนผ้าไม่ตัดยอดคลังย่อย</div></div>
        <?php if ($canApprove): ?>
            <?= Html::beginForm(['set-receiving-warehouse'], 'post', ['class' => 'd-flex flex-column flex-sm-row gap-2']) ?>
                <?= Html::dropDownList('warehouse_id', $receivingWarehouseId, $warehouseOptions, ['prompt' => 'เลือกคลังซักฟอก', 'class' => 'form-select', 'required' => true]) ?>
                <?= Html::submitButton('บันทึกคลังรับ', ['class' => 'btn btn-outline-primary rounded-3 text-nowrap']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div></div>

    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">ชนิดผ้าและยอดที่คลังซักฟอก</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">รหัส / ชนิดผ้า</th><th>รหัสพัสดุ</th><th class="text-end">สะอาด (ชิ้น)</th><th class="text-end pe-4">รับคืนรอตรวจ (ชิ้น)</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr><td class="ps-4 fw-semibold"><?= Html::encode($item['item_code'] . ' · ' . $item['item_name']) ?></td><td><?= Html::encode($item['stock_item_code'] ?: '—') ?></td><td class="text-end"><?= number_format($item['clean_qty']) ?></td><td class="text-end pe-4"><?= number_format($item['dirty_qty']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$items): ?><tr><td colspan="4" class="text-center text-muted py-4">ยังไม่มีชนิดผ้า</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>

    <?php if ($canManage): ?>
        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                <h5 class="fw-semibold">เพิ่มชนิดผ้า</h5>
                <?= Html::beginForm(['create-item'], 'post', ['class' => 'row g-2']) ?>
                    <div class="col-12 col-sm-4"><label class="form-label">รหัสผ้า</label><?= Html::textInput('item_code', '', ['class' => 'form-control', 'required' => true, 'maxlength' => 50]) ?></div>
                    <div class="col-12 col-sm-8"><label class="form-label">ชื่อผ้า</label><?= Html::textInput('item_name', '', ['class' => 'form-control', 'required' => true, 'maxlength' => 255]) ?></div>
                    <div class="col-12"><label class="form-label">รหัสพัสดุที่เชื่อม (ถ้ามี)</label><?= Html::textInput('stock_item_code', '', ['class' => 'form-control', 'maxlength' => 50]) ?></div>
                    <div class="col-12"><?= Html::submitButton('บันทึกชนิดผ้า', ['class' => 'btn btn-primary rounded-3']) ?></div>
                <?= Html::endForm() ?>
            </div></div></div>
            <div class="col-12 col-xl-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                <h5 class="fw-semibold">ลงทะเบียนผ้าใหม่เข้าบัญชีหมุนเวียน</h5>
                <p class="small text-muted">อ้างบรรทัดใบจ่ายคลังปกติที่ยืนยันแล้วให้คลังย่อยซักฟอก เพื่อนำจำนวนชิ้นเข้าบัญชีหมุนเวียนหนึ่งครั้ง ขั้นตอนนี้ไม่จ่ายหรือตัดยอดคลังย่อยซ้ำ</p>
                <?= Html::beginForm(['receive'], 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <div class="col-12 col-sm-7"><label class="form-label">ชนิดผ้า</label><?= $itemField() ?></div>
                    <div class="col-12 col-sm-5"><label class="form-label">ID บรรทัดใบจ่ายคลัง</label><?= Html::input('number', 'stock_detail_id', '', ['class' => 'form-control', 'min' => 1, 'required' => true]) ?></div>
                    <div class="col-12"><?= Html::submitButton('ลงทะเบียนผ้าเข้าบัญชีหมุนเวียน', ['class' => 'btn btn-primary rounded-3']) ?></div>
                <?= Html::endForm() ?>
            </div></div></div>
            <div class="col-12 col-xl-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                <h5 class="fw-semibold">จ่ายผ้าสะอาดให้หน่วยงาน</h5>
                <p class="small text-muted">เลือกล็อตผ้าสะอาดที่มีจำนวนคงเหลือ ระบบบันทึกล็อตที่จ่ายเพื่อสอบย้อนกลับ</p>
                <?= Html::beginForm(['issue'], 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <div class="col-12 col-sm-5"><label class="form-label">ล็อตผ้าสะอาด</label><?= Html::dropDownList('lot_id', null, $availableLots, ['prompt' => 'เลือกล็อตที่มีผ้า', 'class' => 'form-select', 'required' => true]) ?></div>
                    <div class="col-12 col-sm-5"><label class="form-label">หน่วยงาน</label><?= $departmentField() ?></div>
                    <div class="col-12 col-sm-2"><label class="form-label">ชิ้น</label><?= $qtyField() ?></div>
                    <div class="col-12"><?= Html::submitButton('ยืนยันการจ่ายตามล็อต', ['class' => 'btn btn-primary rounded-3', 'disabled' => !$availableLots]) ?></div>
                <?= Html::endForm() ?>
                <?php if (!$availableLots): ?><div class="small text-body-secondary mt-2">ยังไม่มีล็อตผ้าสะอาดพร้อมจ่าย</div><?php endif; ?>
            </div></div></div>
            <div class="col-12 col-xl-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                <h5 class="fw-semibold">รับคืนแบบนับชิ้น</h5>
                <p class="small text-muted">ใช้เมื่อทราบหน่วยงานต้นทางและนับชนิด/จำนวนจริงแล้วเท่านั้น; การชั่งกิโลกรัมไม่ลดบัญชีชิ้น</p>
                <?= Html::beginForm(['return-counted'], 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <div class="col-12 col-sm-5"><label class="form-label">ชนิดผ้า</label><?= $itemField() ?></div>
                    <div class="col-12 col-sm-5"><label class="form-label">หน่วยงาน</label><?= $departmentField() ?></div>
                    <div class="col-12 col-sm-2"><label class="form-label">ชิ้น</label><?= $qtyField() ?></div>
                    <div class="col-12"><?= Html::submitButton('ยืนยันการรับคืน', ['class' => 'btn btn-primary rounded-3']) ?></div>
                <?= Html::endForm() ?>
            </div></div></div>
            <div class="col-12 col-xl-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                <h5 class="fw-semibold">นับชิ้นหลังอบเพื่อผูกกับรอบเครื่อง</h5>
                <p class="small text-muted">นับจริงแยกชนิดผ้าจากรอบอบที่เสร็จแล้ว ระบุหลักฐานรอบ/ถุง ผู้อนุมัติต้องเป็นคนละคน ผ้าจะถูกกันไว้รอ QC หลังอนุมัติ</p>
                <?= Html::beginForm(['request-dry-count'], 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <div class="col-12 col-sm-6"><label class="form-label">ชนิดผ้า</label><?= $itemField() ?></div>
                    <div class="col-12 col-sm-6"><label class="form-label">รอบอบที่เสร็จ</label><?= Html::dropDownList('dry_batch_id', null, $batchOptions, ['prompt' => 'เลือกรอบอบ', 'class' => 'form-select', 'required' => true]) ?></div>
                    <div class="col-12 col-sm-3"><label class="form-label">ชิ้น</label><?= $qtyField() ?></div>
                    <div class="col-12 col-sm-9"><label class="form-label">หลักฐานการนับ / เลขถุง</label><?= Html::textInput('evidence', '', ['class' => 'form-control', 'minlength' => 5, 'maxlength' => 255, 'required' => true]) ?></div>
                    <div class="col-12"><?= Html::submitButton('ส่งผลนับเพื่ออนุมัติ', ['class' => 'btn btn-outline-primary rounded-3']) ?></div>
                <?= Html::endForm() ?>
            </div></div></div>
            <div class="col-12 col-xl-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                <h5 class="fw-semibold">บันทึกการรีดหลังอบ</h5>
                <p class="small text-muted">บันทึกเป็นชิ้นตามรอบอบและชนิดผ้าที่อนุมัติผลนับแล้ว รีดได้หลายครั้งแต่รวมไม่เกินผลนับ</p>
                <?= Html::beginForm(['record-ironing'], 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <div class="col-12 col-sm-6"><label class="form-label">ชนิดผ้า</label><?= $itemField() ?></div>
                    <div class="col-12 col-sm-6"><label class="form-label">รอบอบที่เสร็จ</label><?= Html::dropDownList('dry_batch_id', null, $batchOptions, ['prompt' => 'เลือกรอบอบ', 'class' => 'form-select', 'required' => true]) ?></div>
                    <div class="col-12 col-sm-4"><label class="form-label">เริ่มรีด</label><?= Html::input('datetime-local', 'started_at', '', ['class' => 'form-control', 'required' => true]) ?></div>
                    <div class="col-12 col-sm-4"><label class="form-label">รีดเสร็จ</label><?= Html::input('datetime-local', 'ended_at', '', ['class' => 'form-control', 'required' => true]) ?></div>
                    <div class="col-12 col-sm-4"><label class="form-label">จำนวน (ชิ้น)</label><?= $qtyField() ?></div>
                    <div class="col-12"><label class="form-label">หลักฐาน / ผู้ปฏิบัติงาน</label><?= Html::textInput('evidence', '', ['class' => 'form-control', 'minlength' => 5, 'maxlength' => 255, 'required' => true]) ?></div>
                    <div class="col-12"><?= Html::submitButton('บันทึกการรีด', ['class' => 'btn btn-outline-primary rounded-3']) ?></div>
                <?= Html::endForm() ?>
            </div></div></div>
            <div class="col-12 col-xl-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                <h5 class="fw-semibold">ตรวจคุณภาพผ้าหลังอบ</h5>
                <p class="small text-muted">ทำได้หลังอนุมัติผลนับและบันทึกการรีด จำนวน QC รวมต้องไม่เกินจำนวนที่รีดและผลนับ</p>
                <?= Html::beginForm(['qc'], 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <div class="col-12 col-sm-6"><label class="form-label">ชนิดผ้า</label><?= $itemField() ?></div>
                    <div class="col-12 col-sm-6"><label class="form-label">รอบอบที่เสร็จ</label><?= Html::dropDownList('dry_batch_id', null, $batchOptions, ['prompt' => 'เลือกรอบอบ', 'class' => 'form-select', 'required' => true]) ?></div>
                    <div class="col-6 col-sm-4"><label class="form-label">ผลตรวจ</label><?= Html::dropDownList('destination', 'CLEAN', ['CLEAN' => 'พร้อมจ่าย', 'REWORK' => 'ซักซ้ำ', 'REPAIR' => 'ซ่อมผ้า', 'DISPOSAL_PENDING' => 'รอตัดจำหน่าย'], ['class' => 'form-select']) ?></div>
                    <div class="col-6 col-sm-3"><label class="form-label">ชิ้น</label><?= $qtyField() ?></div>
                    <div class="col-12 col-sm-5"><?= Html::submitButton('บันทึกผลตรวจ', ['class' => 'btn btn-primary rounded-3 w-100']) ?></div>
                <?= Html::endForm() ?>
            </div></div></div>
            <div class="col-12 col-xl-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                <h5 class="fw-semibold">ยอดเป้าหมายประจำหน่วยงาน</h5>
                <?= Html::beginForm(['set-par'], 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <div class="col-12 col-sm-6"><label class="form-label">หน่วยงาน</label><?= $departmentField() ?></div>
                    <div class="col-12 col-sm-6"><label class="form-label">ชนิดผ้า</label><?= $itemField() ?></div>
                    <div class="col-6 col-sm-4"><label class="form-label">เป้าหมาย (ชิ้น)</label><?= Html::input('number', 'target_qty', '', ['class' => 'form-control', 'min' => 0, 'required' => true]) ?></div>
                    <div class="col-6 col-sm-4"><label class="form-label">ขั้นต่ำ (ชิ้น)</label><?= Html::input('number', 'min_qty', 0, ['class' => 'form-control', 'min' => 0, 'required' => true]) ?></div>
                    <div class="col-12 col-sm-4"><?= Html::submitButton('บันทึกเป้าหมาย', ['class' => 'btn btn-primary rounded-3 w-100']) ?></div>
                <?= Html::endForm() ?>
            </div></div></div>
            <div class="col-12 col-xl-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                <h5 class="fw-semibold">แจ้งผ้าสูญเสีย/ทำลาย</h5>
                <p class="small text-muted">ยังไม่หักยอดจนกว่าผู้มีสิทธิ์อนุมัติ</p>
                <?= Html::beginForm(['request-loss'], 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <div class="col-12 col-sm-5"><label class="form-label">ชนิดผ้า</label><?= $itemField() ?></div>
                    <div class="col-12 col-sm-5"><label class="form-label">หน่วยงาน</label><?= $departmentField() ?></div>
                    <div class="col-12 col-sm-2"><label class="form-label">ชิ้น</label><?= $qtyField() ?></div>
                    <div class="col-12"><label class="form-label">เหตุ (เช่น refer / เสียชีวิต / ทำลาย)</label><?= Html::textInput('reason', '', ['class' => 'form-control', 'minlength' => 5, 'required' => true]) ?></div>
                    <div class="col-12"><?= Html::submitButton('ส่งขออนุมัติ', ['class' => 'btn btn-outline-danger rounded-3']) ?></div>
                <?= Html::endForm() ?>
            </div></div></div>
            <?php if ($canApprove): ?>
                <div class="col-12 col-xl-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                    <h5 class="fw-semibold">ตั้งยอดเริ่มต้น</h5>
                    <?= Html::beginForm(['opening'], 'post', ['class' => 'row g-2 align-items-end']) ?>
                        <div class="col-12 col-sm-6"><label class="form-label">ชนิดผ้า</label><?= $itemField() ?></div>
                        <div class="col-6 col-sm-3"><label class="form-label">ตำแหน่ง</label><?= Html::dropDownList('location', 'CLEAN', ['CLEAN' => 'คลังสะอาด', 'WARD' => 'หน่วยงาน'], ['class' => 'form-select']) ?></div>
                        <div class="col-6 col-sm-3"><label class="form-label">ชิ้น</label><?= $qtyField() ?></div>
                        <div class="col-12 col-sm-6"><label class="form-label">หน่วยงาน (ถ้าเลือกหน่วยงาน)</label><?= Html::dropDownList('department_id', null, $departments, ['prompt' => 'เลือกหน่วยงาน', 'class' => 'form-select']) ?></div>
                        <div class="col-12 col-sm-6"><label class="form-label">หลักฐาน/เหตุยอดตั้งต้น</label><?= Html::textInput('reason', '', ['class' => 'form-control', 'minlength' => 5, 'required' => true]) ?></div>
                        <div class="col-12"><?= Html::submitButton('บันทึกยอดตั้งต้น', ['class' => 'btn btn-outline-primary rounded-3']) ?></div>
                    <?= Html::endForm() ?>
                </div></div></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <section class="mb-3" aria-labelledby="qc-followup-heading">
        <div class="d-flex flex-column flex-sm-row justify-content-between gap-1 mb-2">
            <h5 id="qc-followup-heading" class="fw-semibold mb-0">ผ้าไม่ผ่าน QC ที่รอดำเนินการ</h5>
            <span class="text-body-secondary small">ทุกขั้นตอนบันทึกเป็นจำนวนชิ้น ไม่แปลงจากกิโลกรัม</span>
        </div>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">ชนิดผ้า</th><th class="text-end">รอซักซ้ำ</th><th class="text-end">รอซ่อม</th><th class="text-end pe-4">รอตัดจำหน่าย</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <?php if ($item['rework_qty'] || $item['repair_qty'] || $item['disposal_pending_qty']): ?>
                    <tr><td class="ps-4 fw-semibold"><?= Html::encode($item['item_code'] . ' · ' . $item['item_name']) ?></td><td class="text-end"><?= number_format($item['rework_qty']) ?></td><td class="text-end"><?= number_format($item['repair_qty']) ?></td><td class="text-end pe-4"><?= number_format($item['disposal_pending_qty']) ?></td></tr>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!$reworkOptions && !$repairOptions && !$disposalOptions): ?><tr><td colspan="4" class="text-center text-body-secondary py-4">ไม่มีผ้าที่ค้างหลัง QC</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
        <?php if ($canManage || $canApprove): ?>
            <div class="row g-3">
                <?php if ($canManage): ?>
                    <div class="col-12 col-xl-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                        <h6 class="fw-semibold">ส่งเข้ารอซักซ้ำ</h6>
                        <p class="text-body-secondary small">ย้ายจากผ้าไม่ผ่าน QC กลับเข้าคลังผ้าเปื้อน ยังไม่ถือว่าซักเสร็จ</p>
                        <?= Html::beginForm(['return-for-rewash'], 'post', ['class' => 'd-grid gap-2']) ?>
                            <?= Html::dropDownList('item_id', null, $reworkOptions, ['prompt' => 'เลือกชนิดผ้า', 'class' => 'form-select', 'required' => true, 'aria-label' => 'ชนิดผ้าที่ส่งซักซ้ำ']) ?>
                            <?= Html::input('number', 'qty', '', ['class' => 'form-control', 'min' => 1, 'step' => 1, 'required' => true, 'placeholder' => 'จำนวนชิ้น', 'aria-label' => 'จำนวนชิ้นที่ส่งซักซ้ำ']) ?>
                            <?= Html::textInput('evidence', '', ['class' => 'form-control', 'minlength' => 5, 'maxlength' => 255, 'required' => true, 'placeholder' => 'หลักฐาน / เลขถุง', 'aria-label' => 'หลักฐานการส่งซักซ้ำ']) ?>
                            <?= Html::submitButton('ย้ายเข้ารอซักซ้ำ', ['class' => 'btn btn-outline-primary', 'disabled' => !$reworkOptions]) ?>
                        <?= Html::endForm() ?>
                    </div></div></div>
                    <div class="col-12 col-xl-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                        <h6 class="fw-semibold">ขอตัดจำหน่าย</h6>
                        <p class="text-body-secondary small">ส่งคำขอพร้อมเหตุ ผ้ายังไม่ถูกตัดยอดจนผู้มีสิทธิ์อนุมัติ</p>
                        <?= Html::beginForm(['request-qc-disposal'], 'post', ['class' => 'd-grid gap-2']) ?>
                            <?= Html::dropDownList('item_id', null, $disposalOptions, ['prompt' => 'เลือกชนิดผ้า', 'class' => 'form-select', 'required' => true, 'aria-label' => 'ชนิดผ้าที่ขอตัดจำหน่าย']) ?>
                            <?= Html::input('number', 'qty', '', ['class' => 'form-control', 'min' => 1, 'step' => 1, 'required' => true, 'placeholder' => 'จำนวนชิ้น', 'aria-label' => 'จำนวนชิ้นที่ขอตัดจำหน่าย']) ?>
                            <?= Html::textInput('reason', '', ['class' => 'form-control', 'minlength' => 5, 'maxlength' => 255, 'required' => true, 'placeholder' => 'เหตุและหลักฐาน', 'aria-label' => 'เหตุและหลักฐานการตัดจำหน่าย']) ?>
                            <?= Html::submitButton('ส่งขออนุมัติตัด', ['class' => 'btn btn-outline-danger', 'disabled' => !$disposalOptions]) ?>
                        <?= Html::endForm() ?>
                    </div></div></div>
                <?php endif; ?>
                <?php if ($canApprove): ?>
                    <div class="col-12 col-xl-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                        <h6 class="fw-semibold">รับผ้าซ่อมเสร็จ</h6>
                        <p class="text-body-secondary small">ผู้มีสิทธิ์ตรวจหลักฐานซ่อมและย้ายผ้ากลับคลังสะอาด</p>
                        <?= Html::beginForm(['complete-repair'], 'post', ['class' => 'd-grid gap-2']) ?>
                            <?= Html::dropDownList('item_id', null, $repairOptions, ['prompt' => 'เลือกชนิดผ้า', 'class' => 'form-select', 'required' => true, 'aria-label' => 'ชนิดผ้าที่ซ่อมเสร็จ']) ?>
                            <?= Html::input('number', 'qty', '', ['class' => 'form-control', 'min' => 1, 'step' => 1, 'required' => true, 'placeholder' => 'จำนวนชิ้น', 'aria-label' => 'จำนวนชิ้นที่ซ่อมเสร็จ']) ?>
                            <?= Html::textInput('evidence', '', ['class' => 'form-control', 'minlength' => 5, 'maxlength' => 255, 'required' => true, 'placeholder' => 'หลักฐานซ่อม / ผลตรวจ', 'aria-label' => 'หลักฐานการซ่อมและตรวจผ้า']) ?>
                            <?= Html::submitButton('ยืนยันคืนคลังสะอาด', ['class' => 'btn btn-outline-success', 'disabled' => !$repairOptions]) ?>
                        <?= Html::endForm() ?>
                    </div></div></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">ผลนับชิ้นหลังอบล่าสุด</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="ps-4">รอบอบ</th><th>ชนิดผ้า</th><th class="text-end">นับได้</th><th>หลักฐาน</th><th class="pe-4">สถานะ</th></tr></thead><tbody>
            <?php foreach ($dryCounts as $count): ?><tr><td class="ps-4 fw-semibold"><?= Html::encode($count['batch_no']) ?></td><td><?= Html::encode($count['item_name']) ?></td><td class="text-end"><?= number_format($count['qty']) ?></td><td><?= Html::encode($count['evidence']) ?></td><td class="pe-4">
                <?php if ($count['status'] === 'PENDING' && $canApprove && (int) $count['created_by'] !== (int) Yii::$app->user->id): ?>
                    <?= Html::beginForm(['approve-dry-count', 'id' => $count['id']], 'post') ?><?= Html::submitButton('อนุมัติผลนับ', ['class' => 'btn btn-sm btn-outline-success', 'data-confirm' => 'ยืนยันจำนวนชิ้นและหลักฐานของรอบอบนี้?']) ?><?= Html::endForm() ?>
                <?php else: ?><span class="badge <?= $count['status'] === 'APPROVED' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= Html::encode($count['status']) ?></span><?php endif; ?>
            </td></tr><?php endforeach; ?>
            <?php if (!$dryCounts): ?><tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีผลนับชิ้นหลังอบ</td></tr><?php endif; ?>
        </tbody></table></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">ล็อตผ้าสะอาดล่าสุด</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="ps-4">เลขล็อต</th><th>ชนิดผ้า</th><th>ที่มา</th><th class="text-end pe-4">คงเหลือ (ชิ้น)</th></tr></thead><tbody>
            <?php foreach ($lots as $lot): ?><tr><td class="ps-4 fw-semibold"><?= Html::encode($lot['lot_no']) ?></td><td><?= Html::encode($lot['item_code'] . ' · ' . $lot['item_name']) ?></td><td><?= Html::encode($lot['batch_no'] ?: $lot['source_type']) ?></td><td class="text-end pe-4"><?= number_format($lot['available_qty']) ?></td></tr><?php endforeach; ?>
            <?php if (!$lots): ?><tr><td colspan="4" class="text-center text-muted py-4">ยังไม่มีล็อตผ้าสะอาด</td></tr><?php endif; ?>
        </tbody></table></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">บันทึกการรีดล่าสุด</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="ps-4">รอบอบ</th><th>ชนิดผ้า</th><th>เวลาเริ่ม–สิ้นสุด</th><th>หลักฐาน</th><th class="text-end pe-4">รีดแล้ว (ชิ้น)</th></tr></thead><tbody>
            <?php foreach ($ironing as $row): ?><tr><td class="ps-4 fw-semibold"><?= Html::encode($row['batch_no']) ?></td><td><?= Html::encode($row['item_name']) ?></td><td><?= Html::encode($row['started_at'] . ' – ' . $row['ended_at']) ?></td><td><?= Html::encode($row['evidence']) ?></td><td class="text-end pe-4"><?= number_format($row['qty']) ?></td></tr><?php endforeach; ?>
            <?php if (!$ironing): ?><tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีบันทึกการรีด</td></tr><?php endif; ?>
        </tbody></table></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">ยอดเป้าหมาย</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="ps-4">หน่วยงาน</th><th>ชนิดผ้า</th><th class="text-end">เป้าหมาย</th><th class="text-end pe-4">ขั้นต่ำ</th></tr></thead><tbody>
            <?php foreach ($par as $row): ?><tr><td class="ps-4"><?= Html::encode($row['department_name'] ?: '#' . $row['department_id']) ?></td><td><?= Html::encode($row['item_name']) ?></td><td class="text-end"><?= number_format($row['target_qty']) ?></td><td class="text-end pe-4"><?= number_format($row['min_qty']) ?></td></tr><?php endforeach; ?>
            <?php if (!$par): ?><tr><td colspan="4" class="text-center text-muted py-4">ยังไม่กำหนดยอดเป้าหมาย</td></tr><?php endif; ?>
        </tbody></table></div></div>
    </div>
    <div class="card border-0 shadow-sm rounded-4"><div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">รายการเคลื่อนไหวล่าสุด</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="ps-4">เอกสาร / เวลา</th><th>ชนิดผ้า</th><th>รายการ</th><th class="text-end">ชิ้น</th><th>สถานะ</th><th class="pe-4">จัดการ</th></tr></thead><tbody>
            <?php foreach ($events as $event): ?><tr>
                <td class="ps-4 fw-semibold"><?= Html::encode($event['event_no']) ?><div class="small text-muted"><?= Html::encode($event['occurred_at']) ?></div></td>
                <td><?= Html::encode($event['item_name']) ?></td><td><?= Html::encode($event['event_type']) ?><div class="small text-muted"><?= Html::encode($event['reason'] ?: '') ?></div></td>
                <td class="text-end"><?= number_format($event['qty']) ?></td><td><span class="badge <?= $event['status'] === 'CONFIRMED' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= Html::encode($event['status']) ?></span></td>
                <td class="pe-4"><?php if ($event['event_type'] === 'LOSS' && $event['status'] === 'PENDING' && $canApprove && (int) $event['created_by'] !== (int) Yii::$app->user->id): ?>
                    <?= Html::beginForm(['approve-loss', 'id' => $event['id']], 'post') ?><?= Html::submitButton('อนุมัติ', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'ยืนยันการตัดผ้าจากยอดหน่วยงาน?']) ?><?= Html::endForm() ?>
                <?php elseif ($event['event_type'] === 'QC_DISPOSAL' && $event['status'] === 'PENDING' && $canApprove && (int) $event['created_by'] !== (int) Yii::$app->user->id): ?>
                    <?= Html::beginForm(['approve-qc-disposal', 'id' => $event['id']], 'post') ?><?= Html::submitButton('อนุมัติตัด', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'ยืนยันตัดผ้าที่ QC ไม่ผ่านออกจากบัญชี?']) ?><?= Html::endForm() ?>
                <?php endif; ?></td>
            </tr><?php endforeach; ?>
            <?php if (!$events): ?><tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีรายการเคลื่อนไหว</td></tr><?php endif; ?>
        </tbody></table></div></div>
    </div>
</div>
