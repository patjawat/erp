<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ตรวจไฟล์ยอดปิดเดือน';
$this->params['breadcrumbs'][] = ['label' => 'สรุปรายงานวัสดุคงคลัง', 'url' => ['/inventory-v2/report/material-summary']];
$this->params['breadcrumbs'][] = $this->title;
$money = static fn($v) => number_format((float) $v, 2);
$quantity = static fn($v) => rtrim(rtrim(number_format((float) $v, 6), '0'), '.');
$months = [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'report-monthly-snapshot']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div><h1 class="h4 mb-2"><?= Html::encode($this->title) ?></h1>
            <p class="text-body-secondary mb-0">เทียบยอดยกไปจาก Excel รวมทุกคลังกับข้อมูลปิดเดือนในระบบ ก่อนเตรียมคืนยอด</p></div>
        <?= Html::a('กลับรายงาน', ['/inventory-v2/report/material-summary', 'year' => $year, 'month' => $month], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <div class="alert alert-info">“ตรวจและเทียบยอด” อ่านข้อมูลเท่านั้น หากต้องการคืนยอด ให้เลือก “เตรียมคืนยอดจากไฟล์” แล้วตรวจการจับคู่และตัวอย่างก่อนยืนยัน</div>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= Html::encode($error) ?></div><?php endif; ?>
    <div class="card mb-4"><div class="card-body">
        <?= Html::beginForm(Url::to(['index']), 'post', ['enctype' => 'multipart/form-data', 'class' => 'row g-3 align-items-end']) ?>
            <div class="col-12 col-lg-6"><label for="snapshot-file" class="form-label">Excel ยอดปิดเดือนที่รับรองแล้ว</label>
                <input id="snapshot-file" type="file" name="workbook" accept=".xlsx" required class="form-control" aria-describedby="snapshot-file-help">
                <div id="snapshot-file-help" class="form-text">ไม่เกิน 10 MB ต้องมีชีต สรุปวัสดุคงคลัง และ สรุปรายการ</div></div>
            <div class="col-6 col-lg-2"><label for="snapshot-month" class="form-label">เดือน</label>
                <?= Html::dropDownList('month', $month, $months, ['id' => 'snapshot-month', 'class' => 'form-select']) ?></div>
            <div class="col-6 col-lg-2"><label for="snapshot-year" class="form-label">ปี พ.ศ.</label>
                <?= Html::dropDownList('year', $year, array_combine(range(2000, 2100), range(2543, 2643)), ['id' => 'snapshot-year', 'class' => 'form-select']) ?></div>
            <div class="col-12 col-lg-2 d-grid gap-2"><button type="submit" class="btn btn-primary">ตรวจและเทียบยอด</button>
                <?php if (\app\modules\inventoryV2\services\MonthlySnapshotRestoreService::ready()): ?>
                    <button type="submit" name="prepare" value="1" class="btn btn-outline-primary">เตรียมคืนยอดจากไฟล์</button>
                <?php endif; ?>
            </div>
        <?= Html::endForm() ?>
    </div></div>
    <?php if (\app\modules\inventoryV2\services\MonthlySnapshotRestoreService::ready()): ?>
        <?php $batches=(new \yii\db\Query())->select(['id','report_year','report_month','status','created_at'])->from('stock_monthly_restore')->orderBy(['id'=>SORT_DESC])->limit(20)->all(); ?>
        <?php if ($batches): ?><details class="mb-4" open><summary class="h5">ชุดคืนยอดล่าสุด</summary><ul class="mb-0">
            <?php foreach ($batches as $b): ?><li><?= Html::a('ชุดที่ '.$b['id'].' · '.$months[$b['report_month']].' '.($b['report_year']+543).' · '.(['draft'=>'กำลังเตรียม','committed'=>'คืนยอดและล็อกแล้ว','reverted'=>'ย้อนคืนแล้ว'][$b['status']] ?? $b['status']),['draft','id'=>$b['id']]) ?></li><?php endforeach; ?>
        </ul></details><?php endif; ?>
    <?php endif; ?>
    <?php if ($result): ?>
        <h2 class="h5">ผลตรวจ <?= Html::encode($months[$month] . ' ' . ($year + 543)) ?></h2>
        <p class="text-body-secondary"><?= Html::encode($filename) ?> · ตรวจเมื่อ <?= Html::encode($result['checked_at']) ?> · <?= count($result['rows']) ?> แถว</p>
        <div class="table-responsive mb-4"><table class="table">
            <thead><tr><th scope="col">รายการเปรียบเทียบ</th><th scope="col" class="text-end">มูลค่า (บาท)</th></tr></thead>
            <tbody><tr><th scope="row">ยอดยกไปใน Excel</th><td class="text-end"><?= $money($result['source_total']) ?></td></tr>
            <tr><th scope="row">ยอดยกไปในระบบ</th><td class="text-end"><?= $money($result['current_total']) ?></td></tr>
            <tr><th scope="row">ผลต่าง (ระบบ − Excel)</th><td class="text-end fw-semibold"><?= $money($result['delta']) ?></td></tr></tbody>
        </table></div>
        <?php foreach ($result['errors'] as $message): ?><div class="alert alert-danger"><?= Html::encode($message) ?></div><?php endforeach; ?>
        <?php if ($result['later_periods']): ?>
            <div class="alert alert-warning"><strong>มีข้อมูลปิดเดือนหลังงวดนี้แล้ว</strong>
                <p class="mb-2">ต้องพิจารณาข้อมูลเหล่านี้ก่อนคืนยอด เพื่อไม่ให้ยอดยกมาเดือนถัดไปค้างเป็นชุดเดิม</p>
                <ul class="mb-0"><?php foreach ($result['later_periods'] as $p): ?><li><?= Html::encode(($result['warehouses'][$p['warehouse_id']] ?? $p['warehouse_id']) . ' · ' . $months[$p['report_month']] . ' ' . ($p['report_year'] + 543)) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
        <h2 class="h5 mt-4">รายการที่ต้องตรวจเพิ่มเติม (<?= count($result['unresolved']) ?> แถว)</h2>
        <p class="text-body-secondary">ชื่อคลังเป็นข้อมูลประกอบจากการตั้งค่าและยอดปิดปัจจุบัน ยังไม่ได้ยืนยันการจับคู่ รหัสซ้ำแสดงแยกตามแถวต้นฉบับ</p>
        <?php if (!$result['unresolved']): ?><p>ไม่พบข้อขัดแย้งเบื้องต้น ยังต้องรับรองการจับคู่และตรวจข้อมูลจ่ายแยกปลายทางก่อนคืนยอด</p><?php endif; ?>
        <div class="table-responsive"><table class="table table-hover align-middle">
            <thead><tr><th scope="col">แถว / วัสดุ</th><th scope="col">ประเด็นที่ต้องยืนยัน</th><th scope="col">คลังที่พบหลักฐาน</th><th scope="col" class="text-end">จำนวนในไฟล์ / ตามสูตร</th><th scope="col" class="text-end">มูลค่าในไฟล์</th></tr></thead>
            <tbody><?php foreach ($result['unresolved'] as $r): ?><tr>
                <td><span class="text-body-secondary">แถว <?= (int) $r['row'] ?></span> · <?= Html::encode($r['item_code']) ?><div><?= Html::encode($r['item_name']) ?></div></td>
                <td><?= Html::encode(implode(' / ', $r['issues'])) ?></td>
                <td><?= Html::encode(implode(' / ', $r['candidates']) ?: 'ไม่พบคลัง ต้องตรวจเอกสารประกอบ') ?></td>
                <td class="text-end text-nowrap"><?= $quantity($r['closing_qty']) ?> / <?= $quantity($r['closing_qty'] + $r['qty_equation_delta']) ?><div class="small text-body-secondary"><?= Html::encode($r['unit']) ?></div></td>
                <td class="text-end text-nowrap"><?= $money($r['closing_value']) ?></td>
            </tr><?php endforeach; ?></tbody>
        </table></div>
        <details class="mt-4" open><summary class="h5">ผลต่างรายรหัส รวมทุกคลัง (<?= count($result['changes']) ?> รหัส)</summary>
            <p class="text-body-secondary">ใช้เทียบยอดรวมเท่านั้น ไม่ใช้ยอดรวมนี้แทนการจับคู่รายคลัง</p>
            <div class="table-responsive"><table class="table table-hover">
                <thead><tr><th scope="col">วัสดุ</th><th scope="col" class="text-end">จำนวน Excel</th><th scope="col" class="text-end">จำนวนระบบ</th><th scope="col" class="text-end">มูลค่า Excel</th><th scope="col" class="text-end">มูลค่าระบบ</th><th scope="col" class="text-end">ผลต่างบาท</th></tr></thead>
                <tbody><?php foreach ($result['changes'] as $r): ?><tr><td><?= Html::encode($r['item_code'] . ' · ' . $r['item_name']) ?><?= $r['snapshot_missing'] ? '<div class="text-warning-emphasis">ไม่มีข้อมูลปิดในระบบ</div>' : '' ?></td>
                    <td class="text-end"><?= $quantity($r['source_qty']) ?></td><td class="text-end"><?= $quantity($r['current_qty']) ?></td>
                    <td class="text-end"><?= $money($r['source_value']) ?></td><td class="text-end"><?= $money($r['current_value']) ?></td><td class="text-end"><?= $money($r['delta']) ?></td></tr><?php endforeach; ?></tbody>
            </table></div>
        </details>
        <?php if ($result['database_only']): ?><details class="mt-4"><summary class="h5">รหัสในระบบที่ไม่มีในไฟล์ (<?= count($result['database_only']) ?> รหัส)</summary>
            <p>ต้องตรวจสอบขอบเขตก่อนคืนยอด ห้ามลบทิ้งหรือถือเป็นศูนย์โดยอัตโนมัติ</p>
            <div class="table-responsive"><table class="table"><thead><tr><th scope="col">รหัส</th><th scope="col" class="text-end">จำนวนระบบ</th><th scope="col" class="text-end">มูลค่าระบบ</th></tr></thead><tbody>
                <?php foreach ($result['database_only'] as $r): ?><tr><td><?= Html::encode($r['item_code']) ?></td><td class="text-end"><?= $quantity($r['closing_qty']) ?></td><td class="text-end"><?= $money($r['closing_value']) ?></td></tr><?php endforeach; ?>
            </tbody></table></div></details><?php endif; ?>
        <p class="text-body-secondary mt-4">ผลตรวจนี้เป็นข้อมูล ณ เวลาที่ตรวจ เมื่อฐานข้อมูลหรือไฟล์เปลี่ยน ต้องตรวจใหม่ก่อนดำเนินการต่อ</p>
    <?php endif; ?>
</div>
