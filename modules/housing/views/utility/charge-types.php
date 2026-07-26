<?php
use app\modules\housing\models\ChargeType;
use yii\helpers\Html;
$this->title='ทะเบียนประเภทค่าใช้จ่าย';
$this->beginBlock('page-title');?><?=Html::encode($this->title)?><?php $this->endBlock();
$this->beginBlock('page-action');?><?=$this->render('../_menu',['active'=>'utility'])?><?php $this->endBlock();?>
<style>
.charge-types-page{--u-bg:var(--bs-tertiary-bg);--u-soft:var(--bs-primary-bg-subtle);--u-border:var(--bs-border-color);--u-ink:var(--bs-emphasis-color);--u-primary:var(--bs-primary);color:var(--u-ink)}
.charge-types-page .soft-panel{background:var(--bs-body-bg);border:1px solid var(--u-border);border-radius:.85rem}
.charge-types-page .method-pill{display:inline-flex;padding:.25rem .6rem;border-radius:999px;background:var(--u-soft);color:var(--bs-primary);font-size:.8rem;font-weight:600}
.charge-types-page .btn-primary{background:var(--u-primary);border-color:var(--u-primary)}
</style>
<div class="container-fluid py-3 charge-types-page"><div class="soft-panel overflow-hidden">
<div class="p-3 d-flex flex-wrap justify-content-between align-items-start gap-3">
<div><h1 class="h5 mb-1">รายการค่าใช้จ่ายของโรงพยาบาล</h1><div class="small text-body-secondary">กำหนดชื่อ หมวด วิธีคำนวณ และหน่วยนับให้ตรงกับระเบียบของแต่ละแห่ง</div></div>
<div class="d-flex flex-wrap gap-2"><?=Html::a('ลงค่าใช้จ่ายรายเดือน',['monthly'],['class'=>'btn btn-outline-primary'])?> <?=Html::a('<i data-lucide="plus"></i> เพิ่มประเภทค่าใช้จ่าย',['create-charge-type'],['class'=>'btn btn-primary open-modal','data-size'=>'modal-lg'])?></div>
</div>
<?php if($models):?><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>รหัส/ชื่อรายการ</th><th>หมวด</th><th>วิธีคำนวณ</th><th>หน่วย</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead><tbody>
<?php foreach($models as $model):?><tr>
<td><strong><?=Html::encode($model->name)?></strong><div class="small text-body-secondary"><?=Html::encode($model->code)?></div></td>
<td><?=Html::encode(ChargeType::categoryOptions()[$model->category]??$model->category)?></td>
<td><span class="method-pill"><?=Html::encode(ChargeType::methodOptions()[$model->calculation_method]??$model->calculation_method)?></span></td>
<td><?=Html::encode($model->unit_name?:'บาท')?></td>
<td><span class="badge <?=$model->status==='active'?'bg-success-subtle text-success-emphasis':'bg-secondary-subtle text-secondary'?>"><?=$model->status==='active'?'เปิดใช้งาน':'ปิดใช้งาน'?></span></td>
<td class="text-end"><?=Html::a('แก้ไข',['update-charge-type','id'=>$model->id],['class'=>'btn btn-sm btn-outline-secondary open-modal','data-size'=>'modal-lg'])?></td>
</tr><?php endforeach;?></tbody></table></div>
<?php else:?><div class="text-center py-5"><div class="fw-semibold">ยังไม่มีประเภทค่าใช้จ่าย</div><div class="small text-body-secondary mt-1">เพิ่มรายการแรกเพื่อเริ่มลงค่าใช้จ่ายรายเดือน</div></div><?php endif;?>
</div></div>
