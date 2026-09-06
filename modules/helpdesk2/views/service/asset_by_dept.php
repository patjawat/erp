<?php

use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $active */
/** @var string $title */
/** @var string $icon */
/** @var array{rows:array,totals:array} $summary */

$this->title = $title;
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ครุภัณฑ์รายหน่วยงาน';

$rows = $summary['rows'] ?? [];
$totals = $summary['totals'] ?? ['dept' => 0, 'total' => 0, 'good' => 0, 'damaged' => 0, 'repairing' => 0, 'value' => 0.0];
$nf = static fn($n) => number_format((int) $n);
$maxTotal = 1;
foreach ($rows as $r) {
    $maxTotal = max($maxTotal, (int) $r['total']);
}
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <?= $icon ?> <?= Html::encode($this->title) ?> — ครุภัณฑ์รายหน่วยงาน
    </h4>
    <span class="small text-muted">สรุปว่าแต่ละหน่วยงานถือครุภัณฑ์อะไร กี่ชิ้น สภาพเป็นอย่างไร — คลิกแถวเพื่อดูรายชิ้น</span>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/helpdesk2/menu', ['active' => $active]) ?>
<?php $this->endBlock(); ?>

<!-- ===== แถบสลับมุมมอง ===== -->
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <div class="btn-group btn-group-sm" role="group" aria-label="สลับมุมมองทรัพย์สิน">
        <?= Html::a('<i class="bi bi-list-ul me-1"></i>รายการทั้งหมด', ['asset'], ['class' => 'btn btn-outline-primary']) ?>
        <?= Html::a('<i class="bi bi-diagram-3 me-1"></i>สรุปรายหน่วยงาน', ['asset-by-dept'], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<!-- ===== การ์ดสรุปรวม ===== -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="small text-uppercase text-secondary fw-semibold mb-1"><i class="bi bi-diagram-3 me-1"></i>หน่วยงาน</div>
            <div class="fw-bold fs-3 lh-1 text-primary"><?= $nf($totals['dept']) ?></div>
            <div class="small text-muted mt-1">หน่วยที่ถือครุภัณฑ์</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="small text-uppercase text-secondary fw-semibold mb-1"><i class="bi bi-box-seam me-1"></i>ครุภัณฑ์รวม</div>
            <div class="fw-bold fs-3 lh-1"><?= $nf($totals['total']) ?></div>
            <div class="small text-muted mt-1">รายการ</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="small text-uppercase text-secondary fw-semibold mb-1"><i class="bi bi-tools me-1"></i>อยู่ระหว่างซ่อม</div>
            <div class="fw-bold fs-3 lh-1 text-warning"><?= $nf($totals['repairing']) ?></div>
            <div class="small text-muted mt-1">ชำรุด <?= $nf($totals['damaged']) ?> รายการ</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="small text-uppercase text-secondary fw-semibold mb-1"><i class="bi bi-cash-stack me-1"></i>มูลค่ารวม</div>
            <div class="fw-bold fs-5 lh-1 text-success"><?= number_format($totals['value'], 0) ?></div>
            <div class="small text-muted mt-1">บาท (ราคาแรกรับ)</div>
        </div></div>
    </div>
</div>

<!-- ===== ตารางสรุปรายหน่วยงาน ===== -->
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex align-items-center gap-2 py-3">
        <div class="erp-icon-box bg-primary bg-opacity-10"><i class="bi bi-clipboard-data"></i></div>
        <h6 class="text-uppercase text-secondary m-0">ครุภัณฑ์แยกตามหน่วยงาน</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($rows)): ?>
            <p class="text-muted p-4 mb-0">ยังไม่มีข้อมูลครุภัณฑ์ในขอบเขตนี้</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">หน่วยงาน</th>
                            <th class="text-end">จำนวน</th>
                            <th class="text-end d-none d-md-table-cell">สภาพดี</th>
                            <th class="text-end">ชำรุด</th>
                            <th class="text-end">ส่งซ่อม</th>
                            <th class="text-end d-none d-lg-table-cell">รอจำหน่าย</th>
                            <th class="text-end pe-4">มูลค่า (฿)</th>
                            <th class="text-center" style="width:56px;"></th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $drill = $r['has_dept']
                                ? ['asset', 'AssetSearch[q_department]' => $r['dept_id']]
                                : ['asset', 'AssetSearch[no_department]' => 1];
                            $goodPct = $r['total'] > 0 ? (int) round($r['good'] / $r['total'] * 100) : 0;
                            $barPct = (int) round($r['total'] / $maxTotal * 100);
                            ?>
                            <tr role="button" style="cursor:pointer;" onclick="window.location='<?= Url::to($drill) ?>'">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!$r['has_dept']): ?>
                                            <i class="bi bi-exclamation-triangle text-warning" title="ยังไม่กำหนดหน่วยงาน"></i>
                                        <?php endif; ?>
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="fw-medium text-truncate <?= $r['has_dept'] ? '' : 'text-muted fst-italic' ?>" style="max-width:340px;"><?= Html::encode($r['dept_name']) ?></div>
                                            <div class="progress mt-1" style="height:4px; max-width:340px;">
                                                <div class="progress-bar <?= $r['has_dept'] ? 'bg-primary' : 'bg-secondary' ?>" style="width:<?= $barPct ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold"><?= $nf($r['total']) ?></td>
                                <td class="text-end d-none d-md-table-cell">
                                    <span class="text-success"><?= $nf($r['good']) ?></span>
                                    <span class="small text-muted">(<?= $goodPct ?>%)</span>
                                </td>
                                <td class="text-end"><?= $r['damaged'] > 0 ? '<span class="text-danger fw-medium">' . $nf($r['damaged']) . '</span>' : '<span class="text-muted">0</span>' ?></td>
                                <td class="text-end"><?= $r['repairing'] > 0 ? '<span class="text-warning fw-medium">' . $nf($r['repairing']) . '</span>' : '<span class="text-muted">0</span>' ?></td>
                                <td class="text-end d-none d-lg-table-cell text-muted"><?= $nf($r['wait_dispose']) ?></td>
                                <td class="text-end pe-4 font-monospace"><?= number_format($r['value'], 0) ?></td>
                                <td class="text-center"><i class="bi bi-chevron-right text-muted"></i></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
