<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\development\models\Development $model */
/** @var array $info จาก SiteHelper::getInfo() */

$this->title = 'ใบขอไปราชการ - ' . ($model->topic ?? '');
$emp = $model->createdByEmp ?? $model->emp;
$assignedTo = $model->assignedTo;
$totalDays = 0;
if ($model->date_start && $model->date_end) {
    $s = new \DateTime($model->date_start);
    $e = new \DateTime($model->date_end);
    $totalDays = $s->diff($e)->days + 1;
}
$dataJson = $model->data_json ?? [];
$members = $model->listMemberPrint();
$assignedToName = $assignedTo ? $assignedTo->fullname() : '-';
?>
<style>
.print-official { font-family: 'TH Sarabun New', 'Sarabun', sans-serif; font-size: 16px; line-height: 1.5; color: #000; max-width: 210mm; margin: 0 auto; }
.print-official .doc-header { text-align: center; margin-bottom: 1rem; }
.print-official .doc-title { font-size: 18px; font-weight: bold; margin-bottom: 0.5rem; }
.print-official table { width: 100%; border-collapse: collapse; margin-bottom: 0.75rem; }
.print-official td { padding: 2px 6px; vertical-align: top; }
.print-official .label-cell { width: 140px; font-weight: bold; }
.print-official .section { margin-bottom: 1rem; }
.print-official .signature-row { margin-top: 1.5rem; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
.print-official .signature-block { text-align: center; min-width: 120px; }
.print-official .signature-line { border-bottom: 1px solid #000; margin-bottom: 4px; min-height: 20px; }
.no-print { margin-bottom: 1rem; }
@media print {
    .no-print { display: none !important; }
    .print-official { font-size: 14px; }
}
</style>

<div class="no-print">
    <?= Html::a('<i class="bi bi-arrow-left"></i> ย้อนกลับ', Yii::$app->request->referrer ?: ['/development/default/dashboard'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    <button type="button" class="btn btn-primary btn-sm ms-2" onclick="window.print();">
        <i class="bi bi-printer"></i> พิมพ์
    </button>
</div>

<div class="print-official">
    <div class="doc-header">
        <div class="doc-title"><?= Html::encode($info['company_name'] ?? 'ส่วนราชการ') ?></div>
        <div><?= Html::encode($info['address'] ?? '') ?></div>
    </div>

    <table>
        <tr>
            <td class="label-cell">เลขที่</td>
            <td><?= Html::encode($info['doc_number'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="label-cell">วันที่</td>
            <td><?= ThaiDateHelper::formatThaiDate(date('Y-m-d'), 'medium') ?></td>
        </tr>
    </table>

    <div class="section">
        <p><strong>เรื่อง</strong> ขออนุญาตเดินทางไปราชการ</p>
        <p style="text-indent: 2rem;">ด้วยข้าพเจ้า <strong><?= Html::encode($emp ? $emp->fullname() : $model->emp_id) ?></strong> ตำแหน่ง <strong><?= $emp && method_exists($emp, 'positionName') ? Html::encode($emp->positionName()) : '-' ?></strong> ขออนุญาตเดินทางไปราชการ ดังนี้</p>
    </div>

    <table>
        <tr>
            <td class="label-cell">หัวข้อ / เรื่อง</td>
            <td><?= Html::encode($model->topic) ?></td>
        </tr>
        <tr>
            <td class="label-cell">สถานที่</td>
            <td><?= Html::encode($dataJson['location'] ?? 'ไม่ระบุ') ?></td>
        </tr>
        <tr>
            <td class="label-cell">หน่วยงานที่จัด</td>
            <td><?= Html::encode($dataJson['location_org'] ?? 'ไม่ระบุ') ?></td>
        </tr>
        <tr>
            <td class="label-cell">ตั้งแต่วันที่</td>
            <td><?= $model->date_start ? ThaiDateHelper::formatThaiDate($model->date_start, 'medium') : '-' ?></td>
        </tr>
        <tr>
            <td class="label-cell">ถึงวันที่</td>
            <td><?= $model->date_end ? ThaiDateHelper::formatThaiDate($model->date_end, 'medium') : '-' ?></td>
        </tr>
        <tr>
            <td class="label-cell">วันออกเดินทาง</td>
            <td><?= $model->vehicle_date_start ? ThaiDateHelper::formatThaiDate($model->vehicle_date_start, 'medium') : '-' ?> <?= Html::encode($dataJson['vehicle_time_start'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="label-cell">วันกลับ</td>
            <td><?= $model->vehicle_date_end ? ThaiDateHelper::formatThaiDate($model->vehicle_date_end, 'medium') : '-' ?> <?= Html::encode($dataJson['vehicle_time_end'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="label-cell">ประเภทการรับรอง</td>
            <td><?= Html::encode($dataJson['claim_type_name'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="label-cell">จำนวนวัน</td>
            <td><?= (int) $totalDays ?> วัน</td>
        </tr>
        <tr>
            <td class="label-cell">เดินทางโดย</td>
            <td><?= Html::encode($model->vehicleType ? $model->vehicleType->title : '-') ?></td>
        </tr>
    </table>

    <?php if (!empty($members)): ?>
    <div class="section">
        <p><strong>คณะเดินทาง</strong></p>
        <table>
            <?php foreach ($members as $i => $member): ?>
            <?php $mEmp = $member->emp; ?>
            <tr>
                <td class="label-cell" style="width: 30px;"><?= ($i + 1) ?>.</td>
                <td><?= Html::encode($mEmp ? $mEmp->fullname() : $member->emp_id) ?></td>
                <td><?= $mEmp && method_exists($mEmp, 'positionName') ? Html::encode($mEmp->positionName()) : '-' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <div class="section">
        <p><strong>มอบหมายงานระหว่างเดินทางให้</strong> <?= $assignedTo ? Html::encode($assignedTo->fullname()) : '-' ?> (<?= $assignedTo && method_exists($assignedTo, 'positionName') ? Html::encode($assignedTo->positionName()) : '-' ?>)</p>
    </div>

    <div class="signature-row">
        <div class="signature-block">
            <div class="signature-line" style="width: 120px;"></div>
            <div>(<?= Html::encode($emp ? $emp->fullname() : '-') ?>)</div>
            <div>ผู้ขอ</div>
        </div>
        <div class="signature-block">
            <div class="signature-line" style="width: 120px;"></div>
            <div>(<?= Html::encode($assignedToName) ?>)</div>
            <div>ผู้รับมอบหมาย</div>
        </div>
    </div>

    <?php if ($model->approveDate()): ?>
    <p class="mt-3 small">วันที่อนุมัติ: <?= ThaiDateHelper::formatThaiDate($model->approveDate(), 'medium') ?></p>
    <?php endif; ?>
</div>
