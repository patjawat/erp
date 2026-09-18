<?php

use yii\helpers\Html;

$this->title = 'ช่องว่างผ้าสำหรับวางแผนจัดหา';
?>
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3">
        <div><h4 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h4><div class="text-muted">เทียบเป้าหมายรายหน่วยงานกับผลสอบยอดที่อนุมัติแล้ว</div></div>
        <?= Html::a('กลับบัญชีผ้า', ['/laundry/inventory/index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
    </div>
    <div class="alert alert-info">เป็นตัวเลขเบื้องต้นเพื่อวางแผนเท่านั้น ไม่ใช่ใบขอซื้อ: ยอดสอบนับเป็นภาพ ณ วันตัดยอดของแต่ละหน่วยงาน แต่ยอดคลังสะอาดเป็นยอดปัจจุบัน ยังไม่หักใบสั่งซื้อค้างรับ ยอดสำรองกลาง ผ้ารอซ่อม หรือความต้องการขยายบริการ ต้องตรวจทานก่อนเสนอพัสดุ</div>
    <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex gap-2 align-items-end mb-3']) ?>
        <div><label class="form-label">ปีสอบยอด (พ.ศ.)</label><?= Html::input('number', 'year', $year, ['class' => 'form-control', 'min' => 2543, 'max' => 2743, 'required' => true]) ?></div>
        <?= Html::submitButton('แสดงรายงาน', ['class' => 'btn btn-primary']) ?>
    <?= Html::endForm() ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th class="ps-4">ชนิดผ้า</th><th class="text-end">เป้าหมายรวม</th><th class="text-end">หน่วยงานสอบแล้ว</th><th class="text-end">ยังไม่สอบ</th><th class="text-end">ขาดที่หน่วยงาน</th><th class="text-end">คลังสะอาดปัจจุบัน</th><th class="text-end pe-4">ช่องว่างเบื้องต้น</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?><tr>
            <td class="ps-4 fw-semibold"><?= Html::encode($row['item_code'] . ' · ' . $row['item_name']) ?></td>
            <td class="text-end"><?= number_format($row['target_qty']) ?></td>
            <td class="text-end"><?= number_format($row['counted_departments']) ?></td>
            <td class="text-end"><?= number_format($row['missing_departments']) ?></td>
            <td class="text-end"><?= $row['missing_departments'] ? '—' : number_format($row['ward_deficit']) ?></td>
            <td class="text-end"><?= number_format($row['clean_qty']) ?></td>
            <td class="text-end pe-4"><?= $row['preliminary_gap'] === null ? '<span class="badge bg-warning text-dark">ข้อมูลไม่ครบ</span>' : number_format($row['preliminary_gap']) ?></td>
        </tr><?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-muted py-4">ยังไม่มีชนิดผ้า</td></tr><?php endif; ?>
        </tbody>
    </table></div></div>
</div>
