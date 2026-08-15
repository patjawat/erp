<?php

use yii\helpers\Html;

$this->title = 'เช็คและการโอนเงิน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$cheques = [
    ['no' => 'CHQ-000124', 'payee' => 'บริษัท ตัวอย่างเวชภัณฑ์ จำกัด', 'date' => '16 ส.ค. 2569', 'amount' => 175000, 'status' => 'รอลงนาม', 'class' => 'bg-warning-subtle text-warning-emphasis'],
    ['no' => 'CHQ-000123', 'payee' => 'ร้านวัสดุสำนักงานตัวอย่าง', 'date' => '15 ส.ค. 2569', 'amount' => 48500, 'status' => 'พร้อมจ่าย', 'class' => 'bg-info-subtle text-info-emphasis'],
    ['no' => 'CHQ-000122', 'payee' => 'บริษัท บริการตัวอย่าง จำกัด', 'date' => '14 ส.ค. 2569', 'amount' => 86500, 'status' => 'รับเช็คแล้ว', 'class' => 'bg-success-subtle text-success-emphasis'],
];

$transfers = [
    ['batch' => 'TRF-2569-0012', 'account' => 'เงินบำรุง · 123-4-XX789-0', 'count' => 8, 'amount' => 328450.75, 'status' => 'รอยืนยันผล', 'class' => 'bg-warning-subtle text-warning-emphasis'],
    ['batch' => 'TRF-2569-0011', 'account' => 'เงินงบประมาณ · 456-7-XX321-0', 'count' => 12, 'amount' => 618000, 'status' => 'โอนสำเร็จ', 'class' => 'bg-success-subtle text-success-emphasis'],
];

$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <i class="bi bi-bank fs-4" aria-hidden="true"></i>
    <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
    <span class="badge bg-warning-subtle text-warning-emphasis">ต้นแบบ</span>
</div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ควบคุมเช็ค ชุดโอนเงิน และสถานะการจ่าย<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();
?>

<?= $this->render('@app/modules/finance/views/_prototype_notice') ?>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="cheque-tab" data-bs-toggle="tab" data-bs-target="#cheque-pane" type="button" role="tab" aria-controls="cheque-pane" aria-selected="true">ทะเบียนเช็ค</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="transfer-tab" data-bs-toggle="tab" data-bs-target="#transfer-pane" type="button" role="tab" aria-controls="transfer-pane" aria-selected="false">ชุดโอนเงิน</button>
    </li>
</ul>

<div class="tab-content">
    <section class="tab-pane fade show active" id="cheque-pane" role="tabpanel" aria-labelledby="cheque-tab" tabindex="0">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">รายการเช็คล่าสุด</h5>
            <button class="btn btn-success" type="button" disabled><i class="bi bi-plus-circle me-1"></i> เตรียมเช็ค</button>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-secondary"><tr><th>เลขที่เช็ค</th><th>ผู้รับเงิน</th><th>วันที่หน้าเช็ค</th><th class="text-end">จำนวนเงิน</th><th class="text-center">สถานะ</th><th class="text-end">ดำเนินการ</th></tr></thead>
                        <tbody>
                        <?php foreach ($cheques as $row): ?>
                            <tr>
                                <td><strong class="text-primary"><?= Html::encode($row['no']) ?></strong></td>
                                <td><?= Html::encode($row['payee']) ?></td>
                                <td><?= Html::encode($row['date']) ?></td>
                                <td class="text-end text-nowrap"><?= number_format($row['amount'], 2) ?></td>
                                <td class="text-center"><span class="badge <?= Html::encode($row['class']) ?>"><?= Html::encode($row['status']) ?></span></td>
                                <td class="text-end"><button class="btn btn-sm btn-outline-primary" type="button" disabled>ดูรายละเอียด</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <ul class="list-group list-group-flush d-lg-none" role="list">
                    <?php foreach ($cheques as $row): ?>
                        <li class="list-group-item py-3">
                            <div class="d-flex justify-content-between gap-2"><strong class="text-primary"><?= Html::encode($row['no']) ?></strong><span class="badge <?= Html::encode($row['class']) ?>"><?= Html::encode($row['status']) ?></span></div>
                            <div class="mt-2"><?= Html::encode($row['payee']) ?></div>
                            <div class="d-flex justify-content-between mt-2 small"><span><?= Html::encode($row['date']) ?></span><strong><?= number_format($row['amount'], 2) ?> บาท</strong></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <section class="tab-pane fade" id="transfer-pane" role="tabpanel" aria-labelledby="transfer-tab" tabindex="0">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">ชุดโอนเงินล่าสุด</h5>
            <button class="btn btn-success" type="button" disabled><i class="bi bi-plus-circle me-1"></i> สร้างชุดโอน</button>
        </div>
        <div class="card shadow-sm">
            <div class="list-group list-group-flush">
                <?php foreach ($transfers as $row): ?>
                    <div class="list-group-item py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1"><strong class="text-primary"><?= Html::encode($row['batch']) ?></strong><span class="badge <?= Html::encode($row['class']) ?>"><?= Html::encode($row['status']) ?></span></div>
                                <div><?= Html::encode($row['account']) ?></div>
                                <small class="text-body-secondary"><?= number_format($row['count']) ?> รายการจ่าย</small>
                            </div>
                            <strong class="text-nowrap align-self-md-center"><?= number_format($row['amount'], 2) ?> บาท</strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
