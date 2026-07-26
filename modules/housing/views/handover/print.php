<?php

use app\modules\housing\models\AssetAssignment;
use yii\helpers\Html;

$occupancy = $model->occupancy;
$locationName = implode(' / ', array_filter([
    $occupancy?->unit?->building?->name,
    $occupancy?->unit?->floor?->name,
    $occupancy?->unit?->name,
    $occupancy?->room?->name,
]));
?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><title><?= Html::encode($model->handover_no) ?></title>
<style>body{font-family:Tahoma,sans-serif;color:#111;font-size:14px;line-height:1.55;margin:32px}h1{text-align:center;font-size:20px;margin:0 0 6px}.doc-no{text-align:center;margin-bottom:24px}.row{display:flex;margin:7px 0}.label{width:180px;font-weight:bold}table{width:100%;border-collapse:collapse;margin:18px 0}th,td{border:1px solid #555;padding:7px;text-align:left}th{background:#f1f1f1}.signatures{display:flex;gap:70px;margin-top:56px}.signature{flex:1;text-align:center}.line{border-bottom:1px solid #333;height:32px;margin-bottom:6px}@media print{body{margin:12mm}.no-print{display:none}}button{padding:8px 16px}</style></head><body>
<button class="no-print" onclick="window.print()">พิมพ์เอกสาร</button>
<h1>เอกสารรับมอบบ้านพัก/ห้องพัก</h1><div class="doc-no">เลขที่ <?= Html::encode($model->handover_no) ?></div>
<div class="row"><div class="label">ผู้รับมอบ</div><div><?= Html::encode($model->received_by_name) ?></div></div>
<div class="row"><div class="label">สถานที่</div><div><?= Html::encode($locationName) ?></div></div>
<div class="row"><div class="label">วันที่รับมอบ</div><div><?= Yii::$app->formatter->asDate($model->handover_date, 'php:d/m/Y') ?></div></div>
<div class="row"><div class="label">เลขมิเตอร์ไฟฟ้าเริ่มต้น</div><div><?= $model->electric_meter_value === null ? 'ไม่ระบุ' : Yii::$app->formatter->asDecimal($model->electric_meter_value, 2) ?></div></div>
<div class="row"><div class="label">เลขมิเตอร์น้ำเริ่มต้น</div><div><?= $model->water_meter_value === null ? 'ไม่ระบุ' : Yii::$app->formatter->asDecimal($model->water_meter_value, 2) ?></div></div>
<table><thead><tr><th>รายการอุปกรณ์</th><th>จำนวน</th><th>สภาพ</th><th>หมายเหตุ</th></tr></thead><tbody>
<?php if ($model->assetItems() === []): ?><tr><td colspan="4" style="text-align:center">ไม่มีรายการอุปกรณ์</td></tr><?php else: foreach ($model->assetItems() as $item): ?><tr><td><?= Html::encode($item['item_name']) ?></td><td><?= Html::encode(Yii::$app->formatter->asDecimal($item['quantity'], 2) . ' ' . $item['unit_name']) ?></td><td><?= Html::encode(AssetAssignment::conditionOptions()[$item['condition']] ?? $item['condition']) ?></td><td><?= Html::encode($item['note'] ?: '') ?></td></tr><?php endforeach; endif; ?>
</tbody></table>
<div><strong>สภาพห้องและหมายเหตุ</strong><br><?= nl2br(Html::encode($model->condition_note ?: 'ไม่มีหมายเหตุเพิ่มเติม')) ?></div>
<div class="signatures"><div class="signature"><div class="line"></div><div><?= Html::encode($model->handed_over_by_name) ?></div><div>ผู้ส่งมอบ</div></div><div class="signature"><div class="line"></div><div><?= Html::encode($model->received_by_name) ?></div><div>ผู้รับมอบ</div></div></div>
</body></html>
