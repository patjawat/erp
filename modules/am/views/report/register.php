<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\widgets\TomSelectWidget;

/** @var yii\web\View $this */
/** @var int $thaiYear */
/** @var string $assetTypeId */
/** @var \yii\base\Model $dateFilterModel */
/** @var app\modules\am\models\AssetType[] $assetTypes */
/** @var array $reportData */

$this->title = 'รายงานครุภัณฑ์คงเหลือประจำปี';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;

$rows = $reportData['rows'] ?? [];
$summary = $reportData['summary'] ?? [];
$organizationName = (string) ($reportData['organizationName'] ?? '-');
$periodStartLabel = (string) ($reportData['periodStartLabel'] ?? ('1 ต.ค. ' . ($thaiYear - 1)));
$periodEndLabel = (string) ($reportData['periodEndLabel'] ?? ('30 ก.ย. ' . $thaiYear));
$surveyLabel = (string) ($reportData['surveyLabel'] ?? ('1 ต.ค. ' . $thaiYear));
$finishLabel = (string) ($reportData['finishLabel'] ?? ('31 ต.ค. ' . $thaiYear));
$assetTypeItems = ['' => 'ทั้งหมด'];
foreach (isset($assetTypes) ? $assetTypes : [] as $type) {
    $code = (string) ($type->code ?? $type->id ?? '');
    if ($code === '') {
        continue;
    }
    $assetTypeItems[$code] = (string) ($type->title ?? $type->name ?? $code);
}

$money = static fn($value): string => number_format((float) $value, 2);

$totalCount = count($rows);
$totalCost = array_sum(array_map(static fn($row) => (float) ($row['cost'] ?? 0), $rows));
$totalAccumulated = array_sum(array_map(static fn($row) => (float) ($row['accumulated_current'] ?? 0), $rows));
$totalRemaining = array_sum(array_map(static fn($row) => (float) ($row['remaining_current'] ?? 0), $rows));

$conditionLabelMap = [
    'good' => 'จำเป็น',
    'fair' => 'จำเป็น',
    'worn' => 'เสื่อมสภาพ',
    'damaged' => 'ชำรุด',
];

$conditionColumn = static function (?string $condition, string $target) use ($conditionLabelMap): string {
    $label = $conditionLabelMap[$condition ?? ''] ?? '';
    return $label === $target ? '/' : '';
};

