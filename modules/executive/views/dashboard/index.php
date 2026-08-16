<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $summary */

$this->title = 'Dashboard ผู้บริหาร';
$this->params['breadcrumbs'][] = $this->title;

$metrics = [$summary['cash'], $summary['payable'], $summary['receivable'], $summary['inventory']];
$analysisItems = [
    ['label' => 'เงินสดและแผนการเงิน', 'description' => 'เงินสด เงินฝากธนาคาร และกระแสเงินสด', 'icon' => 'bi-wallet2', 'url' => null],
    ['label' => 'เจ้าหนี้และภาระผูกพัน', 'description' => 'เจ้าหนี้ค้างจ่าย อายุหนี้ และภาระที่ครบกำหนด', 'icon' => 'bi-receipt', 'url' => null],
    ['label' => 'ลูกหนี้และการเรียกเก็บ', 'description' => 'ลูกหนี้ค้างรับ อายุหนี้ และสถานะการจัดเก็บ', 'icon' => 'bi-people', 'url' => null],
    ['label' => 'คลังและวัสดุคงเหลือ', 'description' => 'มูลค่าคงคลัง จุดสั่งซื้อ และวัสดุใกล้หมดอายุ', 'icon' => 'bi-box-seam', 'url' => ['/executive/dashboard/inventory']],
];

$this->beginBlock('page-title');
?>
<div>
    <h1 class="h4 mb-1"><?= Html::encode($this->title) ?></h1>
    <div class="text-body-secondary">ภาพรวมข้อมูลสำคัญเพื่อสนับสนุนการติดตามและตัดสินใจ</div>
</div>
<?php
$this->endBlock();

