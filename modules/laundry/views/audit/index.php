<?php

use yii\helpers\Html;

$this->title = 'ตรวจสอบยอดผ้าหมุนเวียน';
$items = $report['items'];
$overdue = $report['overdue'];
$problemCount = 0;
foreach ($items as $item) {
    if ($item['audit']['difference_qty'] !== 0 || $item['audit']['negative_locations']) {
        $problemCount++;
    }
}
?>
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h4>
            <div class="text-body-secondary">ตรวจบัญชีชิ้นจากรายการที่ยืนยันแล้ว ไม่ใช่น้ำหนักผ้าส่งซัก</div>
        </div>
        <?= Html::a('กลับบัญชีผ้า', ['/laundry/inventory/index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3" aria-live="polite">
        <span class="badge <?= !$items ? 'text-bg-secondary' : ($problemCount ? 'text-bg-danger' : 'text-bg-success') ?>"><?= !$items ? 'ยังไม่มีทะเบียนผ้า' : ($problemCount ? 'พบยอดผิดปกติ ' . number_format($problemCount) . ' ชนิด' : 'ยอดบัญชีตรงกัน') ?></span>
        <?php if ($items): ?><span class="badge <?= $overdue ? 'text-bg-warning' : 'text-bg-secondary' ?>"><?= $overdue ? 'รอ QC เกิน ' . $report['overdue_hours'] . ' ชม. ' . number_format(count($overdue)) . ' รายการ' : 'ไม่มีรายการ QC ค้างเกินกำหนด' ?></span><?php endif; ?>
    </div>

    <section class="mb-4" aria-labelledby="ledger-heading">
        <h5 id="ledger-heading" class="fw-semibold mb-1">ยอดตามชนิดผ้า</h5>
        <p class="text-body-secondary small mb-3">ยอดที่ควรมี = รับใหม่และยอดตั้งต้น − ยอดตัดออก; เทียบกับยอดทุกตำแหน่งที่ยังหมุนเวียน</p>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">ชนิดผ้า</th><th class="text-end">ควรมี</th><th class="text-end">นับจากบัญชี</th><th class="text-end">ผลต่าง</th><th class="text-end">อยู่หน่วยงาน</th><th class="text-end">รอ QC</th><th class="pe-4">ผลตรวจ</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): $a = $item['audit']; $bad = $a['difference_qty'] !== 0 || $a['negative_locations']; ?>
                <tr>
                    <td class="ps-4 fw-semibold"><?= Html::encode($item['item_code'] . ' · ' . $item['item_name']) ?></td>
                    <td class="text-end"><?= number_format($a['expected_qty']) ?></td><td class="text-end"><?= number_format($a['actual_qty']) ?></td>
                    <td class="text-end <?= $a['difference_qty'] !== 0 ? 'text-danger fw-semibold' : '' ?>"><?= number_format($a['difference_qty']) ?></td>
                    <td class="text-end"><?= number_format($a['ward_qty']) ?></td><td class="text-end"><?= number_format($a['qc_hold_qty']) ?></td>
                    <td class="pe-4">
                        <?php if ($bad): ?>
                            <span class="badge text-bg-danger">ต้องตรวจสอบ</span>
                            <?php foreach ($a['negative_locations'] as $negative): ?>
                                <div class="small text-danger mt-1">ยอดติดลบ <?= Html::encode($negative['location']) ?><?= $negative['department_id'] ? ' · หน่วยงาน #' . (int) $negative['department_id'] : '' ?>: <?= number_format($negative['qty']) ?> ชิ้น</div>
                            <?php endforeach; ?>
                        <?php else: ?><span class="badge text-bg-success">ตรงกัน</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$items): ?><tr><td colspan="7" class="text-center text-body-secondary py-4">ยังไม่มีทะเบียนชนิดผ้า</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </section>

    <section class="mb-4" aria-labelledby="warehouse-heading">
        <h5 id="warehouse-heading" class="fw-semibold mb-1">เทียบคลังย่อยกับผ้าหมุนเวียน</h5>
        <p class="text-body-secondary small mb-3">อ่านยอดสองบัญชี ณ เวลาปัจจุบันเท่านั้น การจ่ายผ้าให้หน่วยงานและการนำกลับมาซักไม่ตัดยอดคลังย่อย ส่วนผ้าหายหรือชำรุดต้องตัดในงานคลังตามเอกสารปกติแยกจากการบันทึกในซักฟอก</p>
        <div class="alert alert-warning">ระบบคลังเดิมใช้ยอดคลังย่อยเป็นยอดที่จ่ายต่อได้ด้วย หากผ้าที่อยู่ระหว่างหมุนเวียนยังคงอยู่ในยอดนี้ ต้องกำหนดวิธีป้องกันการเบิกจ่ายผ้าชุดเดิมซ้ำก่อนใช้งานจริง</div>
        <?php if (!$report['warehouse_id']): ?>
            <div class="alert alert-warning mb-0">ยังไม่ได้กำหนดคลังย่อยซักฟอก จึงยังเทียบยอดสองบัญชีไม่ได้</div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th class="ps-4">ชนิดผ้า / รหัสพัสดุ</th><th class="text-end">ยอดคลังย่อย</th><th class="text-end">ยอดผ้าหมุนเวียน</th><th class="text-end pe-4">ส่วนต่างที่ต้องตรวจ</th></tr></thead>
                <tbody>
                <?php foreach ($report['warehouse_comparison'] as $row): ?><tr>
                    <td class="ps-4 fw-semibold"><?= Html::encode($row['item_code'] . ' · ' . $row['item_name']) ?><div class="small text-body-secondary"><?= Html::encode($row['stock_item_code']) ?></div></td>
                    <td class="text-end"><?= number_format($row['warehouse_qty'], 3) ?></td>
                    <td class="text-end"><?= number_format($row['circulating_qty']) ?></td>
                    <td class="text-end pe-4 <?= abs($row['difference_qty']) > 0.0001 ? 'fw-semibold text-warning-emphasis' : '' ?>"><?= number_format($row['difference_qty'], 3) ?></td>
                </tr><?php endforeach; ?>
                <?php if (!$report['warehouse_comparison']): ?><tr><td colspan="4" class="text-center text-body-secondary py-4">ยังไม่มีชนิดผ้าที่ผูกรหัสพัสดุสำหรับเทียบยอด</td></tr><?php endif; ?>
                </tbody>
            </table></div></div>
            <p class="text-body-secondary small mt-2 mb-0">ส่วนต่างไม่ใช่ยอดสูญหายโดยอัตโนมัติ อาจเกิดจากยอดตั้งต้น วันที่บันทึกต่างกัน หรือเอกสารตัดคลังที่ยังไม่ครบ ต้องตรวจหลักฐานก่อนปรับยอด</p>
        <?php endif; ?>
    </section>

    <section aria-labelledby="qc-heading">
        <h5 id="qc-heading" class="fw-semibold mb-1">รายการนับชิ้นที่รอ QC เกิน <?= (int) $report['overdue_hours'] ?> ชั่วโมง</h5>
        <p class="text-body-secondary small mb-3">แสดงเฉพาะผลนับที่อนุมัติแล้วแต่ยัง QC ไม่ครบจำนวน</p>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">รอบอบ</th><th>ชนิดผ้า</th><th>อนุมัติเมื่อ</th><th class="text-end">นับได้</th><th class="text-end pe-4">ยังรอ QC</th></tr></thead>
            <tbody>
            <?php foreach ($overdue as $row): ?><tr><td class="ps-4 fw-semibold"><?= Html::encode($row['batch_no']) ?></td><td><?= Html::encode($row['item_name']) ?></td><td><?= Html::encode($row['approved_at']) ?></td><td class="text-end"><?= number_format($row['qty']) ?></td><td class="text-end pe-4 fw-semibold"><?= number_format($row['remaining_qty']) ?></td></tr><?php endforeach; ?>
            <?php if (!$overdue): ?><tr><td colspan="5" class="text-center text-body-secondary py-4">ไม่มีรายการค้างเกินกำหนด</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </section>
</div>