$this->registerCss(<<<CSS
.am-register-report {
    --report-border: #1f2937;
    --report-line: #374151;
    --report-bg: #f5f7fb;
    font-family: "TH Sarabun New", "Sarabun", "Tahoma", sans-serif;
}
.am-register-report .report-shell {
    background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 1rem;
    padding: 1rem;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
}
.am-register-report .report-sheet {
    background: #fff;
    border: 1px solid var(--report-border);
    border-radius: .75rem;
    padding: 1rem 1rem .75rem;
}
.am-register-report .report-meta {
    display: grid;
    grid-template-columns: 1.35fr 1fr 1fr;
    gap: .75rem;
    align-items: start;
    margin-bottom: .75rem;
}
.am-register-report .report-meta > div {
    min-width: 0;
}
.am-register-report .report-kicker {
    font-size: 18px;
    line-height: 1.1;
    font-weight: 500;
    color: #111827;
    margin-bottom: .25rem;
}
.am-register-report .report-org {
    font-size: 16px;
    line-height: 1.2;
    font-weight: 500;
    color: #111827;
}
.am-register-report .report-unit {
    font-size: 15px;
    line-height: 1.25;
    color: #111827;
    margin-top: .15rem;
}
.am-register-report .report-title {
    font-size: 20px;
    line-height: 1.15;
    font-weight: 500;
    text-align: center;
    padding: .35rem .5rem .15rem;
}
.am-register-report .report-period {
    font-size: 15px;
    line-height: 1.3;
    text-align: right;
    color: #111827;
}
.am-register-report .report-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .5rem;
    margin-bottom: .75rem;
}
.am-register-report .register-filter-wrap {
    min-width: 16rem;
}
.am-register-report .register-filter-wrap .ts-wrapper {
    width: 100% !important;
}
.am-register-report .summary-card {
    border: 1px solid rgba(15, 23, 42, 0.12);
    border-radius: .75rem;
    background: #f8fafc;
    padding: .65rem .75rem;
}
.am-register-report .summary-label {
    font-size: 13px;
    color: #64748b;
    margin-bottom: .15rem;
}
.am-register-report .summary-value {
    font-size: 20px;
    font-weight: 500;
    line-height: 1.1;
    color: #0f172a;
}
.am-register-report .report-table-wrap {
    border: 1px solid var(--report-border);
    overflow-x: auto;
    background: #fff;
}
.am-register-report table.report-table {
    width: 100%;
    min-width: 1800px;
    table-layout: fixed;
    border-collapse: collapse;
    color: #111827;
    font-size: 13px;
}
.am-register-report .report-table th,
.am-register-report .report-table td {
    border: 1px solid var(--report-border);
    padding: .32rem .35rem;
    vertical-align: middle;
    line-height: 1.15;
}
.am-register-report .report-table th {
    text-align: center;
    font-weight: 500;
    background: #f8fafc;
}
.am-register-report .report-table thead tr.group-row th {
    font-size: 13px;
    padding-top: .4rem;
    padding-bottom: .4rem;
}
.am-register-report .report-table thead tr.sub-row th {
    font-size: 12px;
    font-weight: 500;
}
.am-register-report .report-table tbody td {
    background: #fff;
}
.am-register-report .report-table tbody tr:nth-child(even) td {
    background: #fcfcfd;
}
.am-register-report .text-right {
    text-align: right;
}
.am-register-report .text-center {
    text-align: center;
}
.am-register-report .nowrap {
    white-space: nowrap;
}
.am-register-report .asset-name {
    padding-left: .65rem;
}
.am-register-report tfoot td {
    font-weight: 500;
    background: #f8fafc;
}
@media print {
    .am-register-report .no-print,
    .am-register-report .report-actions {
        display: none !important;
    }
    .am-register-report {
        background: #fff !important;
    }
    .am-register-report .report-shell {
        box-shadow: none;
        border: none;
        padding: 0;
        background: #fff;
    }
    .am-register-report .report-sheet {
        border: none;
        border-radius: 0;
        padding: 0;
    }
    .am-register-report .report-table-wrap {
        overflow: visible;
    }
}
CSS);
?>

