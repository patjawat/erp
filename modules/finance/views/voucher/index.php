<?php

use yii\helpers\Html;

$this->title = 'ทะเบียนฎีกา';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$rows = [
    ['no' => 'ฎก.2569/0018', 'date' => '15 ส.ค. 2569', 'payee' => 'บริษัท ตัวอย่างเวชภัณฑ์ จำกัด', 'source' => 'เงินบำรุง', 'amount' => 175000, 'status' => 'รอตรวจสอบ', 'class' => 'bg-warning-subtle text-warning-emphasis'],
    ['no' => 'ฎก.2569/0017', 'date' => '14 ส.ค. 2569', 'payee' => 'การไฟฟ้าส่วนภูมิภาค', 'source' => 'เงินบำรุง', 'amount' => 268450.75, 'status' => 'รออนุมัติ', 'class' => 'bg-info-subtle text-info-emphasis'],
    ['no' => 'ฎก.2569/0016', 'date' => '13 ส.ค. 2569', 'payee' => 'บริษัท บริการตัวอย่าง จำกัด', 'source' => 'งบประมาณ', 'amount' => 86500, 'status' => 'รอจ่าย', 'class' => 'bg-primary-subtle text-primary-emphasis'],
    ['no' => 'ฎก.2569/0015', 'date' => '12 ส.ค. 2569', 'payee' => 'ร้านวัสดุสำนักงานตัวอย่าง', 'source' => 'เงินบำรุง', 'amount' => 48500, 'status' => 'จ่ายแล้ว', 'class' => 'bg-success-subtle text-success-emphasis'],
];

$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <i class="bi bi-file-earmark-check fs-4" aria-hidden="true"></i>
    <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
    <span class="badge bg-warning-subtle text-warning-emphasis">ต้นแบบ</span>
</div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ติดตามเอกสารตั้งแต่ฉบับร่างจนถึงจ่ายเงินและปิดรายการ<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'voucher']);
$this->endBlock();
?>

<?= $this->render('@app/modules/finance/views/_prototype_notice') ?>

<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
    <div class="d-flex flex-wrap gap-2" aria-label="กรองสถานะฎีกา">
        <button type="button" class="btn btn-primary">ทั้งหมด <span class="badge text-bg-secondary ms-1">4</span></button>
        <button type="button" class="btn btn-outline-secondary">รอตรวจสอบ</button>
        <button type="button" class="btn btn-outline-secondary">รออนุมัติ</button>
        <button type="button" class="btn btn-outline-secondary">รอจ่าย</button>
        <button type="button" class="btn btn-outline-secondary">จ่ายแล้ว</button>
    </div>
    <button type="button" class="btn btn-success" disabled title="เปิดใช้งานเมื่อฐานข้อมูล Finance Core พร้อม">
        <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> สร้างฎีกา
    </button>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-lg-5">
                <label for="voucher-search" class="form-label">ค้นหา</label>
                <input id="voucher-search" type="search" class="form-control" placeholder="เลขที่ฎีกา หรือชื่อผู้รับเงิน">
            </div>
            <div class="col-sm-6 col-lg-3">
                <label for="voucher-source" class="form-label">แหล่งเงิน</label>
                <select id="voucher-source" class="form-select">
                    <option>ทุกแหล่งเงิน</option>
                    <option>เงินบำรุง</option>
                    <option>งบประมาณ</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2 d-grid align-self-end">
                <button type="button" class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> ค้นหา</button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="d-none d-lg-block">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-secondary">
                    <tr>
                        <th scope="col">เลขที่ฎีกา</th>
                        <th scope="col">วันที่</th>
                        <th scope="col">ผู้รับเงิน</th>
                        <th scope="col">แหล่งเงิน</th>
                        <th scope="col" class="text-end">ยอดสุทธิ</th>
                        <th scope="col" class="text-center">สถานะ</th>
                        <th scope="col" class="text-end">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><strong class="text-primary"><?= Html::encode($row['no']) ?></strong></td>
                            <td class="text-nowrap"><?= Html::encode($row['date']) ?></td>
                            <td><?= Html::encode($row['payee']) ?></td>
                            <td><?= Html::encode($row['source']) ?></td>
                            <td class="text-end text-nowrap"><?= number_format($row['amount'], 2) ?></td>
                            <td class="text-center"><span class="badge <?= Html::encode($row['class']) ?>"><?= Html::encode($row['status']) ?></span></td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-primary" type="button" disabled>ดูรายละเอียด</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <ul class="list-group list-group-flush d-lg-none" role="list">
            <?php foreach ($rows as $row): ?>
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between gap-2 mb-2">
                        <strong class="text-primary"><?= Html::encode($row['no']) ?></strong>
                        <span class="badge <?= Html::encode($row['class']) ?>"><?= Html::encode($row['status']) ?></span>
                    </div>
                    <div><?= Html::encode($row['payee']) ?></div>
                    <div class="d-flex justify-content-between gap-2 mt-2 text-body-secondary small">
                        <span><?= Html::encode($row['date']) ?> · <?= Html::encode($row['source']) ?></span>
                        <strong class="text-body text-nowrap"><?= number_format($row['amount'], 2) ?> บาท</strong>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card-footer bg-body text-body-secondary small">แสดง 1 ถึง 4 จาก 4 รายการ</div>
</div>
