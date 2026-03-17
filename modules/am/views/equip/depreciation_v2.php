<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;
use app\modules\am\services\DepreciationScheduleService;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var array $scheduleData */
/** @var array $scheduleDataMonthly */

$this->title = 'ค่าเสื่อมราคา (ชุดใหม่)';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'ครุภัณฑ์', 'url' => ['/am/equip']];
$this->params['breadcrumbs'][] = ['label' => $model->asset_name ?? $model->code, 'url' => ['view-asset', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <i data-lucide="trending-down"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0">วิธีเส้นตรง ตามมาตรฐานการบัญชีภาครัฐ — มูลค่าซาก 1 บาท, คำนวณรายปี</p>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<div class="d-flex align-items-center gap-2">
    <button type="button" onclick="window.print();" class="btn btn-outline-primary d-flex align-items-center gap-2 no-print">
        <i data-lucide="printer" style="width:1rem;height:1rem;"></i>
        พิมพ์ค่าเสื่อม
    </button>
    <?= $this->render('@app/modules/am/views/asset/_action_menu', ['model' => $model]) ?>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid px-2 px-md-3 pb-3 depreciation-print-area">
    <div class="d-none print-only-header mb-3 py-2 border-bottom">
        <h5 class="mb-1">ตารางค่าเสื่อมราคา (ชุดใหม่) — รายปี และ รายเดือน</h5>
        <div class="small text-muted">ครุภัณฑ์: <?= Html::encode($model->asset_name ?? $model->code) ?> — รหัส <?= Html::encode($model->code) ?></div>
        <div class="small text-muted">วันที่พิมพ์: <?= date('d/m/Y H:i') ?> น.</div>
    </div>
    <?= $this->render('@app/modules/am/views/asset/_title', ['model' => $model]) ?>

    <div class="row g-3 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom">
                    <h6 class="text-uppercase text-secondary mb-0">ข้อมูลพื้นฐาน (ชุดใหม่)</h6>
                </div>
                <div class="card-body">
                    <?php if (!$scheduleData['can_calculate']): ?>
                        <div class="alert alert-info mb-0 d-flex align-items-center gap-2">
                            <i data-lucide="info" class="flex-shrink-0"></i>
                            <div>
                                <strong>ยังคำนวณไม่ได้</strong><br>
                                <span class="small">กรุณาระบุ <strong>อายุการใช้งาน (ปี)</strong> ในฟอร์มแก้ไขครุภัณฑ์ เพื่อดูตารางค่าเสื่อม (มูลค่าซากตามมาตรฐาน = 1 บาท)</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="text-muted small mb-1">ราคาทุน</div>
                                <div class="fw-semibold"><?= number_format($model->price, 2) ?> บาท</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted small mb-1">วันที่รับเข้า</div>
                                <div class="fw-semibold"><?= $model->receive_date ? ThaiDateHelper::formatThaiDate($model->receive_date) : '-' ?></div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted small mb-1">อายุการใช้งาน (ปี)</div>
                                <div class="fw-semibold"><?= (int) $model->useful_life ?> ปี</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted small mb-1">มูลค่าซาก (ตามมาตรฐาน)</div>
                                <div class="fw-semibold">1.00 บาท</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted small mb-1">วิธีคำนวณ</div>
                                <div class="fw-semibold"><?= Html::encode(DepreciationScheduleService::getMethodLabel($model->depreciation_method)) ?></div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted small mb-1">ค่าเสื่อมต่อปี</div>
                                <div class="fw-semibold"><?= number_format($scheduleData['annual_amount'], 2) ?> บาท</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted small mb-1">ค่าเสื่อมต่อเดือน</div>
                                <div class="fw-semibold"><?= number_format($scheduleData['monthly_amount'], 2) ?> บาท</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <div class="card border shadow-sm h-100">
                                    <div class="card-header border-bottom bg-light bg-opacity-50">
                                        <h6 class="text-uppercase text-secondary mb-0">รายปี</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive depreciation-year-scroll">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th scope="col" class="text-center" style="width:3rem;">ลำดับ</th>
                                                        <th scope="col">ปี (พ.ศ.)</th>
                                                        <th scope="col" class="text-end">มูลค่าต้นปี</th>
                                                        <th scope="col" class="text-end">ค่าเสื่อมประจำปี</th>
                                                        <th scope="col" class="text-end">ค่าเสื่อมสะสม</th>
                                                        <th scope="col" class="text-end">มูลค่าปลายปี</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="align-middle table-group-divider">
                                                    <?php foreach ($scheduleData['schedule'] as $index => $row): ?>
                                                        <tr>
                                                            <td class="text-center"><?= $index + 1 ?></td>
                                                            <td><?= Html::encode($row['year_label']) ?></td>
                                                            <td class="text-end"><?= number_format($row['begin_value'], 2) ?></td>
                                                            <td class="text-end"><?= number_format($row['annual_depreciation'], 2) ?></td>
                                                            <td class="text-end"><?= number_format($row['accumulated'], 2) ?></td>
                                                            <td class="text-end"><?= number_format($row['end_value'], 2) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="card border shadow-sm h-100">
                                    <div class="card-header border-bottom bg-light bg-opacity-50">
                                        <h6 class="text-uppercase text-secondary mb-0">รายเดือน</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (!empty($scheduleDataMonthly['daily_amount'])): ?>
                                        <p class="text-muted small mb-2 px-3 pt-2"><i class="fa-solid fa-info-circle me-1"></i> เดือนแรกตามวันใช้จริง; ค่าเสื่อมต่อวัน = <?= number_format($scheduleDataMonthly['daily_amount'], 2) ?> บาท</p>
                                        <?php endif; ?>
                                        <div class="table-responsive depreciation-monthly-scroll">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th scope="col" class="text-center" style="width:3rem;">ลำดับ</th>
                                                        <th scope="col">เดือน (พ.ศ.)</th>
                                                        <th scope="col" class="text-center">จำนวนวันใช้</th>
                                                        <th scope="col" class="text-end">มูลค่าต้นเดือน</th>
                                                        <th scope="col" class="text-end">ค่าเสื่อม</th>
                                                        <th scope="col" class="text-end">ค่าเสื่อมสะสม</th>
                                                        <th scope="col" class="text-end">มูลค่าปลายเดือน</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="align-middle table-group-divider">
                                                    <?php foreach ($scheduleDataMonthly['schedule'] as $index => $row): ?>
                                                        <tr class="<?= !empty($row['is_first_month_of_year']) ? 'table-warning fw-bold' : '' ?>">
                                                            <td class="text-center"><?= $index + 1 ?></td>
                                                            <td><?= Html::encode($row['period_label']) ?></td>
                                                            <td class="text-center"><?= (int) ($row['days_used'] ?? 30) ?></td>
                                                            <td class="text-end"><?= number_format($row['begin_value'], 2) ?></td>
                                                            <td class="text-end"><?= number_format($row['depreciation'], 2) ?></td>
                                                            <td class="text-end"><?= number_format($row['accumulated'], 2) ?></td>
                                                            <td class="text-end"><?= number_format($row['end_value'], 2) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.depreciation-year-scroll,
.depreciation-monthly-scroll { max-height: min(70vh, 500px); overflow-y: auto; }
@media print {
    .no-print { display: none !important; }
    .print-only-header { display: block !important; }
    .depreciation-print-area .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
    .depreciation-year-scroll,
    .depreciation-monthly-scroll { max-height: none; }
    body * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>