<div class="container-fluid px-2 px-md-3 pb-3 am-register-report">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 no-print report-actions">
        <div>
            <h4 class="mb-1"><?= Html::encode($this->title) ?></h4>
            <div class="text-muted small">จัดรูปแบบรายงานให้ใกล้เคียงฟอร์มเอกสารราชการในภาพตัวอย่าง</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-file-excel me-1"></i> Export Excel', ['register', 'format' => 'xlsx', 'year' => $thaiYear, 'asset_type_id' => $assetTypeId ?? '', 'date_filter' => $dateFilterModel->date_filter ?? '', 'date_start' => $dateFilterModel->date_start ?? '', 'date_end' => $dateFilterModel->date_end ?? ''], ['class' => 'btn btn-outline-success']) ?>
            <?= Html::a('<i class="fa-solid fa-file-csv me-1"></i> Export CSV', ['register', 'format' => 'csv', 'year' => $thaiYear, 'asset_type_id' => $assetTypeId ?? '', 'date_filter' => $dateFilterModel->date_filter ?? '', 'date_start' => $dateFilterModel->date_start ?? '', 'date_end' => $dateFilterModel->date_end ?? ''], ['class' => 'btn btn-outline-primary']) ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3 no-print">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => Url::to(['register']), 'options' => ['class' => 'row g-3 align-items-end']]); ?>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label fw-semibold">ปีงบประมาณ (พ.ศ.)</label>
                    <input type="number" name="year" id="thaiYear" class="form-control" value="<?= Html::encode($thaiYear) ?>" min="2500" max="2999">
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label fw-semibold">ประเภททรัพย์สิน</label>
                    <div class="register-filter-wrap">
                        <?= TomSelectWidget::widget([
                            'name' => 'asset_type_id',
                            'id' => 'asset_type_id',
                            'value' => (string) ($assetTypeId ?? ''),
                            'options' => ['class' => 'form-select'],
                            'items' => $assetTypeItems,
                            'clientOptions' => [
                                'placeholder' => 'ทั้งหมด',
                                'allowEmptyOption' => true,
                            ],
                        ]) ?>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $dateFilterModel, 'label' => false]) ?>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $dateFilterModel, 'label' => false]) ?>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $dateFilterModel, 'label' => false]) ?>
                </div>
                <div class="col-12 col-md-4 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">แสดงรายงาน</button>
                </div>
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        ใช้ข้อมูลจาก <?= Html::encode($organizationName) ?> สำหรับงวดตั้งแต่ <?= Html::encode($periodStartLabel) ?> ถึง <?= Html::encode($periodEndLabel) ?>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="report-shell">
        <div class="report-sheet">
            <div class="report-meta">
                <div>
                    <div class="report-kicker">บัญชีแสดงการ - จ่ายพัสดุคงเหลือ</div>
                    <div class="report-org"><?= Html::encode($organizationName) ?></div>
                    <div class="report-unit">อาคารสำนักงาน</div>
                </div>
                <div class="report-title">
                    แบบฟอร์มรายงานพัสดุคงเหลือประจำปี <?= Html::encode($thaiYear) ?> (ครุภัณฑ์ตามเกณฑ์)
                </div>
                <div class="report-period">
                    งวดตั้งแต่วันที่ <?= Html::encode($periodStartLabel) ?> - <?= Html::encode($periodEndLabel) ?><br>
                    สำรวจเมื่อ <?= Html::encode($surveyLabel) ?><br>
                    วันเสร็จสิ้น <?= Html::encode($finishLabel) ?>
                </div>
            </div>

            <div class="report-summary">
                <div class="summary-card">
                    <div class="summary-label">จำนวนรายการทั้งหมด</div>
                    <div class="summary-value"><?= number_format($totalCount) ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">ราคาทุนรวม</div>
                    <div class="summary-value"><?= $money($totalCost) ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">ค่าเสื่อมราคาสะสม</div>
                    <div class="summary-value"><?= $money($totalAccumulated) ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">มูลค่าคงเหลือ</div>
                    <div class="summary-value"><?= $money($totalRemaining) ?></div>
                </div>
            </div>

            <div class="report-table-wrap">
                <table class="report-table">
                    <colgroup>
                        <col style="width:50px">
                        <col style="width:150px">
                        <col style="width:110px">
                        <col style="width:320px">
                        <col style="width:120px">
                        <col style="width:110px">
                        <col style="width:80px">
                        <col style="width:110px">
                        <col style="width:80px">
                        <col style="width:120px">
                        <col style="width:110px">
                        <col style="width:120px">
                        <col style="width:120px">
                        <col style="width:70px">
                        <col style="width:70px">
                        <col style="width:70px">
                        <col style="width:70px">
                        <col style="width:70px">
                        <col style="width:70px">
                    </colgroup>
                    <thead>
                        <tr class="group-row">
                            <th rowspan="2">ลำดับที่</th>
                            <th rowspan="2">รหัสครุภัณฑ์</th>
                            <th rowspan="2">วัน/เดือน/ปี<br>ที่ได้มา</th>
                            <th rowspan="2">รายการครุภัณฑ์</th>
                            <th rowspan="2">ราคาทุนทรัพย์สิน<br>ที่ซื้อมา</th>
                            <th colspan="2">รับใหม่</th>
                            <th colspan="2">จ่ายทั้งหมด</th>
                            <th rowspan="2">ค่าเสื่อมราคาสะสม</th>
                            <th rowspan="2">คงเหลือ<br>เมื่อ <br><?= Html::encode($periodEndLabel) ?></th>
                            <th colspan="2">ผลการตรวจ</th>
                            <th colspan="2">ถ้าไม่ถูกต้อง</th>
                            <th colspan="3">สภาพ</th>
                        </tr>
                        <tr class="sub-row">
                            <th><?= Html::encode($periodStartLabel) ?><br>ถึง <br><?= Html::encode($periodEndLabel) ?></th>
                            <th>จำนวนเงิน</th>
                            <th><?= Html::encode($periodStartLabel) ?><br>ถึง <br><?= Html::encode($periodEndLabel) ?></th>
                            <th>จำนวนเงิน</th>
                            <th>ถูกต้อง</th>
                            <th>ไม่ถูกต้อง</th>
                            <th>ขาด</th>
                            <th>เกิน</th>
                            <th>ชำรุด</th>
                            <th>เสื่อมสภาพ</th>
                            <th>ไม่</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="18" class="text-center text-muted py-4">ไม่พบข้อมูลสำหรับรายงานปีนี้</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $index => $row): ?>
                                <?php $asset = $row['asset'] ?? null; ?>
                                <?php $condition = (string) ($row['condition'] ?? ''); ?>
                                <?php $isCorrect = in_array($condition, ['good', 'fair'], true) ? '/' : ''; ?>
                                <?php $isIncorrect = in_array($condition, ['worn', 'damaged'], true) ? '/' : ''; ?>
                                <?php $assetCode = (string) ($asset->code ?? $row['code'] ?? ''); ?>
                                <?php $assetDate = $asset->receive_date ? \app\components\ThaiDateHelper::formatThaiDate($asset->receive_date) : ($row['receive_date'] ?? '-'); ?>
                                <?php $assetName = trim((string) ($asset->asset_name ?? $asset->AssetitemName() ?? $row['name'] ?? '')); ?>
                                <?php $assetType = (string) ($asset->assetType->title ?? $asset->AssetTypeName() ?? ''); ?>
                                <?php $assetPrice = (float) ($asset->price ?? ($row['cost'] ?? 0)); ?>
                                <?php $assetAccumulated = (float) ($row['accumulated_current'] ?? 0); ?>
                                <?php $assetRemaining = (float) ($row['remaining_current'] ?? 0); ?>
                                <tr>
                                    <td class="text-center nowrap"><?= $index + 1 ?></td>
                                    <td class="nowrap"><?= Html::encode($assetCode) ?></td>
                                    <td class="text-center nowrap"><?= Html::encode($assetDate) ?></td>
                                    <td class="asset-name">
                                        <div class="fw-semibold"><?= Html::encode($assetName !== '' ? $assetName : '-') ?></div>
                                        <?php if ($assetType !== ''): ?>
                                            <div class="text-muted small"><?= Html::encode($assetType) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right nowrap"><?= $money($assetPrice) ?></td>
                                    <td class="text-center"></td>
                                    <td class="text-right nowrap"></td>
                                    <td class="text-center"></td>
                                    <td class="text-right nowrap"></td>
                                    <td class="text-right nowrap"><?= $money($assetAccumulated) ?></td>
                                    <td class="text-right nowrap"><?= $money($assetRemaining) ?></td>
                                    <td class="text-center"><?= $isCorrect ?></td>
                                    <td class="text-center"><?= $isIncorrect ?></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"><?= Html::encode($conditionColumn($condition, 'ชำรุด')) ?></td>
                                    <td class="text-center"><?= Html::encode($conditionColumn($condition, 'เสื่อมสภาพ')) ?></td>
                                    <td class="text-center"></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-center">รวมทั้งสิ้น</td>
                            <td class="text-right nowrap"><?= $money($totalCost) ?></td>
                            <td></td>
                            <td></td>
                            <td class="text-right nowrap"><?= $money($totalAccumulated) ?></td>
                            <td class="text-right nowrap"><?= $money($totalRemaining) ?></td>
                            <td colspan="9"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <?php if (!empty($summary)): ?>
        <div class="mt-3 small text-muted no-print">
            หมายเหตุ: รายงานนี้สรุปจาก <?= count($summary) ?> กลุ่มหลัก และแสดงรายการตามข้อมูลทรัพย์สินที่พบในระบบ
        </div>
    <?php endif; ?>
</div>
