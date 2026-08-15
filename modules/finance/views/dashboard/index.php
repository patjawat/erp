<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ภาพรวมการเงิน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$tasks = [
    ['type' => 'ฎีกา', 'no' => 'ฎก.2569/0018', 'title' => 'ค่าจัดซื้อวัสดุสำนักงาน', 'amount' => 48500, 'status' => 'รอตรวจสอบ', 'class' => 'bg-warning-subtle text-warning-emphasis'],
    ['type' => 'เงินยืม', 'no' => 'ยืม.2569/0007', 'title' => 'ประชุมราชการจังหวัดเลย', 'amount' => 12000, 'status' => 'ใกล้ครบกำหนด', 'class' => 'bg-warning-subtle text-warning-emphasis'],
    ['type' => 'การจ่าย', 'no' => 'จ่าย.2569/0024', 'title' => 'ค่าบำรุงรักษาเครื่องปรับอากาศ', 'amount' => 86500, 'status' => 'รอยืนยันโอน', 'class' => 'bg-info-subtle text-info-emphasis'],
    ['type' => 'เช็ค', 'no' => 'CHQ-000124', 'title' => 'บริษัท ตัวอย่างเวชภัณฑ์ จำกัด', 'amount' => 175000, 'status' => 'รอลงนาม', 'class' => 'bg-secondary-subtle text-secondary-emphasis'],
];

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-calculator fs-4" aria-hidden="true"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4><span class="badge bg-warning-subtle text-warning-emphasis">ต้นแบบ</span></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo 'งานที่ต้องดำเนินการและภาพรวมงบประมาณประจำปี 2569';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'dashboard']);
$this->endBlock();
?>

<?= $this->render('@app/modules/finance/views/_prototype_notice') ?>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
    <div>
        <h5 class="mb-1">งานของฉัน</h5>
        <p class="text-body-secondary mb-0">เรียงตามรายการที่ควรดำเนินการก่อน</p>
    </div>
    <a href="<?= Url::to(['/finance/voucher']) ?>" class="btn btn-success">
        <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> สร้างฎีกา
    </a>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['label' => 'รอตรวจสอบ', 'value' => 8, 'class' => 'text-warning-emphasis'],
        ['label' => 'รออนุมัติ', 'value' => 4, 'class' => 'text-info-emphasis'],
        ['label' => 'รอจ่าย', 'value' => 6, 'class' => 'text-primary'],
        ['label' => 'เกินกำหนด', 'value' => 2, 'class' => 'text-danger-emphasis'],
    ] as $summary): ?>
        <div class="col-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body py-3">
                    <div class="text-body-secondary small"><?= Html::encode($summary['label']) ?></div>
                    <div class="fs-3 fw-semibold <?= $summary['class'] ?>"><?= number_format($summary['value']) ?> <span class="fs-6 fw-normal">รายการ</span></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <section class="card shadow-sm h-100" aria-labelledby="finance-task-heading">
            <div class="card-header bg-body d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="finance-task-heading">รายการที่ต้องดำเนินการ</h5>
                <span class="text-body-secondary small">4 รายการตัวอย่าง</span>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($tasks as $task): ?>
                    <a href="<?= Url::to($task['type'] === 'เงินยืม' ? ['/finance/loan'] : ($task['type'] === 'ฎีกา' ? ['/finance/voucher'] : ['/finance/payment'])) ?>" class="list-group-item list-group-item-action py-3">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="min-w-0">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <strong><?= Html::encode($task['no']) ?></strong>
                                    <span class="badge <?= Html::encode($task['class']) ?>"><?= Html::encode($task['status']) ?></span>
                                </div>
                                <div><?= Html::encode($task['title']) ?></div>
                                <small class="text-body-secondary"><?= Html::encode($task['type']) ?></small>
                            </div>
                            <strong class="text-nowrap"><?= number_format($task['amount'], 2) ?> บาท</strong>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <div class="col-xl-4" id="budget-overview">
        <section class="card shadow-sm h-100" aria-labelledby="budget-heading">
            <div class="card-header bg-body">
                <h5 class="mb-0" id="budget-heading">สถานะงบประมาณ</h5>
            </div>
            <div class="card-body">
                <?php foreach ([
                    ['label' => 'งบอนุมัติ', 'amount' => 10000000, 'class' => 'text-body'],
                    ['label' => 'เงินผูกพัน', 'amount' => 4200000, 'class' => 'text-warning-emphasis'],
                    ['label' => 'เบิกจ่ายแล้ว', 'amount' => 3100000, 'class' => 'text-primary'],
                    ['label' => 'งบคงเหลือ', 'amount' => 2700000, 'class' => 'text-success-emphasis'],
                ] as $budget): ?>
                    <div class="d-flex justify-content-between align-items-baseline py-2 border-bottom">
                        <span class="text-body-secondary"><?= Html::encode($budget['label']) ?></span>
                        <strong class="<?= $budget['class'] ?>"><?= number_format($budget['amount'], 2) ?></strong>
                    </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between align-items-baseline mt-4">
                    <span class="text-body-secondary small">รวมเงินผูกพันและเบิกจ่ายแล้ว</span>
                    <strong>73%</strong>
                </div>
            </div>
        </section>
    </div>
</div>
