<?php

use yii\helpers\Html;

$this->title = 'รายงานผ้าส่งซักรายหน่วยงาน';
$soiled = $infectious = 0;
foreach ($rows as $row) {
    $soiled += (float) $row['soiled_kg'];
    $infectious += (float) $row['infectious_kg'];
}
?>
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3">
        <div><h4 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h4><div class="text-muted">น้ำหนักสุทธิที่ชั่ง ณ โรงซัก ตามวันที่ออกเก็บ (ปีปฏิทิน)</div></div>
        <?= Html::a('กลับรอบเก็บ', ['index'], ['class' => 'btn btn-outline-secondary rounded-3 align-self-start']) ?>
    </div>
    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body">
        <?= Html::beginForm(['report'], 'get', ['class' => 'row g-3 align-items-end']) ?>
            <div class="col-12 col-sm-3"><label class="form-label">ปี พ.ศ.</label><?= Html::input('number', 'year', $year, ['class' => 'form-control', 'min' => 2543, 'max' => 2743]) ?></div>
            <div class="col-12 col-sm-6"><label class="form-label">หน่วยงาน</label><?= Html::dropDownList('department_id', $departmentId ?: '', $departments, ['prompt' => 'ทุกหน่วยงาน', 'class' => 'form-select']) ?></div>
            <div class="col-12 col-sm-3"><?= Html::submitButton('แสดงรายงาน', ['class' => 'btn btn-primary rounded-3 w-100']) ?></div>
        <?= Html::endForm() ?>
    </div></div>
    <div class="row g-3 mb-3">
        <?php foreach (['ผ้าเปื้อน' => $soiled, 'ผ้าติดเชื้อ' => $infectious, 'รวม' => $soiled + $infectious] as $label => $kg): ?>
            <div class="col-12 col-sm-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body py-3"><div class="fw-bold fs-3"><?= number_format($kg, 3) ?> <span class="fs-6">กก.</span></div><div class="text-primary small fw-semibold mt-2"><?= Html::encode($label) ?></div></div></div></div>
        <?php endforeach; ?>
    </div>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden"><div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">สรุปรายเดือน ปี <?= Html::encode($year) ?></h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">เดือน</th><th>หน่วยงาน</th><th class="text-end">รอบเก็บ</th><th class="text-end">ผ้าเปื้อน (กก.)</th><th class="text-end">ผ้าติดเชื้อ (กก.)</th><th class="text-end pe-4">รวม (กก.)</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr><td class="ps-4"><?= Html::encode($row['month']) ?></td><td><?= Html::encode($row['department_name'] ?: '#' . $row['department_id']) ?></td><td class="text-end"><?= (int) $row['round_count'] ?></td><td class="text-end"><?= number_format((float) $row['soiled_kg'], 3) ?></td><td class="text-end"><?= number_format((float) $row['infectious_kg'], 3) ?></td><td class="text-end pe-4 fw-semibold"><?= number_format((float) $row['total_kg'], 3) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">ไม่มีข้อมูลในช่วงที่เลือก</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>
</div>
