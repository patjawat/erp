<?php

use app\modules\finance\models\FinanceLoan;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var FinanceLoan[] $loans ค้างเกินกำหนดแล้ว */
/** @var FinanceLoan[] $upcoming ครบกำหนดภายใน 7 วัน */
/** @var FinanceLoan[] $missing ยังไม่มีวันครบกำหนด */

$this->title = 'ลูกหนี้เงินยืมค้างชำระ';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเงินยืม', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2"><i class="bi bi-hourglass-split fs-4" aria-hidden="true"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>เรียงตามค้างนานที่สุด พร้อมประวัติการติดตามของแต่ละราย<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'loan']); $this->endBlock();

$sum = static fn(array $rows) => array_sum(array_map(static fn(FinanceLoan $l) => (float) $l->outstanding_amount, $rows));
$date = fn($value) => $value ? Yii::$app->formatter->asDate($value, 'php:d/m/Y') : '—';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับทะเบียน', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <span class="text-body-secondary small">ระบบเตือนผู้ยืมทาง Telegram อัตโนมัติทุกวัน · ออกหนังสือได้จากหน้ารายละเอียดใบยืม</span>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card border h-100"><div class="card-body">
            <div class="text-body-secondary small">เกินกำหนดแล้ว</div>
            <div class="fs-4 fw-semibold <?= $loans ? 'text-danger-emphasis' : '' ?>"><?= count($loans) ?> <small class="fs-6 fw-normal">ราย</small></div>
            <div class="font-monospace"><?= number_format($sum($loans), 2) ?> บาท</div>
        </div></div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border h-100"><div class="card-body">
            <div class="text-body-secondary small">ครบกำหนดภายใน 7 วัน</div>
            <div class="fs-4 fw-semibold <?= $upcoming ? 'text-warning-emphasis' : '' ?>"><?= count($upcoming) ?> <small class="fs-6 fw-normal">ราย</small></div>
            <div class="font-monospace"><?= number_format($sum($upcoming), 2) ?> บาท</div>
        </div></div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border h-100"><div class="card-body">
            <div class="text-body-secondary small">ยังไม่มีวันครบกำหนด</div>
            <div class="fs-4 fw-semibold"><?= count($missing) ?> <small class="fs-6 fw-normal">ราย</small></div>
            <div class="text-body-secondary small">รอกรอกวันที่ดำเนินการเสร็จ</div>
        </div></div>
    </div>
</div>

<?php
$sections = [
    ['title' => 'เกินกำหนดแล้ว', 'rows' => $loans, 'empty' => 'ไม่มีลูกหนี้เกินกำหนด', 'overdue' => true],
    ['title' => 'ใกล้ครบกำหนด (ภายใน 7 วัน)', 'rows' => $upcoming, 'empty' => 'ไม่มีรายการที่ใกล้ครบกำหนด', 'overdue' => false],
    ['title' => 'ยังไม่ระบุวันครบกำหนด', 'rows' => $missing, 'empty' => 'ทุกใบยืมมีวันครบกำหนดครบแล้ว', 'overdue' => false],
];
?>

<?php foreach ($sections as $section): ?>
<section class="card border mb-3">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
        <h5 class="mb-0"><?= Html::encode($section['title']) ?></h5>
        <span class="text-body-secondary small"><?= count($section['rows']) ?> ราย</span>
    </div>
    <?php if (!$section['rows']): ?>
        <div class="card-body text-body-secondary small"><?= Html::encode($section['empty']) ?></div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th>เลขที่สัญญา</th><th>ผู้ยืม</th><th>วัตถุประสงค์</th>
                <th>ครบกำหนด</th>
                <?php if ($section['overdue']): ?><th class="text-end">เกิน (วัน)</th><?php endif; ?>
                <th class="text-end">คงเหลือ</th><th>ติดตามล่าสุด</th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($section['rows'] as $loan): ?>
                <tr>
                    <td class="text-nowrap"><?= Html::a(Html::encode($loan->contract_no), ['view', 'id' => $loan->id], ['class' => 'fw-semibold']) ?></td>
                    <td class="text-nowrap"><?= Html::encode($loan->borrower_name) ?></td>
                    <td class="small"><?= Html::encode(mb_substr((string) $loan->purpose, 0, 60)) ?></td>
                    <td class="text-nowrap"><?= $date($loan->due_at) ?></td>
                    <?php if ($section['overdue']): ?>
                        <td class="text-end fw-semibold text-danger-emphasis"><?= $loan->daysOverdue() ?></td>
                    <?php endif; ?>
                    <td class="text-end font-monospace fw-semibold"><?= number_format($loan->outstanding_amount, 2) ?></td>
                    <td class="small text-nowrap">
                        <?php if ((int) $loan->followup_count > 0): ?>
                            <?= (int) $loan->followup_count ?> ครั้ง · <?= $date($loan->last_followup_at) ?>
                        <?php else: ?>
                            <span class="text-body-secondary">ยังไม่เคยติดตาม</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-nowrap">
                        <?php if ($section['overdue']): ?>
                            <?= Html::a('<i class="bi bi-envelope-paper me-1"></i> ออกหนังสือ', ['followup', 'id' => $loan->id], ['class' => 'btn btn-sm btn-outline-danger']) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
<?php endforeach; ?>
