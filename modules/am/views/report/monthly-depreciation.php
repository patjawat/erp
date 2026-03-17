<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;
use app\widgets\TomSelectWidget;

$assetTypeItems = ['' => 'ทั้งหมด'];
foreach (isset($assetTypes) ? $assetTypes : [] as $t) {
    $assetTypeItems[(string)($t->code ?? $t->id)] = $t->title ?? '';
}

/** @var yii\web\View $this */
/** @var app\modules\am\models\AmAssetDepreciationMonthly[] $records */
/** @var float $totalDepreciation */
/** @var int $fiscalYear */
/** @var int $month */
/** @var string $periodLabel */
/** @var array $thaiMonths */
/** @var bool $tableExists */
/** @var array $summaryByType */
/** @var string|mixed $assetTypeId */
/** @var app\modules\am\models\AssetType[] $assetTypes */

$this->title = 'รายงานค่าเสื่อมรายเดือน';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'รายงาน', 'url' => ['/am/report/index']];
$this->params['breadcrumbs'][] = $this->title;

// ช่อง filter ประเภทครุภัณฑ์ ให้ความกว้างคงที่ (Tom Select สร้าง .ts-wrapper ภายหลัง)
$this->registerCss('.am-monthly-depreciation-asset-type-wrap { min-width: 14rem; } .am-monthly-depreciation-asset-type-wrap .ts-wrapper { width: 100% !important; }');
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <i data-lucide="file-text"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0">รายงานค่าเสื่อมรายเดือนสำหรับนำส่งหน่วยงานบัญชี (กรมบัญชีกลาง)</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="fa-solid fa-file-pdf me-1"></i> พิมพ์รายงาน PDF', ['monthly-depreciation', 'fiscal_year' => $fiscalYear, 'month' => $month, 'asset_type_id' => $assetTypeId ?? '', 'format' => 'pdf'], ['class' => 'btn btn-primary', 'target' => '_blank']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid px-2 px-md-3 pb-3">
    <?php if (!$tableExists): ?>
        <div class="alert alert-warning">ตาราง am_asset_depreciation_monthly ยังไม่มี กรุณารัน migration ก่อน</div>
    <?php else: ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header border-bottom">
                <h6 class="text-uppercase text-secondary mb-0 d-flex align-items-center gap-2"><i class="fa-solid fa-calendar-days"></i> เลือกเดือน</h6>
            </div>
            <div class="card-body">
                <form method="get" action="<?= \yii\helpers\Url::to(['monthly-depreciation']) ?>" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label class="form-label">ปี (พ.ศ.)</label>
                        <select name="fiscal_year" class="form-select">
                            <?php for ($y = date('Y') + 1; $y >= date('Y') - 5; $y--): ?>
                                <option value="<?= $y ?>" <?= (int) $fiscalYear === $y ? 'selected' : '' ?>><?= $y + 543 ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label">เดือน</label>
                        <select name="month" class="form-select">
                            <?php foreach ($thaiMonths as $m => $label): ?>
                                <option value="<?= $m ?>" <?= (int) $month === $m ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label">ประเภทครุภัณฑ์</label>
                        <div class="am-monthly-depreciation-asset-type-wrap">
                            <?= TomSelectWidget::widget([
                                'name' => 'asset_type_id',
                                'id' => 'asset_type_id',
                                'value' => isset($assetTypeId) ? (string) $assetTypeId : '',
                                'options' => ['class' => 'form-select'],
                                'items' => $assetTypeItems,
                                'clientOptions' => [
                                    'placeholder' => 'ทั้งหมด',
                                    'allowEmptyOption' => true,
                                ],
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary">แสดง</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h6 class="text-uppercase text-secondary mb-0 d-flex align-items-center gap-2"><i class="fa-solid fa-file-lines"></i> รายงานค่าเสื่อมรายเดือน — <?= Html::encode($periodLabel) ?></h6>
                <?= Html::a('<i class="fa-solid fa-file-pdf me-1"></i> พิมพ์ PDF', ['monthly-depreciation', 'fiscal_year' => $fiscalYear, 'month' => $month, 'asset_type_id' => $assetTypeId ?? '', 'format' => 'pdf'], ['class' => 'btn btn-sm btn-primary', 'target' => '_blank']) ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($records)): ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-3">ยังไม่มีข้อมูลค่าเสื่อมรายเดือนของเดือน <strong><?= Html::encode($periodLabel) ?></strong></p>
                        <p class="small text-muted mb-3">ถ้าคุณเพิ่งประมวลผลแล้ว ให้เลือก <strong>ปี และเดือน</strong> ด้านบนให้ตรงกับเดือนที่คุณรัน แล้วกด <strong>แสดง</strong></p>
                        <?= Html::a('<i class="fa-solid fa-play me-1"></i> รันประมวลผลสำหรับเดือนนี้', ['/am/depreciation/monthly-processing', 'fiscal_year' => $fiscalYear, 'month' => $month], ['class' => 'btn btn-primary']) ?>
                    </div>
                <?php else: ?>
                    <?php if (!empty($summaryByType)): ?>
                    <div class="p-3 border-bottom">
                        <h6 class="text-uppercase text-secondary mb-2">รวมมูลค่าแยกตามประเภทครุภัณฑ์</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:3rem;">ลำดับ</th>
                                        <th style="min-width:11rem;width:11rem;">ประเภทครุภัณฑ์</th>
                                        <th class="text-center">จำนวนรายการ</th>
                                        <th class="text-end">มูลค่าต้นเดือน</th>
                                        <th class="text-end">ค่าเสื่อมเดือน</th>
                                        <th class="text-end">ค่าเสื่อมสะสม</th>
                                        <th class="text-end">มูลค่าปลายเดือน</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider">
                                    <?php $no = 1; foreach ($summaryByType as $typeName => $row): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><?= Html::encode($typeName) ?></td>
                                            <td class="text-center"><?= (int) $row['count'] ?></td>
                                            <td class="text-end fw-bold"><?= number_format($row['beginning_value'], 2) ?></td>
                                            <td class="text-end fw-bold"><?= number_format($row['depreciation_amount'], 2) ?></td>
                                            <td class="text-end fw-bold"><?= number_format($row['accumulated_depreciation'], 2) ?></td>
                                            <td class="text-end fw-bold"><?= number_format($row['remaining_value'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center">ลำดับ</th>
                                    <th scope="col">รหัสครุภัณฑ์</th>
                                    <th scope="col">ชื่อครุภัณฑ์</th>
                                    <th scope="col" style="min-width:11rem;width:11rem;">ประเภทครุภัณฑ์</th>
                                    <th scope="col">วันที่รับเข้า</th>
                                    <th scope="col" class="text-center">อายุการใช้งาน (ปี)</th>
                                    <th scope="col" class="text-center">ปีที่ใช้มาแล้ว</th>
                                    <th scope="col" class="text-end">มูลค่าต้นเดือน</th>
                                    <th scope="col" class="text-center">วันใช้</th>
                                    <th scope="col" class="text-end">ค่าเสื่อมเดือน</th>
                                    <th scope="col" class="text-end">ค่าเสื่อมสะสม</th>
                                    <th scope="col" class="text-end">มูลค่าปลายเดือน</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle table-group-divider">
                                <?php $no = 1; foreach ($records as $r): ?>
                                    <?php $a = $r->asset; ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= Html::encode($a->code ?? '') ?></td>
                                        <td><?= Html::encode($a->asset_name ?? $a->AssetitemName() ?? '') ?></td>
                                        <td><?= Html::encode($a->assetType->title ?? $a->AssetTypeName() ?? '-') ?></td>
                                        <td><?= $a->receive_date ? ThaiDateHelper::formatThaiDate($a->receive_date) : '-' ?></td>
                                        <td class="text-center"><?= (int) ($a->useful_life ?? 0) ?></td>
                                        <td class="text-center"><?php
                                            $yearsUsed = '-';
                                            if (!empty($a->receive_date)) {
                                                $reportEnd = new \DateTime($fiscalYear . '-' . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '-01');
                                                $reportEnd->modify('last day of this month');
                                                $receive = new \DateTime($a->receive_date);
                                                if ($receive <= $reportEnd) {
                                                    $diff = $receive->diff($reportEnd);
                                                    $yearsUsed = (int) $diff->y;
                                                } else {
                                                    $yearsUsed = 0;
                                                }
                                            }
                                            echo $yearsUsed;
                                        ?></td>
                                        <td class="text-end fw-bold"><?= number_format((float) $r->beginning_value, 2) ?></td>
                                        <td class="text-center"><?= (int) $r->days_used ?></td>
                                        <td class="text-end fw-bold"><?= number_format((float) $r->depreciation_amount, 2) ?></td>
                                        <td class="text-end fw-bold"><?= number_format((float) $r->accumulated_depreciation, 2) ?></td>
                                        <td class="text-end fw-bold"><?= number_format((float) $r->remaining_value, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="9" class="text-end fw-bold">รวมค่าเสื่อมประจำเดือน</td>
                                    <td class="text-end fw-bold"><?= number_format($totalDepreciation, 2) ?></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