$this->registerCss(<<<'CSS'
.executive-dashboard { max-width: 1480px; margin-inline: auto; }
.executive-card { border-radius: .75rem; overflow: hidden; }
.executive-metric { min-height: 168px; }
.executive-metric__icon { width: 40px; height: 40px; }
.executive-metric__value { font-variant-numeric: tabular-nums; }
.executive-metric__footer { min-height: 42px; }
.executive-detail {
    min-width: 0;
    border-right: var(--bs-border-width) solid var(--bs-border-color);
}
.executive-detail:last-child { border-right: 0; }
.executive-detail__value { font-variant-numeric: tabular-nums; }
.executive-analysis-link { transition: background-color 160ms ease-out; }
.executive-analysis-link:hover,
.executive-analysis-link:focus-visible { background-color: var(--bs-tertiary-bg); }
.executive-analysis-icon { width: 42px; height: 42px; }
@media (max-width: 991.98px) {
    .executive-metric { min-height: 158px; }
}
@media (max-width: 575.98px) {
    .executive-metric { min-height: 0; }
    .executive-detail { border-right: 0; border-bottom: var(--bs-border-width) solid var(--bs-border-color); }
    .executive-detail:last-child { border-bottom: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .executive-analysis-link { transition: none; }
}
CSS);
?>

<div class="container-fluid py-3 py-md-4 px-3 px-md-4 executive-dashboard">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <div class="fw-semibold">ภาพรวมปีงบประมาณ <?= (int) $summary['selectedFiscalYear'] ?></div>
            <div class="small text-body-secondary">
                ข้อมูล ณ <?= Html::encode(Yii::$app->formatter->asDatetime($summary['asOf'], 'php:d/m/Y H:i')) ?> น.
            </div>
        </div>
        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2 align-self-stretch align-self-md-auto">
            <span class="badge bg-secondary-subtle text-secondary-emphasis align-self-start align-self-sm-center"><i class="bi bi-shield-check me-1" aria-hidden="true"></i>ข้อมูลจากระบบ ERP</span>
            <form method="get" class="card executive-card border shadow-sm p-2">
                <label class="d-flex align-items-center gap-2 mb-0">
                    <span class="small text-body-secondary text-nowrap">ปีงบประมาณ</span>
                    <select class="form-select form-select-sm" name="year" onchange="this.form.submit()" aria-label="เลือกปีงบประมาณ">
                        <?php foreach ($summary['availableYears'] as $year): ?>
                            <option value="<?= (int) $year ?>" <?= (int) $year === (int) $summary['selectedFiscalYear'] ? 'selected' : '' ?>><?= (int) $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </form>
        </div>
    </div>

    <section class="mb-4" aria-labelledby="executive-summary-heading">
        <h2 id="executive-summary-heading" class="visually-hidden">ตัวชี้วัดภาพรวม</h2>
        <div class="row g-3">
            <?php foreach ($metrics as $index => $metric): ?>
                <?php $available = $metric['status'] === 'available'; ?>
                <div class="col-12 col-md-6 col-xl-3">
                    <article class="card bg-body border shadow-sm h-100 executive-card executive-metric">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                <span class="executive-metric__icon rounded-3 bg-<?= Html::encode($metric['color']) ?>-subtle text-<?= Html::encode($metric['color']) ?>-emphasis d-inline-flex align-items-center justify-content-center flex-shrink-0">
                                    <i class="bi <?= Html::encode($metric['icon']) ?> fs-5" aria-hidden="true"></i>
                                </span>
                                <span class="badge <?= $available ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>">
                                    <?= $available ? 'ข้อมูลปัจจุบัน' : 'รอเชื่อมข้อมูล' ?>
                                </span>
                            </div>

                            <h3 class="h6 mb-1"><?= Html::encode($metric['label']) ?></h3>
                            <?php if ($available): ?>
                                <div class="executive-metric__value fs-4 fw-semibold lh-sm mb-1">
                                    <?= number_format((float) $metric['value'], 2) ?>
                                    <span class="fs-6 fw-normal text-body-secondary"><?= Html::encode($metric['unit']) ?></span>
                                </div>
                            <?php else: ?>
                                <div class="fw-semibold text-body-secondary mb-2">ยังไม่แสดงยอด</div>
                            <?php endif; ?>

                            <p class="small text-body-secondary mb-0"><?= Html::encode($metric['description']) ?></p>
                        </div>
                        <div class="card-footer bg-body border-top px-3 py-2 executive-metric__footer d-flex align-items-center">
                            <?php if (!empty($metric['url'])): ?>
                                <a href="<?= Url::to($metric['url']) ?>" class="btn btn-sm btn-outline-primary">
                                    ดูรายละเอียด <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                                </a>
                            <?php else: ?>
                                <span class="small text-body-secondary"><i class="bi bi-clock me-1" aria-hidden="true"></i>รอยืนยันแหล่งข้อมูล</span>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <section class="card bg-body border shadow-sm h-100 executive-card" aria-labelledby="executive-attention-heading">
                <div class="card-header bg-body py-3">
                    <h2 id="executive-attention-heading" class="h5 mb-1">เรื่องที่ควรติดตาม</h2>
                    <div class="small text-body-secondary">สัญญาณจากชุดข้อมูลที่เชื่อมต่อแล้ว</div>
                </div>
                <div class="card-body">
                    <?php if ($summary['inventory']['status'] === 'available'): ?>
                        <div class="row g-0 mb-3">
                            <?php foreach ($summary['inventory']['details'] as $detail): ?>
                                <div class="col-12 col-sm-4 px-sm-3 py-2 executive-detail">
                                    <div class="small text-body-secondary mb-1"><?= Html::encode($detail['label']) ?></div>
                                    <div class="d-flex align-items-baseline gap-2">
                                        <strong class="fs-4 executive-detail__value text-<?= Html::encode($detail['status']) ?>-emphasis">
                                            <?= number_format((int) $detail['value']) ?>
                                        </strong>
                                        <span class="small text-body-secondary">รายการ</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="<?= Url::to(['/executive/dashboard/inventory']) ?>" class="btn btn-outline-primary">วิเคราะห์คลัง</a>
                        </div>
                    <?php else: ?>
                        <div class="py-4 text-center">
                            <div class="fw-semibold mb-1">ยังไม่มีสัญญาณที่พร้อมแสดง</div>
                            <div class="text-body-secondary">ระบบจะแสดงรายการที่ควรติดตามเมื่อเชื่อมข้อมูลสำเร็จ</div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-4">
            <section class="card bg-body border shadow-sm h-100 executive-card" aria-labelledby="executive-analysis-heading">
                <div class="card-header bg-body py-3">
                    <h2 id="executive-analysis-heading" class="h5 mb-1">วิเคราะห์เชิงลึก</h2>
                    <div class="small text-body-secondary">เลือกหัวข้อที่ต้องการติดตาม</div>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($analysisItems as $item): ?>
                        <?php if ($item['url']): ?>
                            <a href="<?= Url::to($item['url']) ?>" class="list-group-item list-group-item-action executive-analysis-link d-flex align-items-center gap-3 py-3">
                        <?php else: ?>
                            <div class="list-group-item d-flex align-items-center gap-3 py-3">
                        <?php endif; ?>
                            <span class="executive-analysis-icon rounded-3 bg-body-tertiary d-inline-flex align-items-center justify-content-center flex-shrink-0">
                                <i class="bi <?= Html::encode($item['icon']) ?>" aria-hidden="true"></i>
                            </span>
                            <span class="min-w-0 flex-grow-1">
                                <span class="d-block fw-semibold"><?= Html::encode($item['label']) ?></span>
                                <span class="d-block small text-body-secondary"><?= Html::encode($item['description']) ?></span>
                            </span>
                            <?php if ($item['url']): ?>
                                <i class="bi bi-chevron-right text-body-secondary" aria-hidden="true"></i>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">เร็ว ๆ นี้</span>
                            <?php endif; ?>
                        <?= $item['url'] ? '</a>' : '</div>' ?>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>
</div>
