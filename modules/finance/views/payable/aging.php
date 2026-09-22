<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $rows */
/** @var array $buckets */
/** @var float $sumOutstanding */
/** @var float $sumOverdue */
/** @var float $sumDueThisMonth */

$this->title = 'เจ้าหนี้ค้างชำระ (Aging)';
$this->params['breadcrumbs'][] = ['label' => 'บัญชี', 'url' => ['/accounting/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเจ้าหนี้', 'url' => ['/accounting/payable']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'หนี้ที่อนุมัติเข้าทะเบียนแล้วและยังคงค้าง จัดกลุ่มตามวันครบกำหนด';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/accounting/menu', ['active' => 'aging']);
$this->endBlock();

$fmt = fn($v) => number_format((float) $v, 2);
$thDate = function ($d) {
    if (!$d) {
        return '–';
    }
    $t = date_create($d);
    return $t ? $t->format('d/m/') . ((int) $t->format('Y') + 543) : $d;
};
$bucketBadge = [
    'not_due' => 'bg-secondary-subtle text-secondary-emphasis',
    'd30' => 'bg-warning-subtle text-warning-emphasis',
    'd60' => 'bg-warning-subtle text-warning-emphasis',
    'd90' => 'bg-danger-subtle text-danger-emphasis',
    'd90p' => 'bg-danger-subtle text-danger-emphasis',
];
?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= Url::to(['pay']) ?>" class="btn btn-primary"><i class="bi bi-cash-stack me-1"></i>จ่ายชำระเจ้าหนี้</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border h-100"><div class="card-body">
            <div class="text-body-secondary small">ค้างชำระรวมทั้งสิ้น</div>
            <div class="fs-4 fw-bold"><?= $fmt($sumOutstanding) ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border h-100"><div class="card-body">
            <div class="text-body-secondary small">ครบกำหนดภายในเดือนนี้</div>
            <div class="fs-4 fw-bold text-warning-emphasis"><?= $fmt($sumDueThisMonth) ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border h-100 <?= $sumOverdue > 0 ? 'border-danger-subtle' : '' ?>"><div class="card-body">
            <div class="text-body-secondary small">เกินกำหนดแล้ว</div>
            <div class="fs-4 fw-bold <?= $sumOverdue > 0 ? 'text-danger' : '' ?>"><?= $fmt($sumOverdue) ?></div>
        </div></div>
    </div>
</div>

<div class="card border mb-3"><div class="card-body d-flex flex-wrap gap-2">
    <?php foreach ($buckets as $b): ?>
        <span class="badge rounded-pill bg-body-secondary text-body px-3 py-2">
            <?= Html::encode($b['label']) ?>: <span class="fw-bold"><?= $fmt($b['total']) ?></span>
            <span class="text-body-secondary">(<?= $b['count'] ?>)</span>
        </span>
    <?php endforeach; ?>
</div></div>

<section class="card border shadow-sm">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
        <h5 class="mb-0">รายการเจ้าหนี้ค้างชำระ</h5>
        <span class="text-body-secondary small"><?= count($rows) ?> รายการ</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>เลขทะเบียน</th>
                    <th>เจ้าหนี้</th>
                    <th>ใบแจ้งหนี้</th>
                    <th class="text-center">ครบกำหนด</th>
                    <th class="text-end">ยอดหนี้</th>
                    <th class="text-end">จ่ายแล้ว</th>
                    <th class="text-end">คงค้าง</th>
                    <th class="text-center">อายุหนี้</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="8" class="text-center text-body-secondary py-4"><i class="bi bi-check2-circle me-1"></i>ไม่มีหนี้ค้างชำระ</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= Html::a(Html::encode($r['payable_no'] ?: '#' . $r['id']), ['view', 'id' => $r['id']], ['class' => 'fw-semibold']) ?></td>
                        <td><?= Html::encode($r['vendor_name_snapshot']) ?></td>
                        <td class="text-body-secondary"><?= Html::encode($r['invoice_no']) ?></td>
                        <td class="text-center"><?= $thDate($r['due_date']) ?></td>
                        <td class="text-end text-body-secondary"><?= $fmt($r['net_amount']) ?></td>
                        <td class="text-end text-body-secondary"><?= $fmt($r['paid']) ?></td>
                        <td class="text-end fw-semibold"><?= $fmt($r['outstanding']) ?></td>
                        <td class="text-center">
                            <span class="badge rounded-pill <?= $bucketBadge[$r['bucket']] ?? '' ?>">
                                <?= Html::encode($buckets[$r['bucket']]['label'] ?? '') ?>
                                <?php if (($r['overdue_days'] ?? 0) > 0): ?>(<?= $r['overdue_days'] ?> วัน)<?php endif; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
