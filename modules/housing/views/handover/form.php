<?php

use app\modules\housing\models\AssetAssignment;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'จัดทำเอกสารรับมอบ ' . $model->handover_no;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'request']) ?><?php $this->endBlock();
$locationName = implode(' / ', array_filter([
    $occupancy->unit?->building?->name,
    $occupancy->unit?->floor?->name,
    $occupancy->unit?->name,
    $occupancy->room?->name,
]));
?>
<style>
.handover-page{--ho-ink:var(--bs-emphasis-color);--ho-muted:var(--bs-secondary-color);--ho-line:var(--bs-border-color-translucent);--ho-surface:var(--bs-body-bg);--ho-surface-2:var(--bs-tertiary-bg);--ho-primary:var(--bs-primary);color:var(--ho-ink)}
.handover-shell{max-width:1120px;margin:0 auto}.handover-section{background:var(--ho-surface);border:1px solid var(--ho-line);border-radius:10px;box-shadow:0 1px 2px var(--bs-border-color-translucent);margin-bottom:1rem}
.handover-section__head{padding:.9rem 1.1rem;border-bottom:1px solid var(--ho-line);background:var(--ho-surface-2)}.handover-section__body{padding:1.1rem}
.handover-title{font-size:.98rem;font-weight:600;margin:0}.handover-caption{font-size:.8rem;color:var(--ho-muted);margin-top:.2rem}
.handover-context{display:flex;flex-wrap:wrap;gap:1.25rem}.handover-context>div{min-width:180px}.handover-context small{display:block;color:var(--ho-muted);margin-bottom:.15rem}
.asset-table th{background:var(--ho-surface-2);font-size:.8rem;color:var(--ho-muted);font-weight:600}.asset-table td,.asset-table th{padding:.65rem .8rem;vertical-align:middle}
.asset-check{width:1.1rem;height:1.1rem}.form-control,.form-select{min-height:42px;border-radius:8px}.form-control:focus,.form-select:focus{border-color:var(--ho-primary);box-shadow:0 0 0 3px var(--bs-primary-bg-subtle)}
@media(max-width:767.98px){.handover-section__body{padding:.9rem}.asset-table thead{display:none}.asset-table,.asset-table tbody,.asset-table tr,.asset-table td{display:block;width:100%}.asset-table tr{padding:.8rem;border-bottom:1px solid var(--ho-line)}.asset-table td{border:0;padding:.25rem 0}.asset-table td[data-label]::before{content:attr(data-label);display:block;font-size:.75rem;color:var(--ho-muted);font-weight:600;margin-bottom:.15rem}}
</style>
<div class="container-fluid py-3 handover-page">
<div class="handover-shell">
<?= Html::a('<i class="bi bi-arrow-left"></i> กลับไปคำขอ', ['/housing/request/view', 'id' => $request->id], ['class' => 'btn btn-outline-secondary mb-3']) ?>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
<section class="handover-section">
    <div class="handover-section__head"><h2 class="handover-title">ข้อมูลการจัดสรร</h2><div class="handover-caption">ตรวจชื่อผู้รับมอบและสถานที่ก่อนบันทึก</div></div>
    <div class="handover-section__body">
        <div class="handover-context">
            <div><small>เลขคำขอ</small><strong><?= Html::encode($request->request_no) ?></strong></div>
            <div><small>ผู้รับมอบ</small><strong><?= Html::encode($model->received_by_name) ?></strong></div>
            <div class="flex-grow-1"><small>บ้านพัก/ห้องพัก</small><strong><?= Html::encode($locationName) ?></strong></div>
        </div>
    </div>
