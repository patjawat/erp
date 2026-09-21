<?php

use yii\helpers\Html;

$this->title = 'รายงานรอบเครื่องซัก–อบ';
?>
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3">
        <div><h4 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h4><div class="text-muted">สรุปจากรอบที่เริ่มในปีที่เลือก แยกตามเลขครุภัณฑ์</div></div>
        <?= Html::a('กลับรอบเครื่อง', ['/laundry/processing/index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
    </div>
    <div class="alert alert-info">อัตราบรรทุก = น้ำหนักเข้าของรอบที่เสร็จ ÷ (กำลังเครื่อง × จำนวนรอบที่เสร็จ) ไม่ใช่อัตราเครื่องพร้อมใช้ ส่วน “รอบหยุด” และน้ำหนักที่ได้รับผลกระทบไม่ใช่ระยะเวลาหยุดซ่อม ซึ่งต้องอ่านจากงานครุภัณฑ์/ใบซ่อมแยกต่างหาก</div>
    <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-end gap-2 mb-3']) ?>
        <div><label class="form-label">ปี พ.ศ.</label><?= Html::input('number', 'year', $year, ['class' => 'form-control', 'min' => 2543, 'max' => 2743, 'required' => true]) ?></div>
        <?= Html::submitButton('แสดงรายงาน', ['class' => 'btn btn-primary']) ?>
    <?= Html::endForm() ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th class="ps-4">เครื่อง / เลขครุภัณฑ์</th><th class="text-end">เสร็จ</th><th class="text-end">หยุด</th><th class="text-end">กำลังทำ</th><th class="text-end">ผ้าเปื้อนเข้า</th><th class="text-end">ผ้าติดเชื้อเข้า</th><th class="text-end">น้ำหนักออก</th><th class="text-end">น้ำหนักรอบหยุด</th><th class="text-end">นาทีรอบเสร็จ</th><th class="text-end pe-4">อัตราบรรทุก</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): $s = $row['statistics']; ?><tr>
            <td class="ps-4 fw-semibold"><?= Html::encode($row['asset_code'] ?: '#' . $row['asset_id']) ?><div class="small text-muted"><?= Html::encode($row['asset_name']) ?> · <?= $row['machine_type'] === 'WASH' ? 'ซัก' : 'อบ' ?> (<?= number_format((float) $row['capacity_kg'], 3) ?> กก.)</div></td>
            <td class="text-end"><?= number_format($s['completed']) ?></td><td class="text-end"><?= number_format($s['aborted']) ?></td><td class="text-end"><?= number_format($s['running']) ?></td>
            <td class="text-end"><?= number_format($s['soiled_kg'], 3) ?></td><td class="text-end"><?= number_format($s['infectious_kg'], 3) ?></td><td class="text-end"><?= number_format($s['output_kg'], 3) ?></td>
            <td class="text-end"><?= number_format($s['aborted_input_kg'], 3) ?></td><td class="text-end"><?= number_format($s['completed_minutes']) ?></td><td class="text-end pe-4"><?= $s['load_percent'] === null ? '—' : number_format($s['load_percent'], 1) . '%' ?></td>
        </tr><?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="10" class="text-center text-muted py-4">ยังไม่มีเครื่องที่ลงทะเบียน</td></tr><?php endif; ?>
        </tbody>
    </table></div></div>
</div>
