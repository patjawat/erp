<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fy */
/** @var array $cash */
/** @var array $ar */
/** @var array $ap */
/** @var array $budget */
/** @var float $petty */
/** @var array $queue */
/** @var int[] $fiscalYears */

$this->title = 'ภาพรวมการเงิน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$money = fn ($v) => number_format((float) $v, 2);

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-speedometer2 fs-4" aria-hidden="true"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo 'สรุปสถานะการเงินตามข้อมูลจริง ปีงบประมาณ ' . $fy;
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'dashboard']);
$this->endBlock();
?>

<div class="d-flex justify-content-end mb-3">
    <form method="get" class="d-flex align-items-end gap-2">
        <div>
            <label class="form-label small mb-1">ปีงบประมาณ</label>
            <select name="fiscal_year" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($fiscalYears as $y): ?><option value="<?= $y ?>" <?= $y === $fy ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<!-- วันนี้ -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 h-100" style="background:linear-gradient(135deg,#198754,#20c997);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="small opacity-75"><i class="bi bi-cash-coin me-1"></i>รับวันนี้</div>
                        <div class="fs-3 fw-bold"><?= $money($cash['in_today']) ?></div></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 h-100" style="background:linear-gradient(135deg,#fd7e14,#ffc107);">
            <div class="card-body text-white">
                <div class="small opacity-75"><i class="bi bi-receipt me-1"></i>จ่ายวันนี้</div>
                <div class="fs-3 fw-bold"><?= $money($cash['out_today']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 h-100" style="background:linear-gradient(135deg,#0d6efd,#0dcaf0);">
            <div class="card-body text-white">
                <div class="small opacity-75"><i class="bi bi-graph-up-arrow me-1"></i>สุทธิวันนี้</div>
                <div class="fs-3 fw-bold"><?= $money($cash['net_today']) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- KPI หลัก -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['label' => 'เงินบำรุงคงเหลือ (ปีงบ)', 'value' => $cash['balance_year'], 'icon' => 'bi-bank', 'class' => 'text-primary', 'url' => ['/finance/cash'], 'sub' => 'รับ ' . $money($cash['in_year']) . ' · จ่าย ' . $money($cash['out_year'])],
        ['label' => 'ลูกหนี้ค้างรับ (AR)', 'value' => $ar['outstanding'], 'icon' => 'bi-clipboard2-pulse', 'class' => 'text-success-emphasis', 'url' => ['/finance/ar'], 'sub' => $ar['count'] . ' รายการเปิดค้าง'],
        ['label' => 'เจ้าหนี้ค้างจ่าย (AP)', 'value' => $ap['outstanding'], 'icon' => 'bi-journal-text', 'class' => 'text-warning-emphasis', 'url' => ['/finance/payable'], 'sub' => 'ยอดคงค้างรวม'],
        ['label' => 'เงินสดย่อยในมือ', 'value' => $petty, 'icon' => 'bi-wallet2', 'class' => 'text-info-emphasis', 'url' => ['/finance/petty-cash'], 'sub' => 'ทุกกองรวม'],
    ];
    foreach ($kpis as $k): ?>
        <div class="col-6 col-xl-3">
            <a href="<?= Url::to($k['url']) ?>" class="text-decoration-none">
                <div class="card h-100 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi <?= $k['icon'] ?> fs-4 <?= $k['class'] ?>" aria-hidden="true"></i>
                            <span class="text-body-secondary small"><?= Html::encode($k['label']) ?></span>
                        </div>
                        <div class="fs-4 fw-semibold <?= $k['class'] ?>"><?= $money($k['value']) ?></div>
                        <div class="text-body-secondary" style="font-size:.75rem"><?= Html::encode($k['sub']) ?></div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <!-- งานที่ต้องดำเนินการ -->
    <div class="col-xl-6">
        <section class="card shadow-sm h-100">
            <div class="card-header bg-body"><h5 class="mb-0">งานที่ต้องดำเนินการ</h5></div>
            <div class="list-group list-group-flush">
                <?php
                $tasks = [
                    ['label' => 'กล่องรับเรื่องรอตรวจ (พัสดุส่งมา)', 'count' => $queue['inbox_pending'], 'icon' => 'bi-inbox', 'url' => ['/finance/inbox'], 'class' => 'text-warning-emphasis'],
                    ['label' => 'เจ้าหนี้รอตรวจอนุมัติ', 'count' => $queue['payable_pending'], 'icon' => 'bi-journal-check', 'url' => ['/finance/payable'], 'class' => 'text-info-emphasis'],
                    ['label' => 'ลูกหนี้ค่ารักษาเปิดค้าง', 'count' => $queue['ar_open'], 'icon' => 'bi-clipboard2-pulse', 'url' => ['/finance/ar/invoices'], 'class' => 'text-primary'],
                ];
                foreach ($tasks as $t): ?>
                    <a href="<?= Url::to($t['url']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <span><i class="bi <?= $t['icon'] ?> me-2 <?= $t['class'] ?>" aria-hidden="true"></i><?= Html::encode($t['label']) ?></span>
                        <span class="badge rounded-pill <?= $t['count'] > 0 ? 'bg-danger' : 'bg-secondary' ?>"><?= number_format($t['count']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <!-- งบประมาณ -->
    <div class="col-xl-6">
        <section class="card shadow-sm h-100">
            <div class="card-header bg-body d-flex justify-content-between align-items-center">
                <h5 class="mb-0">เงินงบประมาณ (ปีงบ <?= $fy ?>)</h5>
                <a href="<?= Url::to(['/finance/budget']) ?>" class="small">ดูรายละเอียด</a>
            </div>
            <div class="card-body">
                <?php
                $rows = [
                    ['label' => 'ได้รับจัดสรร', 'value' => $budget['allot'], 'class' => 'text-body'],
                    ['label' => 'เบิกจ่ายแล้ว', 'value' => $budget['disbursed'], 'class' => 'text-primary'],
                    ['label' => 'คงเหลือ', 'value' => $budget['remaining'], 'class' => 'text-success-emphasis'],
                ];
                $pct = $budget['allot'] > 0 ? round($budget['disbursed'] / $budget['allot'] * 100) : 0;
                foreach ($rows as $r): ?>
                    <div class="d-flex justify-content-between align-items-baseline py-2 border-bottom">
                        <span class="text-body-secondary"><?= Html::encode($r['label']) ?></span>
                        <strong class="<?= $r['class'] ?>"><?= $money($r['value']) ?></strong>
                    </div>
                <?php endforeach; ?>
                <div class="mt-3">
                    <div class="d-flex justify-content-between small mb-1"><span class="text-body-secondary">อัตราเบิกจ่าย</span><span><?= $pct ?>%</span></div>
                    <div class="progress" style="height:.5rem"><div class="progress-bar bg-primary" style="width:<?= min(100, $pct) ?>%"></div></div>
                </div>
            </div>
        </section>
    </div>
</div>

<p class="text-body-secondary small mt-3">
    <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
    ตัวเลขคำนวณจากข้อมูลจริงในระบบ ณ ปัจจุบัน — คลิกการ์ด/รายการเพื่อไปยังหน้างานนั้น
</p>