</section>
<section class="handover-section">
    <div class="handover-section__head"><h2 class="handover-title">วันรับมอบและค่ามิเตอร์เริ่มต้น</h2><div class="handover-caption">ใช้เป็นค่าเริ่มต้นสำหรับตรวจสอบเมื่อส่งคืนห้อง</div></div>
    <div class="handover-section__body">
        <div class="row g-3">
            <div class="col-md-4"><?= $form->field($model, 'handover_date')->input('date') ?></div>
            <div class="col-md-4"><?= $form->field($model, 'electric_meter_value')->input('number', ['step' => '0.01', 'min' => 0, 'placeholder' => 'ไม่ระบุได้']) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'water_meter_value')->input('number', ['step' => '0.01', 'min' => 0, 'placeholder' => 'ไม่ระบุได้']) ?></div>
        </div>
    </div>
</section>
<section class="handover-section">
    <div class="handover-section__head"><h2 class="handover-title">ตรวจรับอุปกรณ์และของใช้</h2><div class="handover-caption">ตรวจสภาพและทำเครื่องหมายรับทราบทุกรายการก่อนลงนาม</div></div>
    <?php if ($assetItems === []): ?>
        <div class="handover-section__body text-center text-body-secondary">สถานที่นี้ยังไม่มีรายการอุปกรณ์ สามารถดำเนินการบันทึกสภาพห้องต่อได้</div>
    <?php else: ?>
        <div class="table-responsive"><table class="table asset-table mb-0"><thead><tr><th>รายการ</th><th style="width:110px">จำนวน</th><th style="width:190px">สภาพเมื่อรับมอบ</th><th>หมายเหตุ</th><th class="text-center" style="width:90px">ตรวจแล้ว</th></tr></thead><tbody>
        <?php foreach ($assetItems as $item): $key = (string)$item['asset_id']; ?>
            <tr>
                <td data-label="รายการ"><strong><?= Html::encode($item['item_name']) ?></strong></td>
                <td data-label="จำนวน"><?= Html::encode(Yii::$app->formatter->asDecimal($item['quantity'], 2) . ' ' . $item['unit_name']) ?></td>
                <td data-label="สภาพ"><?= Html::dropDownList("asset[$key][condition]", $item['condition'], AssetAssignment::conditionOptions(), ['class' => 'form-select']) ?></td>
                <td data-label="หมายเหตุ"><?= Html::textInput("asset[$key][note]", $item['note'], ['class' => 'form-control', 'placeholder' => 'ถ้ามี']) ?></td>
                <td data-label="ตรวจแล้ว" class="text-center"><?= Html::checkbox("asset[$key][acknowledged]", (bool)$item['acknowledged'], ['value' => 1, 'class' => 'form-check-input asset-check', 'aria-label' => 'ตรวจรับ ' . $item['item_name']]) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<section class="handover-section">
    <div class="handover-section__head"><h2 class="handover-title">สภาพห้องและหลักฐาน</h2><div class="handover-caption">เพิ่มภาพสภาพห้องในวันรับมอบได้สูงสุด 10 ภาพ</div></div>
    <div class="handover-section__body">
        <?= $form->field($model, 'condition_note')->textarea(['rows' => 4, 'placeholder' => 'บันทึกร่องรอย ความชำรุด หรือข้อตกลงเพิ่มเติม']) ?>
        <?= $form->field($model, 'condition_photos[]')->fileInput(['multiple' => true, 'accept' => 'image/jpeg,image/png,image/webp']) ?>
    </div>
</section>
<section class="handover-section">
    <div class="handover-section__head"><h2 class="handover-title">ผู้ส่งมอบและผู้รับมอบ</h2><div class="handover-caption">รายชื่อจะปรากฏในเอกสารสำหรับลงนามยืนยัน</div></div>
    <div class="handover-section__body"><div class="row g-3">
        <div class="col-md-6"><?= $form->field($model, 'handed_over_by_name')->textInput() ?></div>
        <div class="col-md-6"><?= $form->field($model, 'received_by_name')->textInput(['readonly' => true]) ?></div>
    </div></div>
</section>
<div class="d-flex flex-wrap justify-content-end gap-2">
    <?= Html::a('ยกเลิก', ['/housing/request/view', 'id' => $request->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('บันทึกร่างเอกสารรับมอบ', ['class' => 'btn btn-primary px-4']) ?>
</div>
<?php ActiveForm::end(); ?>
</div></div>
