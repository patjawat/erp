<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $detail */

$warehouse = $detail['warehouse'];
$this->title = 'รายละเอียดการเบิกใช้ ' . $warehouse->warehouse_name;
$this->registerCss('.sub-warehouse-detail .executive-card{border-radius:.75rem}.sub-warehouse-detail .metric-value{font-variant-numeric:tabular-nums}.sub-warehouse-detail .summary-card{min-height:92px}');
?>

<div class="sub-warehouse-detail container-fluid py-3 py-lg-4">
    <header class="mb-4">
        <?= Html::a('<i class="bi bi-arrow-left me-2"></i>กลับภาพรวมคลัง', ['/executive/dashboard/inventory', 'year' => $detail['fiscalYear']], ['class' => 'btn btn-outline-secondary btn-sm mb-3']) ?>
        <div class="small text-primary fw-semibold mb-1">รายละเอียดคลังย่อย · ปีงบประมาณ <?= (int) $detail['fiscalYear'] ?></div>
        <h1 class="h3 mb-1"><?= Html::encode($warehouse->warehouse_name) ?></h1>
        <div class="text-body-secondary">รายการใบขอเบิกจากคลังหลัก (REQUEST) ที่ยืนยันแล้ว</div>
    </header>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4"><article class="card executive-card summary-card border shadow-sm h-100"><div class="card-body p-3 d-flex justify-content-between align-items-center gap-3"><div><div class="small text-body-secondary mb-1">มูลค่าเบิกทั้งปี</div><div class="fs-4 fw-semibold metric-value"><?= number_format((float) $detail['totalValue'], 2) ?> <span class="fs-6 fw-normal">บาท</span></div></div><span class="rounded-3 bg-primary-subtle text-primary-emphasis p-2"><i class="bi bi-receipt"></i></span></div></article></div>
        <div class="col-12 col-md-4"><article class="card executive-card summary-card border shadow-sm h-100"><div class="card-body p-3 d-flex justify-content-between align-items-center gap-3"><div><div class="small text-body-secondary mb-1">จำนวนเบิกรวม</div><div class="fs-4 fw-semibold metric-value"><?= number_format((float) $detail['totalQty'], 2) ?></div></div><span class="rounded-3 bg-info-subtle text-info-emphasis p-2"><i class="bi bi-box-arrow-right"></i></span></div></article></div>
        <div class="col-12 col-md-4"><article class="card executive-card summary-card border shadow-sm h-100"><div class="card-body p-3 d-flex justify-content-between align-items-center gap-3"><div><div class="small text-body-secondary mb-1">รายการวัสดุที่มีการเบิก</div><div class="fs-4 fw-semibold metric-value"><?= number_format(count($detail['rows'])) ?> <span class="fs-6 fw-normal">รายการ</span></div></div><span class="rounded-3 bg-success-subtle text-success-emphasis p-2"><i class="bi bi-box-seam"></i></span></div></article></div>
    </div>

    <section class="mb-4" aria-labelledby="category-summary-heading">
        <div class="d-flex justify-content-between align-items-end gap-3 mb-3"><div><h2 id="category-summary-heading" class="h5 mb-1">สรุปแยกประเภทวัสดุ</h2><div class="small text-body-secondary">เปรียบเทียบสัดส่วนการเบิกของแต่ละประเภทในปีงบประมาณ</div></div><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= number_format(count($detail['categories'])) ?> ประเภท</span></div>
        <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-xl-4">
            <?php $categoryColors = ['primary', 'success', 'warning', 'info']; ?>
            <?php foreach ($detail['categories'] as $index => $category): $color = $categoryColors[$index % count($categoryColors)]; ?>
                <div class="col"><article class="card executive-card summary-card border shadow-sm h-100"><div class="card-body p-3 d-flex align-items-center justify-content-between gap-3"><div class="min-w-0"><h3 class="h6 text-truncate mb-1" title="<?= Html::encode($category['name']) ?>"><?= Html::encode($category['name']) ?></h3><div class="fs-5 fw-semibold metric-value"><?= number_format((float) $category['value'], 2) ?> <span class="small fw-normal">บาท</span></div><div class="small text-body-secondary"><?= number_format((int) $category['item_count']) ?> รายการ · <?= number_format((float) $category['qty'], 2) ?> หน่วย</div></div><span class="rounded-3 bg-<?= $color ?>-subtle text-<?= $color ?>-emphasis p-2 flex-shrink-0"><i class="bi bi-boxes"></i></span></div></article></div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="card executive-card border shadow-sm">
        <div class="card-header bg-body py-3"><h2 class="h5 mb-1">วัสดุที่คลังย่อยขอเบิก</h2><div class="small text-body-secondary">เรียงจากมูลค่าการเบิกสูงสุด</div></div>
        <?php if ($detail['rows']): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>รหัส</th><th>รายการ</th><th class="text-end">จำนวนเบิก</th><th class="text-end">มูลค่า (บาท)</th><th class="text-end">เบิกล่าสุด</th></tr></thead>
                    <tbody>
                    <?php $currentCategory = null; ?>
                    <?php foreach ($detail['rows'] as $row): ?>
                        <?php if ($currentCategory !== $row['category_name']): $currentCategory = $row['category_name']; ?>
                            <tr><th colspan="5" class="bg-body-tertiary text-body-secondary py-2"><i class="bi bi-tag me-2 text-primary"></i><?= Html::encode($currentCategory) ?></th></tr>
                        <?php endif; ?>
                        <tr><td class="text-body-secondary"><?= Html::encode($row['code']) ?></td><td class="fw-semibold"><?= Html::encode($row['name'] ?: $row['code']) ?></td><td class="text-end metric-value"><?= number_format((float) $row['qty'], 2) ?></td><td class="text-end fw-semibold metric-value"><?= number_format((float) $row['value'], 2) ?></td><td class="text-end text-nowrap"><?= Yii::$app->formatter->asDatetime((int) $row['last_disbursed_at'], 'php:d/m/Y H:i') ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card-body py-5 text-center"><i class="bi bi-inbox fs-2 text-body-secondary"></i><h3 class="h6 mt-3 mb-1">ยังไม่มีใบขอเบิกที่ยืนยันแล้วในปีงบประมาณนี้</h3><div class="small text-body-secondary">ตรวจสอบเฉพาะรายการ REQUEST ของคลังย่อยนี้</div></div>
        <?php endif; ?>
    </section>
</div>
