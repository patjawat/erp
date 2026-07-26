<?php
use app\modules\housing\models\MonthlyAccount;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
$this->title='ค่าใช้จ่ายประจำเดือน';
$this->beginBlock('page-title');?><?=Html::encode($this->title)?><?php $this->endBlock();
$this->beginBlock('page-action');?><?=$this->render('../_menu',['active'=>'utility'])?><?php $this->endBlock();
$periodOptions=ArrayHelper::map($periods,'id','name');
?>
<style>
.expense-month{--e-bg:var(--bs-tertiary-bg);--e-soft:var(--bs-primary-bg-subtle);--e-border:var(--bs-border-color);--e-ink:var(--bs-emphasis-color);--e-muted:var(--bs-secondary-color);--e-primary:var(--bs-primary);color:var(--e-ink)}
.expense-month .panel{background:var(--bs-body-bg);border:1px solid var(--e-border);border-radius:.85rem}
.expense-month .summary{display:flex;gap:2rem;flex-wrap:wrap;padding:1rem 1.25rem;background:var(--e-bg);border-top:1px solid var(--e-border);border-bottom:1px solid var(--e-border)}
.expense-month .summary strong{display:block;font-size:1.1rem}.expense-month .status{display:inline-flex;padding:.25rem .6rem;border-radius:999px;font-size:.8rem;font-weight:600}
.status-pending,.payment-unpaid{background:var(--bs-warning-bg-subtle);color:var(--bs-warning-text-emphasis)}.status-saved,.payment-paid{background:var(--bs-success-bg-subtle);color:var(--bs-success-text-emphasis)}.payment-partial{background:var(--bs-primary-bg-subtle);color:var(--bs-primary)}
.expense-month .btn-primary{background:var(--e-primary);border-color:var(--e-primary)}
@media(max-width:767.98px){.expense-actions{width:100%}.expense-actions .btn{flex:1;min-height:44px}}
</style>
<div class="container-fluid py-3 expense-month">
<?php foreach(['success','error'] as $flash):if(Yii::$app->session->hasFlash($flash)):?><div class="alert alert-<?=$flash==='error'?'danger':'success'?>"><?=Html::encode(Yii::$app->session->getFlash($flash))?></div><?php endif;endforeach;?>
<div class="panel overflow-hidden">
<div class="p-3 d-flex flex-wrap justify-content-between align-items-start gap-3">
<div><h1 class="h5 mb-1">รายการบ้านพักและผู้รับผิดชอบค่าใช้จ่าย</h1><div class="small text-body-secondary">สร้างข้อมูลของเดือนก่อน แล้วกดลงค่าใช้จ่ายทีละคนหรือห้องว่าง</div></div>
<div class="d-flex flex-wrap gap-2 expense-actions"><?=Html::a('<i data-lucide="history"></i> ประวัติรับชำระ',['/housing/payment/index'],['class'=>'btn btn-outline-secondary'])?> <?=Html::a('ตั้งค่าประเภทค่าใช้จ่าย',['charge-types'],['class'=>'btn btn-outline-secondary'])?> <?=Html::a('<i data-lucide="calendar-plus"></i> สร้างเดือนใหม่',['create-period'],['class'=>'btn btn-outline-primary open-modal','data-size'=>'modal-lg'])?></div>
</div>
<?=Html::beginForm(['monthly'],'get',['class'=>'p-3 border-top border-bottom'])?><div class="row g-2"><div class="col-md-9"><?=Html::dropDownList('period_id',$period?->id,$periodOptions,['class'=>'form-select','prompt'=>'เลือกเดือน'])?></div><div class="col-md-3 d-grid"><?=Html::submitButton('แสดงรายการ',['class'=>'btn btn-primary'])?></div></div><?=Html::endForm()?>
<?php if(!$period):?><div class="text-center py-5"><div class="fw-semibold">ยังไม่มีรอบค่าใช้จ่าย</div><div class="small text-body-secondary mt-1">กด “สร้างเดือนใหม่” เพื่อเริ่มต้น</div></div>
<?php else:?>
<div class="px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
<div><strong><?=Html::encode($period->name)?></strong><span class="text-body-secondary ms-2"><?=$period->status==='closed'?'ปิดรอบแล้ว':'กำลังจัดทำ'?></span></div>
<div class="d-flex gap-2"><?php if(!$accounts&&$period->status==='open'):?><?=Html::a('<i data-lucide="list-plus"></i> ดึงข้อมูลบ้านพักทั้งหมด',['generate-month','period_id'=>$period->id],['class'=>'btn btn-primary','data-method'=>'post','data-confirm'=>'สร้างรายการจากข้อมูลบ้านพักและผู้พักอาศัยปัจจุบันหรือไม่?'])?><?php endif;?>
<?php if($accounts&&$period->status==='open'):?><?=Html::a('<i data-lucide="lock"></i> ปิดค่าใช้จ่ายประจำเดือน',['close-period','id'=>$period->id],['class'=>'btn btn-outline-danger','data-method'=>'post','data-confirm'=>'เมื่อปิดรอบแล้วจะไม่สามารถแก้ไขข้อมูลได้ ยืนยันหรือไม่?'])?><?php endif;?></div>
</div>
<?php if($accounts):?><div class="summary"><div><span class="small text-body-secondary">ค่าใช้จ่ายรวม</span><strong><?=Yii::$app->formatter->asDecimal($summary['total'],2)?> บาท</strong></div><div><span class="small text-body-secondary">ชำระแล้ว</span><strong><?=Yii::$app->formatter->asDecimal($summary['paid'],2)?> บาท</strong></div><div><span class="small text-body-secondary">คงเหลือ</span><strong><?=Yii::$app->formatter->asDecimal($summary['balance'],2)?> บาท</strong></div><div><span class="small text-body-secondary">ยังไม่ลงรายการ</span><strong><?=$summary['pending']?> รายการ</strong></div><div><span class="small text-body-secondary">ยังชำระไม่ครบ</span><strong><?=$summary['unpaid']?> รายการ</strong></div></div>
<div class="px-3 py-2 small border-bottom">ตรวจค่าไฟ: ยอดลงรายการ <strong><?=Yii::$app->formatter->asDecimal($summary['electric'],2)?></strong> บาท · ยอดบิลการไฟฟ้า <strong><?=Yii::$app->formatter->asDecimal($period->external_electric_total,2)?></strong> บาท · ส่วนต่าง <strong><?=Yii::$app->formatter->asDecimal((float)$period->external_electric_total-$summary['electric'],2)?></strong> บาท &nbsp; <?=Html::a('แก้ไขยอดบิล',['update-period','id'=>$period->id],['class'=>'open-modal','data-size'=>'modal-lg'])?></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>บ้านพัก/ห้อง</th><th>หมายเลขผู้ใช้ไฟฟ้า</th><th>เจ้าหน้าที่</th><th class="text-center">ผู้พัก &gt; 15 ปี</th><th class="text-end">ค่าใช้จ่าย</th><th class="text-end">ชำระแล้ว</th><th class="text-end">คงเหลือ</th><th>สถานะ</th><th></th></tr></thead><tbody>
<?php foreach($accounts as $account):?><tr>
<td><strong><?=Html::encode($account->building_name)?></strong><div class="small text-body-secondary"><?=Html::encode(implode(' / ',array_filter([$account->unit_name,$account->room_name]))?:'ทั้งหลัง')?></div></td>
<td><?=Html::encode($account->electric_account_no?:'ยังไม่ระบุ')?></td>
<td><strong><?=Html::encode($account->payer_name?:'ห้องว่าง')?></strong><div class="small text-body-secondary"><?=Html::encode($account->position_name?:($account->payer_name?'ไม่ระบุตำแหน่ง':'บันทึกค่าใช้จ่ายประจำห้อง'))?></div></td>
<td class="text-center"><?=$account->occupants_over_15?></td><td class="text-end"><?=Yii::$app->formatter->asDecimal($account->total_amount,2)?></td><td class="text-end"><?=Yii::$app->formatter->asDecimal($account->paid_amount,2)?></td><td class="text-end fw-semibold"><?=Yii::$app->formatter->asDecimal($account->balance_amount,2)?></td>
<td><span class="status status-<?=$account->status?>"><?=$account->status===MonthlyAccount::STATUS_SAVED?'บันทึกแล้ว':'ยังไม่ลงรายการ'?></span><div class="mt-1"><span class="status payment-<?=$account->payment_status?>"><?=$account->payment_status===MonthlyAccount::PAYMENT_PAID?'ชำระครบ':($account->payment_status===MonthlyAccount::PAYMENT_PARTIAL?'ชำระบางส่วน':'ยังไม่ชำระ')?></span></div></td>
<td class="text-end"><div class="d-flex justify-content-end gap-1">
<?php if($period->status==='closed'&&$account->status===MonthlyAccount::STATUS_SAVED&&$account->occupancy_id&&(float)$account->balance_amount>0):?><?=Html::a('<i data-lucide="banknote"></i> รับชำระ',['/housing/payment/receive','account_id'=>$account->id],['class'=>'btn btn-sm btn-success open-modal','data-size'=>'modal-lg'])?><?php endif;?>
<?php if($period->status==='open'):?><?=Html::a($account->status===MonthlyAccount::STATUS_SAVED?'แก้ไข':'ลงค่าใช้จ่าย',['edit-account','id'=>$account->id],['class'=>'btn btn-sm btn-outline-primary open-modal','data-size'=>'modal-xl'])?><?php else:?><?=Html::a('ดูรายละเอียด',['edit-account','id'=>$account->id],['class'=>'btn btn-sm btn-outline-secondary open-modal','data-size'=>'modal-xl'])?><?php endif;?>
</div></td>
</tr><?php endforeach;?></tbody></table></div>
<?php else:?><div class="text-center py-5"><div class="fw-semibold">พร้อมสร้างรายการของเดือนนี้</div><div class="small text-body-secondary mt-1">ระบบจะดึงบ้านพัก ห้อง เจ้าหน้าที่ และห้องว่างทั้งหมดในครั้งเดียว</div></div><?php endif;?>
<?php if($period->status==='closed'):?><div class="p-3 border-top small text-body-secondary"><i data-lucide="lock"></i> ปิดรอบวันที่ <?=Yii::$app->formatter->asDatetime($period->closed_at,'php:d/m/Y H:i')?> โดย <?=Html::encode($period->closed_by_name?:('รหัสผู้ใช้ '.$period->closed_by))?> ข้อมูลถูกล็อกแล้ว</div><?php endif;?>
<?php endif;?>
</div></div>
