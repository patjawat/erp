<?php

use yii\helpers\Html;

$this->title = 'ทะเบียนเงินยืม';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$rows = [
    ['no' => 'ยืม.2569/0007', 'borrower' => 'นางสาวสมใจ ตัวอย่าง', 'purpose' => 'ประชุมราชการจังหวัดเลย', 'due' => '18 ส.ค. 2569', 'amount' => 12000, 'outstanding' => 12000, 'status' => 'ใกล้ครบกำหนด', 'class' => 'bg-warning-subtle text-warning-emphasis'],
    ['no' => 'ยืม.2569/0006', 'borrower' => 'นายสมชาย ตัวอย่าง', 'purpose' => 'อบรมระบบเวชระเบียน', 'due' => '10 ส.ค. 2569', 'amount' => 8500, 'outstanding' => 3500, 'status' => 'เกินกำหนด', 'class' => 'bg-danger-subtle text-danger-emphasis'],
    ['no' => 'ยืม.2569/0005', 'borrower' => 'นางสาวสุขใจ ตัวอย่าง', 'purpose' => 'จัดกิจกรรมส่งเสริมสุขภาพ', 'due' => '25 ส.ค. 2569', 'amount' => 25000, 'outstanding' => 25000, 'status' => 'รอส่งใช้', 'class' => 'bg-info-subtle text-info-emphasis'],
    ['no' => 'ยืม.2569/0004', 'borrower' => 'นายตั้งใจ ตัวอย่าง', 'purpose' => 'เดินทางไปราชการ', 'due' => '5 ส.ค. 2569', 'amount' => 6800, 'outstanding' => 0, 'status' => 'ปิดรายการ', 'class' => 'bg-success-subtle text-success-emphasis'],
];

$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <i class="bi bi-person-vcard fs-4" aria-hidden="true"></i>
    <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
    <span class="badge bg-warning-subtle text-warning-emphasis">ต้นแบบ</span>
</div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ติดตามการอนุมัติ การจ่าย และการส่งใช้เงินยืม<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'loan']);
$this->endBlock();
?>

<?= $this->render('@app/modules/finance/views/_prototype_notice') ?>

<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-primary" type="button">ทั้งหมด</button>
        <button class="btn btn-outline-secondary" type="button">รออนุมัติ</button>
        <button class="btn btn-outline-secondary" type="button">รอส่งใช้</button>
        <button class="btn btn-outline-warning" type="button">ใกล้ครบกำหนด</button>
        <button class="btn btn-outline-danger" type="button">เกินกำหนด</button>
    </div>
    <button class="btn btn-success" type="button" disabled><i class="bi bi-plus-circle me-1"></i> สร้างคำขอยืม</button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="d-none d-lg-block">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-secondary">
                    <tr>
                        <th>เลขที่สัญญา</th>
                        <th>ผู้ยืม</th>
                        <th>วัตถุประสงค์</th>
                        <th>ครบกำหนด</th>
                        <th class="text-end">จำนวนอนุมัติ</th>
                        <th class="text-end">ยอดค้าง</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-end">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><strong class="text-primary"><?= Html::encode($row['no']) ?></strong></td>
                            <td><?= Html::encode($row['borrower']) ?></td>
                            <td><?= Html::encode($row['purpose']) ?></td>
                            <td class="text-nowrap"><?= Html::encode($row['due']) ?></td>
                            <td class="text-end text-nowrap"><?= number_format($row['amount'], 2) ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?= number_format($row['outstanding'], 2) ?></td>
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
                    <div class="fw-semibold"><?= Html::encode($row['borrower']) ?></div>
                    <div class="text-body-secondary small"><?= Html::encode($row['purpose']) ?></div>
                    <div class="d-flex justify-content-between gap-2 mt-2">
                        <small>ครบกำหนด <?= Html::encode($row['due']) ?></small>
                        <strong class="text-nowrap">ค้าง <?= number_format($row['outstanding'], 2) ?> บาท</strong>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card-footer bg-body text-body-secondary small">แสดง 1 ถึง 4 จาก 4 รายการ</div>
</div>
