<?php
use yii\helpers\Html;

/** @var int $cntAll */
/** @var int $cntDraft */
/** @var int $cntConfirmed */
/** @var int $cntCancelled */
/** @var string $currentStatus */
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <?= Html::a(
            '<div class="card h-100 border-0 shadow-sm"><div class="card-body py-3 px-3">' .
            '<div class="d-flex justify-content-between align-items-start">' .
            '<div class="d-flex flex-column gap-1"><span class="small text-muted">ทั้งหมด</span><span class="fs-4 fw-semibold">' . number_format($cntAll) . '</span></div>' .
            '<span class="erp-icon-box"><i class="bi bi-inbox fs-4"></i></span>' .
            '</div></div></div>',
            ['index'],
            ['class' => 'text-decoration-none text-dark', 'title' => 'แสดงทั้งหมด']
        ) ?>
    </div>
    <div class="col-6 col-md-3">
        <?= Html::a(
            '<div class="card h-100 border-0 shadow-sm' . ($currentStatus === 'DRAFT' ? ' border-start border-3 border-warning' : '') . '"><div class="card-body py-3 px-3">' .
            '<div class="d-flex justify-content-between align-items-start">' .
            '<div class="d-flex flex-column gap-1"><span class="small text-muted">ร่าง</span><span class="fs-4 fw-semibold">' . number_format($cntDraft) . '</span></div>' .
            '<span class="erp-icon-box"><i class="bi bi-file-earmark fs-4"></i></span>' .
            '</div></div></div>',
            ['index', 'StockOrderSearch' => ['status' => 'DRAFT']],
            ['class' => 'text-decoration-none text-dark', 'title' => 'กรอง: ร่าง']
        ) ?>
    </div>
    <div class="col-6 col-md-3">
        <?= Html::a(
            '<div class="card h-100 border-0 shadow-sm' . ($currentStatus === 'CONFIRMED' ? ' border-start border-3 border-success' : '') . '"><div class="card-body py-3 px-3">' .
            '<div class="d-flex justify-content-between align-items-start">' .
            '<div class="d-flex flex-column gap-1"><span class="small text-muted">บันทึกแล้ว</span><span class="fs-4 fw-semibold">' . number_format($cntConfirmed) . '</span></div>' .
            '<span class="erp-icon-box"><i class="bi bi-check-circle fs-4"></i></span>' .
            '</div></div></div>',
            ['index', 'StockOrderSearch' => ['status' => 'CONFIRMED']],
            ['class' => 'text-decoration-none text-dark', 'title' => 'กรอง: บันทึกแล้ว']
        ) ?>
    </div>
    <div class="col-6 col-md-3">
        <?php
        $cancelOpacity = $cntCancelled === 0 ? ' opacity-60' : '';
        echo Html::a(
            '<div class="card h-100 border-0 shadow-sm' . $cancelOpacity . ($currentStatus === 'CANCELLED' ? ' border-start border-3 border-danger' : '') . '"><div class="card-body py-3 px-3">' .
            '<div class="d-flex justify-content-between align-items-start">' .
            '<div class="d-flex flex-column gap-1"><span class="small text-muted">ยกเลิก</span><span class="fs-4 fw-semibold">' . number_format($cntCancelled) . '</span></div>' .
            '<span class="erp-icon-box"><i class="bi bi-x-circle fs-4"></i></span>' .
            '</div></div></div>',
            ['index', 'StockOrderSearch' => ['status' => 'CANCELLED']],
            ['class' => 'text-decoration-none text-dark', 'title' => 'กรอง: ยกเลิก']
        );
        ?>
    </div>
</div>
