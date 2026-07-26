<?php

use app\modules\housing\models\AssetAssignment;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'ตรวจรับคืน ' . $model->checkout_no;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'checkout']) ?><?php $this->endBlock();
$location = implode(' / ', array_filter([$occupancy->unit?->building?->name, $occupancy->unit?->floor?->name, $occupancy->unit?->name, $occupancy->room?->name]));
?>
<style>
.checkout-shell{max-width:1120px;margin:auto;--line:var(--bs-border-color-translucent);--surface-2:var(--bs-tertiary-bg);--ink-2:var(--bs-secondary-color)}.checkout-section{background:var(--bs-body-bg);border:1px solid var(--line);border-radius:10px;margin-bottom:1rem;overflow:hidden}.checkout-head{padding:.85rem 1.1rem;background:var(--surface-2);border-bottom:1px solid var(--line)}.checkout-body{padding:1.1rem}.checkout-head h2{font-size:.95rem;font-weight:600;margin:0}.checkout-head p{font-size:.78rem;color:var(--ink-2);margin:.2rem 0 0}.checkout-table th{font-size:.8rem;color:var(--ink-2);background:var(--surface-2)}
@media(max-width:767.98px){.checkout-table thead{display:none}.checkout-table,.checkout-table tbody,.checkout-table tr,.checkout-table td{display:block;width:100%}.checkout-table tr{padding:.75rem;border-bottom:1px solid var(--line)}.checkout-table td{border:0;padding:.25rem}.checkout-table td:before{content:attr(data-label);display:block;font-size:.75rem;color:var(--ink-2);font-weight:600}}
</style>
<div class="container-fluid py-3"><div class="checkout-shell">
<?= Html::a('กลับไปรายการส่งคืน', ['index'], ['class' => 'btn btn-outline-secondary mb-3']) ?>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
<section class="checkout-section"><div class="checkout-head"><h2>ข้อมูลการส่งคืน</h2><p>ตรวจสอบผู้พักและสถานที่ก่อนบันทึกผล</p></div><div class="checkout-body">
<div class="row g-3"><div class="col-md-4"><small class="text-body-secondary d-block">ผู้ส่งคืน</small><strong><?= Html::encode($model->resident_name) ?></strong></div><div class="col-md-8"><small class="text-body-secondary d-block">บ้านพัก/ห้องพัก</small><strong><?= Html::encode($location) ?></strong></div><div class="col-12"><small class="text-body-secondary d-block">เหตุผล</small><?= nl2br(Html::encode($model->move_out_reason)) ?></div></div>
</div></section>
<section class="checkout-section"><div class="checkout-head"><h2>วันตรวจและเลขมิเตอร์สุดท้าย</h2><p>ใช้เป็นหลักฐานปิดค่าใช้จ่ายของการเข้าพักรอบสุดท้าย</p></div><div class="checkout-body"><div class="row g-3">
<div class="col-md-3"><?= $form->field($model, 'checkout_date')->input('date') ?></div><div class="col-md-3"><?= $form->field($model, 'electric_meter_value')->input('number', ['step'=>'.01','min'=>0]) ?></div><div class="col-md-3"><?= $form->field($model, 'water_meter_value')->input('number', ['step'=>'.01','min'=>0]) ?></div><div class="col-md-3"><?= $form->field($model, 'outstanding_amount')->textInput(['readonly'=>true,'class'=>'form-control bg-body-tertiary']) ?></div>
</div></div></section>
<section class="checkout-section"><div class="checkout-head"><h2>อุปกรณ์และของใช้</h2><p>ระบุสภาพจริงและตรวจให้ครบทุกรายการ</p></div>
<?php if ($assetItems === []): ?><div class="checkout-body text-body-secondary">สถานที่นี้ไม่มีรายการอุปกรณ์ที่ต้องตรวจ</div><?php else: ?><div class="table-responsive"><table class="table checkout-table align-middle mb-0"><thead><tr><th>รายการ</th><th>จำนวน</th><th>สภาพเมื่อคืน</th><th>หมายเหตุ</th><th>ตรวจแล้ว</th></tr></thead><tbody>
<?php foreach ($assetItems as $item): $key=(string)$item['asset_id']; ?><tr><td data-label="รายการ"><strong><?= Html::encode($item['item_name']) ?></strong></td><td data-label="จำนวน"><?= Html::encode($item['quantity'].' '.$item['unit_name']) ?></td><td data-label="สภาพ"><?= Html::dropDownList("asset[$key][condition]", $item['condition'], AssetAssignment::conditionOptions(), ['class'=>'form-select']) ?></td><td data-label="หมายเหตุ"><?= Html::textInput("asset[$key][note]", $item['note'], ['class'=>'form-control']) ?></td><td data-label="ตรวจแล้ว"><?= Html::checkbox("asset[$key][acknowledged]", (bool)$item['acknowledged'], ['value'=>1,'class'=>'form-check-input']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<section class="checkout-section"><div class="checkout-head"><h2>สภาพและหลักฐาน</h2><p>เพิ่มรูปได้สูงสุด 10 ภาพ</p></div><div class="checkout-body">
<?= $form->field($model, 'condition_note')->textarea(['rows'=>4]) ?><?= $form->field($model, 'condition_photos[]')->fileInput(['multiple'=>true,'accept'=>'image/jpeg,image/png,image/webp']) ?>
</div></section>
<div class="d-flex justify-content-end gap-2"><?= Html::a('ยกเลิก', ['view','id'=>$model->id], ['class'=>'btn btn-outline-secondary']) ?><?= Html::submitButton('บันทึกผลตรวจและส่งให้ผู้พักลงนาม', ['class'=>'btn btn-primary']) ?></div>
<?php ActiveForm::end(); ?></div></div>
