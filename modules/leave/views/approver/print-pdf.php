<?php
use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\Leave[] $models */
/** @var app\modules\leave\models\LeaveSearch $searchModel */
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-size: 11pt; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
    th { background: #e0e0e0; font-weight: bold; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    h1 { font-size: 16pt; margin-bottom: 10px; }
    .summary { margin-bottom: 12px; font-size: 10pt; color: #444; }
</style>
</head>
<body>
<h1>ทะเบียนวันลา</h1>
<div class="summary">พิมพ์เมื่อ <?= date('d/m/Y H:i') ?> — จำนวน <?= count($models) ?> รายการ</div>
<table>
    <thead>
        <tr>
            <th class="text-center" style="width:4%">ลำดับ</th>
            <th style="width:18%">ผู้ขออนุมัติ</th>
            <th style="width:14%">ประเภทการลา</th>
            <th class="text-center" style="width:8%">จำนวนวัน</th>
            <th style="width:20%">เหตุผลการลา</th>
            <th class="text-center" style="width:10%">วันที่เริ่ม</th>
            <th class="text-center" style="width:10%">วันที่สิ้นสุด</th>
            <th style="width:14%">หน่วยงาน</th>
            <th class="text-center" style="width:12%">สถานะใบลา</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($models as $index => $item): ?>
            <?php
            $emp = $item->employee ?? null;
            $dataJson = is_array($item->data_json) ? $item->data_json : (is_string($item->data_json) ? json_decode($item->data_json, true) : []);
            ?>
            <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td><?= Html::encode($emp ? $emp->fullname : '-') ?></td>
                <td><?= Html::encode($item->leaveType->title ?? '-') ?></td>
                <td class="text-center"><?= (float) $item->total_days ?></td>
                <td><?= Html::encode($dataJson['reason'] ?? '-') ?></td>
                <td class="text-center"><?= $item->date_start ? ThaiDateHelper::formatThaiDate($item->date_start) : '-' ?></td>
                <td class="text-center"><?= $item->date_end ? ThaiDateHelper::formatThaiDate($item->date_end) : '-' ?></td>
                <td><?= Html::encode($emp ? $emp->departmentName() : '-') ?></td>
                <td class="text-center"><?= Html::encode($item->leaveStatus ? $item->leaveStatus->title : $item->status) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (count($models) === 0): ?>
            <tr>
                <td colspan="9" class="text-center">ไม่มีรายการวันลา</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</body>
</html>
