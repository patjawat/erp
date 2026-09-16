<?php

use app\modules\complaint\models\Complaint;
use yii\helpers\Html;
use yii\web\View;

/** @var View $this */
/** @var array $m */
/** @var int $fiscalYear */
/** @var int[] $years */

$this->title = 'รายงานสรุปเรื่องร้องเรียน';
$statusLabels = Complaint::statusLabels();
$kpi = $m['kpi'];
$fmt = static function ($k) {
    if ($k['value'] === null) {
        return '—';
    }
    return number_format((float) $k['value'], $k['unit'] === 'ต่อหมื่น' ? 2 : 1) . ' ' . $k['unit'];
};
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ปีงบประมาณ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
        <h1 class="h4 fw-semibold mb-0"><i class="bi bi-file-earmark-bar-graph me-1"></i> รายงานสรุป</h1>
        <div class="d-flex gap-2">
            <?= Html::beginForm(['report'], 'get', ['class' => 'd-flex align-items-center gap-2 mb-0']) ?>
                <label class="small text-body-secondary mb-0">ปีงบ</label>
                <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()']) ?>
            <?= Html::endForm() ?>
            <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> พิมพ์</button>
        </div>
    </div>

    <div class="mb-3 d-print-none"><?= $this->render('@app/modules/complaint/menu', ['active' => 'report']) ?></div>

    <div class="card border shadow-sm print-area">
        <div class="card-body">
            <div class="text-center mb-3">
                <h2 class="h5 fw-bold mb-1">รายงานสรุปการจัดการเรื่องร้องเรียน</h2>
                <div class="text-body-secondary">ปีงบประมาณ <?= $fiscalYear ?> · จำนวนเรื่องทั้งหมด <?= number_format($m['total']) ?> เรื่อง<?php if ($m['visits']): ?> · <?= number_format($m['visits']) ?> visit<?php endif; ?></div>
                <div class="small text-body-secondary">พิมพ์เมื่อ <?= \app\components\AppHelper::convertToThai(date('Y-m-d')) ?></div>
            </div>

            <h3 class="h6 fw-semibold border-bottom pb-1"><i class="bi bi-graph-up me-1"></i> ตัวชี้วัด (KPI)</h3>
            <table class="table table-sm table-bordered mb-4">
                <thead class="table-light"><tr><th style="width:80px">รหัส</th><th>ตัวชี้วัด</th><th style="width:160px" class="text-end">ค่า</th><th style="width:140px">ฐานคำนวณ</th></tr></thead>
                <tbody>
                    <?php foreach ($kpi as $code => $k): ?>
                        <tr>
                            <td class="fw-semibold"><?= $code ?></td>
                            <td><?= Html::encode($k['label']) ?></td>
                            <td class="text-end fw-semibold"><?= $fmt($k) ?></td>
                            <td class="small text-body-secondary"><?= Html::encode($k['raw']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="row g-4">
                <div class="col-md-6">
                    <h3 class="h6 fw-semibold border-bottom pb-1"><i class="bi bi-diagram-2 me-1"></i> จำแนกตามสถานะ</h3>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php foreach ($m['statusCount'] as $st => $n): ?>
                                <tr><td><?= Html::encode($statusLabels[$st] ?? $st) ?></td><td class="text-end"><?= number_format($n) ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h3 class="h6 fw-semibold border-bottom pb-1"><i class="bi bi-exclamation-triangle me-1"></i> จำแนกตามระดับ</h3>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php foreach ($m['levelCount'] as $lv => $n): ?>
                                <tr><td>ระดับ <?= $lv ?></td><td class="text-end"><?= number_format($n) ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h3 class="h6 fw-semibold border-bottom pb-1"><i class="bi bi-tags me-1"></i> จำแนกตามประเภท</h3>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php if (!$m['typeCount']): ?><tr><td class="text-body-secondary">— ไม่มีข้อมูล —</td></tr><?php endif; ?>
                            <?php foreach ($m['typeCount'] as $name => $n): ?>
                                <tr><td><?= Html::encode($name) ?></td><td class="text-end"><?= number_format($n) ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h3 class="h6 fw-semibold border-bottom pb-1"><i class="bi bi-signpost me-1"></i> จำแนกตามช่องทาง</h3>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php if (!$m['channelCount']): ?><tr><td class="text-body-secondary">— ไม่มีข้อมูล —</td></tr><?php endif; ?>
                            <?php foreach ($m['channelCount'] as $name => $n): ?>
                                <tr><td><?= Html::encode($name) ?></td><td class="text-end"><?= number_format($n) ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$css = <<<'CSS'
@media print {
  .d-print-none { display: none !important; }
  .print-area { border: 0 !important; box-shadow: none !important; }
  body { background: #fff !important; }
}
CSS;
$this->registerCss($css);
?>
